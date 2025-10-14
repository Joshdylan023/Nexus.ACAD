<template>
  <div>
    <PageHeader 
      title="Gestão de Mantenedoras"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Mantenedoras' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por razão social, CNPJ ou grupo..."
      v-model="buscaRapida"
    >
      <template #actions>
        <!-- ⭐ BOTÃO DE FILTROS -->
        <button 
          v-if="!showForm"
          @click="toggleFilters" 
          class="btn btn-outline-light me-2"
          :class="{ 'active': showFilters }"
        >
          <i class="bi bi-funnel me-2"></i>
          Filtros
          <span v-if="activeFilterCount > 0" class="badge bg-primary ms-2">
            {{ activeFilterCount }}
          </span>
        </button>

        <ViewToggle 
          v-if="!showForm"
          v-model="viewMode"
          class="me-2"
        />
        <ExportButton 
          v-if="!showForm"
          :data="mantenedorasFiltradas"
          :columns="exportColumns"
          fileName="mantenedoras"
          class="me-2"
        />
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Nova Mantenedora
        </button>
      </template>
    </PageHeader>

    <!-- ⭐ PAINEL DE FILTROS -->
    <AdvancedFilters
      :show="showFilters"
      :filters="filterConfig"
      v-model="activeFilters"
      @close="showFilters = false"
      @clear-all="clearAllFilters"
    />
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Mantenedora' : 'Adicionar Nova Mantenedora' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateMantenedora() : createMantenedora()">
          <div class="mb-3">
            <label class="form-label">Grupo Educacional *</label>
            <select 
              class="form-select" 
              v-model="form.grupo_educacional_id" 
              :class="{ 'is-invalid': errors.grupo_educacional_id }"
              required
            >
              <option :value="null">-- Selecione --</option>
              <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nome }}</option>
            </select>
            <div v-if="errors.grupo_educacional_id" class="invalid-feedback">{{ errors.grupo_educacional_id }}</div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Razão Social *</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.razao_social" 
                :class="{ 'is-invalid': errors.razao_social }"
                required
              >
              <div v-if="errors.razao_social" class="invalid-feedback">{{ errors.razao_social }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nome Fantasia</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.nome_fantasia"
                :class="{ 'is-invalid': errors.nome_fantasia }"
              >
              <div v-if="errors.nome_fantasia" class="invalid-feedback">{{ errors.nome_fantasia }}</div>
            </div>
          </div>
          
          <div class="mb-3">
            <MaskedInput
              v-model="form.cnpj"
              label="CNPJ *"
              mask="cnpj"
              placeholder="00.000.000/0000-00"
              required
              :error="errors.cnpj"
              hint="Informe um CNPJ válido com 14 dígitos"
            />
          </div>
          
          <div class="mb-3">
            <label class="form-label">Endereço Completo</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="form.endereco_completo"
              :class="{ 'is-invalid': errors.endereco_completo }"
            >
            <div v-if="errors.endereco_completo" class="invalid-feedback">{{ errors.endereco_completo }}</div>
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
            {{ submitting ? 'Salvando...' : 'Salvar' }}
          </button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2" :disabled="submitting">
            Cancelar
          </button>
        </form>
      </div>
    </div>

    <!-- Visualização em Tabela -->
    <div v-if="viewMode === 'table'" class="card card-glass">
      <div class="card-header"><h4>Mantenedoras Cadastradas</h4></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Razão Social</th>
              <th>Nome Fantasia</th>
              <th>CNPJ</th>
              <th>Grupo Educacional</th>
              <th>Auditoria</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="6" :rows="5" />
          <tbody v-else>
            <tr v-for="mantenedora in mantenedorasPaginadas" :key="mantenedora.id">
              <td class="ps-4">{{ mantenedora.razao_social }}</td>
              <td>{{ mantenedora.nome_fantasia || 'N/A' }}</td>
              <td>{{ formatCnpj(mantenedora.cnpj) }}</td>
              <td>{{ mantenedora.grupo_educacional?.nome || 'N/A' }}</td>
              <td>
                <div class="audit-info-compact">
                  <small>
                    <i class="bi bi-person-plus text-success me-1"></i>
                    <strong>{{ mantenedora.creator?.name || (mantenedora.created_by ? 'ID: ' + mantenedora.created_by : 'Sistema') }}</strong>
                    <br>
                    <i class="bi bi-clock text-muted me-1"></i>
                    {{ formatDate(mantenedora.created_at) }}
                    <template v-if="mantenedora.updated_at !== mantenedora.created_at">
                      <br>
                      <i class="bi bi-pencil text-warning me-1"></i>
                      <strong>{{ mantenedora.updater?.name || (mantenedora.updated_by ? 'ID: ' + mantenedora.updated_by : 'Sistema') }}</strong>
                      <br>
                      <i class="bi bi-clock text-muted me-1"></i>
                      {{ formatDate(mantenedora.updated_at) }}
                    </template>
                  </small>
                </div>
              </td>
              <td class="text-center">
                <button @click="showEditForm(mantenedora)" class="btn btn-sm btn-primary me-2" title="Editar Mantenedora">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(mantenedora)" class="btn btn-sm btn-danger me-2" title="Excluir Mantenedora">
                  <i class="bi bi-trash"></i>
                </button>
                <router-link :to="`/admin/institucional/mantenedoras/${mantenedora.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores">
                  <i class="bi bi-diagram-3"></i>
                </router-link>
              </td>
            </tr>
            <tr v-if="mantenedorasFiltradas.length === 0">
              <td colspan="6" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhuma mantenedora encontrada.' }}
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination
          :current-page="currentPage"
          :total-items="mantenedorasFiltradas.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <!-- Visualização em Cards -->
    <div v-else>
      <TableSkeleton v-if="loading" :columns="3" :rows="3" />
      <CardView 
        v-else
        :items="mantenedorasPaginadas"
        empty-message="Nenhuma mantenedora encontrada"
      >
        <template #header="{ item }">
          <div>
            <h5 class="mb-1" style="color: white; font-size: 1.125rem; font-weight: 600;">{{ item.razao_social }}</h5>
            <span class="badge bg-info text-white">{{ item.nome_fantasia || 'Sem nome fantasia' }}</span>
          </div>
        </template>

        <template #body="{ item }">
          <div class="info-group">
            <div class="info-item">
              <i class="bi bi-building me-2"></i>
              <span class="label">CNPJ:</span>
              <span class="value">{{ formatCnpj(item.cnpj) }}</span>
            </div>
            <div class="info-item">
              <i class="bi bi-diagram-3 me-2"></i>
              <span class="label">Grupo:</span>
              <span class="value">{{ item.grupo_educacional?.nome || 'Não vinculado' }}</span>
            </div>
            <div class="info-item" v-if="item.endereco_completo">
              <i class="bi bi-geo-alt me-2"></i>
              <span class="label">Endereço:</span>
              <span class="value">{{ item.endereco_completo }}</span>
            </div>

            <!-- AUDITORIA -->
            <div class="audit-info-card">
              <div class="audit-header">
                <i class="bi bi-shield-check me-2"></i>
                <strong>Informações de Auditoria</strong>
              </div>
              <div class="audit-details">
                <div class="audit-row">
                  <i class="bi bi-person-plus text-success me-2"></i>
                  <span class="audit-label">Criado por:</span>
                  <span class="audit-value">
                    {{ item.creator?.name || (item.created_by ? 'ID: ' + item.created_by : 'Sistema') }}
                  </span>
                </div>
                <div class="audit-row">
                  <i class="bi bi-clock text-muted me-2"></i>
                  <span class="audit-label">Em:</span>
                  <span class="audit-value">{{ formatDate(item.created_at) }}</span>
                </div>

                <template v-if="item.updated_at !== item.created_at">
                  <div class="audit-divider"></div>
                  <div class="audit-row">
                    <i class="bi bi-pencil text-warning me-2"></i>
                    <span class="audit-label">Última atualização:</span>
                    <span class="audit-value">{{ formatDate(item.updated_at) }}</span>
                  </div>
                  <div class="audit-row">
                    <i class="bi bi-person text-info me-2"></i>
                    <span class="audit-label">Por:</span>
                    <span class="audit-value">
                      {{ item.updater?.name || (item.updated_by ? 'ID: ' + item.updated_by : 'Sistema') }}
                    </span>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </template>

        <template #footer="{ item }">
          <div class="action-buttons">
            <button @click="showEditForm(item)" class="btn btn-sm btn-primary" title="Editar">
              <i class="bi bi-pencil"></i>
            </button>
            <button @click="prepareDelete(item)" class="btn btn-sm btn-danger" title="Excluir">
              <i class="bi bi-trash"></i>
            </button>
            <router-link :to="`/admin/institucional/mantenedoras/${item.id}/setores`" class="btn btn-sm btn-secondary" title="Setores">
              <i class="bi bi-diagram-3"></i>
            </router-link>
          </div>
        </template>
      </CardView>

      <div class="mt-3">
        <Pagination
          :current-page="currentPage"
          :total-items="mantenedorasFiltradas.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir a mantenedora ${itemToDelete?.razao_social}?`"
      confirm-text="Excluir"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';
import Pagination from '@/components/Pagination.vue';
import CardView from '@/components/CardView.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import MaskedInput from '@/components/MaskedInput.vue';
import AdvancedFilters from '@/components/AdvancedFilters.vue';
import { usePagination } from '@/composables/usePagination';
import { useFilters } from '@/composables/useFilters';

const mantenedoras = ref([]);
const grupos = ref([]);
const loading = ref(true);
const submitting = ref(false);
const form = ref({});
const errors = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
const viewMode = ref('table');
let confirmModalInstance = null;

const { currentPage, perPage, paginateItems, changePage, changePerPage } = usePagination(25);

// ⭐ CONFIGURAÇÃO DOS FILTROS (SIMPLIFICADA E GARANTIDA)
const filterConfig = [
  {
    key: 'razao_social',
    label: 'Razão Social',
    type: 'text',
    placeholder: 'Filtrar por razão social...'
  },
  {
    key: 'nome_fantasia',
    label: 'Nome Fantasia',
    type: 'text',
    placeholder: 'Filtrar por nome fantasia...'
  }
];

// ⭐ USAR COMPOSABLE DE FILTROS
const {
  activeFilters,
  filteredItems,
  showFilters,
  clearAllFilters,
  activeFilterCount,
  toggleFilters
} = useFilters(mantenedoras, filterConfig);

// ⭐ COMPUTED ATUALIZADO
const mantenedorasFiltradas = computed(() => {
  let result = filteredItems.value;
  
  if (buscaRapida.value) {
    const termo = buscaRapida.value.toLowerCase();
    result = result.filter(mantenedora => 
      mantenedora.razao_social?.toLowerCase().includes(termo) ||
      mantenedora.nome_fantasia?.toLowerCase().includes(termo) ||
      mantenedora.cnpj?.includes(termo) ||
      mantenedora.grupo_educacional?.nome.toLowerCase().includes(termo)
    );
  }
  
  return result;
});

const mantenedorasPaginadas = computed(() => {
  return paginateItems(mantenedorasFiltradas.value);
});

watch(buscaRapida, () => {
  currentPage.value = 1;
});

const exportColumns = [
  { key: 'razao_social', label: 'Razão Social' },
  { key: 'nome_fantasia', label: 'Nome Fantasia' },
  { key: 'cnpj', label: 'CNPJ' },
  { key: 'grupo_educacional.nome', label: 'Grupo Educacional' },
  { key: 'endereco_completo', label: 'Endereço' }
];

const formatDate = (date) => {
  if (!date) return 'N/A';
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: ptBR });
};

const formatCnpj = (cnpj) => {
  if (!cnpj) return '';
  const cleaned = cnpj.replace(/\D/g, '');
  if (cleaned.length !== 14) return cnpj;
  return cleaned.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
};

const resetForm = () => {
  form.value = {
    grupo_educacional_id: null,
    razao_social: '',
    nome_fantasia: '',
    cnpj: '',
    endereco_completo: ''
  };
  errors.value = {};
};

const fetchMantenedoras = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/mantenedoras');
    mantenedoras.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar mantenedoras:', error);
  } finally {
    loading.value = false;
  }
};

const fetchGrupos = async () => {
  try {
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar grupos:', error);
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (mantenedora) => {
  isEditing.value = true;
  editingId.value = mantenedora.id;
  form.value = { ...mantenedora };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
  resetForm();
};

const createMantenedora = async () => {
  try {
    submitting.value = true;
    errors.value = {};
    await axios.post('/api/v1/mantenedoras', form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar mantenedora:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro ao criar mantenedora. Verifique os dados e tente novamente.');
    }
  } finally {
    submitting.value = false;
  }
};

const updateMantenedora = async () => {
  try {
    submitting.value = true;
    errors.value = {};
    await axios.put(`/api/v1/mantenedoras/${editingId.value}`, form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar mantenedora:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro ao atualizar mantenedora. Verifique os dados e tente novamente.');
    }
  } finally {
    submitting.value = false;
  }
};

const prepareDelete = (mantenedora) => {
  itemToDelete.value = mantenedora;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/mantenedoras/${itemToDelete.value.id}`);
    await fetchMantenedoras();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao excluir mantenedora:', error);
    alert('Erro ao excluir mantenedora. Pode haver registros vinculados (instituições, setores).');
  }
};

onMounted(() => {
  fetchMantenedoras();
  fetchGrupos();
  
  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>

<style scoped>
.info-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  align-items: flex-start;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.875rem;
  line-height: 1.5;
}

.info-item i {
  color: rgba(102, 126, 234, 0.8);
  margin-top: 0.125rem;
  flex-shrink: 0;
}

.info-item .label {
  font-weight: 600;
  margin-right: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
}

.info-item .value {
  color: rgba(255, 255, 255, 0.9);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.audit-info-compact {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.4;
}

.audit-info-card {
  margin-top: 1rem;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.2);
  border-left: 3px solid rgba(102, 126, 234, 0.6);
  border-radius: 6px;
}

.audit-header {
  display: flex;
  align-items: center;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.audit-header i {
  color: rgba(102, 126, 234, 0.8);
}

.audit-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.audit-row {
  display: flex;
  align-items: center;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

.audit-row i {
  flex-shrink: 0;
}

.audit-label {
  font-weight: 500;
  margin-right: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
}

.audit-value {
  color: rgba(255, 255, 255, 0.9);
}

.audit-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 0.5rem 0;
}

.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* ⭐ Botão de filtros ativo */
.btn.active {
  background-color: rgba(102, 126, 234, 0.2) !important;
  border-color: rgba(102, 126, 234, 0.5) !important;
}
</style>
