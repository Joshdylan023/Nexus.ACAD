<template>
  <div>
    <PageHeader 
      title="Catálogo de Setores"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Setores' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por nome, sigla ou tipo..."
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
          :data="setoresFiltrados"
          :columns="exportColumns"
          fileName="setores"
          class="me-2"
        />
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Setor
        </button>
      </template>
    </PageHeader>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Setor' : 'Adicionar Novo Setor' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateSetor() : createSetor()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nome do Setor</label>
              <input type="text" class="form-control" v-model="form.nome" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Sigla</label>
              <input type="text" class="form-control" v-model="form.sigla" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Tipo</label>
              <select class="form-select" v-model="form.tipo" required>
                <option value="Corporativo">Corporativo</option>
                <option value="Institucional">Institucional</option>
                <option value="Operacional">Operacional</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <!-- Visualização em Tabela -->
    <div v-if="viewMode === 'table'" class="card card-glass">
      <div class="card-header"><h4>Setores Cadastrados</h4></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nome do Setor</th>
              <th>Sigla</th>
              <th>Tipo</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="4" :rows="5" />
          <tbody v-else>
            <tr v-for="setor in setoresPaginados" :key="setor.id">
              <td class="ps-4">{{ setor.nome }}</td>
              <td>{{ setor.sigla }}</td>
              <td><StatusBadge :status="setor.tipo" type="tipo" /></td>
              <td class="text-center">
                <button @click="showEditForm(setor)" class="btn btn-sm btn-primary me-2" title="Editar Setor">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(setor)" class="btn btn-sm btn-danger" title="Excluir Setor">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="setoresFiltrados.length === 0">
              <td colspan="4" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado.' : 'Nenhum setor encontrado.' }}
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination
          :current-page="currentPage"
          :total-items="setoresFiltrados.length"
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
        :items="setoresPaginados"
        empty-message="Nenhum setor encontrado"
      >
        <template #header="{ item }">
          <div>
            <h5 class="mb-1" style="color: white; font-size: 1.125rem; font-weight: 600;">{{ item.nome }}</h5>
            <div class="d-flex gap-2">
              <span class="badge bg-secondary">{{ item.sigla }}</span>
              <StatusBadge :status="item.tipo" type="tipo" />
            </div>
          </div>
        </template>

        <template #body="{ item }">
          <div class="info-group">
            <div class="info-item">
              <i class="bi bi-tag me-2"></i>
              <span class="label">Sigla:</span>
              <span class="value">{{ item.sigla }}</span>
            </div>
            <div class="info-item">
              <i class="bi bi-diagram-3 me-2"></i>
              <span class="label">Tipo:</span>
              <StatusBadge :status="item.tipo" type="tipo" />
            </div>
            <div class="info-item">
              <i class="bi bi-info-circle me-2"></i>
              <span class="label">ID:</span>
              <span class="value">{{ item.id }}</span>
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
          </div>
        </template>
      </CardView>

      <div class="mt-3">
        <Pagination
          :current-page="currentPage"
          :total-items="setoresFiltrados.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir o setor ${itemToDelete?.nome}?`"
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
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';
import Pagination from '@/components/Pagination.vue';
import CardView from '@/components/CardView.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import { usePagination } from '@/composables/usePagination';

const setores = ref([]);
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

const setoresFiltrados = computed(() => {
  if (!buscaRapida.value) return setores.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return setores.value.filter(setor => 
    setor.nome?.toLowerCase().includes(termo) ||
    setor.sigla?.toLowerCase().includes(termo) ||
    setor.tipo?.toLowerCase().includes(termo)
  );
});

const setoresPaginados = computed(() => {
  return paginateItems(setoresFiltrados.value);
});

watch(buscaRapida, () => {
  currentPage.value = 1;
});

const exportColumns = [
  { key: 'nome', label: 'Nome do Setor' },
  { key: 'sigla', label: 'Sigla' },
  { key: 'tipo', label: 'Tipo' }
];

const resetForm = () => {
  form.value = {
    nome: '',
    sigla: '',
    tipo: 'Corporativo'
  };
};

const fetchSetores = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/setores');
    setores.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar setores:', error);
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

const showEditForm = (setor) => {
  isEditing.value = true;
  editingId.value = setor.id;
  form.value = { ...setor };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createSetor = async () => {
  try {
    await axios.post('/api/v1/setores', form.value);
    await fetchSetores();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar setor:', error);
    alert('Erro ao criar setor. Verifique os dados e tente novamente.');
  }
};

const updateSetor = async () => {
  try {
    await axios.put(`/api/v1/setores/${editingId.value}`, form.value);
    await fetchSetores();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar setor:', error);
    alert('Erro ao atualizar setor. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (setor) => {
  itemToDelete.value = setor;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/setores/${itemToDelete.value.id}`);
    await fetchSetores();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao excluir setor:', error);
    alert('Erro ao excluir setor. Pode haver vínculos ativos.');
  }
};

onMounted(() => {
  fetchSetores();
  
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
