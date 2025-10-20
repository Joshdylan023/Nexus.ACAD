<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content glass-card">
        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-journal-text me-2"></i>
            Detalhes da Sincronização
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <!-- Loading -->
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>

          <!-- Content -->
          <div v-else-if="log">
            <!-- Status Banner -->
            <div class="alert" :class="getStatusAlertClass(log.status)">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                  <i :class="getStatusIcon(log.status)" style="font-size: 2rem;"></i>
                  <div>
                    <h5 class="mb-1">{{ getStatusText(log.status) }}</h5>
                    <p class="mb-0">{{ log.message || 'Sincronização executada' }}</p>
                  </div>
                </div>
                <div class="text-end">
                  <div class="small opacity-75">ID: #{{ log.id }}</div>
                  <div class="small opacity-75">{{ formatDate(log.created_at) }}</div>
                </div>
              </div>
            </div>

            <!-- Informações Gerais -->
            <div class="glass-card mb-4">
              <h6 class="mb-3">
                <i class="bi bi-info-circle me-2"></i>Informações Gerais
              </h6>
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Tipo</span>
                  <span class="info-value">
                    <span class="badge bg-secondary">{{ getTypeText(log.type) }}</span>
                  </span>
                </div>
                <div class="info-item">
                  <span class="info-label">Disparado por</span>
                  <span class="info-value">
                    <i :class="getTriggerIcon(log.trigger_type)" class="me-2"></i>
                    {{ log.triggered_by?.name || 'Sistema' }}
                  </span>
                </div>
                <div class="info-item">
                  <span class="info-label">Iniciado em</span>
                  <span class="info-value">{{ formatDate(log.started_at) }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Concluído em</span>
                  <span class="info-value">{{ log.completed_at ? formatDate(log.completed_at) : 'Em andamento' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Duração</span>
                  <span class="info-value">{{ formatDuration(log.duration_seconds) }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Taxa de Sucesso</span>
                  <span class="info-value">
                    <span class="badge bg-success">{{ log.success_rate }}%</span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Métricas -->
            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <div class="metric-card">
                  <div class="metric-icon bg-primary">
                    <i class="bi bi-database"></i>
                  </div>
                  <div class="metric-info">
                    <div class="metric-label">Total</div>
                    <div class="metric-value">{{ log.records_total }}</div>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="metric-card">
                  <div class="metric-icon bg-success">
                    <i class="bi bi-plus-circle"></i>
                  </div>
                  <div class="metric-info">
                    <div class="metric-label">Criados</div>
                    <div class="metric-value">{{ log.records_created }}</div>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="metric-card">
                  <div class="metric-icon bg-info">
                    <i class="bi bi-arrow-repeat"></i>
                  </div>
                  <div class="metric-info">
                    <div class="metric-label">Atualizados</div>
                    <div class="metric-value">{{ log.records_updated }}</div>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="metric-card">
                  <div class="metric-icon bg-danger">
                    <i class="bi bi-x-circle"></i>
                  </div>
                  <div class="metric-info">
                    <div class="metric-label">Falharam</div>
                    <div class="metric-value">{{ log.records_failed }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Erros -->
            <div v-if="log.sync_errors && log.sync_errors.length > 0" class="glass-card mb-4">
              <h6 class="mb-3 text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Erros ({{ log.sync_errors.length }})
              </h6>
              
              <div class="accordion" id="errorsAccordion">
                <div 
                  v-for="(error, index) in log.sync_errors" 
                  :key="error.id"
                  class="accordion-item"
                >
                  <h2 class="accordion-header">
                    <button 
                      class="accordion-button collapsed"
                      type="button"
                      data-bs-toggle="collapse"
                      :data-bs-target="'#error-' + error.id"
                    >
                      <div class="d-flex align-items-center gap-3 w-100">
                        <span class="badge bg-danger">{{ error.entity_type }}</span>
                        <span class="flex-grow-1">{{ error.error_message }}</span>
                        <span v-if="error.is_resolved" class="badge bg-success">
                          <i class="bi bi-check-circle me-1"></i>Resolvido
                        </span>
                      </div>
                    </button>
                  </h2>
                  <div 
                    :id="'error-' + error.id"
                    class="accordion-collapse collapse"
                    data-bs-parent="#errorsAccordion"
                  >
                    <div class="accordion-body">
                      <div class="error-details">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <strong>Tipo de Entidade:</strong> {{ error.entity_type }}
                          </div>
                          <div class="col-md-6" v-if="error.entity_id">
                            <strong>ID Externo:</strong> {{ error.entity_id }}
                          </div>
                          <div class="col-md-6" v-if="error.error_code">
                            <strong>Código do Erro:</strong> {{ error.error_code }}
                          </div>
                          <div class="col-12">
                            <strong>Mensagem:</strong>
                            <p class="text-danger mb-0">{{ error.error_message }}</p>
                          </div>
                          <div class="col-12" v-if="error.error_context">
                            <strong>Contexto:</strong>
                            <pre class="context-code">{{ JSON.stringify(error.error_context, null, 2) }}</pre>
                          </div>
                          <div class="col-12" v-if="error.is_resolved">
                            <div class="alert alert-success mb-0">
                              <strong>Resolvido por:</strong> {{ error.resolver?.name || 'N/A' }}<br>
                              <strong>Em:</strong> {{ formatDate(error.resolved_at) }}<br>
                              <strong>Nota:</strong> {{ error.resolution_note }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resumo -->
            <div v-if="log.summary" class="glass-card">
              <h6 class="mb-3">
                <i class="bi bi-file-text me-2"></i>Resumo da Sincronização
              </h6>
              <pre class="summary-code">{{ JSON.stringify(log.summary, null, 2) }}</pre>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  logId: {
    type: Number,
    required: true
  },
  integrationId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['close']);

// State
const log = ref(null);
const loading = ref(true);

// Methods
const loadLogDetails = async () => {
  try {
    loading.value = true;
    const response = await axios.get(
      `/api/v1/hr/integrations/${props.integrationId}/logs/${props.logId}`
    );
    log.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar detalhes do log:', error);
    alert('Erro ao carregar detalhes do log');
    emit('close');
  } finally {
    loading.value = false;
  }
};

// Helpers
const getStatusAlertClass = (status) => {
  const classes = {
    completed: 'alert-success',
    failed: 'alert-danger',
    processing: 'alert-warning',
    pending: 'alert-info'
  };
  return classes[status] || 'alert-secondary';
};

const getStatusIcon = (status) => {
  const icons = {
    completed: 'bi-check-circle-fill',
    failed: 'bi-x-circle-fill',
    processing: 'bi-arrow-repeat',
    pending: 'bi-clock-fill'
  };
  return icons[status] || 'bi-info-circle-fill';
};

const getStatusText = (status) => {
  const texts = {
    completed: 'Sincronização Concluída com Sucesso',
    failed: 'Sincronização Falhou',
    processing: 'Sincronização em Andamento',
    pending: 'Sincronização Pendente'
  };
  return texts[status] || status;
};

const getTypeText = (type) => {
  const texts = {
    colaboradores: 'Colaboradores',
    estrutura: 'Estrutura Organizacional',
    completo: 'Sincronização Completa'
  };
  return texts[type] || type;
};

const getTriggerIcon = (trigger) => {
  const icons = {
    manual: 'bi-person',
    scheduled: 'bi-clock',
    webhook: 'bi-link-45deg'
  };
  return icons[trigger] || 'bi-gear';
};

const formatDuration = (seconds) => {
  if (!seconds) return '-';
  const minutes = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return minutes > 0 ? `${minutes}m ${secs}s` : `${secs}s`;
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
};

// Lifecycle
onMounted(() => {
  loadLogDetails();
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
  max-width: 1200px;
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
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
}

.info-value {
  font-weight: 600;
}

/* Metric Cards */
.metric-card {
  padding: 1.25rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.metric-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.metric-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.25rem;
}

.metric-value {
  font-size: 1.75rem;
  font-weight: 700;
}

/* Accordion */
.accordion {
  --bs-accordion-bg: rgba(255, 255, 255, 0.05);
  --bs-accordion-border-color: rgba(255, 255, 255, 0.1);
  --bs-accordion-btn-color: white;
  --bs-accordion-active-bg: rgba(255, 255, 255, 0.1);
  --bs-accordion-active-color: white;
}

.accordion-button:not(.collapsed) {
  box-shadow: none;
}

.accordion-button:focus {
  box-shadow: none;
  border-color: rgba(255, 255, 255, 0.1);
}

.accordion-item {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 0.5rem;
  border-radius: 8px;
  overflow: hidden;
}

.error-details {
  padding: 1rem;
}

/* Code Blocks */
.context-code,
.summary-code {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
  color: #10B981;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  overflow-x: auto;
  max-height: 300px;
  margin: 0;
}
</style>