<template>
  <teleport to="body">
    <div class="modal-overlay" @click="$emit('close')">
      <div class="modal-container card-glass" @click.stop>
        <div class="modal-header">
          <h4 class="text-white mb-0">
            <i class="bi bi-layers me-2"></i>
            {{ bloco ? 'Editar Bloco' : 'Novo Bloco' }}
          </h4>
          <button @click="$emit('close')" class="btn-close btn-close-white"></button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="salvar">
            <div class="row g-3">
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
                  <option v-for="campus in campi" :key="campus?.id" :value="campus?.id">
                    {{ campus?.nome }}
                  </option>
                </select>
              </div>

              <!-- Prédio -->
              <div class="col-md-12">
                <label class="form-label text-white">
                  Prédio <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.predio_id" 
                  class="form-select bg-transparent text-white border-secondary"
                  :disabled="!form.campus_id"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="predio in predios" :key="predio?.id" :value="predio?.id">
                    {{ predio?.nome }}
                  </option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label text-white">
                  Código <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.codigo" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="BLC-A"
                  required
                />
              </div>

              <div class="col-md-5">
                <label class="form-label text-white">
                  Nome <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.nome" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Bloco A"
                  required
                />
              </div>

              <div class="col-md-3">
                <label class="form-label text-white">Status</label>
                <select 
                  v-model="form.status" 
                  class="form-select bg-transparent text-white border-secondary"
                >
                  <option value="Ativo">Ativo</option>
                  <option value="Inativo">Inativo</option>
                </select>
              </div>

              <div class="col-md-12">
                <label class="form-label text-white">Descrição</label>
                <textarea 
                  v-model="form.descricao" 
                  class="form-control bg-transparent text-white border-secondary"
                  rows="3"
                  placeholder="Descrição do bloco..."
                ></textarea>
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button @click="$emit('close')" type="button" class="btn btn-secondary">
            <i class="bi bi-x-circle me-1"></i> Cancelar
          </button>
          <button @click="salvar" type="button" class="btn btn-primary" :disabled="salvando">
            <span v-if="salvando">
              <span class="spinner-border spinner-border-sm me-1"></span>
              Salvando...
            </span>
            <span v-else>
              <i class="bi bi-check-circle me-1"></i> Salvar
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
  bloco: { type: Object, default: null }
});

const emit = defineEmits(['close', 'success']);

const salvando = ref(false);
const instituicoes = ref([]); // ⭐ NOVO
const campi = ref([]);
const predios = ref([]);

const form = reactive({
  instituicao_id: '', // ⭐ NOVO (auxiliar)
  campus_id: '', // (auxiliar)
  predio_id: '',
  codigo: '',
  nome: '',
  descricao: '',
  status: 'Ativo'
});

onMounted(async () => {
  await carregarInstituicoes();
  
  if (props.bloco) {
    Object.assign(form, props.bloco);
    // Se tem prédio, carregar a hierarquia completa
    if (props.bloco.predio) {
      // Carregar campus do prédio
      if (props.bloco.predio.campus_id) {
        form.campus_id = props.bloco.predio.campus_id;
        
        // Buscar instituição do campus
        try {
          const { data: campusData } = await axios.get(`/api/v1/campi/${props.bloco.predio.campus_id}`);
          if (campusData.instituicao_id) {
            form.instituicao_id = campusData.instituicao_id;
            await carregarCampi();
          }
        } catch (error) {
          console.error('Erro ao carregar campus:', error);
        }
        
        await carregarPredios();
      }
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

// ⭐ NOVO
const carregarCampi = async () => {
  if (!form.instituicao_id) {
    campi.value = [];
    form.campus_id = '';
    predios.value = [];
    form.predio_id = '';
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

// Carregar prédios filtrados pelo campus
const carregarPredios = async () => {
  if (!form.campus_id) {
    predios.value = [];
    form.predio_id = '';
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

const salvar = async () => {
  salvando.value = true;
  try {
    // Remover campos auxiliares antes de salvar
    const payload = { ...form };
    delete payload.instituicao_id;
    delete payload.campus_id;
    
    if (props.bloco) {
      await axios.put(`/api/v1/blocos/${props.bloco.id}`, payload);
    } else {
      await axios.post('/api/v1/blocos', payload);
    }
    emit('success');
  } catch (error) {
    console.error('Erro ao salvar:', error);
    Swal.fire('Erro!', error.response?.data?.message || 'Erro ao salvar bloco', 'error');
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
}

.modal-container {
  width: 100%;
  max-width: 700px;
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

.modal-header, .modal-footer {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  border-bottom: none;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
