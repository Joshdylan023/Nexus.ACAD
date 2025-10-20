<template>
  <teleport to="body">
    <div class="modal-overlay" @click="$emit('close')">
      <div class="modal-container-large card-glass" @click.stop>
        <!-- Header -->
        <div class="modal-header">
          <h4 class="text-white mb-0">
            <i class="bi bi-calendar-plus me-2"></i>
            {{ reserva ? 'Editar Reserva' : 'Nova Reserva' }}
          </h4>
          <button @click="$emit('close')" class="btn-close btn-close-white"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form @submit.prevent="salvar">
            <!-- Seleção de Espaço -->
            <div class="section-header">
              <i class="bi bi-door-open me-2"></i>
              Espaço Físico
            </div>

            <div class="row g-3 mb-4">
              <!-- ⭐ HIERARQUIA COMPLETA -->
              
              <!-- Instituição -->
              <div class="col-md-3">
                <label class="form-label text-white">Instituição</label>
                <select 
                  v-model="filtrosEspaco.instituicao_id" 
                  @change="carregarCampi"
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="">Selecione...</option>
                  <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                    {{ inst.nome_fantasia || inst.razao_social }}
                  </option>
                </select>
              </div>

              <!-- Campus -->
              <div class="col-md-3">
                <label class="form-label text-white">Campus</label>
                <select 
                  v-model="filtrosEspaco.campus_id" 
                  @change="carregarPredios"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!filtrosEspaco.instituicao_id"
                >
                  <option value="">Selecione...</option>
                  <option v-for="campus in campi" :key="campus.id" :value="campus.id">
                    {{ campus.nome }}
                  </option>
                </select>
              </div>

              <!-- Prédio -->
              <div class="col-md-3">
                <label class="form-label text-white">Prédio</label>
                <select 
                  v-model="filtrosEspaco.predio_id" 
                  @change="carregarBlocos"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!filtrosEspaco.campus_id"
                >
                  <option value="">Selecione...</option>
                  <option v-for="predio in predios" :key="predio.id" :value="predio.id">
                    {{ predio.nome }}
                  </option>
                </select>
              </div>

              <!-- Bloco -->
              <div class="col-md-3">
                <label class="form-label text-white">Bloco</label>
                <select 
                  v-model="filtrosEspaco.bloco_id" 
                  @change="carregarAndares"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!filtrosEspaco.predio_id"
                >
                  <option value="">Selecione...</option>
                  <option v-for="bloco in blocos" :key="bloco.id" :value="bloco.id">
                    {{ bloco.nome }}
                  </option>
                </select>
              </div>

              <!-- Andar -->
              <div class="col-md-3">
                <label class="form-label text-white">Andar</label>
                <select 
                  v-model="filtrosEspaco.andar_id" 
                  @change="carregarEspacos"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!filtrosEspaco.bloco_id"
                >
                  <option value="">Selecione...</option>
                  <option v-for="andar in andares" :key="andar.id" :value="andar.id">
                    {{ andar.nome_completo || andar.nome }}
                  </option>
                </select>
              </div>

              <!-- Tipo -->
              <div class="col-md-3">
                <label class="form-label text-white">Tipo</label>
                <select 
                  v-model="filtrosEspaco.tipo" 
                  @change="carregarEspacos"
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

              <!-- Capacidade Mínima -->
              <div class="col-md-3">
                <label class="form-label text-white">Capacidade Mín.</label>
                <input 
                  v-model.number="filtrosEspaco.capacidade_minima" 
                  @input="carregarEspacos"
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Ex: 30"
                />
              </div>

              <!-- Seleção Final do Espaço -->
              <div class="col-md-12">
                <label class="form-label text-white">
                  Espaço <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.espaco_fisico_id" 
                  @change="verificarDisponibilidade"
                  class="form-select bg-transparent text-white border-secondary"
                  required
                >
                  <option value="">Selecione o espaço...</option>
                  <option v-for="espaco in espacos" :key="espaco.id" :value="espaco.id">
                    {{ espaco.codigo }} - {{ espaco.nome }} (Cap: {{ espaco.capacidade || '-' }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Data e Horário -->
            <div class="section-header">
              <i class="bi bi-calendar-event me-2"></i>
              Data e Horário
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label class="form-label text-white">
                  Data Início <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.data_inicio" 
                  @change="verificarDisponibilidade"
                  type="date" 
                  :min="hoje"
                  class="form-control bg-transparent text-white border-secondary"
                  required
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">
                  Data Fim <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.data_fim" 
                  @change="verificarDisponibilidade"
                  type="date" 
                  :min="form.data_inicio || hoje"
                  class="form-control bg-transparent text-white border-secondary"
                  required
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">
                  Hora Início <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.hora_inicio" 
                  @change="verificarDisponibilidade"
                  type="time" 
                  class="form-control bg-transparent text-white border-secondary"
                  required
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">
                  Hora Fim <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.hora_fim" 
                  @change="verificarDisponibilidade"
                  type="time" 
                  class="form-control bg-transparent text-white border-secondary"
                  required
                />
              </div>
            </div>

            <!-- Alerta de Disponibilidade -->
            <div v-if="disponibilidade.verificado" class="alert mb-4" :class="disponibilidade.disponivel ? 'alert-success' : 'alert-danger'">
              <i class="bi" :class="disponibilidade.disponivel ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
              {{ disponibilidade.mensagem }}
            </div>

            <!-- Recorrência -->
            <div class="section-header">
              <i class="bi bi-arrow-repeat me-2"></i>
              Recorrência (Opcional)
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-12">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.recorrente" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="recorrente"
                  />
                  <label class="form-check-label text-white" for="recorrente">
                    Esta reserva é recorrente
                  </label>
                </div>
              </div>

              <template v-if="form.recorrente">
                <div class="col-md-6">
                  <label class="form-label text-white">Frequência</label>
                  <select 
                    v-model="form.frequencia_recorrencia" 
                    class="form-select bg-transparent text-white border-secondary"
                  >
                    <option value="">Selecione...</option>
                    <option value="Diário">Diário</option>
                    <option value="Semanal">Semanal</option>
                    <option value="Mensal">Mensal</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-white">Repetir até</label>
                  <input 
                    v-model="form.recorrencia_ate" 
                    type="date" 
                    :min="form.data_fim"
                    class="form-control bg-transparent text-white border-secondary"
                  />
                </div>

                <div class="col-md-12" v-if="form.frequencia_recorrencia === 'Semanal'">
                  <label class="form-label text-white">Dias da Semana</label>
                  <div class="d-flex gap-2">
                    <div class="form-check" v-for="dia in diasSemana" :key="dia.valor">
                      <input 
                        v-model="form.dias_semana" 
                        class="form-check-input" 
                        type="checkbox" 
                        :value="dia.valor"
                        :id="dia.valor"
                      />
                      <label class="form-check-label text-white" :for="dia.valor">
                        {{ dia.label }}
                      </label>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Informações da Reserva -->
            <div class="section-header">
              <i class="bi bi-info-circle me-2"></i>
              Informações da Reserva
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-12">
                <label class="form-label text-white">
                  Motivo/Título <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.motivo" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Ex: Aula de Cálculo I"
                  required
                />
              </div>

              <div class="col-md-12">
                <label class="form-label text-white">Descrição</label>
                <textarea 
                  v-model="form.descricao" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="3"
                  placeholder="Detalhes adicionais sobre a reserva..."
                ></textarea>
              </div>

              <div class="col-md-4">
                <label class="form-label text-white">Finalidade</label>
                <select 
                  v-model="form.finalidade" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="">Selecione...</option>
                  <option value="Aula">Aula</option>
                  <option value="Reunião">Reunião</option>
                  <option value="Evento">Evento</option>
                  <option value="Palestra">Palestra</option>
                  <option value="Workshop">Workshop</option>
                  <option value="Treinamento">Treinamento</option>
                  <option value="Outro">Outro</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label text-white">Qtd. Participantes</label>
                <input 
                  v-model.number="form.quantidade_pessoas" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Ex: 30"
                />
              </div>

              <div class="col-md-4">
                <label class="form-label text-white">Recursos Necessários</label>
                <input 
                  v-model="recursosTexto" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Ex: Projetor, Wi-Fi"
                />
                <small class="text-white-50">Separe por vírgula</small>
              </div>

              <div class="col-md-12">
                <label class="form-label text-white">Observações</label>
                <textarea 
                  v-model="form.observacoes" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="2"
                  placeholder="Observações adicionais..."
                ></textarea>
              </div>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button @click="$emit('close')" type="button" class="btn btn-secondary">
            <i class="bi bi-x-circle me-1"></i>
            Cancelar
          </button>
          <button 
            @click="salvar" 
            type="button" 
            class="btn btn-primary" 
            :disabled="salvando || (disponibilidade.verificado && !disponibilidade.disponivel)"
          >
            <span v-if="salvando">
              <span class="spinner-border spinner-border-sm me-1"></span>
              Salvando...
            </span>
            <span v-else>
              <i class="bi bi-check-circle me-1"></i>
              {{ reserva ? 'Atualizar' : 'Solicitar Reserva' }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  reserva: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const salvando = ref(false);
const instituicoes = ref([]);
const campi = ref([]);
const predios = ref([]);
const blocos = ref([]);
const andares = ref([]);
const espacos = ref([]);
const disponibilidade = ref({
  verificado: false,
  disponivel: false,
  mensagem: ''
});

const hoje = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const filtrosEspaco = reactive({
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  bloco_id: '',
  andar_id: '',
  tipo: '',
  capacidade_minima: null
});

const form = reactive({
  espaco_fisico_id: '',
  data_inicio: '',
  data_fim: '',
  hora_inicio: '',
  hora_fim: '',
  recorrente: false,
  frequencia_recorrencia: '',
  recorrencia_ate: '',
  dias_semana: [],
  motivo: '',
  descricao: '',
  finalidade: '',
  quantidade_pessoas: null,
  recursos_adicionais: [],
  observacoes: ''
});

const recursosTexto = computed({
  get: () => form.recursos_adicionais.join(', '),
  set: (val) => form.recursos_adicionais = val ? val.split(',').map(r => r.trim()) : []
});

const diasSemana = [
  { valor: 'seg', label: 'Seg' },
  { valor: 'ter', label: 'Ter' },
  { valor: 'qua', label: 'Qua' },
  { valor: 'qui', label: 'Qui' },
  { valor: 'sex', label: 'Sex' },
  { valor: 'sab', label: 'Sáb' },
  { valor: 'dom', label: 'Dom' }
];

onMounted(async () => {
  await carregarInstituicoes();
  
  if (props.reserva) {
    Object.assign(form, props.reserva);
    // Carregar hierarquia se estiver editando
    if (props.reserva.espaco_fisico) {
      // Implementar lógica de carregamento em cascata
    }
  }
});

const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
};

const carregarCampi = async () => {
  if (!filtrosEspaco.instituicao_id) {
    campi.value = [];
    predios.value = [];
    blocos.value = [];
    andares.value = [];
    espacos.value = [];
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/instituicoes/${filtrosEspaco.instituicao_id}/campi`);
    campi.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarPredios = async () => {
  if (!filtrosEspaco.campus_id) {
    predios.value = [];
    blocos.value = [];
    andares.value = [];
    espacos.value = [];
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/campi/${filtrosEspaco.campus_id}/predios`);
    predios.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
  }
};

const carregarBlocos = async () => {
  if (!filtrosEspaco.predio_id) {
    blocos.value = [];
    andares.value = [];
    espacos.value = [];
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/predios/${filtrosEspaco.predio_id}/blocos`);
    blocos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
  }
};

const carregarAndares = async () => {
  if (!filtrosEspaco.bloco_id) {
    andares.value = [];
    espacos.value = [];
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/blocos/${filtrosEspaco.bloco_id}/andares`);
    andares.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
  }
};

const carregarEspacos = async () => {
  try {
    const params = new URLSearchParams();
    if (filtrosEspaco.andar_id) params.append('andar_id', filtrosEspaco.andar_id);
    if (filtrosEspaco.tipo) params.append('tipo', filtrosEspaco.tipo);
    if (filtrosEspaco.capacidade_minima) params.append('capacidade_minima', filtrosEspaco.capacidade_minima);
    params.append('status', 'Disponível');
    params.append('all', 'true');

    const { data } = await axios.get(`/api/v1/espacos-fisicos?${params.toString()}`);
    espacos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar espaços:', error);
  }
};

const verificarDisponibilidade = async () => {
  if (!form.espaco_fisico_id || !form.data_inicio || !form.data_fim || !form.hora_inicio || !form.hora_fim) {
    disponibilidade.value.verificado = false;
    return;
  }

  try {
    const { data } = await axios.post(`/api/v1/espacos-fisicos/${form.espaco_fisico_id}/verificar-disponibilidade`, {
      data_inicio: form.data_inicio,
      data_fim: form.data_fim,
      hora_inicio: form.hora_inicio,
      hora_fim: form.hora_fim
    });

    disponibilidade.value = {
      verificado: true,
      disponivel: data.disponivel,
      mensagem: data.disponivel 
        ? 'Espaço disponível para o período selecionado!' 
        : 'Espaço já possui reserva para este período.'
    };
  } catch (error) {
    console.error('Erro ao verificar disponibilidade:', error);
  }
};

const salvar = async () => {
  salvando.value = true;
  try {
    if (props.reserva) {
      await axios.put(`/api/v1/reservas-espacos/${props.reserva.id}`, form);
    } else {
      await axios.post('/api/v1/reservas-espacos', form);
    }
    emit('success');
  } catch (error) {
    console.error('Erro ao salvar:', error);
    Swal.fire('Erro!', error.response?.data?.message || 'Erro ao salvar reserva', 'error');
  } finally {
    salvando.value = false;
  }
};
</script>

<style scoped>
.modal-container-large {
  width: 100%;
  max-width: 1100px;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 15px;
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 20px;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1.5rem;
  max-height: calc(90vh - 200px);
  overflow-y: auto;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.section-header {
  color: white;
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.alert {
  padding: 1rem;
  border-radius: 8px;
  font-weight: 500;
}

.alert-success {
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.4);
  color: #86efac;
}

.alert-danger {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.4);
  color: #fca5a5;
}
</style>
