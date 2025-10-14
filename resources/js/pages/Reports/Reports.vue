<template>
  <div>
    <PageHeader 
      title="Relatórios Customizáveis"
      :breadcrumbs="[{ label: 'Relatórios' }]"
    >
      <template #actions>
        <button class="btn btn-primary" @click="showCreateModal = true">
          <i class="bi bi-plus-lg"></i> Novo Relatório
        </button>
      </template>
    </PageHeader>

    <!-- Lista de Relatórios -->
    <div class="row g-4">
      <div 
        v-for="report in reports" 
        :key="report.id"
        class="col-md-6 col-lg-4"
      >
        <div class="card card-glass report-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="report-icon" :class="`icon-${report.type}`">
                <i :class="getTypeIcon(report.type)"></i>
              </div>
              <div class="dropdown">
                <button 
                  class="btn btn-sm btn-link text-white" 
                  data-bs-toggle="dropdown"
                >
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" @click="executeReport(report.id)">
                      <i class="bi bi-play-circle"></i> Executar
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" @click="exportReport(report.id)">
                      <i class="bi bi-download"></i> Exportar
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" @click="editReport(report)">
                      <i class="bi bi-pencil"></i> Editar
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item text-danger" @click="deleteReport(report.id)">
                      <i class="bi bi-trash"></i> Excluir
                    </a>
                  </li>
                </ul>
              </div>
            </div>

            <h5 class="report-title">{{ report.name }}</h5>
            <p class="report-description">{{ report.description || 'Sem descrição' }}</p>

            <div class="report-meta">
              <span class="badge bg-secondary">{{ formatType(report.type) }}</span>
              <span class="badge bg-info">{{ report.format.toUpperCase() }}</span>
              <span v-if="report.is_public" class="badge bg-success">Público</span>
              <span v-if="report.is_scheduled" class="badge bg-warning">Agendado</span>
            </div>

            <div class="report-stats mt-3">
              <small class="text-muted">
                <i class="bi bi-play-circle"></i> {{ report.execution_count }} execuções
              </small>
              <small class="text-muted">
                <i class="bi bi-person"></i> {{ report.creator?.name }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <div v-if="reports.length === 0" class="col-12">
        <div class="text-center py-5">
          <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
          <p class="text-muted mt-2">Nenhum relatório criado ainda</p>
        </div>
      </div>
    </div>

    <!-- Modal Criar/Editar Relatório -->
    <div 
      class="modal fade" 
      :class="{ show: showCreateModal }" 
      :style="{ display: showCreateModal ? 'block' : 'none' }"
      tabindex="-1"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingReport ? 'Editar' : 'Novo' }} Relatório</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveReport">
              <!-- Nome -->
              <div class="mb-3">
                <label class="form-label">Nome do Relatório</label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.name"
                  required
                >
              </div>

              <!-- Descrição -->
              <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea 
                  class="form-control" 
                  v-model="form.description"
                  rows="2"
                ></textarea>
              </div>

              <!-- Tipo -->
              <div class="mb-3">
                <label class="form-label">Tipo de Relatório</label>
                <select 
                  class="form-select" 
                  v-model="form.type"
                  @change="loadAvailableColumns"
                  required
                >
                  <option value="">Selecione...</option>
                  <option value="institucional">Institucional</option>
                  <option value="academico">Acadêmico</option>
                  <option value="rh">Recursos Humanos</option>
                </select>
              </div>

              <!-- Colunas -->
              <div class="mb-3" v-if="form.type">
                <label class="form-label">Colunas</label>
                <div class="columns-selector">
                  <div 
                    v-for="(label, column) in availableColumns" 
                    :key="column"
                    class="form-check"
                  >
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      :value="column"
                      v-model="form.columns"
                      :id="`col-${column}`"
                    >
                    <label class="form-check-label" :for="`col-${column}`">
                      {{ label }}
                    </label>
                  </div>
                </div>
              </div>

              <!-- Filtros -->
              <div class="mb-3">
                <label class="form-label">Filtros</label>
                
                <!-- Filtros Institucionais -->
                <div v-if="form.type === 'institucional'" class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">Estado</label>
                    <select class="form-select form-select-sm" v-model="form.filters.estado">
                      <option value="">Todos</option>
                      <option value="SP">São Paulo</option>
                      <option value="RJ">Rio de Janeiro</option>
                      <option value="MG">Minas Gerais</option>
                      <!-- Adicionar mais estados -->
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Tipo Organização</label>
                    <select class="form-select form-select-sm" v-model="form.filters.tipo_organizacao">
                      <option value="">Todos</option>
                      <option value="Universidade">Universidade</option>
                      <option value="Centro Universitário">Centro Universitário</option>
                      <option value="Faculdade">Faculdade</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Data Início</label>
                    <input type="date" class="form-control form-control-sm" v-model="form.filters.data_inicio">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Data Fim</label>
                    <input type="date" class="form-control form-control-sm" v-model="form.filters.data_fim">
                  </div>
                </div>

                <!-- Filtros Acadêmicos -->
                <div v-if="form.type === 'academico'" class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">Grau Acadêmico</label>
                    <select class="form-select form-select-sm" v-model="form.filters.grau_academico">
                      <option value="">Todos</option>
                      <option value="Bacharelado">Bacharelado</option>
                      <option value="Licenciatura">Licenciatura</option>
                      <option value="Tecnólogo">Tecnólogo</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Modalidade</label>
                    <select class="form-select form-select-sm" v-model="form.filters.modalidade">
                      <option value="">Todas</option>
                      <option value="Presencial">Presencial</option>
                      <option value="EAD">EAD</option>
                      <option value="Híbrido">Híbrido</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Formato -->
              <div class="mb-3">
                <label class="form-label">Formato de Exportação</label>
                <div class="btn-group w-100" role="group">
                  <input 
                    type="radio" 
                    class="btn-check" 
                    name="format" 
                    id="format-excel"
                    value="excel"
                    v-model="form.format"
                  >
                  <label class="btn btn-outline-success" for="format-excel">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                  </label>

                  <input 
                    type="radio" 
                    class="btn-check" 
                    name="format" 
                    id="format-csv"
                    value="csv"
                    v-model="form.format"
                  >
                  <label class="btn btn-outline-info" for="format-csv">
                    <i class="bi bi-file-earmark-text"></i> CSV
                  </label>

                  <input 
                    type="radio" 
                    class="btn-check" 
                    name="format" 
                    id="format-pdf"
                    value="pdf"
                    v-model="form.format"
                  >
                  <label class="btn btn-outline-danger" for="format-pdf">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                  </label>
                </div>
              </div>

              <!-- Opções -->
              <div class="mb-3">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="is_public"
                    v-model="form.is_public"
                  >
                  <label class="form-check-label" for="is_public">
                    Tornar público (visível para outros usuários)
                  </label>
                </div>

                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="is_scheduled"
                    v-model="form.is_scheduled"
                  >
                  <label class="form-check-label" for="is_scheduled">
                    Agendar execução automática
                  </label>
                </div>

                <div v-if="form.is_scheduled" class="mt-2">
                  <label class="form-label small">Frequência</label>
                  <select class="form-select form-select-sm" v-model="form.schedule_frequency">
                    <option value="daily">Diária</option>
                    <option value="weekly">Semanal</option>
                    <option value="monthly">Mensal</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="saveReport">
              <i class="bi bi-save"></i> Salvar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Preview de Resultados -->
    <div 
      class="modal fade" 
      :class="{ show: showPreviewModal }" 
      :style="{ display: showPreviewModal ? 'block' : 'none' }"
      tabindex="-1"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
          <div class="modal-header">
            <h5 class="modal-title">Prévia do Relatório</h5>
            <button type="button" class="btn-close btn-close-white" @click="showPreviewModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="previewData.length > 0" class="table-responsive">
              <table class="table table-dark table-striped">
                <thead>
                  <tr>
                    <th v-for="column in Object.keys(previewData[0])" :key="column">
                      {{ column }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in previewData.slice(0, 10)" :key="index">
                    <td v-for="column in Object.keys(row)" :key="column">
                      {{ row[column] }}
                    </td>
                  </tr>
                </tbody>
              </table>
              <p class="text-muted text-center mt-2">
                Mostrando 10 de {{ previewData.length }} registros
              </p>
            </div>
            <div v-else class="text-center py-5">
              <i class="bi bi-inbox fs-1 text-muted"></i>
              <p class="text-muted mt-2">Nenhum dado encontrado</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Overlays -->
    <div 
      v-if="showCreateModal || showPreviewModal" 
      class="modal-backdrop fade show"
      @click="closeModal"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PageHeader from '@/components/PageHeader.vue';
import Swal from 'sweetalert2';

const reports = ref([]);
const showCreateModal = ref(false);
const showPreviewModal = ref(false);
const editingReport = ref(null);
const availableColumns = ref({});
const previewData = ref([]);

const form = ref({
  name: '',
  description: '',
  type: '',
  filters: {},
  columns: [],
  format: 'excel',
  is_public: false,
  is_scheduled: false,
  schedule_frequency: null
});

const fetchReports = async () => {
  try {
    const response = await axios.get('/api/v1/reports');
    reports.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar relatórios:', error);
  }
};

const loadAvailableColumns = async () => {
  try {
    const response = await axios.get(`/api/v1/reports/columns/${form.value.type}`);
    availableColumns.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar colunas:', error);
  }
};

const saveReport = async () => {
  try {
    if (editingReport.value) {
      await axios.put(`/api/v1/reports/${editingReport.value.id}`, form.value);
      Swal.fire('Sucesso!', 'Relatório atualizado com sucesso', 'success');
    } else {
      await axios.post('/api/v1/reports', form.value);
      Swal.fire('Sucesso!', 'Relatório criado com sucesso', 'success');
    }
    closeModal();
    fetchReports();
  } catch (error) {
    console.error('Erro ao salvar relatório:', error);
    Swal.fire('Erro!', 'Erro ao salvar relatório', 'error');
  }
};

const editReport = (report) => {
  editingReport.value = report;
  form.value = {
    name: report.name,
    description: report.description,
    type: report.type,
    filters: report.filters,
    columns: report.columns,
    format: report.format,
    is_public: report.is_public,
    is_scheduled: report.is_scheduled,
    schedule_frequency: report.schedule_frequency
  };
  loadAvailableColumns();
  showCreateModal.value = true;
};

const deleteReport = async (id) => {
  const result = await Swal.fire({
    title: 'Confirmar exclusão?',
    text: 'Esta ação não pode ser desfeita',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/v1/reports/${id}`);
      Swal.fire('Excluído!', 'Relatório excluído com sucesso', 'success');
      fetchReports();
    } catch (error) {
      console.error('Erro ao excluir relatório:', error);
      Swal.fire('Erro!', 'Erro ao excluir relatório', 'error');
    }
  }
};

const executeReport = async (id) => {
  try {
    const response = await axios.post(`/api/v1/reports/${id}/execute`);
    previewData.value = response.data.data;
    showPreviewModal.value = true;
  } catch (error) {
    console.error('Erro ao executar relatório:', error);
    Swal.fire('Erro!', 'Erro ao executar relatório', 'error');
  }
};

const exportReport = async (id) => {
  try {
    const response = await axios.get(`/api/v1/reports/${id}/export`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `relatorio_${id}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();

    Swal.fire('Sucesso!', 'Relatório exportado com sucesso', 'success');
  } catch (error) {
    console.error('Erro ao exportar relatório:', error);
    Swal.fire('Erro!', 'Erro ao exportar relatório', 'error');
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showPreviewModal.value = false;
  editingReport.value = null;
  form.value = {
    name: '',
    description: '',
    type: '',
    filters: {},
    columns: [],
    format: 'excel',
    is_public: false,
    is_scheduled: false,
    schedule_frequency: null
  };
};

const formatType = (type) => {
  const types = {
    'institucional': 'Institucional',
    'academico': 'Acadêmico',
    'rh': 'RH',
    'financeiro': 'Financeiro'
  };
  return types[type] || type;
};

const getTypeIcon = (type) => {
  const icons = {
    'institucional': 'bi-building',
    'academico': 'bi-mortarboard',
    'rh': 'bi-people',
    'financeiro': 'bi-cash-stack'
  };
  return icons[type] || 'bi-file-earmark-text';
};

onMounted(() => {
  fetchReports();
});
</script>

<style scoped>
.report-card {
  transition: transform 0.2s;
  height: 100%;
}

.report-card:hover {
  transform: translateY(-5px);
}

.report-icon {
  width: 50px;
  height: 50px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.icon-institucional {
  background: rgba(102, 126, 234, 0.2);
  color: #667eea;
}

.icon-academico {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.icon-rh {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.icon-financeiro {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.report-title {
  color: rgba(255, 255, 255, 0.95);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.report-description {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.report-meta {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.report-stats {
  display: flex;
  justify-content: space-between;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.columns-selector {
  max-height: 300px;
  overflow-y: auto;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 0.5rem;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
  display: block;
}
</style>
