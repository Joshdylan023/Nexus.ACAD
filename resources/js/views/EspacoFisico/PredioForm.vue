<template>
  <teleport to="body">
    <div class="modal-overlay" @click="$emit('close')">
      <div class="modal-container card-glass" @click.stop>
        <!-- Header -->
        <div class="modal-header">
          <h4 class="text-white mb-0">
            <i class="bi bi-building me-2"></i>
            {{ predio ? 'Editar Prédio' : 'Novo Prédio' }}
          </h4>
          <button @click="$emit('close')" class="btn-close btn-close-white"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form @submit.prevent="salvar">
            <!-- Informações Básicas -->
            <div class="section-header">
              <i class="bi bi-info-circle me-2"></i>
              Informações Básicas
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
                  <option v-for="inst in instituicoes" :key="inst?.id" :value="inst?.id">
                  {{ inst?.nome_fantasia || inst?.razao_social }}
                </option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">
                  Campus <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.campus_id" 
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!form.instituicao_id"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="campus in campi" :key="campus?.id" :value="campus?.id">
                    {{ campus?.nome }}
                  </option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">
                  Código <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.codigo" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="PRD-001"
                  required
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">
                  Status <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.status" 
                  class="form-select bg-transparent text-white border-secondary"
                  required
                >
                  <option value="Ativo">Ativo</option>
                  <option value="Inativo">Inativo</option>
                  <option value="Manutenção">Manutenção</option>
                  <option value="Reforma">Reforma</option>
                </select>
              </div>

              <div class="col-md-6"></div>

              <div class="col-md-12">
                <label class="form-label text-white">
                  Nome do Prédio <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.nome" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Prédio Principal"
                  required
                />
              </div>

              <div class="col-md-12">
                <label class="form-label text-white">Descrição</label>
                <textarea 
                  v-model="form.descricao" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="3"
                  placeholder="Descrição detalhada do prédio..."
                ></textarea>
              </div>
            </div>

            <!-- Localização -->
            <div class="section-header">
              <i class="bi bi-geo-alt me-2"></i>
              Localização
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-12">
                <label class="form-label text-white">Endereço</label>
                <input 
                  v-model="form.endereco" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Rua, número, bairro..."
                />
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Latitude</label>
                <input 
                  v-model="form.latitude" 
                  type="number" 
                  step="0.00000001"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="-23.5505"
                />
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Longitude</label>
                <input 
                  v-model="form.longitude" 
                  type="number" 
                  step="0.00000001"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="-46.6333"
                />
              </div>
            </div>

            <!-- Características -->
            <div class="section-header">
              <i class="bi bi-rulers me-2"></i>
              Características
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label class="form-label text-white">Total de Blocos</label>
                <input 
                  v-model.number="form.total_blocos" 
                  type="number" 
                  min="0"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="0"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Total de Andares</label>
                <input 
                  v-model.number="form.total_andares" 
                  type="number" 
                  min="0"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="0"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Ano de Construção</label>
                <input 
                  v-model.number="form.ano_construcao" 
                  type="number" 
                  min="1800"
                  :max="new Date().getFullYear()"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="2020"
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Área Construída (m²)</label>
                <input 
                  v-model.number="form.area_construida" 
                  type="number" 
                  step="0.01"
                  min="0"
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="1500.00"
                />
              </div>
            </div>

            <!-- Infraestrutura -->
            <div class="section-header">
              <i class="bi bi-gear me-2"></i>
              Infraestrutura
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.acessibilidade" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="acessibilidade"
                  />
                  <label class="form-check-label text-white" for="acessibilidade">
                    <i class="bi bi-universal-access me-1"></i>
                    Acessibilidade
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.elevador" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="elevador"
                  />
                  <label class="form-check-label text-white" for="elevador">
                    <i class="bi bi-arrow-up-square me-1"></i>
                    Elevador
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-check form-switch">
                  <input 
                    v-model="form.ar_condicionado" 
                    class="form-check-input" 
                    type="checkbox" 
                    id="ar_condicionado"
                  />
                  <label class="form-check-label text-white" for="ar_condicionado">
                    <i class="bi bi-wind me-1"></i>
                    Ar Condicionado
                  </label>
                </div>
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
                    <i class="bi bi-wifi me-1"></i>
                    Wi-Fi
                  </label>
                </div>
              </div>
            </div>

            <!-- Observações -->
            <div class="section-header">
              <i class="bi bi-chat-text me-2"></i>
              Observações
            </div>

            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label text-white">Observações Gerais</label>
                <textarea 
                  v-model="form.observacoes" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="3"
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
  predio: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const instituicoes = ref([]); // ⭐ NOVO
const campi = ref([]);
const salvando = ref(false);

const form = reactive({
  instituicao_id: '', // ⭐ NOVO (auxiliar)
  campus_id: '',
  codigo: '',
  nome: '',
  descricao: '',
  endereco: '',
  latitude: null,
  longitude: null,
  total_blocos: 0,
  total_andares: 0,
  ano_construcao: null,
  area_construida: null,
  acessibilidade: false,
  elevador: false,
  ar_condicionado: false,
  wifi: false,
  status: 'Ativo',
  observacoes: ''
});

onMounted(async () => {
  await carregarInstituicoes();
  
  if (props.predio) {
    Object.assign(form, props.predio);
    // Carregar instituição e campi
    if (props.predio.campus?.instituicao_id) {
      form.instituicao_id = props.predio.campus.instituicao_id;
      await carregarCampi();
    }
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

const carregarCampi = async () => {
  if (!form.instituicao_id) {
    campi.value = [];
    form.campus_id = '';
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

const salvar = async () => {
  salvando.value = true;
  try {
    const payload = { ...form };
    delete payload.instituicao_id; // Remover campo auxiliar
    
    if (props.predio) {
      await axios.put(`/api/v1/predios/${props.predio.id}`, payload);
    } else {
      await axios.post('/api/v1/predios', payload);
    }
    emit('success');
  } catch (error) {
    console.error('Erro ao salvar:', error);
    Swal.fire('Erro!', error.response?.data?.message || 'Erro ao salvar prédio', 'error');
  } finally {
    salvando.value = false;
  }
};
</script>

<style scoped>
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

.modal-container {
  width: 100%;
  max-width: 900px;
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

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-check-label {
  cursor: pointer;
}

/* Scrollbar personalizada */
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

.modal-body::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
