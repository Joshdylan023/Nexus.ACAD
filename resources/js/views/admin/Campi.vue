<template>
  <div>
    <PageHeader 
      title="Gestão de Campi"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Campi' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por nome ou instituição..."
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
          :data="campiFiltrados"
          :columns="exportColumns"
          fileName="campi"
          class="me-2"
        />
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Campus
        </button>
      </template>
    </PageHeader>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Campus' : 'Adicionar Novo Campus' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateCampus() : createCampus()">
          <div class="mb-3">
            <label class="form-label">Instituição (IES)</label>
            <select class="form-select" v-model="form.instituicao_id" required>
              <option :value="null">-- Selecione --</option>
              <option v-for="instituicao in instituicoes" :key="instituicao.id" :value="instituicao.id">
                {{ instituicao.razao_social }}
              </option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nome do Campus</label>
            <input type="text" class="form-control" v-model="form.nome" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Endereço Completo</label>
            <input type="text" class="form-control" v-model="form.endereco_completo" required>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <!-- Visualização em Tabela -->
    <div v-if="viewMode === 'table'" class="card card-glass">
      <div class="card-header"><h4>Campi Cadastrados</h4></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nome do Campus</th>
              <th>Instituição</th>
              <th>Endereço</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="4" :rows="5" />
          <tbody v-else>
            <tr v-for="campus in campiPaginados" :key="campus.id">
              <td class="ps-4">{{ campus.nome }}</td>
              <td>{{ campus.instituicao?.razao_social || 'N/A' }}</td>
              <td>{{ campus.endereco_completo }}</td>
              <td class="text-center">
                <button @click="showEditForm(campus)" class="btn btn-sm btn-primary me-2" title="Editar Campus">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(campus)" class="btn btn-sm btn-danger me-2" title="Excluir Campus">
                  <i class="bi bi-trash"></i>
                </button>
                <router-link :to="`/admin/institucional/campi/${campus.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores">
                  <i class="bi bi-diagram-3"></i>
                </router-link>
              </td>
            </tr>
            <tr v-if="campiFiltrados.length === 0">
              <td colspan="4" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado.' : 'Nenhum campus encontrado.' }}
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination
          :current-page="currentPage"
          :total-items="campiFiltrados.length"
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
        :items="campiPaginados"
        empty-message="Nenhum campus encontrado"
      >
        <template #header="{ item }">
          <div>
            <h5 class="mb-1" style="color: white; font-size: 1.125rem; font-weight: 600;">{{ item.nome }}</h5>
            <span class="badge bg-warning text-dark">Campus</span>
          </div>
        </template>

        <template #body="{ item }">
          <div class="info-group">
            <div class="info-item">
              <i class="bi bi-building me-2"></i>
              <span class="label">Instituição:</span>
              <span class="value">{{ item.instituicao?.razao_social || 'N/A' }}</span>
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
            <router-link :to="`/admin/institucional/campi/${item.id}/setores`" class="btn btn-sm btn-secondary" title="Setores">
              <i class="bi bi-diagram-3"></i>
            </router-link>
          </div>
        </template>
      </CardView>

      <div class="mt-3">
        <Pagination
          :current-page="currentPage"
          :total-items="campiFiltrados.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir o campus ${itemToDelete?.nome}?`"
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

const campi = ref([]);
const instituicoes = ref([]);
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

const campiFiltrados = computed(() => {
  if (!buscaRapida.value) return campi.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return campi.value.filter(campus => 
    campus.nome?.toLowerCase().includes(termo) ||
    campus.instituicao?.razao_social?.toLowerCase().includes(termo)
  );
});

const campiPaginados = computed(() => {
  return paginateItems(campiFiltrados.value);
});

watch(buscaRapida, () => {
  currentPage.value = 1;
});

const exportColumns = [
  { key: 'nome', label: 'Nome do Campus' },
  { key: 'instituicao.razao_social', label: 'Instituição' },
  { key: 'endereco_completo', label: 'Endereço' }
];

const resetForm = () => {
  form.value = {
    instituicao_id: null,
    nome: '',
    endereco_completo: ''
  };
};

const fetchCampi = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/campi');
    campi.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar campi:', error);
  } finally {
    loading.value = false;
  }
};

const fetchInstituicoes = async () => {
  try {
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar instituições:', error);
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (campus) => {
  isEditing.value = true;
  editingId.value = campus.id;
  form.value = { ...campus };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createCampus = async () => {
  try {
    await axios.post('/api/v1/campi', form.value);
    await fetchCampi();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar campus:', error);
    alert('Erro ao criar campus. Verifique os dados e tente novamente.');
  }
};

const updateCampus = async () => {
  try {
    await axios.put(`/api/v1/campi/${editingId.value}`, form.value);
    await fetchCampi();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar campus:', error);
    alert('Erro ao atualizar campus. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (campus) => {
  itemToDelete.value = campus;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/campi/${itemToDelete.value.id}`);
    await fetchCampi();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao excluir campus:', error);
    alert('Erro ao excluir campus. Pode haver registros vinculados (setores).');
  }
};

onMounted(() => {
  fetchCampi();
  fetchInstituicoes();
  
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
