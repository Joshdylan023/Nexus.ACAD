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
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Grupo Educacional' : 'Adicionar Novo Grupo' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateGrupo() : createGrupo()">
          <div class="mb-3">
            <label class="form-label">Nome do Grupo</label>
            <input type="text" class="form-control" v-model="form.nome" required>
          </div>
          <div class="mb-3">
            <label class="form-label">CNPJ</label>
            <input type="text" class="form-control" v-model="form.cnpj" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Endereço Completo</label>
            <input type="text" class="form-control" v-model="form.endereco_completo" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Representante Legal (Opcional)</label>
            <input type="text" class="form-control" v-model="form.representante_legal">
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
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
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="4" :rows="5" />
          <tbody v-else>
            <tr v-for="grupo in gruposPaginados" :key="grupo.id">
              <td class="ps-4">{{ grupo.nome }}</td>
              <td>{{ grupo.cnpj }}</td>
              <td>{{ grupo.representante_legal || 'N/A' }}</td>
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
              <td colspan="4" class="text-center text-muted py-4">
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
              <span class="value">{{ item.cnpj }}</span>
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
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';
import Pagination from '@/components/Pagination.vue';
import CardView from '@/components/CardView.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import { usePagination } from '@/composables/usePagination';

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

const gruposFiltrados = computed(() => {
  if (!buscaRapida.value) return grupos.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return grupos.value.filter(grupo => 
    grupo.nome?.toLowerCase().includes(termo) ||
    grupo.cnpj?.includes(termo)
  );
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

const resetForm = () => {
  form.value = {
    nome: '',
    cnpj: '',
    endereco_completo: '',
    representante_legal: ''
  };
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
};

const createGrupo = async () => {
  try {
    await axios.post('/api/v1/grupos-educacionais', form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar grupo:', error);
    alert('Erro ao criar grupo. Verifique os dados e tente novamente.');
  }
};

const updateGrupo = async () => {
  try {
    await axios.put(`/api/v1/grupos-educacionais/${editingId.value}`, form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar grupo:', error);
    alert('Erro ao atualizar grupo. Verifique os dados e tente novamente.');
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
</style>
