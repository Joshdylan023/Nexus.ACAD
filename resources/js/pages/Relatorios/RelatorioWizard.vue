<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="wizard-modal card-glass">
      <!-- Header -->
      <div class="wizard-header">
        <h4 class="text-white mb-0">
          <i class="bi bi-magic me-2"></i>
          {{ isEditing ? 'Editar Relatório' : 'Criar Novo Relatório' }}
        </h4>
        <button @click="$emit('close')" class="btn-close btn-close-white"></button>
      </div>

      <!-- Progress Stepper -->
      <div class="wizard-stepper">
        <div 
          v-for="(step, index) in steps" 
          :key="index"
          :class="['wizard-step', { 
            'active': currentStep === index + 1, 
            'completed': currentStep > index + 1 
          }]"
        >
          <div class="step-number">
            <i v-if="currentStep > index + 1" class="bi bi-check-lg"></i>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <div class="step-label">{{ step }}</div>
        </div>
      </div>

      <!-- Steps Content -->
      <div class="wizard-body">
        <!-- Step 1: Configurações Básicas -->
        <div v-show="currentStep === 1" class="wizard-step-content">
          <h5 class="text-white mb-4">Configurações Básicas</h5>
          
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label text-white">Nome do Relatório *</label>
              <input 
                v-model="form.name" 
                type="text" 
                class="form-control bg-dark text-white border-secondary"
                placeholder="Ex: Relatório de Colaboradores Ativos"
                required
              >
            </div>

            <div class="col-12">
              <label class="form-label text-white">Descrição</label>
              <textarea 
                v-model="form.description" 
                class="form-control bg-dark text-white border-secondary"
                rows="3"
                placeholder="Descreva o objetivo deste relatório..."
              ></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label text-white">Tipo de Dados *</label>
              <select 
                v-model="form.type" 
                @change="loadAvailableColumns"
                class="form-select bg-dark text-white border-secondary"
                required
              >
                <option value="">Selecione o tipo</option>
                <option value="colaboradores">Colaboradores</option>
                <option value="institucional">Instituições</option>
                <option value="academico">Cursos</option>
                <option value="setores">Setores</option>
                <option value="campi">Campi</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label text-white">Formato de Exportação *</label>
              <select 
                v-model="form.format" 
                class="form-select bg-dark text-white border-secondary"
                required
              >
                <option value="excel">Excel (.xlsx)</option>
                <option value="csv">CSV</option>
                <option value="pdf">PDF</option>
              </select>
            </div>

            <div class="col-12">
              <div class="form-check">
                <input 
                  v-model="form.is_public" 
                  class="form-check-input" 
                  type="checkbox" 
                  id="isPublic"
                >
                <label class="form-check-label text-white" for="isPublic">
                  <i class="bi bi-globe me-2"></i>
                  Tornar este relatório público (visível para todos os usuários)
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Seleção de Colunas -->
        <div v-show="currentStep === 2" class="wizard-step-content">
          <h5 class="text-white mb-4">Selecione as Colunas</h5>
          
          <div v-if="!form.type" class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Selecione o tipo de dados no passo anterior
          </div>

          <div v-else class="row">
            <!-- Colunas Disponíveis -->
            <div class="col-md-6">
              <div class="columns-panel">
                <h6 class="text-white mb-3">
                  <i class="bi bi-list-ul me-2"></i>
                  Colunas Disponíveis
                </h6>
                <div class="columns-list">
                  <div 
                    v-for="(label, field) in availableColumns" 
                    :key="field"
                    v-show="!isColumnSelected(field)"
                    class="column-item"
                    @click="addColumn(field, label)"
                  >
                    <i class="bi bi-plus-circle me-2"></i>
                    {{ label }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Colunas Selecionadas -->
            <div class="col-md-6">
              <div class="columns-panel">
                <h6 class="text-white mb-3">
                  <i class="bi bi-check2-square me-2"></i>
                  Colunas Selecionadas ({{ form.columns.length }})
                </h6>
                <div class="columns-list">
                  <div 
                    v-for="(column, index) in form.columns" 
                    :key="column"
                    class="column-item selected"
                  >
                    <div class="d-flex justify-content-between align-items-center">
                      <span>
                        <i class="bi bi-grip-vertical me-2"></i>
                        {{ getColumnLabel(column) }}
                      </span>
                      <button 
                        @click="removeColumn(index)"
                        class="btn btn-sm btn-outline-danger"
                      >
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div v-if="form.columns.length === 0" class="text-white-50 text-center py-4">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    Nenhuma coluna selecionada
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Filtros -->
        <div v-show="currentStep === 3" class="wizard-step-content">
          <h5 class="text-white mb-4">Filtros (Opcional)</h5>
          
          <div class="row g-3">
            <!-- Filtros para Colaboradores -->
            <template v-if="form.type === 'colaboradores'">
              <div class="col-md-6">
                <label class="form-label text-white">Status</label>
                <select v-model="form.filters.status" class="form-select bg-dark text-white border-secondary">
                  <option value="">Todos</option>
                  <option value="Ativo">Ativo</option>
                  <option value="Afastado">Afastado</option>
                  <option value="Desligado">Desligado</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">É Gestor?</label>
                <select v-model="form.filters.is_gestor" class="form-select bg-dark text-white border-secondary">
                  <option value="">Todos</option>
                  <option value="true">Sim</option>
                  <option value="false">Não</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Data Admissão (Início)</label>
                <input 
                  v-model="form.filters.data_admissao_inicio" 
                  type="date" 
                  class="form-control bg-dark text-white border-secondary"
                >
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Data Admissão (Fim)</label>
                <input 
                  v-model="form.filters.data_admissao_fim" 
                  type="date" 
                  class="form-control bg-dark text-white border-secondary"
                >
              </div>
            </template>

            <!-- Filtros para Instituições -->
            <template v-if="form.type === 'institucional'">
              <div class="col-md-6">
                <label class="form-label text-white">Estado</label>
                <select v-model="form.filters.estado" class="form-select bg-dark text-white border-secondary">
                  <option value="">Todos</option>
                  <option value="PA">Pará</option>
                  <option value="SP">São Paulo</option>
                  <option value="RJ">Rio de Janeiro</option>
                  <!-- Adicionar mais estados -->
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-white">Tipo de Organização</label>
                <select v-model="form.filters.tipo_organizacao" class="form-select bg-dark text-white border-secondary">
                  <option value="">Todos</option>
                  <option value="Faculdade">Faculdade</option>
                  <option value="Universidade">Universidade</option>
                  <option value="Centro Universitário">Centro Universitário</option>
                </select>
              </div>
            </template>

            <!-- Filtros para Setores -->
            <template v-if="form.type === 'setores'">
              <div class="col-md-6">
                <label class="form-label text-white">Tipo de Setor</label>
                <select v-model="form.filters.tipo" class="form-select bg-dark text-white border-secondary">
                  <option value="">Todos</option>
                  <option value="Corporativo">Corporativo</option>
                  <option value="Acadêmico">Acadêmico</option>
                  <option value="Administrativo">Administrativo</option>
                </select>
              </div>
            </template>

            <div class="col-12">
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Os filtros podem ser ajustados na hora da execução do relatório
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="wizard-footer">
        <button 
          v-if="currentStep > 1"
          @click="previousStep" 
          class="btn btn-outline-light"
        >
          <i class="bi bi-arrow-left me-2"></i>
          Voltar
        </button>

        <div class="ms-auto d-flex gap-2">
          <button @click="$emit('close')" class="btn btn-outline-secondary">
            Cancelar
          </button>

          <button 
            v-if="currentStep < 3"
            @click="nextStep" 
            class="btn btn-primary"
            :disabled="!canProceed"
          >
            Próximo
            <i class="bi bi-arrow-right ms-2"></i>
          </button>

          <button 
            v-else
            @click="saveReport" 
            class="btn btn-success"
            :disabled="saving || !canSave"
          >
            <span v-if="saving">
              <span class="spinner-border spinner-border-sm me-2"></span>
              Salvando...
            </span>
            <span v-else>
              <i class="bi bi-check-lg me-2"></i>
              Salvar Relatório
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  report: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const steps = ['Configurações', 'Colunas', 'Filtros'];
const currentStep = ref(1);
const saving = ref(false);

const availableColumns = ref({});

const form = ref({
  name: '',
  description: '',
  type: '',
  format: 'excel',
  columns: [],
  filters: {},
  is_public: false,
  is_scheduled: false,
  schedule_frequency: null
});

const isEditing = computed(() => !!props.report);

const canProceed = computed(() => {
  if (currentStep.value === 1) {
    return form.value.name && form.value.type && form.value.format;
  }
  if (currentStep.value === 2) {
    return form.value.columns.length > 0;
  }
  return true;
});

const canSave = computed(() => {
  return form.value.name && 
         form.value.type && 
         form.value.format && 
         form.value.columns.length > 0;
});

const loadAvailableColumns = async () => {
  if (!form.value.type) return;
  
  try {
    const response = await axios.get(`/api/v1/reports/columns/${form.value.type}`);
    availableColumns.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar colunas:', error);
  }
};

const isColumnSelected = (field) => {
  return form.value.columns.includes(field);
};

const addColumn = (field, label) => {
  if (!isColumnSelected(field)) {
    form.value.columns.push(field);
  }
};

const removeColumn = (index) => {
  form.value.columns.splice(index, 1);
};

const getColumnLabel = (field) => {
  return availableColumns.value[field] || field;
};

const nextStep = () => {
  if (canProceed.value && currentStep.value < 3) {
    currentStep.value++;
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const saveReport = async () => {
  if (!canSave.value) return;
  
  saving.value = true;
  try {
    const url = isEditing.value 
      ? `/api/v1/reports/${props.report.id}`
      : '/api/v1/reports';
    
    const method = isEditing.value ? 'put' : 'post';
    
    await axios[method](url, form.value);
    
    emit('saved');
  } catch (error) {
    console.error('Erro ao salvar relatório:', error);
    alert('Erro ao salvar relatório. Verifique os dados e tente novamente.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  if (isEditing.value) {
    form.value = { ...props.report };
    loadAvailableColumns();
  }
});

watch(() => form.value.type, () => {
  if (form.value.type) {
    loadAvailableColumns();
  }
});
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  overflow-y: auto;
  padding: 2rem;
}

.wizard-modal {
  background: rgba(26, 26, 46, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.wizard-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.wizard-stepper {
  display: flex;
  justify-content: space-between;
  padding: 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.wizard-step {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.wizard-step:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 20px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: rgba(255, 255, 255, 0.2);
  z-index: 0;
}

.wizard-step.completed:not(:last-child)::after {
  background: #4caf50;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  margin-bottom: 0.5rem;
  z-index: 1;
  transition: all 0.3s;
}

.wizard-step.active .step-number {
  background: #667EEA;
  border-color: #667EEA;
  transform: scale(1.1);
}

.wizard-step.completed .step-number {
  background: #4caf50;
  border-color: #4caf50;
}

.step-label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.875rem;
  text-align: center;
}

.wizard-step.active .step-label {
  color: white;
  font-weight: 600;
}

.wizard-body {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
}

.wizard-step-content {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.wizard-footer {
  padding: 1.5rem 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  gap: 1rem;
}

.columns-panel {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 1rem;
  height: 400px;
  display: flex;
  flex-direction: column;
}

.columns-list {
  flex: 1;
  overflow-y: auto;
}

.column-item {
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  margin-bottom: 0.5rem;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.column-item:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: #667EEA;
  transform: translateX(5px);
}

.column-item.selected {
  background: rgba(102, 126, 234, 0.2);
  border-color: #667EEA;
  cursor: default;
}

.column-item.selected:hover {
  transform: none;
}

.form-control, .form-select {
  background: #1a1a2e !important;
  color: white !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

.form-control:focus, .form-select:focus {
  background: #1a1a2e !important;
  color: white !important;
  border-color: #667EEA !important;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-select option {
  background: #1a1a2e;
  color: white;
}
</style>
