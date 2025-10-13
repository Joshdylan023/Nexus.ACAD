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
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Mantenedora' : 'Adicionar Nova Mantenedora' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateMantenedora() : createMantenedora()">
          <div class="mb-3">
            <label class="form-label">Grupo Educacional</label>
            <select class="form-select" v-model="form.grupo_educacional_id" required>
              <option :value="null">-- Selecione --</option>
              <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nome }}</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Razão Social</label>
              <input type="text" class="form-control" v-model="form.razao_social" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nome Fantasia (Opcional)</label>
              <input type="text" class="form-control" v-model="form.nome_fantasia">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">CNPJ</label>
            <input type="text" class="form-control" v-model="form.cnpj" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Endereço Completo</label>
            <input type="text" class="form-control" v-model="form.endereco_completo">
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
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
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="5" :rows="5" />
          <tbody v-else>
            <tr v-for="mantenedora in mantenedorasPaginadas" :key="mantenedora.id">
              <td class="ps-4">{{ mantenedora.razao_social }}</td>
              <td>{{ mantenedora.nome_fantasia || 'N/A' }}</td>
              <td>{{ mantenedora.cnpj }}</td>
              <td>{{ mantenedora.grupo_educacional?.nome || 'N/A' }}</td>
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
              <td colspan="5" class="text-center text-muted py-4">
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
              <span class="value">{{ item.cnpj }}</span>
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
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';
import Pagination from '@/components/Pagination.vue';
import CardView from '@/components/CardView.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import { usePagination } from '@/composables/usePagination';

const mantenedoras = ref([]);
const grupos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
const viewMode = ref('table');
let confirmModalInstance = null;

const { currentPage, perPage, paginateItems, changePage, changePerPage } = usePagination(25);

const mantenedorasFiltradas = computed(() => {
  if (!buscaRapida.value) return mantenedoras.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return mantenedoras.value.filter(mantenedora => 
    mantenedora.razao_social?.toLowerCase().includes(termo) ||
    mantenedora.nome_fantasia?.toLowerCase().includes(termo) ||
    mantenedora.cnpj?.includes(termo) ||
    mantenedora.grupo_educacional?.nome.toLowerCase().includes(termo)
  );
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

const resetForm = () => {
  form.value = {
    grupo_educacional_id: null,
    razao_social: '',
    nome_fantasia: '',
    cnpj: '',
    endereco_completo: ''
  };
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
};

const createMantenedora = async () => {
  try {
    await axios.post('/api/v1/mantenedoras', form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar mantenedora:', error);
    alert('Erro ao criar mantenedora. Verifique os dados e tente novamente.');
  }
};

const updateMantenedora = async () => {
  try {
    await axios.put(`/api/v1/mantenedoras/${editingId.value}`, form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar mantenedora:', error);
    alert('Erro ao atualizar mantenedora. Verifique os dados e tente novamente.');
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
</style>
