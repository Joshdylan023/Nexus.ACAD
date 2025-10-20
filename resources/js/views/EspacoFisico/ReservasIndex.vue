<template>
  <div class="reservas-page">
    <!-- ✅ SKELETON LOADING PROFISSIONAL -->
    <div v-if="loading">
      <!-- Header Skeleton -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <div class="skeleton skeleton-title mb-2"></div>
              <div class="skeleton skeleton-text" style="width: 60%;"></div>
            </div>
            <div class="col-md-4 text-end">
              <div class="skeleton skeleton-button ms-auto"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Skeleton -->
      <div class="mb-4">
        <div class="d-flex gap-3">
          <div class="skeleton skeleton-tab" v-for="i in 4" :key="i"></div>
        </div>
      </div>

      <!-- Filtros Skeleton -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3" v-for="i in 4" :key="i">
              <div class="skeleton skeleton-text mb-2" style="width: 80px;"></div>
              <div class="skeleton skeleton-input"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Estatísticas Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-2" v-for="i in 6" :key="`stat-${i}`">
          <div class="card card-glass">
            <div class="card-body text-center">
              <div class="skeleton skeleton-stat-number mx-auto mb-2"></div>
              <div class="skeleton skeleton-stat-label mx-auto"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cards Skeleton -->
      <div class="row g-3">
        <div class="col-md-6" v-for="i in 4" :key="`card-${i}`">
          <div class="card card-glass">
            <div class="card-body">
              <div class="d-flex justify-content-between mb-3">
                <div class="flex-grow-1">
                  <div class="skeleton skeleton-title mb-2"></div>
                  <div class="skeleton skeleton-badge"></div>
                </div>
                <div class="skeleton skeleton-id"></div>
              </div>
              <div class="mb-3">
                <div class="skeleton skeleton-text mb-2" v-for="j in 4" :key="j"></div>
              </div>
              <div class="d-flex gap-2">
                <div class="skeleton skeleton-button-sm flex-fill"></div>
                <div class="skeleton skeleton-button-sm" style="width: 50px;"></div>
                <div class="skeleton skeleton-button-sm" style="width: 50px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ CONTEÚDO REAL -->
    <div v-else>
      <!-- Header -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h3 class="text-white mb-2">
                <i class="bi bi-calendar-check me-2"></i>
                Reservas de Espaços
              </h3>
              <p class="text-white-50 mb-0">
                Gerencie e acompanhe suas reservas de espaços físicos
              </p>
            </div>
            <div class="col-md-4 text-end">
              <!-- ✅ BOTÃO EXPORTAR PDF -->
              <button 
                @click="exportarPdf" 
                class="btn btn-outline-danger me-2"
                :disabled="loadingPdf"
              >
                <i class="bi me-2" :class="loadingPdf ? 'bi-hourglass-split' : 'bi-file-pdf'"></i>
                {{ loadingPdf ? 'Gerando PDF...' : 'Exportar PDF' }}
              </button>

              <button 
                @click="abrirFormulario()" 
                class="btn btn-primary"
                v-if="$can('criar-reservas')"
              >
                <i class="bi bi-plus-circle me-2"></i>
                Nova Reserva
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <ul class="nav nav-tabs nav-tabs-glass mb-4">
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: filtros.tab === 'todas' }"
            @click="mudarTab('todas')"
          >
            <i class="bi bi-list-ul me-1"></i>
            Todas
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: filtros.tab === 'minhas' }"
            @click="mudarTab('minhas')"
          >
            <i class="bi bi-person-check me-1"></i>
            Minhas Reservas
          </a>
        </li>
        <li class="nav-item" v-if="$can('aprovar-reservas')">
          <a 
            class="nav-link" 
            :class="{ active: filtros.tab === 'pendentes' }"
            @click="mudarTab('pendentes')"
          >
            <i class="bi bi-clock-history me-1"></i>
            Pendentes de Aprovação
            <span v-if="estatisticas.pendentes > 0" class="badge bg-warning ms-1">
              {{ estatisticas.pendentes }}
            </span>
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: filtros.tab === 'calendario' }"
            @click="mudarTab('calendario')"
          >
            <i class="bi bi-calendar3 me-1"></i>
            Calendário
          </a>
        </li>
      </ul>

      <!-- Filtros -->
      <div class="card card-glass mb-4" v-if="filtros.tab !== 'calendario'">
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label text-white">Status</label>
              <select v-model="filtros.status" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todos</option>
                <option value="Pendente">Pendente</option>
                <option value="Aprovada">Aprovada</option>
                <option value="Rejeitada">Rejeitada</option>
                <option value="Cancelada">Cancelada</option>
                <option value="Concluída">Concluída</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label text-white">Data Início</label>
              <input 
                v-model="filtros.data_inicio" 
                @change="filtrar"
                type="date" 
                class="form-control bg-transparent text-white border-secondary"
              />
            </div>

            <div class="col-md-3">
              <label class="form-label text-white">Data Fim</label>
              <input 
                v-model="filtros.data_fim" 
                @change="filtrar"
                type="date" 
                class="form-control bg-transparent text-white border-secondary"
              />
            </div>

            <div class="col-md-3">
              <label class="form-label text-white">Buscar</label>
              <input 
                v-model="filtros.search" 
                @input="filtrar"
                type="text" 
                class="form-control bg-transparent text-white border-secondary"
                placeholder="Motivo, espaço..."
              />
            </div>
          </div>

          <!-- ⭐ FILTROS AVANÇADOS -->
          <div class="row g-3">
            <div class="col-12">
              <button 
                @click="mostrarFiltrosAvancados = !mostrarFiltrosAvancados"
                class="btn btn-sm btn-outline-secondary"
              >
                <i class="bi me-1" :class="mostrarFiltrosAvancados ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                {{ mostrarFiltrosAvancados ? 'Ocultar' : 'Mostrar' }} Filtros Avançados
              </button>
            </div>

            <template v-if="mostrarFiltrosAvancados">
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
                  v-model="filtros.espaco_fisico_id" 
                  @change="filtrar" 
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!filtros.andar_id && !filtros.campus_id"
                >
                  <option value="">Todos</option>
                  <option v-for="espaco in espacos" :key="espaco.id" :value="espaco.id">
                    {{ espaco.codigo }} - {{ espaco.nome }}
                  </option>
                </select>
              </div>

              <!-- Linha 2: Tipo e Finalidade -->
              <div class="col-md-3">
                <label class="form-label text-white">Tipo de Espaço</label>
                <select 
                  v-model="filtros.tipo_espaco" 
                  @change="filtrar" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="">Todos</option>
                  <option value="Sala de Aula">Sala de Aula</option>
                  <option value="Laboratório">Laboratório</option>
                  <option value="Auditório">Auditório</option>
                  <option value="Biblioteca">Biblioteca</option>
                  <option value="Sala de Reunião">Sala de Reunião</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Finalidade</label>
                <select 
                  v-model="filtros.finalidade" 
                  @change="filtrar" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="">Todas</option>
                  <option value="Aula">Aula</option>
                  <option value="Reunião">Reunião</option>
                  <option value="Evento">Evento</option>
                  <option value="Palestra">Palestra</option>
                  <option value="Workshop">Workshop</option>
                  <option value="Treinamento">Treinamento</option>
                </select>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Estatísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-white mb-1">{{ estatisticas.total }}</h5>
              <small class="text-white-50">Total</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-warning mb-1">{{ estatisticas.pendentes }}</h5>
              <small class="text-white-50">Pendentes</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-success mb-1">{{ estatisticas.aprovadas }}</h5>
              <small class="text-white-50">Aprovadas</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-danger mb-1">{{ estatisticas.rejeitadas }}</h5>
              <small class="text-white-50">Rejeitadas</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-secondary mb-1">{{ estatisticas.canceladas }}</h5>
              <small class="text-white-50">Canceladas</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-info mb-1">{{ estatisticas.proximas }}</h5>
              <small class="text-white-50">Próximas</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Reservas -->
      <div v-if="filtros.tab !== 'calendario'" class="row g-3">
        <div class="col-md-6" v-for="reserva in reservas" :key="reserva.id">
          <div class="card card-glass reserva-card">
            <div class="card-body">
              <!-- Header -->
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="text-white mb-1">{{ reserva.motivo }}</h5>
                  <span :class="getStatusBadge(reserva.status)">
                    {{ reserva.status }}
                  </span>
                </div>
                <div class="text-end">
                  <small class="text-white-50 d-block">#{{ reserva.id }}</small>
                </div>
              </div>

              <!-- Informações -->
              <div class="reserva-info mb-3">
                <p class="text-white-50 small mb-2">
                  <i class="bi bi-door-open me-2"></i>
                  <strong>Espaço:</strong> {{ reserva.espaco_fisico?.nome }}
                </p>
                <p class="text-white-50 small mb-2">
                  <i class="bi bi-calendar-event me-2"></i>
                  <strong>Período:</strong> {{ formatDate(reserva.data_inicio) }} a {{ formatDate(reserva.data_fim) }}
                </p>
                <p class="text-white-50 small mb-2">
                  <i class="bi bi-clock me-2"></i>
                  <strong>Horário:</strong> {{ reserva.hora_inicio?.substring(0, 5) }} às {{ reserva.hora_fim?.substring(0, 5) }}
                </p>
                <p class="text-white-50 small mb-0">
                  <i class="bi bi-person me-2"></i>
                  <strong>Solicitante:</strong> {{ reserva.solicitante?.name }}
                </p>
              </div>

              <!-- Ações -->
              <div class="d-flex gap-2">
                <button 
                  @click="verDetalhes(reserva)" 
                  class="btn btn-sm btn-outline-primary flex-fill"
                >
                  <i class="bi bi-eye me-1"></i>
                  Ver
                </button>

                <!-- Ações de Aprovação -->
                <template v-if="reserva.status === 'Pendente' && $can('aprovar-reservas')">
                  <button 
                    @click="aprovar(reserva.id)" 
                    class="btn btn-sm btn-outline-success"
                  >
                    <i class="bi bi-check-circle"></i>
                  </button>
                  <button 
                    @click="rejeitar(reserva.id)" 
                    class="btn btn-sm btn-outline-danger"
                  >
                    <i class="bi bi-x-circle"></i>
                  </button>
                </template>

                <!-- Ações do Solicitante -->
                <template v-if="reserva.solicitante_id === user.id">
                  <button 
                    v-if="['Pendente', 'Aprovada'].includes(reserva.status)"
                    @click="cancelarReserva(reserva.id)" 
                    class="btn btn-sm btn-outline-warning"
                  >
                    <i class="bi bi-slash-circle"></i>
                  </button>
                  <button 
                    v-if="reserva.status === 'Pendente'"
                    @click="abrirFormulario(reserva)" 
                    class="btn btn-sm btn-outline-info"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ CALENDÁRIO INTEGRADO -->
      <CalendarioReservas v-if="filtros.tab === 'calendario'" />

      <!-- Empty State -->
      <div v-if="reservas.length === 0 && filtros.tab !== 'calendario'" class="text-center py-5">
        <i class="bi bi-inbox display-1 text-white-50"></i>
        <p class="text-white-50 mt-3">Nenhuma reserva encontrada</p>
      </div>
    </div>

    <!-- Modal de Formulário -->
    <ReservaForm 
      v-if="mostrarFormulario"
      :reserva="reservaSelecionada"
      @close="fecharFormulario"
      @success="reservaSalva"
    />

    <!-- Modal de Detalhes -->
    <ReservaDetalhes
      v-if="mostrarDetalhes"
      :reserva="reservaSelecionada"
      @close="mostrarDetalhes = false"
      @refresh="carregarReservas"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import ReservaForm from './ReservaForm.vue';
import ReservaDetalhes from './ReservaDetalhes.vue';
import CalendarioReservas from './CalendarioReservas.vue';

const loading = ref(true);
const reservas = ref([]);
const estatisticas = ref({
  total: 0,
  pendentes: 0,
  aprovadas: 0,
  rejeitadas: 0,
  canceladas: 0,
  proximas: 0
});
const mostrarFormulario = ref(false);
const mostrarDetalhes = ref(false);
const reservaSelecionada = ref(null);
const mostrarFiltrosAvancados = ref(false);

const loadingPdf = ref(false);

// ✅ FUNÇÃO EXPORTAR PDF
const exportarPdf = async () => {
  loadingPdf.value = true;
  
  try {
    const params = new URLSearchParams();
    
    // Adicionar filtros ativos
    if (filtros.value.tab === 'minhas') {
      params.append('minhas', 'true');
    } else if (filtros.value.tab === 'pendentes') {
      params.append('pendentes_aprovacao', 'true');
    }
    
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] && 
          key !== 'tab' && 
          key !== 'minhas' && 
          key !== 'pendentes_aprovacao' && 
          key !== 'page') {
        params.append(key, filtros.value[key]);
      }
    });
    
    const queryString = params.toString();
    const url = queryString 
      ? `/api/v1/reservas-espacos/exportar-pdf?${queryString}` 
      : '/api/v1/reservas-espacos/exportar-pdf';
    
    console.log('🔍 URL da requisição PDF:', url);
    
    const response = await axios.get(url, {
      responseType: 'blob',
    });
    
    // Criar link de download
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = `reservas-${new Date().toISOString().split('T')[0]}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(link.href);
    
    Swal.fire({
      icon: 'success',
      title: 'PDF gerado!',
      text: 'O relatório foi baixado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    console.error('❌ Erro ao exportar PDF:', error);
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: error.response?.data?.message || 'Erro ao gerar PDF'
    });
  } finally {
    loadingPdf.value = false;
  }
};

// ✅ REFS PARA HIERARQUIA
const instituicoes = ref([]);
const campi = ref([]);
const predios = ref([]);
const blocos = ref([]);
const andares = ref([]);
const espacos = ref([]);

const filtros = ref({
  tab: 'todas',
  status: '',
  data_inicio: '',
  data_fim: '',
  search: '',
  // ✅ FILTROS HIERÁRQUICOS
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  bloco_id: '',
  andar_id: '',
  espaco_fisico_id: '',
  tipo_espaco: '',
  finalidade: '',
  minhas: false,
  pendentes_aprovacao: false,
  page: 1
});

const user = computed(() => {
  const userData = localStorage.getItem('user');
  return userData ? JSON.parse(userData) : {};
});

onMounted(async () => {
  await Promise.all([
    carregarReservas(),
    carregarEstatisticas(),
    carregarInstituicoes(),
    carregarCampi()
  ]);
  loading.value = false;
});

const carregarReservas = async () => {
  try {
    const params = new URLSearchParams();
    
    if (filtros.value.tab === 'minhas') {
      params.append('minhas', 'true');
    } else if (filtros.value.tab === 'pendentes') {
      params.append('pendentes_aprovacao', 'true');
    }
    
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] && key !== 'tab' && key !== 'minhas' && key !== 'pendentes_aprovacao') {
        params.append(key, filtros.value[key]);
      }
    });

    const { data } = await axios.get(`/api/v1/reservas-espacos?${params.toString()}`);
    reservas.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar reservas:', error);
  }
};

const carregarEstatisticas = async () => {
  try {
    const { data } = await axios.get('/api/v1/reservas-espacos/estatisticas');
    estatisticas.value = data;
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error);
  }
};

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
  if (!filtros.value.instituicao_id) {
    campi.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/instituicoes/${filtros.value.instituicao_id}/campi`);
    campi.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarPrediosFiltrados = async () => {
  if (!filtros.value.campus_id) {
    predios.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/campi/${filtros.value.campus_id}/predios`);
    predios.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
  }
};

const carregarBlocosFiltrados = async () => {
  if (!filtros.value.predio_id) {
    blocos.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/predios/${filtros.value.predio_id}/blocos`);
    blocos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
  }
};

const carregarAndaresFiltrados = async () => {
  if (!filtros.value.bloco_id) {
    andares.value = [];
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/blocos/${filtros.value.bloco_id}/andares`);
    andares.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
  }
};

const carregarEspacosFiltrados = async () => {
  try {
    const params = new URLSearchParams();
    if (filtros.value.andar_id) params.append('andar_id', filtros.value.andar_id);
    if (filtros.value.campus_id) params.append('campus_id', filtros.value.campus_id);
    if (filtros.value.tipo_espaco) params.append('tipo', filtros.value.tipo_espaco);
    params.append('status', 'Disponível');
    
    const { data } = await axios.get(`/api/v1/espacos-fisicos?${params.toString()}&all=true`);
    espacos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar espaços:', error);
  }
};

// ✅ WATCHERS PARA FILTROS EM CASCATA (CORRIGIDOS)
const onInstituicaoChange = async () => {
  // Limpar filtros dependentes
  filtros.value.campus_id = '';
  filtros.value.predio_id = '';
  filtros.value.bloco_id = '';
  filtros.value.andar_id = '';
  filtros.value.espaco_fisico_id = '';
  
  // Limpar arrays
  predios.value = [];
  blocos.value = [];
  andares.value = [];
  espacos.value = [];
  
  if (filtros.value.instituicao_id) {
    await carregarCampiFiltrados();
  } else {
    // Se desmarcar instituição, carrega todos os campi novamente
    await carregarCampi();
  }
  
  filtrar();
};

const onCampusChange = async () => {
  // Limpar filtros dependentes
  filtros.value.predio_id = '';
  filtros.value.bloco_id = '';
  filtros.value.andar_id = '';
  filtros.value.espaco_fisico_id = '';
  
  // Limpar arrays
  blocos.value = [];
  andares.value = [];
  espacos.value = [];
  
  if (filtros.value.campus_id) {
    await Promise.all([
      carregarPrediosFiltrados(),
      carregarEspacosFiltrados()
    ]);
  } else {
    predios.value = [];
    espacos.value = [];
  }
  
  filtrar();
};

const onPredioChange = async () => {
  // Limpar filtros dependentes
  filtros.value.bloco_id = '';
  filtros.value.andar_id = '';
  filtros.value.espaco_fisico_id = '';
  
  // Limpar arrays
  andares.value = [];
  espacos.value = [];
  
  if (filtros.value.predio_id) {
    await carregarBlocosFiltrados();
  } else {
    blocos.value = [];
  }
  
  filtrar();
};

const onBlocoChange = async () => {
  // Limpar filtros dependentes
  filtros.value.andar_id = '';
  filtros.value.espaco_fisico_id = '';
  
  // Limpar arrays
  espacos.value = [];
  
  if (filtros.value.bloco_id) {
    await carregarAndaresFiltrados();
  } else {
    andares.value = [];
  }
  
  filtrar();
};

const onAndarChange = async () => {
  // Limpar filtro dependente
  filtros.value.espaco_fisico_id = '';
  
  if (filtros.value.andar_id) {
    await carregarEspacosFiltrados();
  } else {
    espacos.value = [];
  }
  
  filtrar();
};

const mudarTab = (tab) => {
  filtros.value.tab = tab;
  if (tab !== 'calendario') {
    carregarReservas();
  }
};

const filtrar = () => {
  carregarReservas();
};

const abrirFormulario = (reserva = null) => {
  reservaSelecionada.value = reserva;
  mostrarFormulario.value = true;
};

const fecharFormulario = () => {
  mostrarFormulario.value = false;
  reservaSelecionada.value = null;
};

const reservaSalva = () => {
  fecharFormulario();
  carregarReservas();
  carregarEstatisticas();
  Swal.fire('Sucesso!', 'Reserva salva com sucesso!', 'success');
};

const verDetalhes = async (reserva) => {
  try {
    const { data } = await axios.get(`/api/v1/reservas-espacos/${reserva.id}`);
    reservaSelecionada.value = data;
    mostrarDetalhes.value = true;
  } catch (error) {
    console.error('Erro ao carregar detalhes:', error);
    Swal.fire('Erro!', 'Erro ao carregar detalhes da reserva', 'error');
  }
};

const aprovar = async (id) => {
  const result = await Swal.fire({
    title: 'Aprovar Reserva?',
    text: 'Esta ação aprovará a reserva.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, aprovar!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/v1/reservas-espacos/${id}/aprovar`);
      Swal.fire('Aprovado!', 'Reserva aprovada com sucesso', 'success');
      carregarReservas();
      carregarEstatisticas();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao aprovar reserva', 'error');
    }
  }
};

const rejeitar = async (id) => {
  const { value: motivo } = await Swal.fire({
    title: 'Rejeitar Reserva',
    input: 'textarea',
    inputLabel: 'Motivo da rejeição',
    inputPlaceholder: 'Digite o motivo...',
    inputValidator: (value) => {
      if (!value) {
        return 'Você precisa informar um motivo!';
      }
    },
    showCancelButton: true,
    confirmButtonText: 'Rejeitar',
    cancelButtonText: 'Cancelar'
  });

  if (motivo) {
    try {
      await axios.post(`/api/v1/reservas-espacos/${id}/rejeitar`, { motivo_rejeicao: motivo });
      Swal.fire('Rejeitado!', 'Reserva rejeitada', 'success');
      carregarReservas();
      carregarEstatisticas();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao rejeitar reserva', 'error');
    }
  }
};

const cancelarReserva = async (id) => {
  const result = await Swal.fire({
    title: 'Cancelar Reserva?',
    text: 'Esta ação não poderá ser revertida!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, cancelar!',
    cancelButtonText: 'Não'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/v1/reservas-espacos/${id}/cancelar`);
      Swal.fire('Cancelado!', 'Reserva cancelada com sucesso', 'success');
      carregarReservas();
      carregarEstatisticas();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao cancelar reserva', 'error');
    }
  }
};

const getStatusBadge = (status) => {
  const badges = {
    'Pendente': 'badge bg-warning',
    'Aprovada': 'badge bg-success',
    'Rejeitada': 'badge bg-danger',
    'Cancelada': 'badge bg-secondary',
    'Concluída': 'badge bg-info'
  };
  return badges[status] || 'badge bg-secondary';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('pt-BR');
};
</script>

<style scoped>
/* SKELETON LOADING */
.skeleton {
  background: linear-gradient(90deg, 
    rgba(255, 255, 255, 0.05) 25%, 
    rgba(255, 255, 255, 0.1) 50%, 
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.skeleton-title { height: 32px; width: 250px; }
.skeleton-text { height: 16px; width: 100%; }
.skeleton-button { height: 38px; width: 140px; border-radius: 6px; }
.skeleton-button-sm { height: 32px; width: 100%; border-radius: 4px; }
.skeleton-input { height: 38px; width: 100%; border-radius: 6px; }
.skeleton-tab { height: 42px; width: 150px; border-radius: 6px; }
.skeleton-badge { height: 24px; width: 80px; border-radius: 12px; }
.skeleton-id { height: 20px; width: 50px; border-radius: 4px; }
.skeleton-stat-number { height: 32px; width: 60px; border-radius: 6px; }
.skeleton-stat-label { height: 16px; width: 80px; border-radius: 4px; }

/* CARDS */
.reserva-card {
  transition: all 0.3s ease;
  height: 100%;
}

.reserva-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}

.stat-card-mini {
  transition: transform 0.3s;
}

.stat-card-mini:hover {
  transform: translateY(-3px);
}

/* TABS */
.nav-tabs-glass {
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.nav-tabs-glass .nav-link {
  color: rgba(255, 255, 255, 0.7);
  border: none;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
}

.nav-tabs-glass .nav-link:hover {
  color: white;
  border: none;
}

.nav-tabs-glass .nav-link.active {
  color: white;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-bottom: 2px solid #60a5fa;
}

.reserva-info p {
  line-height: 1.8;
}
</style>
