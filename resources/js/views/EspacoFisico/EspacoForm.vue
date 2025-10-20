<template>
  <teleport to="body">
    <div class="modal-overlay" @click="$emit('close')">
      <div class="modal-container-large card-glass" @click.stop>
        <!-- Header -->
        <div class="modal-header">
          <h4 class="text-white mb-0">
            <i class="bi bi-door-open me-2"></i>
            {{ espaco ? 'Editar Espaço' : 'Novo Espaço Físico' }}
          </h4>
          <button @click="$emit('close')" class="btn-close btn-close-white"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form @submit.prevent="salvar">
            <!-- Localização -->
            <div class="section-header">
              <i class="bi bi-geo-alt me-2"></i>
              Localização
            </div>

            <div class="row g-3 mb-4">
              <!-- ⭐ NOVO: Instituição -->
              <div class="col-md-6">
                <label class="form-label text-white">
                  Instituição <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.instituicao_id" 
                  @change="carregarCampi"
                  class="form-select bg-transparent text-white border-secondary"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="inst in instituicoes" :key="inst?.id || Math.random()" :value="inst?.id">
                    {{ inst?.nome_fantasia || inst?.razao_social || 'Nome não disponível' }}
                  </option>
                </select>
              </div>

              <!-- Campus -->
              <div class="col-md-6">
                <label class="form-label text-white">
                  Campus <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.campus_id" 
                  @change="carregarPredios"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!form.instituicao_id"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="campus in campi" :key="campus?.id || Math.random()" :value="campus?.id">
                    {{ campus?.nome || 'Nome não disponível' }}
                  </option>
                </select>
              </div>

              <!-- Prédio -->
              <div class="col-md-6">
                <label class="form-label text-white">
                  Prédio <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.predio_id" 
                  @change="carregarBlocos"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!form.campus_id"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="predio in predios" :key="predio?.id || Math.random()" :value="predio?.id">
                    {{ predio?.nome || 'Nome não disponível' }}
                  </option>
                </select>
              </div>

              <!-- Bloco -->
              <div class="col-md-6">
                <label class="form-label text-white">
                  Bloco <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.bloco_id" 
                  @change="carregarAndares"
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!form.predio_id"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="bloco in blocos" :key="bloco?.id || Math.random()" :value="bloco?.id">
                    {{ bloco?.nome || 'Nome não disponível' }}
                  </option>
                </select>
              </div>

              <!-- Andar -->
              <div class="col-md-12">
                <label class="form-label text-white">
                  Andar <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.andar_id" 
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!form.bloco_id"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="andar in andares" :key="andar?.id || Math.random()" :value="andar?.id">
                    {{ andar?.nome_completo || andar?.nome || 'Nome não disponível' }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Identificação -->
            <div class="section-header">
              <i class="bi bi-info-circle me-2"></i>
              Identificação
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label class="form-label text-white">
                  Código <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.codigo" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="A101"
                  required
                />
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">
                  Nome <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.nome" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Sala 101"
                  required
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">
                  Tipo <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.tipo" 
                  class="form-select bg-transparent text-white border-secondary"
                  required
                >
                  <option value="Sala de Aula">Sala de Aula</option>
                  <option value="Laboratório">Laboratório</option>
                  <option value="Auditório">Auditório</option>
                  <option value="Biblioteca">Biblioteca</option>
                  <option value="Sala de Reunião">Sala de Reunião</option>
                  <option value="Sala de Professores">Sala de Professores</option>
                  <option value="Coordenação">Coordenação</option>
                  <option value="Diretoria">Diretoria</option>
                  <option value="Secretaria">Secretaria</option>
                  <option value="Almoxarifado">Almoxarifado</option>
                  <option value="Banheiro">Banheiro</option>
                  <option value="Copa/Cozinha">Copa/Cozinha</option>
                  <option value="Área de Convivência">Área de Convivência</option>
                  <option value="Estacionamento">Estacionamento</option>
                  <option value="Quadra Esportiva">Quadra Esportiva</option>
                  <option value="Ginásio">Ginásio</option>
                  <option value="Cantina">Cantina</option>
                  <option value="Outro">Outro</option>
                </select>
              </div>
            </div>

            <!-- Características Físicas -->
            <div class="section-header">
              <i class="bi bi-rulers me-2"></i>
              Características Físicas
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label class="form-label text-white">Área (m²)</label>
                <input 
                  v-model.number="form.area" 
                  type="number" 
                  step="0.01"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="50.00"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Capacidade</label>
                <input 
                  v-model.number="form.capacidade" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="40"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Capacidade p/ Exame</label>
                <input 
                  v-model.number="form.capacidade_exame" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="30"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Status</label>
                <select 
                  v-model="form.status" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="Disponível">Disponível</option>
                  <option value="Ocupado">Ocupado</option>
                  <option value="Manutenção">Manutenção</option>
                  <option value="Reforma">Reforma</option>
                  <option value="Indisponível">Indisponível</option>
                </select>
              </div>
            </div>

            <!-- Infraestrutura Tecnológica -->
            <div class="section-header">
              <i class="bi bi-cpu me-2"></i>
              Infraestrutura Tecnológica
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.ar_condicionado" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="ar_condicionado"
                  />
                  <label class="form-check-label text-white" for="ar_condicionado">
                    <i class="bi bi-wind me-1"></i> Ar Condicionado
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.projetor" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="projetor"
                  />
                  <label class="form-check-label text-white" for="projetor">
                    <i class="bi bi-projector me-1"></i> Projetor
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.lousa_digital" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="lousa_digital"
                  />
                  <label class="form-check-label text-white" for="lousa_digital">
                    <i class="bi bi-tv me-1"></i> Lousa Digital
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.computadores" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="computadores"
                  />
                  <label class="form-check-label text-white" for="computadores">
                    <i class="bi bi-pc-display me-1"></i> Computadores
                  </label>
                </div>
              </div>

              <div class="col-md-3" v-if="form.computadores">
                <label class="form-label text-white">Qtd. Computadores</label>
                <input 
                  v-model.number="form.quantidade_computadores" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="20"
                />
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.wifi" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="wifi"
                  />
                  <label class="form-check-label text-white" for="wifi">
                    <i class="bi bi-wifi me-1"></i> Wi-Fi
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.acessibilidade" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="acessibilidade"
                  />
                  <label class="form-check-label text-white" for="acessibilidade">
                    <i class="bi bi-universal-access me-1"></i> Acessibilidade
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.cameras_seguranca" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="cameras"
                  />
                  <label class="form-check-label text-white" for="cameras">
                    <i class="bi bi-camera-video me-1"></i> Câmeras de Segurança
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.sistema_som" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="som"
                  />
                  <label class="form-check-label text-white" for="som">
                    <i class="bi bi-volume-up me-1"></i> Sistema de Som
                  </label>
                </div>
              </div>
            </div>

            <!-- Mobiliário -->
            <div class="section-header">
              <i class="bi bi-table me-2"></i>
              Mobiliário
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label class="form-label text-white">Qtd. Carteiras</label>
                <input 
                  v-model.number="form.quantidade_carteiras" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="40"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Qtd. Cadeiras</label>
                <input 
                  v-model.number="form.quantidade_cadeiras" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="0"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Qtd. Mesas</label>
                <input 
                  v-model.number="form.quantidade_mesas" 
                  type="number" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="0"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Tipo Mobiliário</label>
                <select 
                  v-model="form.tipo_mobiliario" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="">Selecione...</option>
                  <option value="Fixo">Fixo</option>
                  <option value="Móvel">Móvel</option>
                  <option value="Bancadas">Bancadas</option>
                  <option value="Misto">Misto</option>
                </select>
              </div>
            </div>

            <!-- Disponibilidade -->
            <div class="section-header">
              <i class="bi bi-calendar-check me-2"></i>
              Disponibilidade
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.permite_reserva" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="permite_reserva"
                  />
                  <label class="form-check-label text-white" for="permite_reserva">
                    Permite Reserva
                  </label>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Responsável</label>
                <select 
                  v-model="form.responsavel_id" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option :value="null">Nenhum</option>
                  <option 
                    v-for="colab in colaboradores" 
                    :key="colab?.id || Math.random()" 
                    :value="colab?.id"
                  >
                    {{ colab?.nome_completo || 'Nome não disponível' }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Observações -->
            <div class="section-header">
              <i class="bi bi-chat-text me-2"></i>
              Observações e Restrições
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-white">Observações</label>
                <textarea 
                  v-model="form.observacoes" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="3"
                  placeholder="Observações gerais..."
                ></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Restrições de Uso</label>
                <textarea 
                  v-model="form.restricoes" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="3"
                  placeholder="Ex: Apenas para pós-graduação..."
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
          <button @click="salvar" type="button" class="btn btn-primary" :disabled="salvando">
            <span v-if="salvando">
              <span class="spinner-border spinner-border-sm me-1"></span>
              Salvando...
            </span>
            <span v-else>
              <i class="bi bi-check-circle me-1"></i>
              Salvar
            </span>
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  espaco: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const salvando = ref(false);
const instituicoes = ref([]); // ⭐ NOVO
const campi = ref([]);
const predios = ref([]);
const blocos = ref([]);
const andares = ref([]);
const colaboradores = ref([]);

const form = reactive({
  instituicao_id: '', // ⭐ NOVO (auxiliar)
  campus_id: '', // (auxiliar)
  predio_id: '', // (auxiliar)
  bloco_id: '', // (auxiliar)
  andar_id: '',
  codigo: '',
  nome: '',
  tipo: 'Sala de Aula',
  area: null,
  capacidade: null,
  capacidade_exame: null,
  ar_condicionado: false,
  projetor: false,
  lousa_digital: false,
  computadores: false,
  quantidade_computadores: null,
  wifi: false,
  acessibilidade: false,
  cameras_seguranca: false,
  sistema_som: false,
  quantidade_carteiras: null,
  quantidade_cadeiras: null,
  quantidade_mesas: null,
  tipo_mobiliario: '',
  status: 'Disponível',
  permite_reserva: true,
  responsavel_id: null,
  observacoes: '',
  restricoes: ''
});

onMounted(async () => {
  try {
    await Promise.all([
      carregarInstituicoes(), // ⭐ NOVO
      carregarColaboradores()
    ]);
    
    if (props.espaco) {
      Object.assign(form, props.espaco);
      // Carregar hierarquia completa de forma segura
      if (props.espaco.andar?.bloco?.predio?.campus?.instituicao_id) {
        form.instituicao_id = props.espaco.andar.bloco.predio.campus.instituicao_id;
        await carregarCampi();
      }
      if (props.espaco.andar?.bloco?.predio?.campus_id) {
        form.campus_id = props.espaco.andar.bloco.predio.campus_id;
        await carregarPredios();
      }
      if (props.espaco.andar?.bloco?.predio_id) {
        form.predio_id = props.espaco.andar.bloco.predio_id;
        await carregarBlocos();
      }
      if (props.espaco.andar?.bloco_id) {
        form.bloco_id = props.espaco.andar.bloco_id;
        await carregarAndares();
      }
    }
  } catch (error) {
    console.error('Erro ao inicializar formulário:', error);
    Swal.fire('Erro!', 'Erro ao carregar dados do formulário', 'error');
  }
});

// ⭐ NOVO
const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
    instituicoes.value = [];
  }
};

// ⭐ NOVO
const carregarCampi = async () => {
  if (!form.instituicao_id) {
    campi.value = [];
    form.campus_id = '';
    predios.value = [];
    form.predio_id = '';
    blocos.value = [];
    form.bloco_id = '';
    andares.value = [];
    form.andar_id = '';
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${form.instituicao_id}&all=true`);
    campi.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
    campi.value = [];
  }
};

const carregarPredios = async () => {
  if (!form.campus_id) {
    predios.value = [];
    form.predio_id = '';
    blocos.value = [];
    form.bloco_id = '';
    andares.value = [];
    form.andar_id = '';
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/predios?campus_id=${form.campus_id}&all=true`);
    predios.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
    predios.value = [];
  }
};

const carregarBlocos = async () => {
  if (!form.predio_id) {
    blocos.value = [];
    form.bloco_id = '';
    andares.value = [];
    form.andar_id = '';
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/blocos?predio_id=${form.predio_id}&all=true`);
    blocos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
    blocos.value = [];
  }
};

const carregarAndares = async () => {
  if (!form.bloco_id) {
    andares.value = [];
    form.andar_id = '';
    return;
  }
  try {
    const { data } = await axios.get(`/api/v1/andares?bloco_id=${form.bloco_id}&all=true`);
    andares.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
    andares.value = [];
  }
};

const carregarColaboradores = async () => {
  try {
    const { data } = await axios.get('/api/v1/colaboradores?all=true');
    colaboradores.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar colaboradores:', error);
    colaboradores.value = [];
  }
};

const salvar = async () => {
  salvando.value = true;
  try {
    // ⭐ Remover campos auxiliares antes de salvar
    const payload = { ...form };
    delete payload.instituicao_id;
    delete payload.campus_id;
    delete payload.predio_id;
    delete payload.bloco_id;
    
    // Remover relacionamentos que não devem ser enviados no update
    delete payload.createdBy;
    delete payload.updatedBy;
    delete payload.andar;
    delete payload.responsavel;
    delete payload.reservas;
    
    if (props.espaco) {
      await axios.put(`/api/v1/espacos-fisicos/${props.espaco.id}`, payload);
    } else {
      await axios.post('/api/v1/espacos-fisicos', payload);
    }
    emit('success');
  } catch (error) {
    console.error('Erro ao salvar:', error);
    Swal.fire('Erro!', error.response?.data?.message || 'Erro ao salvar espaço', 'error');
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
</style>
