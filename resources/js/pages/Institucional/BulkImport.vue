<template>
  <div>
    <PageHeader 
      title="Importação em Massa"
      :breadcrumbs="[
        { label: 'Configurações' },
        { label: 'Importação em Massa' }
      ]"
    />

    <!-- Alerta: Evento Necessário -->
    <div v-if="!activeEvent" class="alert alert-danger-glass mb-4">
      <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
        <div>
          <strong>Evento de Importação Necessário</strong>
          <p class="mb-0 mt-1">
            Para realizar importações em massa, é necessário criar e ativar um <strong>Evento de Importação</strong>.
            Isso garante a integridade dos dados e bloqueia acessos durante o processo.
          </p>
          <router-link to="/admin/institucional/system-events" class="btn btn-sm btn-light mt-2">
            <i class="bi bi-calendar-event"></i> Ir para Eventos de Sistema
          </router-link>
        </div>
      </div>
    </div>

    <!-- Alerta: Evento Ativo -->
    <div v-if="activeEvent && activeEvent.type === 'import'" class="alert alert-success-glass mb-4">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <i class="bi bi-check-circle-fill me-3" style="font-size: 1.5rem;"></i>
          <div>
            <strong>Evento Ativo: {{ activeEvent.title }}</strong>
            <p class="mb-0 mt-1">{{ activeEvent.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Templates Disponíveis -->
    <div class="card card-glass mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="bi bi-file-earmark-arrow-down me-2"></i>
          Baixar Templates de Importação
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          Baixe o arquivo modelo correspondente ao tipo de dados que deseja importar. 
          Os templates já contêm as colunas corretas e um exemplo de preenchimento.
        </p>
        
        <TableSkeleton v-if="templates.length === 0" :columns="3" :rows="2" />
        
        <div v-else class="row g-3">
          <div class="col-md-4" v-for="template in templates" :key="template.type">
            <div class="template-card">
              <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="bi bi-file-earmark-spreadsheet text-success me-2" style="font-size: 1.8rem;"></i>
                  <div>
                    <strong class="d-block">{{ template.label }}</strong>
                    <small class="text-muted">{{ template.description }}</small>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <small class="text-muted d-block mb-2">
                  <i class="bi bi-asterisk text-danger" style="font-size: 0.6rem;"></i>
                  <strong>Campos obrigatórios:</strong>
                </small>
                <div class="d-flex flex-wrap gap-1">
                  <span 
                    v-for="field in template.required_fields" 
                    :key="field"
                    class="badge bg-danger"
                    style="font-size: 0.7rem;"
                  >
                    {{ field }}
                  </span>
                </div>
              </div>
              
              <button 
                @click="downloadTemplate(template.type)" 
                class="btn btn-sm btn-outline-success w-100"
              >
                <i class="bi bi-download"></i> Baixar Template CSV
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulário de Upload -->
    <div v-if="activeEvent && activeEvent.type === 'import'" class="card card-glass mb-4">
      <div class="card-header">
        <h5 class="mb-0">Importar Dados</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Tipo de Dados</label>
            <select class="form-select" v-model="importType">
              <option value="">Selecione...</option>
              <option value="grupos_educacionais">Grupos Educacionais</option>
              <option value="mantenedoras">Mantenedoras</option>
              <option value="instituicoes">Instituições</option>
              <option value="campi">Campi</option>
              <option value="setores">Setores</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Arquivo (CSV ou Excel)</label>
            <input 
              type="file" 
              class="form-control" 
              @change="handleFileSelect"
              accept=".csv,.xlsx,.xls"
            >
          </div>
        </div>

        <div class="d-flex gap-2">
          <button 
            @click="previewFile" 
            class="btn btn-primary"
            :disabled="!importType || !selectedFile || loading"
          >
            <i class="bi bi-eye"></i> Visualizar Preview
          </button>
          
          <button 
            v-if="previewData" 
            @click="confirmImport" 
            class="btn btn-success"
            :disabled="importing"
          >
            <i class="bi bi-upload"></i> 
            {{ importing ? 'Importando...' : 'Confirmar Importação' }}
          </button>
          
          <button 
            v-if="previewData" 
            @click="cancelPreview" 
            class="btn btn-secondary"
            :disabled="importing"
          >
            <i class="bi bi-x"></i> Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Preview dos Dados -->
    <div v-if="previewData" class="card card-glass mb-4">
      <div class="card-header">
        <h5 class="mb-0">Preview dos Dados (primeiras 10 linhas)</h5>
        <small class="text-muted">Total de {{ previewData.total_rows }} linhas no arquivo</small>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th class="ps-4">#</th>
                <th v-for="(header, index) in previewData.headers" :key="index">
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rowIndex) in previewData.preview" :key="rowIndex">
                <td class="ps-4">{{ rowIndex + 1 }}</td>
                <td v-for="(cell, cellIndex) in row" :key="cellIndex">
                  {{ cell }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Histórico de Importações -->
    <div class="card card-glass">
      <div class="card-header">
        <h5 class="mb-0">Histórico de Importações</h5>
      </div>
      <div class="card-body p-0">
        <TableSkeleton v-if="loadingHistory" :columns="6" :rows="5" />
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4">Data</th>
                <th>Tipo</th>
                <th>Arquivo</th>
                <th>Status</th>
                <th>Sucesso</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in importHistory" :key="log.id">
                <td class="ps-4">{{ formatDateTime(log.created_at) }}</td>
                <td>
                  <span class="badge bg-primary">{{ getTypeLabel(log.import_type) }}</span>
                </td>
                <td>{{ log.file_name }}</td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(log.status)">
                    {{ getStatusLabel(log.status) }}
                  </span>
                </td>
                <td>
                  {{ log.success_count }}/{{ log.total_rows }}
                  <span v-if="log.error_count > 0" class="text-danger ms-2">
                    ({{ log.error_count }} erros)
                  </span>
                </td>
                <td class="text-center">
                  <button 
                    @click="viewDetails(log)" 
                    class="btn btn-sm btn-primary"
                    title="Ver Detalhes"
                  >
                    <i class="bi bi-eye"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="importHistory.length === 0">
                <td colspan="6" class="text-center text-muted py-4">
                  Nenhuma importação realizada ainda.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Detalhes da Importação -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-glass">
          <div class="modal-header border-bottom border-secondary">
            <h5 class="modal-title text-white">
              <i class="bi bi-info-circle me-2"></i>
              Detalhes da Importação
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="selectedLog">
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="detail-item">
                  <label>Arquivo:</label>
                  <span>{{ selectedLog.file_name }}</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="detail-item">
                  <label>Tipo:</label>
                  <span class="badge bg-primary">{{ getTypeLabel(selectedLog.import_type) }}</span>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <div class="stats-card">
                  <div class="stats-icon bg-info">
                    <i class="bi bi-list-ol"></i>
                  </div>
                  <div class="stats-content">
                    <small>Total de Linhas</small>
                    <strong>{{ selectedLog.total_rows }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="stats-card">
                  <div class="stats-icon bg-success">
                    <i class="bi bi-check-circle"></i>
                  </div>
                  <div class="stats-content">
                    <small>Sucesso</small>
                    <strong>{{ selectedLog.success_count }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="stats-card">
                  <div class="stats-icon bg-danger">
                    <i class="bi bi-x-circle"></i>
                  </div>
                  <div class="stats-content">
                    <small>Erros</small>
                    <strong>{{ selectedLog.error_count }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="selectedLog.errors && selectedLog.errors.length > 0" class="errors-section">
              <div class="errors-header mb-3">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                <strong class="text-white">Erros Encontrados ({{ selectedLog.errors.length }})</strong>
              </div>
              <div class="error-list">
                <div 
                  v-for="(error, index) in selectedLog.errors" 
                  :key="index"
                  class="error-item"
                >
                  <span class="error-line">Linha {{ error.line }}</span>
                  <span class="error-message">{{ error.error }}</span>
                </div>
              </div>
            </div>

            <div v-else class="alert alert-success-glass">
              <i class="bi bi-check-circle-fill me-2"></i>
              Importação concluída sem erros!
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';

const loading = ref(false);
const loadingHistory = ref(true);
const importing = ref(false);
const activeEvent = ref(null);
const importType = ref('');
const selectedFile = ref(null);
const previewData = ref(null);
const importHistory = ref([]);
const selectedLog = ref(null);
const templates = ref([]);
let detailsModalInstance = null;

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0];
  previewData.value = null;
};

const previewFile = async () => {
  if (!selectedFile.value || !importType.value) return;

  loading.value = true;
  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('type', importType.value);

  try {
    const response = await axios.post('/api/v1/imports/preview', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    previewData.value = response.data;
  } catch (error) {
    console.error('Erro ao gerar preview:', error);
    
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      alert('Erro de validação:\n' + errors.join('\n'));
    } else if (error.response?.data?.message) {
      alert('Erro: ' + error.response.data.message);
    } else {
      alert('Erro ao gerar preview do arquivo. Verifique o formato e tente novamente.');
    }
  } finally {
    loading.value = false;
  }
};

const confirmImport = async () => {
  if (!confirm('Tem certeza que deseja importar estes dados? Esta ação não pode ser desfeita.')) {
    return;
  }

  importing.value = true;
  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('type', importType.value);

  try {
    const response = await axios.post('/api/v1/imports/import', formData);
    alert(`Importação concluída!\nSucesso: ${response.data.log.success_count}\nErros: ${response.data.log.error_count}`);
    
    cancelPreview();
    fetchImportHistory();
  } catch (error) {
    console.error('Erro na importação:', error);
    alert('Erro na importação: ' + (error.response?.data?.message || 'Erro desconhecido'));
  } finally {
    importing.value = false;
  }
};

const cancelPreview = () => {
  previewData.value = null;
  selectedFile.value = null;
  importType.value = '';
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) fileInput.value = '';
};

const fetchActiveEvent = async () => {
  try {
    const response = await axios.get('/api/v1/system-events/current');
    activeEvent.value = response.data && response.data.id ? response.data : null;
  } catch (error) {
    console.error('Erro ao buscar evento ativo:', error);
  }
};

const fetchImportHistory = async () => {
  try {
    loadingHistory.value = true;
    const response = await axios.get('/api/v1/imports');
    importHistory.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar histórico:', error);
  } finally {
    loadingHistory.value = false;
  }
};

const fetchTemplates = async () => {
  try {
    const response = await axios.get('/api/v1/imports/templates/list');
    templates.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar templates:', error);
  }
};

const downloadTemplate = async (type) => {
  try {
    const response = await axios.get(`/api/v1/imports/templates/download/${type}`, {
      responseType: 'blob',
      headers: {
        'Accept': 'text/csv'
      }
    });
    
    const blob = new Blob([response.data], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `template_${type}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Erro ao baixar template:', error);
    alert('Erro ao baixar template.');
  }
};

const viewDetails = (log) => {
  selectedLog.value = log;
  detailsModalInstance?.show();
};

const getTypeLabel = (type) => {
  const labels = {
    grupos_educacionais: 'Grupos Educacionais',
    mantenedoras: 'Mantenedoras',
    instituicoes: 'Instituições',
    campi: 'Campi',
    setores: 'Setores'
  };
  return labels[type] || type;
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pendente',
    processing: 'Processando',
    completed: 'Concluído',
    failed: 'Falhou'
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-warning',
    processing: 'bg-info',
    completed: 'bg-success',
    failed: 'bg-danger'
  };
  return classes[status] || 'bg-secondary';
};

const formatDateTime = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  fetchActiveEvent();
  fetchImportHistory();
  fetchTemplates();
  
  const modalEl = document.getElementById('detailsModal');
  if (modalEl) {
    detailsModalInstance = new Modal(modalEl);
  }
});
</script>

<style scoped>
.alert-danger-glass {
  background: rgba(220, 53, 69, 0.15);
  border: 1px solid rgba(220, 53, 69, 0.4);
  border-radius: 0.75rem;
  padding: 1.25rem;
  backdrop-filter: blur(10px);
}

.alert-danger-glass strong {
  color: #dc3545;
}

.alert-danger-glass p {
  color: rgba(255, 255, 255, 0.8);
}

.alert-success-glass {
  background: rgba(40, 167, 69, 0.15);
  border: 1px solid rgba(40, 167, 69, 0.4);
  border-radius: 0.75rem;
  padding: 1.25rem;
  backdrop-filter: blur(10px);
  color: #28a745;
  display: flex;
  align-items: center;
}

.alert-success-glass p {
  color: rgba(255, 255, 255, 0.8);
}

.template-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  padding: 1rem;
  transition: all 0.3s ease;
}

.template-card:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(40, 167, 69, 0.3);
  transform: translateY(-2px);
}

.template-card strong {
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.95rem;
}

.template-card p {
  font-size: 0.8rem;
  line-height: 1.4;
}

.table thead th,
.table tbody td {
  color: rgba(255, 255, 255, 0.85);
}

.table tbody td strong {
  color: rgba(255, 255, 255, 0.95);
}

/* Modal Glass */
.modal-glass {
  background: rgba(20, 20, 30, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
}

.modal-glass .modal-header {
  padding: 1.5rem;
}

.modal-glass .modal-body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
}

/* Detail Items */
.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-item label {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-item span {
  color: rgba(255, 255, 255, 0.95);
  font-size: 1rem;
}

/* Stats Cards */
.stats-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.stats-card:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.stats-icon {
  width: 48px;
  height: 48px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stats-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stats-content small {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stats-content strong {
  color: rgba(255, 255, 255, 0.95);
  font-size: 1.5rem;
  font-weight: 700;
}

/* Errors Section */
.errors-section {
  margin-top: 2rem;
}

.errors-header {
  display: flex;
  align-items: center;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.error-list {
  max-height: 300px;
  overflow-y: auto;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 0.5rem;
  padding: 0;
  margin-top: 1rem;
}

.error-item {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  transition: background 0.2s;
}

.error-item:last-child {
  border-bottom: none;
}

.error-item:hover {
  background: rgba(255, 255, 255, 0.03);
}

.error-line {
  color: #ff6b6b;
  font-weight: 600;
  font-size: 0.875rem;
}

.error-message {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.875rem;
  line-height: 1.5;
}

/* Scrollbar para o modal */
.modal-glass .modal-body::-webkit-scrollbar,
.error-list::-webkit-scrollbar {
  width: 8px;
}

.modal-glass .modal-body::-webkit-scrollbar-track,
.error-list::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

.modal-glass .modal-body::-webkit-scrollbar-thumb,
.error-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.modal-glass .modal-body::-webkit-scrollbar-thumb:hover,
.error-list::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
