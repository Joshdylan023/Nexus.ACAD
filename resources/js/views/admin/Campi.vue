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
            <label class="form-label">Instituição</label>
            <select class="form-select" v-model="form.instituicao_id" required>
              <option :value="null">-- Selecione --</option>
              <option v-for="instituicao in instituicoes" :key="instituicao.id" :value="instituicao.id">{{ instituicao.nome_fantasia }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="nome" class="form-label">Nome do Campus</label>
            <input type="text" id="nome" class="form-control" v-model="form.nome" required>
          </div>
          <div class="mb-3">
            <label for="endereco_completo" class="form-label">Endereço Completo</label>
            <input type="text" id="endereco_completo" class="form-control" v-model="form.endereco_completo">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Gerente da Unidade (Opcional)</label>
                <v-select
                    :get-option-label="option => option.usuario ? option.usuario.name : option.name"
                    :options="gestorOptions"
                    @search="fetchGestores"
                    v-model="selectedGestor"
                    placeholder="Digite a matrícula ou nome..."
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
            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" v-model="form.status" required>
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header"><h4>Campi Cadastrados</h4></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Nome</th>
                  <th>Instituição</th>
                  <th>Gerente</th>
                  <th>Status</th>
                  <th class="text-center">Ações</th>
                </tr>
            </thead>
            <TableSkeleton v-if="loading" :columns="5" :rows="5" />
            <tbody v-else>
                <tr v-for="campus in campiFiltrados" :key="campus.id">
                  <td class="ps-4">{{ campus.nome }}</td>
                  <td>{{ campus.instituicao ? campus.instituicao.nome_fantasia : 'N/A' }}</td>
                  <td>{{ campus.gerente_unidade ? campus.gerente_unidade.name : 'A definir' }}</td>
                  <td><StatusBadge :status="campus.status" /></td>
                  <td class="text-center">
                      <button @click="showEditForm(campus)" class="btn btn-sm btn-primary me-2" title="Editar Campus"><i class="bi bi-pencil"></i></button>
                      <button @click="prepareDelete(campus)" class="btn btn-sm btn-danger me-2" title="Excluir Campus"><i class="bi bi-trash"></i></button>
                      <router-link :to="`/admin/institucional/campi/${campus.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores">
                        <i class="bi bi-diagram-3"></i>
                      </router-link>
                  </td>
                </tr>
                 <tr v-if="campiFiltrados.length === 0">
                    <td colspan="5" class="text-center text-muted py-4">
                      {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum campus encontrado.' }}
                    </td>
                </tr>
            </tbody>
            </table>
      </div>
    </div>

    <!-- Modal de Confirmação -->
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
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const campi = ref([]);
const instituicoes = ref([]);
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

const campiFiltrados = computed(() => {
  if (!buscaRapida.value) return campi.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return campi.value.filter(campus => 
    campus.nome?.toLowerCase().includes(termo) ||
    campus.instituicao?.nome_fantasia.toLowerCase().includes(termo)
  );
});

watch(selectedGestor, (newGestor) => {
    form.value.gerente_unidade_id = newGestor ? (newGestor.user_id || newGestor.id) : null;
});

const resetForm = () => {
    form.value = {
        instituicao_id: null,
        nome: '',
        endereco_completo: '',
        gerente_unidade_id: null,
        status: 'Ativo',
    };
    selectedGestor.value = null;
    gestorOptions.value = [];
};

const fetchCampi = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/campi');
    campi.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar campi:", error); 
  } finally { 
    loading.value = false; 
  }
};

const fetchInstituicoes = async () => {
  try {
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar instituições:", error); 
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
        console.error("Erro ao buscar colaboradores:", error);
        gestorOptions.value = [];
      })
      .finally(() => loading(false));
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
  
  if (campus.gerente_unidade) {
      selectedGestor.value = {
          id: campus.gerente_unidade.id,
          name: campus.gerente_unidade.name,
          usuario: campus.gerente_unidade
      };
      gestorOptions.value = [selectedGestor.value];
  } else {
      selectedGestor.value = null;
      gestorOptions.value = [];
  }
  
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
    console.error("Erro ao criar campus:", error);
    alert('Erro ao criar campus. Verifique os dados e tente novamente.');
  }
};

const updateCampus = async () => {
  try {
    await axios.put(`/api/v1/campi/${editingId.value}`, form.value);
    await fetchCampi();
    hideForm();
  } catch (error) { 
    console.error("Erro ao atualizar campus:", error);
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
    console.error("Erro ao excluir campus:", error);
    alert('Erro ao excluir campus. Pode haver registros vinculados (cursos, turmas).');
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
