<template>
  <div class="integration-details">
    <!-- Skeleton Loading -->
    <div v-if="loading" class="skeleton-container">
      <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="skeleton-button skeleton-sm mb-2" style="width: 100px;"></div>
            <div class="skeleton-title mb-2"></div>
            <div class="d-flex gap-2">
              <div class="skeleton-badge"></div>
              <div class="skeleton-badge"></div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <div class="skeleton-button"></div>
            <div class="skeleton-button"></div>
            <div class="skeleton-button"></div>
          </div>
        </div>
      </div>

      <div class="skeleton-tabs mb-4"></div>

      <div class="row g-4">
        <div v-for="n in 4" :key="n" class="col-md-3">
          <div class="glass-card">
            <div class="skeleton-stat"></div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="glass-card">
            <div class="skeleton-info"></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="glass-card">
            <div class="skeleton-info"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="integration">
      <!-- Sync Progress Alert -->
      <div v-if="syncing" class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
          <span class="spinner-border spinner-border-sm me-3"></span>
          <div class="flex-grow-1">
            <strong>Sincronização em andamento...</strong>
            <p class="mb-0 small">A página será atualizada automaticamente ao concluir.</p>
          </div>
        </div>
      </div>

      <!-- Header -->
      <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <button class="btn btn-sm btn-ghost mb-2" @click="$router.back()">
              <i class="bi bi-arrow-left me-2"></i>Voltar
            </button>
            <h1 class="page-title mb-2">{{ integration.name }}</h1>
            <div class="d-flex gap-2 align-items-center">
              <span class="badge" :class="getProviderBadgeClass(integration.provider)">
                <i :class="getProviderIcon(integration.provider)" class="me-1"></i>
                {{ integration.provider_name }}
              </span>
              <span class="badge" :class="integration.is_active ? 'bg-success' : 'bg-secondary'">
                {{ integration.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" @click="testConnection">
              <i class="bi bi-wifi me-2"></i>Testar Conexão
            </button>
            <button 
              class="btn btn-primary" 
              @click="syncNow"
              :disabled="!integration.is_active || syncing"
            >
              <span v-if="syncing">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Sincronizando...
              </span>
              <span v-else>
                <i class="bi bi-arrow-repeat me-2"></i>Sincronizar Agora
              </span>
            </button>
            <button class="btn btn-outline-secondary" @click="editIntegration">
              <i class="bi bi-pencil"></i>
            </button>
            <div class="dropdown">
              <button class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" @click="toggleActive">
                    <i :class="integration.is_active ? 'bi bi-pause' : 'bi bi-play'" class="me-2"></i>
                    {{ integration.is_active ? 'Desativar' : 'Ativar' }}
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" @click="deleteIntegration">
                    <i class="bi bi-trash me-2"></i>Excluir
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <ul class="nav nav-tabs glass-tabs mb-4">
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'overview' }"
            @click="activeTab = 'overview'"
          >
            <i class="bi bi-bar-chart-line me-2"></i>Visão Geral
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'logs' }"
            @click="activeTab = 'logs'"
          >
            <i class="bi bi-journal-text me-2"></i>Logs
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'stats' }"
            @click="activeTab = 'stats'"
          >
            <i class="bi bi-graph-up me-2"></i>Estatísticas
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'config' }"
            @click="activeTab = 'config'"
          >
            <i class="bi bi-gear me-2"></i>Configuração
          </a>
        </li>
        <!-- Adicionar nova aba -->
        <li class="nav-item">
          <button 
            class="nav-link" 
            :class="{ active: activeTab === 'mapping' }"
            @click="activeTab = 'mapping'"
          >
            <i class="bi bi-link-45deg me-2"></i>
            Mapeamento de Campos
          </button>
        </li>
      </ul>

      <!-- Tab: Visão Geral -->
      <div v-show="activeTab === 'overview'" class="tab-content">
        <div class="row g-4">
          <!-- Stats Cards -->
          <div class="col-md-3">
            <div class="stat-card glass-card">
              <div class="stat-icon bg-primary">
                <i class="bi bi-arrow-repeat"></i>
              </div>
              <div class="stat-info">
                <div class="stat-label">Total de Sincronizações</div>
                <div class="stat-value">{{ stats.overview?.total_syncs || 0 }}</div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="stat-card glass-card">
              <div class="stat-icon bg-success">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="stat-info">
                <div class="stat-label">Sucesso</div>
                <div class="stat-value">{{ stats.overview?.successful_syncs || 0 }}</div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="stat-card glass-card">
              <div class="stat-icon bg-danger">
                <i class="bi bi-x-circle"></i>
              </div>
              <div class="stat-info">
                <div class="stat-label">Falhas</div>
                <div class="stat-value">{{ stats.overview?.failed_syncs || 0 }}</div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="stat-card glass-card">
              <div class="stat-icon bg-info">
                <i class="bi bi-people"></i>
              </div>
              <div class="stat-info">
                <div class="stat-label">Registros Sincronizados</div>
                <div class="stat-value">{{ (stats.records?.total_created || 0) + (stats.records?.total_updated || 0) }}</div>
              </div>
            </div>
          </div>

          <!-- Informações -->
          <div class="col-md-8">
            <div class="glass-card">
              <h5 class="card-title mb-4">
                <i class="bi bi-info-circle me-2"></i>Informações
              </h5>
              <div class="info-grid">
                <div class="info-item">
                  <span class="info-label">Frequência</span>
                  <span class="info-value">{{ getSyncFrequencyLabel(integration.sync_frequency) }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Última Sincronização</span>
                  <span class="info-value">
                    {{ integration.last_sync_at ? formatDate(integration.last_sync_at) : 'Nunca' }}
                  </span>
                </div>
                <div class="info-item">
                  <span class="info-label">Próxima Sincronização</span>
                  <span class="info-value">
                    {{ integration.next_sync_at ? formatDate(integration.next_sync_at) : 'Não agendada' }}
                  </span>
                </div>
                <div class="info-item">
                  <span class="info-label">Criado por</span>
                  <span class="info-value">{{ integration.creator?.name || 'N/A' }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Criado em</span>
                  <span class="info-value">{{ formatDate(integration.created_at) }}</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Atualizado em</span>
                  <span class="info-value">{{ formatDate(integration.updated_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Últimas Sincronizações -->
          <div class="col-md-4">
            <div class="glass-card">
              <h5 class="card-title mb-4">
                <i class="bi bi-clock-history me-2"></i>Últimas Sincronizações
              </h5>
              <div class="recent-syncs">
                <div 
                  v-for="sync in stats.recent_syncs" 
                  :key="sync.id"
                  class="recent-sync-item"
                  @click="viewLogDetails(sync.id)"
                >
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="badge" :class="getSyncStatusClass(sync.status)">
                      {{ getSyncStatusText(sync.status) }}
                    </span>
                    <span class="text-muted small">{{ formatDateRelative(sync.created_at) }}</span>
                  </div>
                  <div class="small text-muted">
                    <i class="bi bi-database me-1"></i>
                    {{ sync.records_total }} registros ({{ sync.duration }})
                  </div>
                </div>
                <div v-if="!stats.recent_syncs?.length" class="text-center text-muted py-3">
                  Nenhuma sincronização ainda
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Logs -->
      <div v-show="activeTab === 'logs'" class="tab-content">
        <SyncLogTable 
          :integration-id="integrationId"
          :key="refreshKey"
          @view-details="viewLogDetails"
        />
      </div>

      <!-- Tab: Estatísticas -->
      <div v-show="activeTab === 'stats'" class="tab-content">
        <StatsPanel :integration-id="integrationId" :stats="stats" />
      </div>

      <!-- Tab: Configuração -->
      <div v-show="activeTab === 'config'" class="tab-content">
        <div class="glass-card">
          <h5 class="card-title mb-4">
            <i class="bi bi-gear me-2"></i>Configurações
          </h5>
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            As credenciais de acesso estão criptografadas e não podem ser visualizadas por segurança.
          </div>
          <button class="btn btn-primary" @click="editIntegration">
            <i class="bi bi-pencil me-2"></i>Editar Configuração
          </button>
        </div>
      </div>

      <!-- Adicionar conteúdo da aba -->
      <div v-if="activeTab === 'mapping'" class="tab-pane fade show active">
        <FieldMappingEditor
          :integration-id="integrationId"
          :initial-mappings="integration.field_mapping"
          :sample-data="sampleData"
          @save="handleMappingSaved"
        />
      </div>
    </div>

    <!-- Log Details Modal -->
    <LogDetailsModal 
      v-if="showLogDetails"
      :log-id="selectedLogId"
      :integration-id="integrationId"
      @close="showLogDetails = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import SyncLogTable from './components/SyncLogTable.vue';
import StatsPanel from './components/StatsPanel.vue';
import LogDetailsModal from './components/LogDetailsModal.vue';
import FieldMappingEditor from './FieldMappingEditor.vue';

const router = useRouter();
const route = useRoute();

const integrationId = computed(() => parseInt(route.params.id));

// State
const integration = ref(null);
const stats = ref({});
const loading = ref(true);
const syncing = ref(false);
const activeTab = ref('overview');
const showLogDetails = ref(false);
const selectedLogId = ref(null);
const refreshKey = ref(0);
const sampleData = ref(null);

// Auto-refresh
let refreshInterval = null;

// Methods
const loadIntegration = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/hr/integrations/${integrationId.value}`);
    integration.value = response.data.integration;
    if (response.data.stats) {
      stats.value = response.data.stats;
    } else {
      await loadStats();
    }
  } catch (error) {
    console.error('Erro ao carregar integração:', error);
    alert('Erro ao carregar integração');
    router.push('/admin/integracoes');
  } finally {
    loading.value = false;
  }
};

const loadSampleData = async () => {
  try {
    const response = await axios.get(`/api/v1/hr/integrations/${integrationId.value}/sample-data`);
    sampleData.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar dados de exemplo:', error);
  }
};

const handleMappingSaved = () => {
  loadIntegration(); // Recarregar dados
};

const loadStats = async () => {
  try {
    const response = await axios.get(`/api/v1/hr/integrations/${integrationId.value}/stats`);
    stats.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error);
  }
};

const startAutoRefresh = () => {
  if (refreshInterval) return;
  
  refreshInterval = setInterval(async () => {
    await loadIntegration();
    refreshKey.value++; // Force re-render log table
  }, 5000); // Check every 5 seconds
};

const stopAutoRefresh = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

const testConnection = async () => {
  try {
    const response = await axios.post(`/api/v1/hr/integrations/${integrationId.value}/test`);
    
    if (response.data.success) {
      alert('✅ Conexão estabelecida com sucesso!');
    } else {
      alert('❌ Falha na conexão: ' + response.data.message);
    }
  } catch (error) {
    alert('❌ Erro ao testar conexão: ' + error.message);
  }
};

const syncNow = async () => {
  if (!confirm('Deseja iniciar a sincronização agora?')) return;

  try {
    syncing.value = true;
    await axios.post(`/api/v1/hr/integrations/${integrationId.value}/sync`, {
      type: 'colaboradores',
      async: true
    });
    
    alert('✅ Sincronização iniciada em segundo plano!');
    startAutoRefresh();
    
  } catch (error) {
    alert('❌ Erro ao sincronizar: ' + error.message);
    syncing.value = false;
  }
};

const toggleActive = async () => {
  try {
    await axios.post(`/api/v1/hr/integrations/${integrationId.value}/toggle`);
    await loadIntegration();
  } catch (error) {
    alert('Erro ao alterar status: ' + error.message);
  }
};

const editIntegration = () => {
  router.push(`/admin/integracoes/${integrationId.value}/editar`);
};

const deleteIntegration = async () => {
  if (!confirm('Tem certeza que deseja excluir esta integração? Esta ação não pode ser desfeita.')) return;

  try {
    await axios.delete(`/api/v1/hr/integrations/${integrationId.value}`);
    alert('Integração excluída com sucesso');
    router.push('/admin/integracoes');
  } catch (error) {
    alert('Erro ao excluir: ' + error.message);
  }
};

const viewLogDetails = (logId) => {
  selectedLogId.value = logId;
  showLogDetails.value = true;
};

// Helpers
const getProviderIcon = (provider) => {
  const icons = {
    generic: 'bi-cloud',
    totvs: 'bi-building',
    adp: 'bi-people',
    adp_expert: 'bi-people-fill',
    sap: 'bi-diagram-3',
    oracle: 'bi-database',
    senior: 'bi-briefcase',
    csv: 'bi-file-earmark-spreadsheet'
  };
  return icons[provider] || 'bi-link';
};

const getProviderBadgeClass = (provider) => {
  const classes = {
    generic: 'bg-secondary',
    totvs: 'bg-primary',
    adp: 'bg-success',
    adp_expert: 'bg-info',
    sap: 'bg-warning',
    oracle: 'bg-danger',
    senior: 'bg-dark',
    csv: 'bg-purple'
  };
  return classes[provider] || 'bg-secondary';
};

const getSyncStatusClass = (status) => {
  const classes = {
    completed: 'bg-success',
    failed: 'bg-danger',
    processing: 'bg-warning',
    pending: 'bg-info'
  };
  return classes[status] || 'bg-secondary';
};

const getSyncStatusText = (status) => {
  const texts = {
    completed: 'Sucesso',
    failed: 'Falhou',
    processing: 'Processando',
    pending: 'Pendente'
  };
  return texts[status] || status;
};

const getSyncFrequencyLabel = (frequency) => {
  const labels = {
    manual: 'Manual',
    hourly: 'A cada hora',
    daily: 'Diariamente',
    weekly: 'Semanalmente'
  };
  return labels[frequency] || frequency;
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatDateRelative = (date) => {
  const now = new Date();
  const syncDate = new Date(date);
  const diffMs = now - syncDate;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffMins < 1) return 'Agora';
  if (diffMins < 60) return `${diffMins}m atrás`;
  if (diffHours < 24) return `${diffHours}h atrás`;
  if (diffDays === 1) return 'Ontem';
  if (diffDays < 7) return `${diffDays}d atrás`;
  return formatDate(date);
};

// Lifecycle
onMounted(() => {
  loadIntegration();
  loadSampleData();
});

onUnmounted(() => {
  stopAutoRefresh();
});
</script>

<style scoped>
.integration-details {
  padding: 2rem;
}

/* Skeleton Loading */
.skeleton-container {
  animation: pulse 1.5s ease-in-out infinite;
}

.skeleton-title {
  height: 32px;
  width: 300px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

.skeleton-button {
  height: 40px;
  width: 150px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

.skeleton-button.skeleton-sm {
  height: 32px;
  width: 80px;
}

.skeleton-badge {
  height: 24px;
  width: 80px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.skeleton-tabs {
  height: 48px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.skeleton-stat {
  height: 80px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

.skeleton-info {
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: white;
}

.btn-ghost {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  padding: 0.5rem 1rem;
  transition: all 0.2s ease;
}

.btn-ghost:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

/* Dropdown */
.dropdown {
  position: relative;
  z-index: 10;
}

.dropdown.show {
  z-index: 1050;
}

.dropdown-menu {
  background: rgba(30, 41, 59, 0.98) !important;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  min-width: 200px;
  padding: 0.5rem 0;
  border-radius: 12px;
  z-index: 9999 !important;
}

.dropdown-item {
  color: rgba(255, 255, 255, 0.9) !important;
  padding: 0.75rem 1.25rem;
  font-weight: 500;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-radius: 8px;
  margin: 0 0.5rem;
}

.dropdown-item i {
  width: 20px;
  text-align: center;
}

.dropdown-item:hover {
  background: rgba(102, 126, 234, 0.2) !important;
  color: white !important;
}

.dropdown-item.text-danger {
  color: #ef4444 !important;
}

.dropdown-item.text-danger:hover {
  background: rgba(239, 68, 68, 0.15) !important;
  color: #ff6b6b !important;
}

.dropdown-divider {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin: 0.5rem 0;
}

/* Tabs */
.glass-tabs {
  border: none;
  gap: 0.5rem;
}

.glass-tabs .nav-link {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.7);
  transition: all 0.3s ease;
  cursor: pointer;
  padding: 0.75rem 1.25rem;
}

.glass-tabs .nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.glass-tabs .nav-link.active {
  background: rgba(102, 126, 234, 0.3);
  border-color: #667EEA;
  color: white;
}

/* Cards */
.glass-card {
  background: rgba(13, 17, 23, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 1.5rem;
}

/* Stat Cards */
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.stat-icon.bg-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.bg-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-icon.bg-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.stat-icon.bg-info {
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
}

/* Card Title */
.card-title {
  color: white;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.card-title i {
  color: #667eea;
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
  gap: 0.5rem;
}

.info-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 500;
}

.info-value {
  font-weight: 600;
  color: rgba(255, 255, 255, 0.95);
  font-size: 1rem;
}

/* Recent Syncs */
.recent-syncs {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.recent-sync-item {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.recent-sync-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(4px);
}

/* Badges */
.badge {
  padding: 0.4rem 0.8rem;
  font-weight: 600;
  font-size: 0.875rem;
}

.bg-success {
  background: rgba(16, 185, 129, 0.2) !important;
  color: #10b981 !important;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.bg-danger {
  background: rgba(239, 68, 68, 0.2) !important;
  color: #ef4444 !important;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.bg-warning {
  background: rgba(245, 158, 11, 0.2) !important;
  color: #f59e0b !important;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.bg-info {
  background: rgba(14, 165, 233, 0.2) !important;
  color: #0ea5e9 !important;
  border: 1px solid rgba(14, 165, 233, 0.3);
}

.bg-secondary {
  background: rgba(255, 255, 255, 0.1) !important;
  color: rgba(255, 255, 255, 0.9) !important;
}

.bg-primary {
  background: rgba(59, 130, 246, 0.2) !important;
  color: #3b82f6 !important;
}

.bg-purple {
  background: rgba(147, 51, 234, 0.2) !important;
  color: #a855f7 !important;
}

/* Alert */
.alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.alert-info {
  background: rgba(14, 165, 233, 0.15);
  color: #0ea5e9;
  border: 1px solid rgba(14, 165, 233, 0.3);
}

.alert-warning {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

/* Text Colors */
.text-muted {
  color: rgba(255, 255, 255, 0.6) !important;
}

.small {
  color: rgba(255, 255, 255, 0.8);
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  font-weight: 600;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-primary {
  border-color: rgba(102, 126, 234, 0.5);
  color: #667eea;
  background: transparent;
}

.btn-outline-primary:hover {
  background: rgba(102, 126, 234, 0.1);
  border-color: #667eea;
  color: #667eea;
}

.btn-outline-secondary {
  border-color: rgba(255, 255, 255, 0.3);
  color: rgba(255, 255, 255, 0.9);
  background: transparent;
}

.btn-outline-secondary:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.5);
  color: white;
}
</style>