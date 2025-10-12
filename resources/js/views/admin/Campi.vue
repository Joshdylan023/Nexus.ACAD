<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gestão de Campi</h2>
      <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Campus
      </button>
    </div>
    
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
                    label="name" 
                    :options="gestorOptions"
                    @search="fetchGestores"
                    v-model="selectedGestor"
                    placeholder="Digite a matrícula ou nome..."
                >
                    <template #option="{ usuario, matricula_funcional }">
                        {{ usuario.name }}<br>
                        <small class="text-muted">Matrícula: {{ matricula_funcional }}</small>
                    </template>
                    <template #selected-option="{ usuario }">
                        <div>{{ usuario.name }}</div>
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
            <tbody>
                <tr v-if="loading"><td colspan="5" class="text-center">A carregar...</td></tr>
                <tr v-for="campus in campi" :key="campus.id">
                  <td class="ps-4">{{ campus.nome }}</td>
                  <td>{{ campus.instituicao ? campus.instituicao.nome_fantasia : 'N/A' }}</td>
                  <td>{{ campus.gerente_unidade ? campus.gerente_unidade.name : 'A definir' }}</td>
                  <td><span class="badge" :class="getStatusClass(campus.status)">{{ campus.status }}</span></td>
                  <td class="text-center">
                      <button @click="showEditForm(campus)" class="btn btn-sm btn-primary me-2" title="Editar Campus"><i class="bi bi-pencil"></i></button>
                      <button @click="deleteCampus(campus.id)" class="btn btn-sm btn-danger me-2" title="Excluir Campus"><i class="bi bi-trash"></i></button>
                      <router-link :to="{ name: 'admin.institucional.campus.setores', params: { id: campus.id } }" class="btn btn-sm btn-secondary" title="Gerir Setores">
                        <i class="bi bi-diagram-3"></i>
                      </router-link>
                  </td>
                </tr>
                 <tr v-if="!loading && campi.length === 0">
                    <td colspan="5" class="text-center text-muted">Nenhum campus encontrado.</td>
                </tr>
            </tbody>
            </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const campi = ref([]);
const instituicoes = ref([]);
const gestorOptions = ref([]);
const selectedGestor = ref(null);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});

watch(selectedGestor, (newGestor) => {
    form.value.gerente_unidade_id = newGestor ? newGestor.user_id : null;
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
    validationErrors.value = {};
};

const getStatusClass = (status) => (status === 'Ativo' ? 'bg-success' : 'bg-danger');

const fetchCampi = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/campi');
    campi.value = response.data;
  } catch (error) { console.error("Erro ao buscar campi:", error); } 
  finally { loading.value = false; }
};

const fetchInstituicoes = async () => {
  try {
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) { console.error("Erro ao buscar instituições:", error); }
};

const fetchGestores = (search, loading) => {
  if (search && search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}`)
      .then(response => {
        gestorOptions.value = response.data.map(c => ({ user_id: c.id, name: c.name, usuario: { name: c.name }, matricula_funcional: c.matricula_funcional }));
      })
      .catch(error => console.error("Erro ao buscar colaboradores:", error))
      .finally(() => loading(false));
  }
};

const handleApiError = (error) => { /* ... */ };

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
      selectedGestor.value = { user_id: campus.gerente_unidade.id, usuario: { name: campus.gerente_unidade.name }};
  } else {
      selectedGestor.value = null;
  }
  validationErrors.value = {};
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
  } catch (error) { handleApiError(error); }
};

const updateCampus = async () => {
  try {
    await axios.put(`/api/v1/campi/${editingId.value}`, form.value);
    await fetchCampi();
    hideForm();
  } catch (error) { handleApiError(error); }
};

const deleteCampus = async (id) => {
  if (confirm("Tem certeza que deseja excluir este campus?")) {
    try {
      await axios.delete(`/api/v1/campi/${id}`);
      await fetchCampi();
    } catch (error) { console.error("Erro ao excluir campus:", error); }
  }
};

onMounted(() => {
  fetchCampi();
  fetchInstituicoes();
});
</script>