<template>
  <div class="relatorios-page">
    <!-- Header -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="text-white mb-2">
              <i class="bi bi-file-earmark-bar-graph me-2"></i>
              Relatórios Personalizados
            </h3>
            <p class="text-white-50 mb-0">
              Crie, gerencie e execute relatórios customizados
            </p>
          </div>
          <div class="col-md-4 text-end">
            <button @click="openWizard()" class="btn btn-primary">
              <i class="bi bi-plus-lg me-2"></i>
              Novo Relatório
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <input 
              v-model="search" 
              type="text" 
              class="form-control bg-transparent text-white border-secondary" 
              placeholder="🔍 Buscar relatórios..."
            >
          </div>
          <div class="col-md-3">
            <select v-model="filterType" class="form-select bg-transparent text-white border-secondary">
              <option value="">Todos os Tipos</option>
              <option value="institucional">Institucional</option>
              <option value="academico">Acadêmico</option>
              <option value="colaboradores">Colaboradores</option>
              <option value="rh">RH</option>
              <option value="setores">Setores</option>
              <option value="campi">Campi</option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="filterFormat" class="form-select bg-transparent text-white border-secondary">
              <option value="">Todos os Formatos</option>
              <option value="excel">Excel</option>
              <option value="csv">CSV</option>
              <option value="pdf">PDF</option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="filterVisibility" class="form-select bg-transparent text-white border-secondary">
              <option value="">Todos</option>
              <option value="public">Públicos</option>
              <option value="private">Privados</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="row g-3">
      <div v-for="n in 6" :key="n" class="col-md-6 col-lg-4">
        <RelatorioCardSkeleton />
      </div>
    </div>

    <!-- Lista de Relatórios -->
    <div v-else class="row g-3">
      <div v-for="report in filteredReports" :key="report.id" class="col-md-6 col-lg-4">
        <div class="card card-glass report-card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span :class="getTypeClass(report.type)" class="badge mb-2">
                  {{ getTypeLabel(report.type) }}
                </span>
                <h5 class="text-white mb-1">{{ report.name }}</h5>
                <p class="text-white-50 small mb-0">{{ report.description || 'Sem descrição' }}</p>
              </div>
              <div class="dropdown">
                <button 
                  class="btn btn-sm btn-outline-light" 
                  type="button" 
                  data-bs-toggle="dropdown"
                >
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark">
                  <li>
                    <a class="dropdown-item" @click="executeReport(report.id)" href="#">
                      <i class="bi bi-play-fill me-2"></i>Executar
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" @click="exportReport(report.id)" href="#">
                      <i class="bi bi-download me-2"></i>Exportar
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item" @click="editReport(report)" href="#">
                      <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item text-danger" @click="deleteReport(report.id)" href="#">
                      <i class="bi bi-trash me-2"></i>Excluir
                    </a>
                  </li>
                </ul>
              </div>
            </div>

            <div class="report-meta">
              <div class="d-flex align-items-center text-white-50 small mb-2">
                <i class="bi bi-file-earmark me-2"></i>
                <span>{{ getFormatLabel(report.format) }}</span>
              </div>
              <div class="d-flex align-items-center text-white-50 small mb-2">
                <i class="bi bi-person me-2"></i>
                <span>{{ report.creator?.name || 'Sistema' }}</span>
              </div>
              <div class="d-flex align-items-center text-white-50 small mb-2">
                <i class="bi bi-calendar me-2"></i>
                <span>{{ formatDate(report.created_at) }}</span>
              </div>
              <div v-if="report.is_public" class="d-flex align-items-center text-success small">
                <i class="bi bi-globe me-2"></i>
                <span>Público</span>
              </div>
              <div v-else class="d-flex align-items-center text-warning small">
                <i class="bi bi-lock me-2"></i>
                <span>Privado</span>
              </div>
            </div>
          </div>

          <div class="card-footer bg-transparent border-0">
            <button @click="executeReport(report.id)" class="btn btn-sm btn-outline-primary w-100">
              <i class="bi bi-play-fill me-2"></i>
              Executar Relatório
            </button>
          </div>
        </div>
      </div>

      <!-- Mensagem vazia -->
      <div v-if="filteredReports.length === 0" class="col-12">
        <div class="text-center py-5">
          <i class="bi bi-inbox display-1 text-white-50"></i>
          <p class="text-white-50 mt-3">Nenhum relatório encontrado</p>
          <button @click="openWizard()" class="btn btn-primary mt-3">
            <i class="bi bi-plus-lg me-2"></i>
            Criar Primeiro Relatório
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Wizard -->
    <teleport to="body">
      <RelatorioWizard 
        v-if="showWizard" 
        :report="selectedReport"
        @close="closeWizard"
        @saved="onReportSaved"
      />
    </teleport>

    <!-- Modal Execução -->
    <teleport to="body">
      <RelatorioExecution 
        v-if="showExecution" 
        :reportId="executingReportId"
        @close="closeExecution"
      />
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import RelatorioWizard from './RelatorioWizard.vue';
import RelatorioExecution from './RelatorioExecution.vue';
import RelatorioCardSkeleton from './RelatorioCardSkeleton.vue';

const loading = ref(true);
const reports = ref([]);
const search = ref('');
const filterType = ref('');
const filterFormat = ref('');
const filterVisibility = ref('');

const showWizard = ref(false);
const showExecution = ref(false);
const selectedReport = ref(null);
const executingReportId = ref(null);

// Computeds
const filteredReports = computed(() => {
  return reports.value.filter(report => {
    const matchSearch = !search.value || 
      report.name.toLowerCase().includes(search.value.toLowerCase()) ||
      (report.description && report.description.toLowerCase().includes(search.value.toLowerCase()));
    
    const matchType = !filterType.value || report.type === filterType.value;
    const matchFormat = !filterFormat.value || report.format === filterFormat.value;
    const matchVisibility = !filterVisibility.value || 
      (filterVisibility.value === 'public' && report.is_public) ||
      (filterVisibility.value === 'private' && !report.is_public);
    
    return matchSearch && matchType && matchFormat && matchVisibility;
  });
});

// Métodos
const loadReports = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/reports');
    reports.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar relatórios:', error);
  } finally {
    loading.value = false;
  }
};

const openWizard = (report = null) => {
  selectedReport.value = report;
  showWizard.value = true;
};

const closeWizard = () => {
  showWizard.value = false;
  selectedReport.value = null;
};

const onReportSaved = () => {
  closeWizard();
  loadReports();
};

const editReport = (report) => {
  openWizard(report);
};

const executeReport = (reportId) => {
  executingReportId.value = reportId;
  showExecution.value = true;
};

const closeExecution = () => {
  showExecution.value = false;
  executingReportId.value = null;
};

const exportReport = async (reportId) => {
  try {
    window.open(`/api/v1/reports/${reportId}/export`, '_blank');
  } catch (error) {
    console.error('Erro ao exportar relatório:', error);
  }
};

const deleteReport = async (reportId) => {
  if (!confirm('Tem certeza que deseja excluir este relatório?')) return;
  
  try {
    await axios.delete(`/api/v1/reports/${reportId}`);
    loadReports();
  } catch (error) {
    console.error('Erro ao excluir relatório:', error);
  }
};

const getTypeLabel = (type) => {
  const labels = {
    'institucional': 'Institucional',
    'academico': 'Acadêmico',
    'colaboradores': 'Colaboradores',
    'rh': 'RH',
    'setores': 'Setores',
    'campi': 'Campi'
  };
  return labels[type] || type;
};

const getTypeClass = (type) => {
  const classes = {
    'institucional': 'bg-primary',
    'academico': 'bg-success',
    'colaboradores': 'bg-info',
    'rh': 'bg-warning',
    'setores': 'bg-danger',
    'campi': 'bg-secondary'
  };
  return classes[type] || 'bg-secondary';
};

const getFormatLabel = (format) => {
  const labels = {
    'excel': 'Excel (.xlsx)',
    'csv': 'CSV',
    'pdf': 'PDF'
  };
  return labels[format] || format;
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

onMounted(() => {
  loadReports();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

.report-card {
  transition: transform 0.3s, box-shadow 0.3s;
}

.report-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.report-meta {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 1rem;
}

.form-select option, .form-control {
  background: #1a1a2e;
  color: white;
}
</style>
