<template>
  <div>
    <PageHeader 
      :title="`Setores - ${grupo.nome || 'Carregando...'}`"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional', path: '/admin/institucional' },
        { label: 'Grupos Educacionais', path: '/admin/institucional/grupos-educacionais' },
        { label: grupo.nome || 'Setores' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar setor..."
      v-model="buscaRapida"
    >
      <template #actions>
        <ExportButton 
          v-if="!showForm"
          :data="setoresFiltrados"
          :columns="exportColumns"
          :fileName="`setores-grupo-${grupo.nome || 'educacional'}`"
          class="me-2"
        />
        <router-link to="/admin/institucional/grupos-educacionais" class="btn btn-secondary me-2">
          <i class="bi bi-arrow-left"></i> Voltar
        </router-link>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Adicionar Setor
        </button>
      </template>
    </PageHeader>

    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Vínculo de Setor' : 'Adicionar Novo Setor' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateSetor() : createSetor()">
          <div class="mb-3">
            <label class="form-label">Setor</label>
            <select class="form-select" v-model="form.setor_id" required :disabled="isEditing">
              <option :value="null">-- Selecione --</option>
              <option v-for="setor in setoresDisponiveis" :key="setor.id" :value="setor.id">
                {{ setor.nome }} ({{ setor.tipo }})
              </option>
            </select>
            <small class="text-white-50">Apenas setores do tipo "Corporativo" podem ser vinculados</small>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Setor Pai (Opcional)</label>
              <select class="form-select" v-model="form.pai_id">
                <option :value="null">-- Nenhum (Setor Raiz) --</option>
                <option v-for="setor in setoresPaiDisponiveis" :key="setor.pivot.id" :value="setor.pivot.id">
                  {{ setor.nome }}
                </option>
              </select>
              <small class="text-white-50">Outros setores já vinculados a este grupo</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Gestor do Setor (Opcional)</label>
              <v-select
                :get-option-label="option => option.usuario ? option.usuario.name : option.name"
                :options="gestorOptions"
                @search="fetchGestores"
                v-model="selectedGestor"
                placeholder="Digite o nome ou matrícula..."
              >
                <template #option="option">
                  {{ option.usuario ? option.usuario.name : option.name }}<br>
                  <small class="text-muted" v-if="option.matricula_funcional">Matrícula: {{ option.matricula_funcional }}</small>
                </template>
                <template #selected-option="option">
                  <div>{{ option.usuario ? option.usuario.name : option.name }}</div>
                </template>
              </v-select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" v-model="form.status" required>
                <option value="Em Implantação">Em Implantação</option>
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
                <option value="Suspenso">Suspenso</option>
                <option value="Concluído">Concluído</option>
                <option value="Cancelado">Cancelado</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Centro de Custo SAP (Opcional)</label>
              <input type="text" class="form-control" v-model="form.centro_custo_sap">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Centro de Resultado SAP (Opcional)</label>
              <input type="text" class="form-control" v-model="form.centro_resultado_sap">
            </div>
          </div>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" v-model="form.requer_portaria_nomeacao_gestor" id="requerPortaria">
              <label class="form-check-label" for="requerPortaria">
                Requer Portaria de Nomeação do Gestor
              </label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
      <div class="card-header"><h4>Setores Vinculados</h4></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nome do Setor</th>
              <th>Tipo</th>
              <th>Gestor</th>
              <th>Status</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="5" :rows="5" />
          <tbody v-else>
            <tr v-for="setor in setoresPaginados" :key="setor.pivot.id">
              <td class="ps-4">{{ setor.nome }}</td>
              <td><StatusBadge :status="setor.tipo" type="tipo" /></td>
              <td>{{ setor.pivot.gestor ? setor.pivot.gestor.name : 'Não definido' }}</td>
              <td><StatusBadge :status="setor.pivot.status" /></td>
              <td class="text-center">
                <button @click="showEditForm(setor)" class="btn btn-sm btn-primary me-2" title="Editar Vínculo">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(setor)" class="btn btn-sm btn-danger" title="Remover Vínculo">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="setoresFiltrados.length === 0">
              <td colspan="5" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum setor vinculado.' }}
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

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Remoção"
      :message="`Tem certeza que deseja remover o vínculo do setor ${itemToDelete?.nome}?`"
      confirm-text="Remover"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { Modal } from 'bootstrap';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';

const route = useRoute();
const grupoId = ref(route.params.id);
const grupo = ref({});
const setoresVinculados = ref([]);
const setoresDisponiveis = ref([]);
const gestorOptions = ref([]);
const selectedGestor = ref(null);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const { currentPage, perPage, paginateItems, changePage, changePerPage } = usePagination(25);

const setoresFiltrados = computed(() => {
  if (!buscaRapida.value) return setoresVinculados.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return setoresVinculados.value.filter(setor => 
    setor.nome?.toLowerCase().includes(termo) ||
    setor.tipo?.toLowerCase().includes(termo)
  );
});

const setoresPaginados = computed(() => {
  return paginateItems(setoresFiltrados.value);
});

const setoresPaiDisponiveis = computed(() => {
  return setoresVinculados.value.filter(setor => !isEditing.value || setor.pivot.id !== editingId.value);
});

watch(buscaRapida, () => {
  currentPage.value = 1;
});

const exportColumns = [
  { key: 'nome', label: 'Nome do Setor' },
  { key: 'tipo', label: 'Tipo' },
  { key: 'pivot.gestor.name', label: 'Gestor' },
  { key: 'pivot.centro_custo_sap', label: 'Centro de Custo SAP' },
  { key: 'pivot.centro_resultado_sap', label: 'Centro de Resultado SAP' },
  { key: 'pivot.status', label: 'Status' }
];

watch(selectedGestor, (newGestor) => {
  form.value.gestor_id = newGestor ? (newGestor.user_id || newGestor.id) : null;
});

const resetForm = () => {
  form.value = {
    setor_id: null,
    pai_id: null,
    gestor_id: null,
    status: 'Ativo',
    centro_custo_sap: '',
    centro_resultado_sap: '',
    requer_portaria_nomeacao_gestor: false
  };
  selectedGestor.value = null;
  gestorOptions.value = [];
};

const fetchGrupo = async () => {
  try {
    const response = await axios.get(`/api/v1/grupos-educacionais/${grupoId.value}`);
    grupo.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar grupo:', error);
  }
};

const fetchSetores = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/grupos-educacionais/${grupoId.value}/setores`);
    setoresVinculados.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar setores:', error);
  } finally {
    loading.value = false;
  }
};

const fetchSetoresDisponiveis = async () => {
  try {
    const response = await axios.get('/api/v1/setores');
    const idsVinculados = setoresVinculados.value.map(s => s.id);
    setoresDisponiveis.value = response.data.filter(s => 
      s.tipo === 'Corporativo' && !idsVinculados.includes(s.id)
    );
  } catch (error) {
    console.error('Erro ao buscar setores disponíveis:', error);
  }
};

const fetchGestores = (search, loading) => {
  if (search && search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}`)
      .then(response => {
        gestorOptions.value = response.data;
      })
      .catch(error => {
        console.error('Erro ao buscar gestores:', error);
        gestorOptions.value = [];
      })
      .finally(() => loading(false));
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  fetchSetoresDisponiveis();
  showForm.value = true;
};

const showEditForm = (setor) => {
  isEditing.value = true;
  editingId.value = setor.pivot.id;
  form.value = {
    setor_id: setor.id,
    pai_id: setor.pivot.pai_id,
    gestor_id: setor.pivot.gestor_id,
    status: setor.pivot.status,
    centro_custo_sap: setor.pivot.centro_custo_sap || '',
    centro_resultado_sap: setor.pivot.centro_resultado_sap || '',
    requer_portaria_nomeacao_gestor: setor.pivot.requer_portaria_nomeacao_gestor || false
  };

  if (setor.pivot.gestor) {
    selectedGestor.value = {
      id: setor.pivot.gestor.id,
      name: setor.pivot.gestor.name,
      usuario: setor.pivot.gestor
    };
    gestorOptions.value = [selectedGestor.value];
  }

  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createSetor = async () => {
  try {
    await axios.post(`/api/v1/grupos-educacionais/${grupoId.value}/setores`, form.value);
    await fetchSetores();
    hideForm();
  } catch (error) {
    console.error('Erro ao vincular setor:', error);
    alert('Erro ao vincular setor. Verifique os dados.');
  }
};

const updateSetor = async () => {
  try {
    await axios.put(`/api/v1/grupos-educacionais/${grupoId.value}/setores/${form.value.setor_id}`, form.value);
    await fetchSetores();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar vínculo:', error);
    alert('Erro ao atualizar vínculo. Verifique os dados.');
  }
};

const prepareDelete = (setor) => {
  itemToDelete.value = setor;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/grupos-educacionais/${grupoId.value}/setores/${itemToDelete.value.id}`);
    await fetchSetores();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao remover vínculo:', error);
    alert('Erro ao remover vínculo do setor.');
  }
};

onMounted(async () => {
  await fetchGrupo();
  await fetchSetores();

  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>
