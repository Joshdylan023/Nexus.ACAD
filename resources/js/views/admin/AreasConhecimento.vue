<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Áreas de Conhecimento</h2>
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Área de Conhecimento
      </button>
    </div>
    
    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Área Existente' : 'Adicionar Nova Área' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateArea() : createArea()">
          <div class="mb-3">
            <label for="grande_area" class="form-label">Grande Área de Conhecimento</label>
            <select class="form-select" :class="{ 'is-invalid': validationErrors.grande_area_conhecimento_id }" id="grande_area" v-model="form.grande_area_conhecimento_id" required>
              <option :value="null">-- Selecione uma Grande Área --</option>
              <option v-for="ga in grandesAreas" :key="ga.id" :value="ga.id">
                {{ ga.nome }}
              </option>
            </select>
            <div v-if="validationErrors.grande_area_conhecimento_id" class="invalid-feedback">
              {{ validationErrors.grande_area_conhecimento_id[0] }}
            </div>
          </div>
          <div class="mb-3">
            <label for="nome" class="form-label">Nome da Área</label>
            <input type="text" class="form-control" :class="{ 'is-invalid': validationErrors.nome }" id="nome" v-model="form.nome" required>
            <div v-if="validationErrors.nome" class="invalid-feedback">
              {{ validationErrors.nome[0] }}
            </div>
          </div>
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Áreas Cadastradas</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Grande Área</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="4">Carregando...</td>
        </tr>
        <tr v-for="area in areas" :key="area.id">
          <td>{{ area.id }}</td>
          <td>{{ area.nome }}</td>
          <td>{{ area.grande_area ? area.grande_area.nome : 'N/A' }}</td>
          <td>
            <button @click="showEditForm(area)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteArea(area.id)" class="btn btn-sm btn-danger">Excluir</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const areas = ref([]);
const grandesAreas = ref([]);
const loading = ref(true);
const form = ref({ nome: '', grande_area_conhecimento_id: null });
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

const fetchAreas = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/areas-conhecimento');
    areas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar áreas:", error);
  } finally {
    loading.value = false;
  }
};

const fetchGrandesAreas = async () => {
  try {
    const response = await axios.get('/api/v1/grandes-areas');
    grandesAreas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar grandes áreas:", error);
  }
};

const handleApiError = (error) => {
  validationErrors.value = {};
  if (error.response && error.response.status === 422) {
    validationErrors.value = error.response.data.errors;
  } else {
    console.error("Ocorreu um erro na API:", error);
    alert("Ocorreu um erro inesperado. Verifique o console para mais detalhes.");
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  form.value = { nome: '', grande_area_conhecimento_id: null };
  validationErrors.value = {};
  showForm.value = true;
};

const showEditForm = (area) => {
  isEditing.value = true;
  editingId.value = area.id;
  form.value = { ...area };
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
  editingId.value = null;
  form.value = { nome: '', grande_area_conhecimento_id: null };
  validationErrors.value = {};
};

const createArea = async () => {
  try {
    await axios.post('/api/v1/areas-conhecimento', form.value);
    await fetchAreas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateArea = async () => {
  try {
    await axios.put(`/api/v1/areas-conhecimento/${editingId.value}`, form.value);
    await fetchAreas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteArea = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta área?")) {
    try {
      await axios.delete(`/api/v1/areas-conhecimento/${id}`);
      await fetchAreas();
    } catch (error) {
      handleApiError(error);
    }
  }
};

onMounted(() => {
  fetchAreas();
  fetchGrandesAreas();
});
</script>