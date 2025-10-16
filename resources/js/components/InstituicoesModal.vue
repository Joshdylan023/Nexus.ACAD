<template>
  <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" @click.self="$emit('close')">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass">
        <div class="modal-header border-0">
          <div>
            <h5 class="modal-title text-white mb-1">
              <i class="fas fa-building me-2"></i>Gerenciar Instituições com Acesso
            </h5>
            <p class="text-white-50 small mb-0">Selecione as instituições que o colaborador terá acesso</p>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-light" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>

          <div v-else class="instituicoes-list">
            <div
              v-for="instituicao in instituicoes"
              :key="instituicao.id"
              class="instituicao-item"
              :class="{ 'selected': isSelected(instituicao.id) }"
              @click="toggleInstituicao(instituicao.id)"
            >
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :checked="isSelected(instituicao.id)"
                  :id="`inst-${instituicao.id}`"
                  @click.stop
                >
                <label class="form-check-label w-100" :for="`inst-${instituicao.id}`">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong class="text-white">{{ instituicao.sigla }}</strong>
                      <span class="text-white-50 ms-2">{{ instituicao.nome }}</span>
                    </div>
                    <i v-if="isSelected(instituicao.id)" class="fas fa-check-circle text-success"></i>
                  </div>
                </label>
              </div>
            </div>

            <div v-if="instituicoes.length === 0" class="text-center py-5">
              <i class="fas fa-info-circle fa-3x text-white-50 mb-3"></i>
              <p class="text-white-50">Nenhuma instituição disponível</p>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <div class="d-flex justify-content-between w-100 align-items-center">
            <span class="text-white-50 small">
              <i class="fas fa-check-double me-2"></i>{{ selectedInstituicoes.length }} selecionada(s)
            </span>
            <div>
              <button type="button" class="btn btn-secondary me-2" @click="$emit('close')">
                <i class="fas fa-times me-2"></i>Cancelar
              </button>
              <button type="button" class="btn btn-success" @click="saveInstituicoes" :disabled="saving">
                <span v-if="saving">
                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                  Salvando...
                </span>
                <span v-else>
                  <i class="fas fa-save me-2"></i>Salvar Alterações
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import axios from 'axios';

const props = defineProps({
  colaboradorId: {
    type: Number,
    required: true
  },
  currentInstituicoes: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'saved']);

const instituicoes = ref([]);
const selectedInstituicoes = ref([]);
const loading = ref(true);
const saving = ref(false);

onMounted(async () => {
  await loadInstituicoes();
  selectedInstituicoes.value = props.currentInstituicoes.map(i => i.id);
});

const loadInstituicoes = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/gestao-acessos/instituicoes');
    instituicoes.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
    alert('Erro ao carregar instituições. Tente novamente.');
  } finally {
    loading.value = false;
  }
};

const isSelected = (id) => {
  return selectedInstituicoes.value.includes(id);
};

const toggleInstituicao = (id) => {
  const index = selectedInstituicoes.value.indexOf(id);
  if (index > -1) {
    selectedInstituicoes.value.splice(index, 1);
  } else {
    selectedInstituicoes.value.push(id);
  }
};

const saveInstituicoes = async () => {
  try {
    saving.value = true;
    await axios.post(`/api/v1/gestao-acessos/colaboradores/${props.colaboradorId}/instituicoes`, {
      instituicoes: selectedInstituicoes.value
    });
    emit('saved');
  } catch (error) {
    console.error('Erro ao salvar instituições:', error);
    alert('Erro ao salvar instituições. Tente novamente.');
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.modal-body {
  max-height: 500px;
  overflow-y: auto;
}

.instituicoes-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.instituicao-item {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.instituicao-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(5px);
  border-color: rgba(255, 255, 255, 0.2);
}

.instituicao-item.selected {
  background: rgba(25, 135, 84, 0.25);
  border-color: rgba(25, 135, 84, 0.6);
  box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
}

.instituicao-item.selected:hover {
  background: rgba(25, 135, 84, 0.35);
}

.form-check {
  margin: 0;
  padding: 0;
}

.form-check-input {
  cursor: pointer;
  width: 1.25rem;
  height: 1.25rem;
  margin-right: 0.75rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  background-color: rgba(255, 255, 255, 0.1);
}

.form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
}

.form-check-label {
  cursor: pointer;
  color: white;
  margin: 0;
  display: flex;
  align-items: center;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}

/* Scrollbar customizada */
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
