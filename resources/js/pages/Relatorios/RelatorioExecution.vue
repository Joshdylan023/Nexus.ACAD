<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="execution-modal card-glass">
      <!-- Header -->
      <div class="execution-header">
        <div>
          <h4 class="text-white mb-1">
            <i class="bi bi-play-circle me-2"></i>
            {{ report.name || 'Carregando...' }}
          </h4>
          <p class="text-white-50 small mb-0">{{ report.description || '' }}</p>
        </div>
        <button @click="$emit('close')" class="btn-close btn-close-white"></button>
      </div>

      <!-- Filtros Adicionais (Opcional) -->
      <div v-if="!executed" class="execution-filters">
        <h6 class="text-white mb-3">
          <i class="bi bi-funnel me-2"></i>
          Filtros Adicionais (Opcional)
        </h6>
        
        <div class="row g-3">
          <!-- Filtros dinâmicos baseados no tipo -->
          <template v-if="report.type === 'colaboradores'">
            <div class="col-md-4">
              <label class="form-label text-white">Status</label>
              <select v-model="additionalFilters.status" class="form-select bg-dark text-white border-secondary">
                <option value="">Todos</option>
                <option value="Ativo">Ativo</option>
                <option value="Afastado">Afastado</option>
                <option value="Desligado">Desligado</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label text-white">Cargo</label>
              <input 
                v-model="additionalFilters.cargo" 
                type="text" 
                class="form-control bg-dark text-white border-secondary"
                placeholder="Filtrar por cargo..."
              >
            </div>
          </template>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button @click="executeReport" class="btn btn-primary" :disabled="loading">
            <span v-if="loading">
              <span class="spinner-border spinner-border-sm me-2"></span>
              Executando...
            </span>
            <span v-else>
              <i class="bi bi-play-fill me-2"></i>
              Executar Relatório
            </span>
          </button>
          
          <button @click="$emit('close')" class="btn btn-outline-secondary">
            Cancelar
          </button>
        </div>
      </div>

      <!-- Resultados -->
      <div v-else class="execution-results">
        <!-- Stats -->
        <div class="results-stats">
          <div class="stat-item">
            <i class="bi bi-table text-primary"></i>
            <div>
              <div class="stat-value">{{ totalRecords }}</div>
              <div class="stat-label">Registros</div>
            </div>
          </div>
          
          <div class="stat-item">
            <i class="bi bi-columns text-success"></i>
            <div>
              <div class="stat-value">{{ report.columns?.length || 0 }}</div>
              <div class="stat-label">Colunas</div>
            </div>
          </div>
          
          <div class="stat-item">
            <i class="bi bi-clock text-warning"></i>
            <div>
              <div class="stat-value">{{ executionTime }}s</div>
              <div class="stat-label">Tempo</div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="results-actions">
          <button @click="exportToExcel" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-2"></i>
            Exportar Excel
          </button>
          
          <button @click="exportToCSV" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-filetype-csv me-2"></i>
            Exportar CSV
          </button>
          
          <button @click="printReport" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-2"></i>
            Imprimir
          </button>

          <button @click="resetExecution" class="btn btn-outline-warning btn-sm ms-auto">
            <i class="bi bi-arrow-clockwise me-2"></i>
            Executar Novamente
          </button>
        </div>

        <!-- Data Table -->
        <div class="results-table-container">
          <div class="table-responsive">
            <table class="table table-dark table-hover table-striped">
              <thead>
                <tr>
                  <th v-for="column in report.columns" :key="column" class="text-white">
                    {{ getColumnLabel(column) }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in paginatedData" :key="index">
                  <td v-for="column in report.columns" :key="column">
                    {{ formatValue(row[column], column) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="pagination-container">
            <button 
              @click="currentPage--" 
              :disabled="currentPage === 1"
              class="btn btn-sm btn-outline-light"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            
            <span class="text-white mx-3">
              Página {{ currentPage }} de {{ totalPages }}
              <span class="text-white-50 ms-2">({{ totalRecords }} registros)</span>
            </span>
            
            <button 
              @click="currentPage++" 
              :disabled="currentPage === totalPages"
              class="btn btn-sm btn-outline-light"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="execution-loading">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="text-white mt-3">Processando relatório...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  reportId: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['close']);

const loading = ref(false);
const executed = ref(false);

// ✅ CORRIGIDO - Inicializar com valores padrão
const report = ref({
  columns: [],
  name: '',
  description: '',
  type: ''
});

const reportData = ref([]);
const additionalFilters = ref({});
const executionTime = ref(0);
const currentPage = ref(1);
const perPage = 50;

const availableColumns = ref({});

// ✅ CORRIGIDO - Proteger contra undefined
const totalRecords = computed(() => reportData.value?.length || 0);

const totalPages = computed(() => {
  if (!totalRecords.value || totalRecords.value === 0) return 1;
  return Math.ceil(totalRecords.value / perPage);
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  const end = start + perPage;
  return reportData.value.slice(start, end);
});

const loadReport = async () => {
  try {
    const response = await axios.get(`/api/v1/reports/${props.reportId}`);
    report.value = response.data;
    
    // Carregar labels das colunas
    if (report.value.type) {
      const columnsResp = await axios.get(`/api/v1/reports/columns/${report.value.type}`);
      availableColumns.value = columnsResp.data;
    }
  } catch (error) {
    console.error('Erro ao carregar relatório:', error);
    alert('Erro ao carregar relatório. Tente novamente.');
  }
};

const executeReport = async () => {
  loading.value = true;
  const startTime = Date.now();
  
  try {
    const response = await axios.post(`/api/v1/reports/${props.reportId}/execute`, additionalFilters.value);
    
    // ✅ CORRIGIDO - Garantir que data existe
    reportData.value = response.data.data || [];
    executionTime.value = ((Date.now() - startTime) / 1000).toFixed(2);
    executed.value = true;
    currentPage.value = 1;
  } catch (error) {
    console.error('Erro ao executar relatório:', error);
    alert('Erro ao executar relatório. Tente novamente.');
  } finally {
    loading.value = false;
  }
};

const resetExecution = () => {
  executed.value = false;
  reportData.value = [];
  additionalFilters.value = {};
  currentPage.value = 1;
};

const exportReport = async (format) => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/v1/reports/${props.reportId}/export`, {
      params: { ...additionalFilters.value, format: format },
      responseType: 'blob', // Important para receber o arquivo
    });

    // Criar um link temporário para o download
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;

    // Extrair o nome do arquivo do header 'Content-Disposition'
    const contentDisposition = response.headers['content-disposition'];
    let filename = `relatorio.${format}`; // Fallback
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch.length > 1) {
        filename = filenameMatch[1];
      }
    }

    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();

    // Limpar
    link.remove();
    window.URL.revokeObjectURL(url);

  } catch (error) {
    console.error(`Erro ao exportar para ${format}:`, error);
    // Tenta ler o erro do blob, caso o backend tenha retornado um JSON de erro
    if (error.response && error.response.data) {
      const reader = new FileReader();
      reader.onload = () => {
        try {
          const errorJson = JSON.parse(reader.result);
          alert(`Erro ao exportar: ${errorJson.message || 'Erro desconhecido.'}`);
        } catch (e) {
          alert(`Erro ao exportar o relatório para ${format}. Verifique o console para mais detalhes.`);
        }
      };
      reader.readAsText(error.response.data);
    } else {
      alert(`Erro ao exportar o relatório para ${format}. Verifique o console para mais detalhes.`);
    }
  } finally {
    loading.value = false;
  }
};

const exportToExcel = async () => {
  try {
    const response = await axios.get(`/api/v1/reports/${props.reportId}/export`, {
      params: { format: 'excel' },
      responseType: 'blob' // ✅ IMPORTANTE!
    });
    
    // Criar blob e fazer download
    const blob = new Blob([response.data], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `relatorio_${props.reportId}_${new Date().getTime()}.xlsx`;
    link.click();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erro ao exportar Excel:', error);
    alert('Erro ao exportar arquivo Excel');
  }
};

const exportToCSV = async () => {
  try {
    const response = await axios.get(`/api/v1/reports/${props.reportId}/export`, {
      params: { format: 'csv' },
      responseType: 'blob' // ✅ IMPORTANTE!
    });
    
    // Criar blob e fazer download
    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `relatorio_${props.reportId}_${new Date().getTime()}.csv`;
    link.click();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erro ao exportar CSV:', error);
    alert('Erro ao exportar arquivo CSV');
  }
};


const printReport = () => {
  window.print();
};

const getColumnLabel = (column) => {
  return availableColumns.value[column] || column;
};

const formatValue = (value, column) => {
  if (value === null || value === undefined) return '-';
  
  // Formatação de datas
  if (column.includes('data_') || column.includes('created_at') || column.includes('updated_at')) {
    try {
      return new Date(value).toLocaleDateString('pt-BR');
    } catch {
      return value;
    }
  }
  
  // Formatação de booleanos
  if (typeof value === 'boolean') {
    return value ? 'Sim' : 'Não';
  }
  
  // Formatação de valores numéricos grandes
  if (typeof value === 'number' && value > 1000000) {
    return value.toLocaleString('pt-BR');
  }
  
  return value;
};

onMounted(() => {
  loadReport();
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

.execution-modal {
  background: rgba(26, 26, 46, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  max-width: 1200px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.execution-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.execution-filters {
  padding: 2rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.execution-results {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
}

.execution-loading {
  padding: 4rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.results-stats {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-item i {
  font-size: 2rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
}

.results-actions {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.results-table-container {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
  padding: 1rem;
}

.table {
  margin-bottom: 0;
}

.table thead th {
  position: sticky;
  top: 0;
  background: rgba(26, 26, 46, 0.95);
  z-index: 10;
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  padding: 1rem;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.875rem;
  letter-spacing: 0.5px;
}

.table tbody td {
  padding: 0.75rem 1rem;
  color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.1);
}

.table-hover tbody tr:hover {
  background: rgba(102, 126, 234, 0.1);
}

.pagination-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
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

@media print {
  .modal-backdrop {
    background: white;
  }
  
  .execution-modal {
    background: white;
    border: none;
    max-width: 100%;
    max-height: none;
  }
  
  .execution-header button,
  .results-actions,
  .pagination-container {
    display: none !important;
  }
  
  .table {
    color: black;
  }
  
  .table thead th {
    background: #f0f0f0;
    color: black;
  }
}
</style>
