<template>
  <div class="calendario-reservas">
    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>

    <!-- Calendário -->
    <div v-else class="card card-glass">
      <div class="card-body">
        <!-- ✅ FILTROS HIERÁRQUICOS -->
        <div class="row g-3 mb-4">
          <!-- Linha 1: Hierarquia -->
          <div class="col-md-2">
            <label class="form-label text-white">Instituição</label>
            <select 
              v-model="filtros.instituicao_id" 
              @change="onInstituicaoChange" 
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todas</option>
              <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                {{ inst.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white">Campus</label>
            <select 
              v-model="filtros.campus_id" 
              @change="onCampusChange" 
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.instituicao_id"
            >
              <option value="">Todos</option>
              <option v-for="campus in campi" :key="campus.id" :value="campus.id">
                {{ campus.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white">Prédio</label>
            <select 
              v-model="filtros.predio_id" 
              @change="onPredioChange" 
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.campus_id"
            >
              <option value="">Todos</option>
              <option v-for="predio in predios" :key="predio.id" :value="predio.id">
                {{ predio.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white">Bloco</label>
            <select 
              v-model="filtros.bloco_id" 
              @change="onBlocoChange" 
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.predio_id"
            >
              <option value="">Todos</option>
              <option v-for="bloco in blocos" :key="bloco.id" :value="bloco.id">
                {{ bloco.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white">Andar</label>
            <select 
              v-model="filtros.andar_id" 
              @change="onAndarChange" 
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.bloco_id"
            >
              <option value="">Todos</option>
              <option v-for="andar in andares" :key="andar.id" :value="andar.id">
                {{ andar.nome || `Andar ${andar.numero}` }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label text-white">Espaço</label>
            <select 
              v-model="filtros.espaco_id" 
              @change="refetchEvents" 
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.andar_id && !filtros.campus_id"
            >
              <option value="">Todos</option>
              <option v-for="espaco in espacos" :key="espaco.id" :value="espaco.id">
                {{ espaco.codigo }} - {{ espaco.nome }}
              </option>
            </select>
          </div>

          <!-- Linha 2: Visualização e Navegação -->
          <div class="col-md-3">
            <label class="form-label text-white">Visualização</label>
            <select v-model="visualizacao" @change="mudarVisualizacao" class="form-select bg-transparent text-white border-secondary">
              <option value="dayGridMonth">Mês</option>
              <option value="timeGridWeek">Semana</option>
              <option value="timeGridDay">Dia</option>
              <option value="listWeek">Lista</option>
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button @click="voltar" class="btn btn-outline-light me-2">
              <i class="bi bi-arrow-left"></i>
            </button>
            <button @click="hoje" class="btn btn-outline-light me-2">Hoje</button>
            <button @click="avancar" class="btn btn-outline-light">
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- FullCalendar -->
        <FullCalendar ref="calendario" :options="calendarOptions" />
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <ReservaDetalhes
      v-if="mostrarDetalhes"
      :reserva="reservaSelecionada"
      @close="mostrarDetalhes = false"
      @refresh="refetchEvents"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';
import axios from 'axios';
import ReservaDetalhes from './ReservaDetalhes.vue';

const loading = ref(true);
const calendario = ref(null);
const visualizacao = ref('dayGridMonth');
const mostrarDetalhes = ref(false);
const reservaSelecionada = ref(null);

// ✅ REFS PARA HIERARQUIA
const instituicoes = ref([]);
const campi = ref([]);
const predios = ref([]);
const blocos = ref([]);
const andares = ref([]);
const espacos = ref([]);

const filtros = reactive({
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  bloco_id: '',
  andar_id: '',
  espaco_id: ''
});

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
  initialView: visualizacao.value,
  locale: ptBrLocale,
  headerToolbar: false,
  height: 'auto',
  events: carregarEventos,
  eventClick: handleEventClick,
  dateClick: handleDateClick,
  slotMinTime: '07:00:00',
  slotMaxTime: '23:00:00',
  allDaySlot: false,
  nowIndicator: true,
  eventTimeFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  },
  buttonText: {
    today: 'Hoje',
    month: 'Mês',
    week: 'Semana',
    day: 'Dia',
    list: 'Lista'
  }
}));

onMounted(async () => {
  await Promise.all([
    carregarInstituicoes(),
    carregarCampi()
  ]);
  loading.value = false;
});

// ✅ FUNÇÕES DE CARREGAMENTO HIERÁRQUICO
const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
};

const carregarCampi = async () => {
  try {
    const { data } = await axios.get('/api/v1/campi?all=true');
    campi.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarCampiFiltrados = async () => {
  if (!filtros.instituicao_id) {
    campi.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/instituicoes/${filtros.instituicao_id}/campi`);
    campi.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarPrediosFiltrados = async () => {
  if (!filtros.campus_id) {
    predios.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/campi/${filtros.campus_id}/predios`);
    predios.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
  }
};

const carregarBlocosFiltrados = async () => {
  if (!filtros.predio_id) {
    blocos.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/predios/${filtros.predio_id}/blocos`);
    blocos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
  }
};

const carregarAndaresFiltrados = async () => {
  if (!filtros.bloco_id) {
    andares.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/blocos/${filtros.bloco_id}/andares`);
    andares.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
  }
};

const carregarEspacosFiltrados = async () => {
  try {
    const params = new URLSearchParams();
    if (filtros.andar_id) params.append('andar_id', filtros.andar_id);
    if (filtros.campus_id) params.append('campus_id', filtros.campus_id);
    params.append('status', 'Disponível');
    params.append('permite_reserva', 'true');
    
    const { data } = await axios.get(`/api/v1/espacos-fisicos?${params.toString()}&all=true`);
    espacos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar espaços:', error);
  }
};

// ✅ WATCHERS PARA FILTROS EM CASCATA
const onInstituicaoChange = async () => {
  filtros.campus_id = '';
  filtros.predio_id = '';
  filtros.bloco_id = '';
  filtros.andar_id = '';
  filtros.espaco_id = '';
  
  predios.value = [];
  blocos.value = [];
  andares.value = [];
  espacos.value = [];
  
  if (filtros.instituicao_id) {
    await carregarCampiFiltrados();
  } else {
    await carregarCampi();
  }
  
  refetchEvents();
};

const onCampusChange = async () => {
  filtros.predio_id = '';
  filtros.bloco_id = '';
  filtros.andar_id = '';
  filtros.espaco_id = '';
  
  blocos.value = [];
  andares.value = [];
  espacos.value = [];
  
  if (filtros.campus_id) {
    await Promise.all([
      carregarPrediosFiltrados(),
      carregarEspacosFiltrados()
    ]);
  } else {
    predios.value = [];
    espacos.value = [];
  }
  
  refetchEvents();
};

const onPredioChange = async () => {
  filtros.bloco_id = '';
  filtros.andar_id = '';
  filtros.espaco_id = '';
  
  andares.value = [];
  espacos.value = [];
  
  if (filtros.predio_id) {
    await carregarBlocosFiltrados();
  } else {
    blocos.value = [];
  }
  
  refetchEvents();
};

const onBlocoChange = async () => {
  filtros.andar_id = '';
  filtros.espaco_id = '';
  
  espacos.value = [];
  
  if (filtros.bloco_id) {
    await carregarAndaresFiltrados();
  } else {
    andares.value = [];
  }
  
  refetchEvents();
};

const onAndarChange = async () => {
  filtros.espaco_id = '';
  
  if (filtros.andar_id) {
    await carregarEspacosFiltrados();
  } else {
    espacos.value = [];
  }
  
  refetchEvents();
};

// ✅ CARREGAR EVENTOS DO CALENDÁRIO
const carregarEventos = async (fetchInfo, successCallback, failureCallback) => {
  try {
    const params = new URLSearchParams();
    
    // Datas do calendário
    params.append('start', fetchInfo.startStr.split('T')[0]);
    params.append('end', fetchInfo.endStr.split('T')[0]);
    
    // ✅ FILTROS HIERÁRQUICOS
    if (filtros.instituicao_id) params.append('instituicao_id', filtros.instituicao_id);
    if (filtros.campus_id) params.append('campus_id', filtros.campus_id);
    if (filtros.predio_id) params.append('predio_id', filtros.predio_id);
    if (filtros.bloco_id) params.append('bloco_id', filtros.bloco_id);
    if (filtros.andar_id) params.append('andar_id', filtros.andar_id);
    if (filtros.espaco_id) params.append('espaco_id', filtros.espaco_id);

    console.log('🔍 Buscando eventos:', params.toString());

    const { data } = await axios.get(`/api/v1/reservas-espacos/calendario?${params.toString()}`);
    
    console.log('✅ Eventos recebidos:', data);
    
    successCallback(data);
  } catch (error) {
    console.error('❌ Erro ao carregar eventos:', error);
    failureCallback(error);
  }
};

const refetchEvents = () => {
  const calendarApi = calendario.value?.getApi();
  if (calendarApi) {
    console.log('🔄 Recarregando eventos do calendário...');
    calendarApi.refetchEvents();
  }
};

const handleEventClick = async (info) => {
  try {
    const { data } = await axios.get(`/api/v1/reservas-espacos/${info.event.id}`);
    reservaSelecionada.value = data;
    mostrarDetalhes.value = true;
  } catch (error) {
    console.error('Erro ao carregar detalhes:', error);
  }
};

const handleDateClick = (info) => {
  console.log('Data clicada:', info.dateStr);
};

const mudarVisualizacao = () => {
  const calendarApi = calendario.value?.getApi();
  if (calendarApi) {
    calendarApi.changeView(visualizacao.value);
  }
};

const hoje = () => {
  const calendarApi = calendario.value?.getApi();
  if (calendarApi) {
    calendarApi.today();
  }
};

const voltar = () => {
  const calendarApi = calendario.value?.getApi();
  if (calendarApi) {
    calendarApi.prev();
  }
};

const avancar = () => {
  const calendarApi = calendario.value?.getApi();
  if (calendarApi) {
    calendarApi.next();
  }
};
</script>

<style scoped>
/* FullCalendar Dark Theme */
:deep(.fc) {
  color: white;
}

:deep(.fc-theme-standard td),
:deep(.fc-theme-standard th) {
  border-color: rgba(255, 255, 255, 0.1);
}

:deep(.fc-col-header-cell) {
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-weight: 600;
}

:deep(.fc-daygrid-day) {
  background: rgba(255, 255, 255, 0.02);
}

:deep(.fc-daygrid-day:hover) {
  background: rgba(255, 255, 255, 0.05);
}

:deep(.fc-day-today) {
  background: rgba(96, 165, 250, 0.1) !important;
}

:deep(.fc-event) {
  cursor: pointer;
  border-radius: 4px;
  padding: 2px 4px;
}

:deep(.fc-event:hover) {
  opacity: 0.85;
}

:deep(.fc-timegrid-slot) {
  height: 3em;
}

:deep(.fc-button) {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  color: white;
}

:deep(.fc-button:hover) {
  background: rgba(255, 255, 255, 0.2);
}

:deep(.fc-button-active) {
  background: rgba(96, 165, 250, 0.3) !important;
}
</style>
