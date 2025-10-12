<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Grandes Áreas de Conhecimento</h2>
      <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Grande Área
      </button>
    </div>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Grande Área' : 'Adicionar Nova Grande Área' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateGrandeArea() : createGrandeArea()">
          <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" id="nome" class="form-control" v-model="form.nome" required>
            <div v-if="validationErrors.nome" class="text-danger mt-1">{{ validationErrors.nome[0] }}</div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
      <div class="card-header">
        <h4>Grandes Áreas Cadastradas</h4>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">ID</th>
              <th>Nome</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="3" class="text-center">A carregar...</td></tr>
            <tr v-for="grandeArea in grandesAreas" :key="grandeArea.id">
              <td class="ps-4">{{ grandeArea.id }}</td>
              <td>{{ grandeArea.nome }}</td>
              <td class="text-center">
                <button @click="showEditForm(grandeArea)" class="btn btn-sm btn-primary me-2" title="Editar"><i class="bi bi-pencil"></i></button>
                <button @click="deleteGrandeArea(grandeArea.id)" class="btn btn-sm btn-danger" title="Excluir"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
            <tr v-if="!loading && grandesAreas.length === 0">
                <td colspan="3" class="text-center text-muted">Nenhuma grande área encontrada.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const grandesAreas = ref([]);
const loading = ref(true);
const form = ref({ nome: '' });
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});

const resetForm = () => {
    form.value = { nome: '' };
    validationErrors.value = {};
};

const fetchGrandesAreas = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/grandes-areas');
    grandesAreas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar grandes áreas:", error);
    if (error.response?.status === 403) {
      alert("Você não tem permissão para aceder a este recurso.");
    }
  } finally {
    loading.value = false;
  }
};

const handleApiError = (error) => {
  if (error.response && error.response.status === 422) {
    validationErrors.value = error.response.data.errors;
  } else {
    console.error("Erro na API:", error);
    alert("Ocorreu um erro inesperado.");
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (grandeArea) => {
  isEditing.value = true;
  editingId.value = grandeArea.id;
  form.value = { ...grandeArea };
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createGrandeArea = async () => {
  try {
    await axios.post('/api/v1/grandes-areas', form.value);
    await fetchGrandesAreas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateGrandeArea = async () => {
  try {
    await axios.put(`/api/v1/grandes-areas/${editingId.value}`, form.value);
    await fetchGrandesAreas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteGrandeArea = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta grande área?")) {
    try {
      await axios.delete(`/api/v1/grandes-areas/${id}`);
      await fetchGrandesAreas();
    } catch (error) {
      console.error("Erro ao excluir grande área:", error);
    }
  }
};

onMounted(fetchGrandesAreas);
</script>