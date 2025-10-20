<template>
  <div class="integration-list">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">
          <i class="bi bi-link-45deg me-2"></i>
          Integrações com Sistemas de RH
        </h1>
        <p class="text-muted">Gerencie integrações com sistemas externos de RH</p>
      </div>
      <button class="btn btn-primary" @click="showNewIntegrationModal = true">
        <i class="bi bi-plus-lg me-2"></i>
        Nova Integração
      </button>
    </div>

    <!-- Filtros -->
    <div class="filters-card glass-card mb-4">
      <div class="row g-3">
        <div class="col-md-4">
          <input 
            v-model="filters.search" 
            type="text" 
            class="form-control"
            placeholder="Buscar integração..."
          >
        </div>
        <div class="col-md-3">
          <select v-model="filters.provider" class="form-select">
            <option value="">Todos os Providers</option>
            <option v-for="(provider, key) in providers" :key="key" :value="key">
              {{ provider.name }}
            </option>
          </select>
        </div>
        <div class="col-md-3">
          <select v-model="filters.status" class="form-select">
            <option value="">Todos os Status</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100" @click="clearFilters">
            <i class="bi bi-x-circle me-2"></i>Limpar
          </button>
        </div>
      </div>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading" class="row g-4">
      <div v-for="n in 6" :key="n" class="col-md-6 col-lg-4">
        <div class="integration-card glass-card skeleton-card">
          <div class="skeleton-header">
            <div class="skeleton-title"></div>
            <div class="skeleton-badge"></div>
          </div>
          <div class="skeleton-body">
            <div class="skeleton-line"></div>
            <div class="skeleton-line short"></div>
            <div class="skeleton-line"></div>
          </div>
          <div class="skeleton-footer">
            <div class="skeleton-button"></div>
            <div class="skeleton-button"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Integrações -->
    <div v-else class="row g-4">
      <div v-for="integration in filteredIntegrations" :key="integration.id" class="col-md-6 col-lg-4">
        <div class="integration-card glass-card h-100" :class="{ 'inactive': !integration.is_active }">
          <!-- Header do Card -->
          <div class="card-header d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
              <h5 class="mb-1">{{ integration.name }}</h5>
              <span class="badge" :class="getProviderBadgeClass(integration.provider)">
                <i :class="getProviderIcon(integration.provider)" class="me-1"></i>
                {{ integration.provider_name }}
              </span>
            </div>
            <div class="dropdown">
              <button class="btn btn-sm btn-link" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" @click="viewDetails(integration.id)">
                    <i class="bi bi-eye me-2"></i>Ver Detalhes
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" @click="editIntegration(integration.id)">
                    <i class="bi bi-pencil me-2"></i>Editar
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" @click="testConnection(integration.id)">
                    <i class="bi bi-wifi me-2"></i>Testar Conexão
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" @click="toggleIntegration(integration)">
                    <i :class="integration.is_active ? 'bi bi-pause' : 'bi bi-play'" class="me-2"></i>
                    {{ integration.is_active ? 'Desativar' : 'Ativar' }}
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" @click="deleteIntegration(integration.id)">
                    <i class="bi bi-trash me-2"></i>Excluir
                  </a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Body do Card -->
          <div class="card-body">
            <!-- Status -->
            <div class="mb-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small">Status</span>
                <span class="badge" :class="integration.is_active ? 'bg-success' : 'bg-secondary'">
                  {{ integration.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small">Última Sincronização</span>
                <span class="small">
                  {{ integration.last_sync_at ? formatDate(integration.last_sync_at) : 'Nunca' }}
                </span>
              </div>
            </div>

            <!-- Última Sincronização -->
            <div v-if="integration.last_sync_status" class="alert" :class="getSyncStatusClass(integration.last_sync_status)">
              <i :class="getSyncStatusIcon(integration.last_sync_status)" class="me-2"></i>
              {{ getSyncStatusText(integration.last_sync_status) }}
            </div>

            <!-- Próxima Sincronização -->
            <div v-if="integration.next_sync_at" class="small text-muted">
              <i class="bi bi-clock me-1"></i>
              Próxima: {{ formatDate(integration.next_sync_at) }}
            </div>
          </div>

          <!-- Footer do Card -->
          <div class="card-footer">
            <div class="btn-group w-100" role="group">
              <button 
                class="btn btn-sm btn-outline-primary" 
                @click="syncNow(integration.id)"
                :disabled="!integration.is_active"
              >
                <i class="bi bi-arrow-repeat me-1"></i>
                Sincronizar
              </button>
              <button 
                class="btn btn-sm btn-outline-secondary" 
                @click="viewLogs(integration.id)"
              >
                <i class="bi bi-journal-text me-1"></i>
                Logs
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredIntegrations.length === 0" class="col-12">
        <div class="text-center py-5">
          <i class="bi bi-link-45deg display-1 text-muted mb-3"></i>
          <h4>Nenhuma integração encontrada</h4>
          <p class="text-muted">Crie sua primeira integração com sistemas de RH</p>
          <button class="btn btn-primary mt-3" @click="showNewIntegrationModal = true">
            <i class="bi bi-plus-lg me-2"></i>
            Nova Integração
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Nova Integração -->
    <IntegrationForm 
      v-if="showNewIntegrationModal"
      @close="showNewIntegrationModal = false"
      @saved="loadIntegrations"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import IntegrationForm from './IntegrationForm.vue';

const router = useRouter();

// State
const integrations = ref([]);
const providers = ref({});
const loading = ref(true);
const showNewIntegrationModal = ref(false);

// Filtros
const filters = ref({
  search: '',
  provider: '',
  status: ''
});

// Computed
const filteredIntegrations = computed(() => {
  let result = integrations.value;

  // Filtro de busca
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    result = result.filter(i => 
      i.name.toLowerCase().includes(search) ||
      i.provider_name.toLowerCase().includes(search)
    );
  }

  // Filtro de provider
  if (filters.value.provider) {
    result = result.filter(i => i.provider === filters.value.provider);
  }

  // Filtro de status
  if (filters.value.status) {
    const isActive = filters.value.status === 'active';
    result = result.filter(i => i.is_active === isActive);
  }

  return result;
});

// Methods
const loadIntegrations = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/hr/integrations');
    integrations.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar integrações:', error);
  } finally {
    loading.value = false;
  }
};

const loadProviders = async () => {
  try {
    const response = await axios.get('/api/v1/hr/integrations/providers');
    providers.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar providers:', error);
  }
};

const viewDetails = (id) => {
  router.push(`/admin/integracoes/${id}`);
};

const editIntegration = (id) => {
  router.push(`/admin/integracoes/${id}/editar`);
};

const viewLogs = (id) => {
  router.push(`/admin/integracoes/${id}/logs`);
};

const testConnection = async (id) => {
  try {
    const response = await axios.post(`/api/v1/hr/integrations/${id}/test`);
    
    if (response.data.success) {
      alert('✅ Conexão estabelecida com sucesso!');
    } else {
      alert('❌ Falha na conexão: ' + response.data.message);
    }
  } catch (error) {
    alert('❌ Erro ao testar conexão: ' + error.message);
  }
};

const syncNow = async (id) => {
  if (!confirm('Deseja iniciar a sincronização agora?')) return;

  try {
    await axios.post(`/api/v1/hr/integrations/${id}/sync`, {
      type: 'colaboradores',
      async: true
    });
    
    alert('✅ Sincronização iniciada em segundo plano!');
    await loadIntegrations();
  } catch (error) {
    alert('❌ Erro ao sincronizar: ' + error.message);
  }
};

const toggleIntegration = async (integration) => {
  try {
    await axios.post(`/api/v1/hr/integrations/${integration.id}/toggle`);
    await loadIntegrations();
  } catch (error) {
    alert('Erro ao alterar status: ' + error.message);
  }
};

const deleteIntegration = async (id) => {
  if (!confirm('Tem certeza que deseja excluir esta integração?')) return;

  try {
    await axios.delete(`/api/v1/hr/integrations/${id}`);
    await loadIntegrations();
  } catch (error) {
    alert('Erro ao excluir: ' + error.message);
  }
};

const clearFilters = () => {
  filters.value = { search: '', provider: '', status: '' };
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
    completed: 'alert-success',
    failed: 'alert-danger',
    processing: 'alert-warning',
    pending: 'alert-info'
  };
  return classes[status] || 'alert-secondary';
};

const getSyncStatusIcon = (status) => {
  const icons = {
    completed: 'bi-check-circle-fill',
    failed: 'bi-x-circle-fill',
    processing: 'bi-arrow-repeat',
    pending: 'bi-clock-fill'
  };
  return icons[status] || 'bi-info-circle-fill';
};

const getSyncStatusText = (status) => {
  const texts = {
    completed: 'Última sincronização: Sucesso',
    failed: 'Última sincronização: Falhou',
    processing: 'Sincronizando...',
    pending: 'Aguardando sincronização'
  };
  return texts[status] || 'Status desconhecido';
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Lifecycle
onMounted(() => {
  loadIntegrations();
  loadProviders();
});
</script>

<style scoped>
.integration-list {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: white;
}

/* Filters Card */
.filters-card {
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 16px;
}

/* Cards */
.integration-card {
  transition: all 0.3s ease;
  border-radius: 16px;
  overflow: visible !important; /* ✅ IMPORTANTE: Permite dropdown expandir */
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.integration-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  background: rgba(255, 255, 255, 0.08);
}

.integration-card.inactive {
  opacity: 0.6;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  overflow: visible !important; /* ✅ IMPORTANTE */
}

.card-header h5 {
  color: white;
  font-weight: 600;
}

.card-body {
  padding: 1.5rem;
}

.card-footer {
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Dropdown de Ações - CORRIGIDO COMPLETO */
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
  max-height: none !important;
  padding: 0.5rem 0;
  border-radius: 12px;
  z-index: 9999 !important;
  overflow: visible !important;
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
  white-space: nowrap;
}

.dropdown-item i {
  width: 20px;
  text-align: center;
  opacity: 0.8;
  flex-shrink: 0;
}

.dropdown-item:hover {
  background: rgba(102, 126, 234, 0.2) !important;
  color: white !important;
}

.dropdown-item:hover i {
  opacity: 1;
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

/* Botão de ações */
.btn-link {
  color: rgba(255, 255, 255, 0.7) !important;
  padding: 0.5rem;
  transition: all 0.2s ease;
  text-decoration: none;
}

.btn-link:hover {
  color: white !important;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
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

.bg-secondary {
  background: rgba(255, 255, 255, 0.1) !important;
  color: rgba(255, 255, 255, 0.9) !important;
}

.bg-purple {
  background: rgba(147, 51, 234, 0.2) !important;
  color: #a855f7 !important;
  border: 1px solid rgba(147, 51, 234, 0.3);
}

.bg-primary {
  background: rgba(59, 130, 246, 0.2) !important;
  color: #3b82f6 !important;
}

.bg-danger {
  background: rgba(239, 68, 68, 0.2) !important;
  color: #ef4444 !important;
}

.bg-warning {
  background: rgba(245, 158, 11, 0.2) !important;
  color: #f59e0b !important;
}

.bg-info {
  background: rgba(14, 165, 233, 0.2) !important;
  color: #0ea5e9 !important;
}

.bg-dark {
  background: rgba(55, 65, 81, 0.3) !important;
  color: rgba(255, 255, 255, 0.9) !important;
}

/* Skeleton Loading */
.skeleton-card {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.skeleton-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.skeleton-title {
  height: 24px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  margin-bottom: 1rem;
  width: 70%;
}

.skeleton-badge {
  height: 28px;
  width: 120px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 16px;
}

.skeleton-body {
  padding: 1.5rem;
}

.skeleton-line {
  height: 16px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  margin-bottom: 1rem;
}

.skeleton-line.short {
  width: 60%;
}

.skeleton-footer {
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  gap: 0.5rem;
}

.skeleton-button {
  height: 36px;
  flex: 1;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 8px;
}

/* Alerts */
.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.alert-success {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.alert-danger {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.alert-warning {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.alert-info {
  background: rgba(14, 165, 233, 0.15);
  color: #0ea5e9;
  border: 1px solid rgba(14, 165, 233, 0.3);
}

/* Text colors */
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

.btn-outline-primary {
  border-color: rgba(102, 126, 234, 0.5);
  color: #667eea;
}

.btn-outline-primary:hover:not(:disabled) {
  background: rgba(102, 126, 234, 0.1);
  border-color: #667eea;
  color: #667eea;
}

.btn-outline-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-secondary {
  border-color: rgba(255, 255, 255, 0.3);
  color: rgba(255, 255, 255, 0.9);
}

.btn-outline-secondary:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.5);
  color: white;
}

/* Form Controls */
.form-control, .form-select {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
}

.form-control:focus, .form-select:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(102, 126, 234, 0.5);
  color: white;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.1);
}

.form-select option {
  background: #1e293b;
  color: white;
}

/* Empty State */
.display-1 {
  font-size: 4rem;
  opacity: 0.3;
}
</style>