<template>
  <div>
    <PageHeader 
      title="Gestão de Grupos Educacionais"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Grupos Educacionais' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por nome ou CNPJ..."
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
          :data="gruposFiltrados"
          :columns="exportColumns"
          fileName="grupos-educacionais"
          class="me-2"
        />
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Grupo
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
      <div class="card-header">{{ isEditing ? 'Editar Grupo Educacional' : 'Adicionar Novo Grupo' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateGrupo() : createGrupo()">
          <div class="mb-3">
            <label class="form-label">Nome do Grupo *</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="form.nome" 
              :class="{ 'is-invalid': errors.nome }"
              required
            >
            <div v-if="errors.nome" class="invalid-feedback">{{ errors.nome }}</div>
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
          
          <div class="mb-3">
            <label class="form-label">Representante Legal</label>
            <input 
              type="text" 
              class="form-control" 
              v-model="form.representante_legal"
              :class="{ 'is-invalid': errors.representante_legal }"
            >
            <div v-if="errors.representante_legal" class="invalid-feedback">{{ errors.representante_legal }}</div>
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
      <div class="card-header"><h4>Grupos Cadastrados</h4></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nome</th>
              <th>CNPJ</th>
              <th>Representante Legal</th>
              <th>Auditoria</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="5" :rows="5" />
          <tbody v-else>
            <tr v-for="grupo in gruposPaginados" :key="grupo.id">
              <td class="ps-4">{{ grupo.nome }}</td>
              <td>{{ formatCnpj(grupo.cnpj) }}</td>
              <td>{{ grupo.representante_legal || 'N/A' }}</td>
              <td>
                <div class="audit-info-compact">
                  <small>
                    <i class="bi bi-person-plus text-success me-1"></i>
                    <strong>{{ grupo.creator?.name || (grupo.created_by ? 'ID: ' + grupo.created_by : 'Sistema') }}</strong>
                    <br>
                    <i class="bi bi-clock text-muted me-1"></i>
                    {{ formatDate(grupo.created_at) }}
                    <template v-if="grupo.updated_at !== grupo.created_at">
                      <br>
                      <i class="bi bi-pencil text-warning me-1"></i>
                      <strong>{{ grupo.updater?.name || (grupo.updated_by ? 'ID: ' + grupo.updated_by : 'Sistema') }}</strong>
                      <br>
                      <i class="bi bi-clock text-muted me-1"></i>
                      {{ formatDate(grupo.updated_at) }}
                    </template>
                  </small>
                </div>
              </td>
              <td class="text-center">
                <button @click="showEditForm(grupo)" class="btn btn-sm btn-primary me-2" title="Editar Grupo">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(grupo)" class="btn btn-sm btn-danger me-2" title="Excluir Grupo">
                  <i class="bi bi-trash"></i>
                </button>
                <router-link :to="`/admin/institucional/grupos-educacionais/${grupo.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores">
                  <i class="bi bi-diagram-3"></i>
                </router-link>
              </td>
            </tr>
            <tr v-if="gruposFiltrados.length === 0">
              <td colspan="5" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum grupo encontrado.' }}
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination
          :current-page="currentPage"
          :total-items="gruposFiltrados.length"
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
        :items="gruposPaginados"
        empty-message="Nenhum grupo encontrado"
      >
        <template #header="{ item }">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-1" style="color: white; font-size: 1.125rem; font-weight: 600;">{{ item.nome }}</h5>
              <span class="badge bg-secondary text-white">ID: {{ item.id }}</span>
            </div>
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
              <i class="bi bi-person me-2"></i>
              <span class="label">Representante:</span>
              <span class="value">{{ item.representante_legal || 'Não informado' }}</span>
            </div>
            <div class="info-item">
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
            <router-link :to="`/admin/institucional/grupos-educacionais/${item.id}/setores`" class="btn btn-sm btn-secondary" title="Setores">
              <i class="bi bi-diagram-3"></i>
            </router-link>
          </div>
        </template>
      </CardView>

      <div class="mt-3">
        <Pagination
          :current-page="currentPage"
          :total-items="gruposFiltrados.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir o grupo ${itemToDelete?.nome}?`"
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

// ⭐ CONFIGURAÇÃO DOS FILTROS
const filterConfig = [
  {
    key: 'nome',
    label: 'Nome do Grupo',
    type: 'text',
    placeholder: 'Filtrar por nome...'
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
} = useFilters(grupos, filterConfig);

// ⭐ COMPUTED ATUALIZADO
const gruposFiltrados = computed(() => {
  let result = filteredItems.value;
  
  if (buscaRapida.value) {
    const termo = buscaRapida.value.toLowerCase();
    result = result.filter(grupo => 
      grupo.nome?.toLowerCase().includes(termo) ||
      grupo.cnpj?.includes(termo)
    );
  }
  
  return result;
});

const gruposPaginados = computed(() => {
  return paginateItems(gruposFiltrados.value);
});

watch(buscaRapida, () => {
  currentPage.value = 1;
});

const exportColumns = [
  { key: 'nome', label: 'Nome do Grupo' },
  { key: 'cnpj', label: 'CNPJ' },
  { key: 'endereco_completo', label: 'Endereço' },
  { key: 'representante_legal', label: 'Representante Legal' }
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
    nome: '',
    cnpj: '',
    razao_social: '',
    endereco_completo: '',
    representante_legal: ''
  };
  errors.value = {};
};

const fetchGrupos = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar grupos:', error);
  } finally {
    loading.value = false;
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (grupo) => {
  isEditing.value = true;
  editingId.value = grupo.id;
  form.value = { ...grupo };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
  resetForm();
};

const createGrupo = async () => {
  try {
    submitting.value = true;
    errors.value = {};
    await axios.post('/api/v1/grupos-educacionais', form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar grupo:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro ao criar grupo. Verifique os dados e tente novamente.');
    }
  } finally {
    submitting.value = false;
  }
};

const updateGrupo = async () => {
  try {
    submitting.value = true;
    errors.value = {};
    await axios.put(`/api/v1/grupos-educacionais/${editingId.value}`, form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar grupo:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro ao atualizar grupo. Verifique os dados e tente novamente.');
    }
  } finally {
    submitting.value = false;
  }
};

const prepareDelete = (grupo) => {
  itemToDelete.value = grupo;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/grupos-educacionais/${itemToDelete.value.id}`);
    await fetchGrupos();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao excluir grupo:', error);
    alert('Erro ao excluir grupo. Pode haver registros vinculados (mantenedoras, setores).');
  }
};

onMounted(() => {
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
