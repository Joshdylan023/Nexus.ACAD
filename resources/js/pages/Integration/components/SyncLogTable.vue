<template>
  <div class="sync-log-table">
    <!-- Filtros -->
    <div class="glass-card mb-3">
      <div class="row g-3">
        <div class="col-md-4">
          <select v-model="filters.status" class="form-select" @change="loadLogs">
            <option value="">Todos os Status</option>
            <option value="completed">Sucesso</option>
            <option value="failed">Falhou</option>
            <option value="processing">Processando</option>
            <option value="pending">Pendente</option>
          </select>
        </div>
        <div class="col-md-4">
          <select v-model="filters.type" class="form-select" @change="loadLogs">
            <option value="">Todos os Tipos</option>
            <option value="colaboradores">Colaboradores</option>
            <option value="estrutura">Estrutura</option>
            <option value="completo">Completo</option>
          </select>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-secondary w-100" @click="clearFilters">
            <i class="bi bi-x-circle me-2"></i>Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>

    <!-- Tabela -->
    <div v-else class="glass-card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Status</th>
              <th>Tipo</th>
              <th>Registros</th>
              <th>Sucesso</th>
              <th>Falhas</th>
              <th>Duração</th>
              <th>Iniciado por</th>
              <th>Data</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs.data" :key="log.id" @click="$emit('view-details', log.id)" style="cursor: pointer;">
              <td>
                <span class="badge" :class="getStatusBadgeClass(log.status)">
                  <i :class="getStatusIcon(log.status)" class="me-1"></i>
                  {{ getStatusText(log.status) }}
                </span>
              </td>
              <td>
                <span class="badge bg-secondary">{{ getTypeText(log.type) }}</span>
              </td>
              <td>{{ log.records_total }}</td>
              <td>
                <span class="text-success">
                  <i class="bi bi-arrow-up me-1"></i>
                  {{ log.records_created + log.records_updated }}
                </span>
              </td>
              <td>
                <span :class="log.records_failed > 0 ? 'text-danger' : 'text-muted'">
                  <i class="bi bi-x-circle me-1"></i>
                  {{ log.records_failed }}
                </span>
              </td>
              <td>{{ log.duration_seconds ? formatDuration(log.duration_seconds) : '-' }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <i :class="getTriggerIcon(log.trigger_type)"></i>
                  <span class="small">{{ log.triggered_by?.name || 'Sistema' }}</span>
                </div>
              </td>
              <td>{{ formatDate(log.created_at) }}</td>
              <td>
                <button 
                  class="btn btn-sm btn-ghost"
                  @click.stop="$emit('view-details', log.id)"
                >
                  <i class="bi bi-eye"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="logs.data?.length === 0" class="text-center py-5">
          <i class="bi bi-journal-text display-1 text-muted mb-3"></i>
          <h5>Nenhum log encontrado</h5>
          <p class="text-muted">Execute uma sincronização para ver os logs aqui</p>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="logs.total > logs.per_page" class="d-flex justify-content-center mt-4">
        <nav>
          <ul class="pagination">
            <li class="page-item" :class="{ disabled: logs.current_page === 1 }">
              <a class="page-link" @click="changePage(logs.current_page - 1)">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>
            <li 
              v-for="page in paginationPages" 
              :key="page"
              class="page-item" 
              :class="{ active: page === logs.current_page }"
            >
              <a class="page-link" @click="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: logs.current_page === logs.last_page }">
              <a class="page-link" @click="changePage(logs.current_page + 1)">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  integrationId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['view-details']);

// State
const logs = ref({
  data: [],
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
});

const loading = ref(true);

const filters = ref({
  status: '',
  type: ''
});

// Computed
const paginationPages = computed(() => {
  const pages = [];
  const maxPages = 5;
  let start = Math.max(1, logs.value.current_page - Math.floor(maxPages / 2));
  let end = Math.min(logs.value.last_page, start + maxPages - 1);
  
  if (end - start < maxPages - 1) {
    start = Math.max(1, end - maxPages + 1);
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Methods
const loadLogs = async (page = 1) => {
  try {
    loading.value = true;
    const params = {
      page,
      per_page: 20,
      ...filters.value
    };
    
    const response = await axios.get(`/api/v1/hr/integrations/${props.integrationId}/logs`, { params });
    logs.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar logs:', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= logs.value.last_page) {
    loadLogs(page);
  }
};

const clearFilters = () => {
  filters.value = { status: '', type: '' };
  loadLogs();
};

// Helpers
const getStatusBadgeClass = (status) => {
  const classes = {
    completed: 'bg-success',
    failed: 'bg-danger',
    processing: 'bg-warning',
    pending: 'bg-info'
  };
  return classes[status] || 'bg-secondary';
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
    completed: 'Sucesso',
    failed: 'Falhou',
    processing: 'Processando',
    pending: 'Pendente'
  };
  return texts[status] || status;
};

const getTypeText = (type) => {
  const texts = {
    colaboradores: 'Colaboradores',
    estrutura: 'Estrutura',
    completo: 'Completo'
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
  const minutes = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return minutes > 0 ? `${minutes}m ${secs}s` : `${secs}s`;
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
  loadLogs();
});
</script>

<style scoped>
.table {
  color: white;
}

.table thead th {
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.875rem;
  padding: 1rem;
}

.table tbody tr {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  transition: background 0.2s ease;
}

.table tbody tr:hover {
  background: rgba(255, 255, 255, 0.05);
}

.table tbody td {
  padding: 1rem;
  vertical-align: middle;
}

.btn-ghost {
  background: transparent;
  border: none;
  color: inherit;
}

.btn-ghost:hover {
  background: rgba(255, 255, 255, 0.1);
}

.pagination {
  gap: 0.5rem;
}

.page-link {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
}

.page-link:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: #667EEA;
  color: white;
}

.page-item.active .page-link {
  background: #667EEA;
  border-color: #667EEA;
}

.page-item.disabled .page-link {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>