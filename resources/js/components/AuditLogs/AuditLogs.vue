<template>
  <div>
    <!-- Header com Título e Botões de Exportação -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">
        <i class="fas fa-history me-2"></i>Logs de Auditoria
      </h2>
      
      <!-- ⭐ BOTÕES DE EXPORTAÇÃO -->
      <div class="btn-group" role="group">
        <button 
          @click="exportExcel" 
          class="btn btn-success" 
          :disabled="loading"
          title="Exportar para Excel"
        >
          <i class="fas fa-file-excel me-2"></i>
          <span class="d-none d-md-inline">Excel</span>
        </button>
        <button 
          @click="exportPdf" 
          class="btn btn-danger" 
          :disabled="loading"
          title="Exportar para PDF"
        >
          <i class="fas fa-file-pdf me-2"></i>
          <span class="d-none d-md-inline">PDF</span>
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <AuditLogFilters @filter="handleFilter" @search="handleSearch" />

    <!-- Timeline de Logs -->
    <AuditLogTimeline 
      :logs="logs" 
      :loading="loading" 
      @view-details="openDetails"
    />

    <!-- Paginação -->
    <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
      <nav>
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <button 
              class="page-link" 
              @click="fetchLogs(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
            >
              Anterior
            </button>
          </li>
          
          <li 
            v-for="page in visiblePages" 
            :key="page" 
            class="page-item" 
            :class="{ active: page === pagination.current_page }"
          >
            <button class="page-link" @click="fetchLogs(page)">
              {{ page }}
            </button>
          </li>
          
          <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
            <button 
              class="page-link" 
              @click="fetchLogs(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
            >
              Próximo
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Informações de Paginação -->
    <div class="text-center text-white-50 mt-3">
      Mostrando {{ pagination.from }} até {{ pagination.to }} de {{ pagination.total }} registros
    </div>

    <!-- Modal de Detalhes -->
    <AuditLogDetailsModal
      v-if="showDetailsModal && selectedLog"
      :log="selectedLog"
      @close="closeDetails"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AuditLogFilters from './AuditLogFilters.vue';
import AuditLogTimeline from './AuditLogTimeline.vue';
import AuditLogDetailsModal from './AuditLogDetailsModal.vue';

const logs = ref([]);
const loading = ref(false);
const selectedLog = ref(null);
const showDetailsModal = ref(false);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0,
});

const filters = ref({
  module: '',
  action: '',
  date_from: '',
  date_to: '',
  search: '',
});

// ⭐ Páginas visíveis na paginação
const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;

  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i);
  }

  return pages;
});

// ⭐ Buscar logs da API
const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    });

    // Remover parâmetros vazios
    for (let [key, value] of [...params.entries()]) {
      if (!value) params.delete(key);
    }

    const token = localStorage.getItem('token');
    const response = await fetch(`/api/v1/audit-logs?${params}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Erro ao carregar logs');
    }

    const data = await response.json();
    
    logs.value = data.data;
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
      from: data.from,
      to: data.to,
    };
  } catch (error) {
    console.error('Erro ao carregar logs:', error);
    alert('Erro ao carregar logs. Verifique sua conexão.');
  } finally {
    loading.value = false;
  }
};

// ⭐ Aplicar filtros
const handleFilter = (newFilters) => {
  filters.value = { ...filters.value, ...newFilters };
  fetchLogs(1);
};

// ⭐ Buscar por texto
const handleSearch = (searchTerm) => {
  filters.value.search = searchTerm;
  fetchLogs(1);
};

// ⭐ Abrir detalhes do log
const openDetails = (log) => {
  selectedLog.value = log;
  showDetailsModal.value = true;
};

// ⭐ Fechar modal de detalhes
const closeDetails = () => {
  showDetailsModal.value = false;
  selectedLog.value = null;
};

// ⭐ FUNÇÃO GENÉRICA DE EXPORTAÇÃO
const exportFile = async (format) => {
  if (loading.value) return;

  try {
    loading.value = true;

    const activeFilters = Object.fromEntries(
      Object.entries(filters.value).filter(([, value]) => value)
    );
    const params = new URLSearchParams(activeFilters);

    const token = localStorage.getItem('token');

    const response = await fetch(`/api/v1/audit-logs/export/${format}?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      const errorMessage = errorData.message || `Erro ao exportar para ${format.toUpperCase()}`;
      throw new Error(errorMessage);
    }

    // Download do arquivo
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `logs-auditoria-${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xlsx' : 'pdf'}`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);

    console.log(`✅ Exportação ${format.toUpperCase()} concluída!`);
  } catch (error) {
    console.error(`❌ Erro ao exportar ${format.toUpperCase()}:`, error);
    alert(error.message || `Erro ao exportar arquivo ${format.toUpperCase()}. Tente novamente.`);
  } finally {
    loading.value = false;
  }
};

// ⭐ Funções de gatilho para os botões
const exportExcel = () => exportFile('excel');
const exportPdf = () => exportFile('pdf');

// ⭐ Carregar logs ao montar o componente
onMounted(() => {
  fetchLogs();
});
</script>

<style scoped>
.btn-group {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-group .btn {
  transition: all 0.3s ease;
}

.btn-group .btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.btn-group .btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.pagination {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  padding: 0.5rem;
}

.page-link {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: white;
  margin: 0 0.2rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.page-link:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(102, 126, 234, 0.5);
  color: white;
  transform: translateY(-1px);
}

.page-item.active .page-link {
  background: rgba(102, 126, 234, 0.8);
  border-color: rgba(102, 126, 234, 1);
  color: white;
}

.page-item.disabled .page-link {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.3);
  cursor: not-allowed;
}

/* Responsividade */
@media (max-width: 768px) {
  .btn-group .btn span {
    display: none;
  }
  
  .btn-group .btn i {
    margin-right: 0 !important;
  }
}
</style>
