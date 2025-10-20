<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content glass-card">
        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-link-45deg me-2"></i>
            {{ isEditing ? 'Editar' : 'Nova' }} Integração
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <!-- Step Indicator -->
          <div class="steps-indicator mb-4">
            <div 
              v-for="(step, index) in steps" 
              :key="index"
              class="step-item"
              :class="{ 
                active: currentStep === index, 
                completed: currentStep > index 
              }"
            >
              <div class="step-number">{{ index + 1 }}</div>
              <div class="step-label">{{ step }}</div>
            </div>
          </div>

          <!-- Step 1: Selecionar Provider -->
          <div v-show="currentStep === 0">
            <h6 class="mb-3">Selecione o Sistema de RH</h6>
            <div class="row g-3">
              <div 
                v-for="(provider, key) in availableProviders" 
                :key="key"
                class="col-md-6"
              >
                <div 
                  class="provider-card"
                  :class="{ 
                    selected: form.provider === key,
                    disabled: !provider.supported 
                  }"
                  @click="provider.supported && selectProvider(key)"
                >
                  <div class="provider-icon">
                    <i :class="provider.icon"></i>
                  </div>
                  <div class="provider-info">
                    <h6>{{ provider.name }}</h6>
                    <p class="text-muted small mb-0">{{ provider.description }}</p>
                  </div>
                  <div v-if="!provider.supported" class="badge bg-warning">
                    Em breve
                  </div>
                  <div v-if="form.provider === key" class="check-icon">
                    <i class="bi bi-check-circle-fill"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Informações Básicas -->
          <div v-show="currentStep === 1">
            <h6 class="mb-3">Informações da Integração</h6>
            
            <div class="mb-3">
              <label class="form-label">Nome da Integração *</label>
              <input 
                v-model="form.name" 
                type="text" 
                class="form-control"
                placeholder="Ex: Integração TOTVS - Matriz"
                required
              >
            </div>

            <div class="mb-3">
              <label class="form-label">Frequência de Sincronização *</label>
              <select v-model="form.sync_frequency" class="form-select" required>
                <option value="manual">Manual</option>
                <option value="hourly">A cada hora</option>
                <option value="daily">Diariamente</option>
                <option value="weekly">Semanalmente</option>
              </select>
              <small class="text-muted">
                Sincronizações automáticas serão executadas em segundo plano
              </small>
            </div>

            <div class="form-check form-switch">
              <input 
                v-model="form.is_active" 
                class="form-check-input" 
                type="checkbox" 
                id="isActive"
              >
              <label class="form-check-label" for="isActive">
                Ativar integração após criar
              </label>
            </div>
          </div>

          <!-- Step 3: Configuração de Conexão -->
          <div v-show="currentStep === 2">
            <h6 class="mb-3">Configuração de Conexão</h6>
            
            <div v-if="loadingFields" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
              </div>
            </div>

            <div v-else>
              <div 
                v-for="field in configFields" 
                :key="field.name"
                class="mb-3"
              >
                <label class="form-label">
                  {{ field.label }}
                  <span v-if="field.required" class="text-danger">*</span>
                </label>

                <!-- Text Input -->
                <input 
                  v-if="field.type === 'text'"
                  v-model="form.config[field.name]"
                  type="text"
                  class="form-control"
                  :placeholder="field.placeholder"
                  :required="field.required"
                >

                <!-- Password Input -->
                <input 
                  v-else-if="field.type === 'password'"
                  v-model="form.config[field.name]"
                  type="password"
                  class="form-control"
                  :placeholder="field.placeholder"
                  :required="field.required"
                >

                <!-- Select Input -->
                <select 
                  v-else-if="field.type === 'select'"
                  v-model="form.config[field.name]"
                  class="form-select"
                  :required="field.required"
                >
                  <option value="">Selecione...</option>
                  <option 
                    v-for="(label, value) in field.options" 
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>

                <!-- Help Text -->
                <small v-if="field.help" class="text-muted d-block mt-1">
                  {{ field.help }}
                </small>
              </div>
            </div>
          </div>

          <!-- Step 4: Opções de Sincronização -->
          <div v-show="currentStep === 3">
            <h6 class="mb-3">Opções de Sincronização</h6>
            
            <div class="mb-3">
              <label class="form-label">O que sincronizar?</label>
              <div class="form-check">
                <input 
                  v-model="form.sync_options.colaboradores" 
                  class="form-check-input" 
                  type="checkbox" 
                  id="syncColaboradores"
                >
                <label class="form-check-label" for="syncColaboradores">
                  Colaboradores
                </label>
              </div>
              <div class="form-check">
                <input 
                  v-model="form.sync_options.estrutura" 
                  class="form-check-input" 
                  type="checkbox" 
                  id="syncEstrutura"
                >
                <label class="form-check-label" for="syncEstrutura">
                  Estrutura Organizacional (Setores, Cargos)
                </label>
              </div>
            </div>

            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>
              Você poderá configurar mapeamento de campos detalhado após criar a integração.
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button 
            v-if="currentStep > 0"
            type="button" 
            class="btn btn-secondary"
            @click="previousStep"
          >
            <i class="bi bi-arrow-left me-2"></i>
            Voltar
          </button>

          <button 
            v-if="currentStep < steps.length - 1"
            type="button" 
            class="btn btn-primary"
            @click="nextStep"
            :disabled="!canProceed"
          >
            Próximo
            <i class="bi bi-arrow-right ms-2"></i>
          </button>

          <button 
            v-if="currentStep === steps.length - 1"
            type="button" 
            class="btn btn-success"
            @click="saveIntegration"
            :disabled="saving"
          >
            <span v-if="saving">
              <span class="spinner-border spinner-border-sm me-2"></span>
              Salvando...
            </span>
            <span v-else>
              <i class="bi bi-check-lg me-2"></i>
              Criar Integração
            </span>
          </button>

          <button 
            type="button" 
            class="btn btn-outline-secondary"
            @click="$emit('close')"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'saved']);

const props = defineProps({
  integrationId: {
    type: Number,
    default: null
  }
});

// State
const currentStep = ref(0);
const steps = ref(['Provider', 'Informações', 'Conexão', 'Opções']);
const availableProviders = ref({});
const configFields = ref([]);
const loadingFields = ref(false);
const saving = ref(false);

const form = ref({
  name: '',
  provider: '',
  config: {},
  sync_frequency: 'manual',
  is_active: false,
  sync_options: {
    colaboradores: true,
    estrutura: false
  }
});

// Computed
const isEditing = computed(() => props.integrationId !== null);

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 0:
      return form.value.provider !== '';
    case 1:
      return form.value.name.trim() !== '';
    case 2:
      return validateConfigFields();
    case 3:
      return true;
    default:
      return false;
  }
});

// Methods
const loadProviders = async () => {
  try {
    const response = await axios.get('/api/v1/hr/integrations/providers');
    availableProviders.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar providers:', error);
  }
};

const selectProvider = (providerKey) => {
  form.value.provider = providerKey;
};

const loadConfigFields = async () => {
  if (!form.value.provider) return;

  try {
    loadingFields.value = true;
    const response = await axios.get(
      `/api/v1/hr/integrations/providers/${form.value.provider}/config-fields`
    );
    configFields.value = response.data;
    
    // Inicializar config com valores padrão
    configFields.value.forEach(field => {
      if (field.default && !form.value.config[field.name]) {
        form.value.config[field.name] = field.default;
      }
    });
  } catch (error) {
    console.error('Erro ao carregar campos:', error);
  } finally {
    loadingFields.value = false;
  }
};

const validateConfigFields = () => {
  // Verificar se todos os campos obrigatórios estão preenchidos
  return configFields.value
    .filter(field => field.required)
    .every(field => {
      const value = form.value.config[field.name];
      return value && value.toString().trim() !== '';
    });
};

const nextStep = () => {
  if (canProceed.value && currentStep.value < steps.value.length - 1) {
    currentStep.value++;
    
    // Carregar campos de configuração ao entrar no step 2
    if (currentStep.value === 2) {
      loadConfigFields();
    }
  }
};

const previousStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
};

const saveIntegration = async () => {
  try {
    saving.value = true;

    const payload = {
      name: form.value.name,
      provider: form.value.provider,
      config: form.value.config,
      sync_frequency: form.value.sync_frequency,
      is_active: form.value.is_active,
      sync_options: form.value.sync_options
    };

    if (isEditing.value) {
      await axios.put(`/api/v1/hr/integrations/${props.integrationId}`, payload);
    } else {
      await axios.post('/api/v1/hr/integrations', payload);
    }

    emit('saved');
    emit('close');
  } catch (error) {
    console.error('Erro ao salvar integração:', error);
    alert('Erro ao salvar integração: ' + error.response?.data?.message || error.message);
  } finally {
    saving.value = false;
  }
};

// Watchers
watch(() => form.value.provider, () => {
  // Limpar configurações ao mudar provider
  form.value.config = {};
  configFields.value = [];
});

// Lifecycle
onMounted(() => {
  loadProviders();
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
  padding: 1rem;
}

.modal-dialog {
  max-width: 800px;
  width: 100%;
}

.modal-content {
  border-radius: 20px;
  overflow: hidden;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-body {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: 1rem 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

/* Steps Indicator */
.steps-indicator {
  display: flex;
  justify-content: space-between;
  position: relative;
  margin-bottom: 2rem;
}

.steps-indicator::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 0;
  right: 0;
  height: 2px;
  background: rgba(255, 255, 255, 0.2);
  z-index: 0;
}

.step-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 1;
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
  font-weight: bold;
  margin-bottom: 0.5rem;
  transition: all 0.3s ease;
}

.step-item.active .step-number {
  background: #667EEA;
  border-color: #667EEA;
  box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
}

.step-item.completed .step-number {
  background: #10B981;
  border-color: #10B981;
}

.step-label {
  font-size: 0.875rem;
  text-align: center;
  opacity: 0.7;
  color: rgba(255, 255, 255, 0.7);
}

.step-item.active .step-label {
  opacity: 1;
  font-weight: 600;
  color: white;
}

/* Provider Cards */
.provider-card {
  padding: 1.5rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.provider-card:hover:not(.disabled) {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(102, 126, 234, 0.5);
  transform: translateY(-2px);
}

.provider-card.selected {
  background: rgba(102, 126, 234, 0.2);
  border-color: #667EEA;
}

.provider-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.provider-icon {
  font-size: 2rem;
  color: #667EEA;
}

.provider-info h6 {
  margin-bottom: 0.5rem; /* ✅ Aumentar espaçamento */
  font-weight: 600;
  font-size: 1.1rem; /* ✅ Aumentar tamanho */
  color: white !important; /* ✅ Forçar branco */
}

.provider-info p {
  font-size: 0.875rem;
  line-height: 1.4;
  color: rgba(255, 255, 255, 0.7) !important; /* ✅ Aumentar opacidade */
  margin-bottom: 0;
}

.check-icon {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  color: #10B981;
}

/* Form Styles */
.form-control,
.form-select {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.form-control:focus,
.form-select:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: #667EEA;
  color: white;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-select option {
  background: #1a1a2e;
  color: white;
}

.form-check-input:checked {
  background-color: #667EEA;
  border-color: #667EEA;
}

/* ================================
   TÍTULOS E LABELS
   ================================ */
h6 {
  color: white; /* ✅ ADICIONAR */
}

.form-label {
  color: rgba(255, 255, 255, 0.9); /* ✅ ADICIONAR */
  font-weight: 500;
}

/* ================================
   SMALL TEXT
   ================================ */
small.text-muted {
  color: rgba(255, 255, 255, 0.5) !important; /* ✅ ADICIONAR */
}

/* ================================
   CHECKBOX E RADIO LABELS
   ================================ */
.form-check-label {
  color: rgba(255, 255, 255, 0.9); /* ✅ ADICIONAR */
}

/* ================================
   ALERT INFO
   ================================ */
.alert-info {
  color: rgba(255, 255, 255, 0.9); /* ✅ ADICIONAR */
}

/* ================================
   BADGE EM BREVE
   ================================ */
.badge.bg-warning {
  color: #1a1a2e; /* ✅ TEXTO ESCURO NO BADGE AMARELO */
  font-weight: 600;
}
</style>