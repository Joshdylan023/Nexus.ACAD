<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Disciplinas</h2>
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Disciplina
      </button>
    </div>
    
    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Disciplina' : 'Adicionar Nova Disciplina' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateDisciplina() : createDisciplina()">
          <div class="mb-3">
            <label for="nome" class="form-label">Nome da Disciplina</label>
            <input type="text" class="form-control" :class="{ 'is-invalid': validationErrors.nome }" v-model="form.nome" required>
            <div v-if="validationErrors.nome" class="invalid-feedback">{{ validationErrors.nome[0] }}</div>
          </div>
          <div class="mb-3">
            <label for="codigo" class="form-label">Código da Disciplina</label>
            <input type="text" class="form-control" :class="{ 'is-invalid': validationErrors.codigo }" v-model="form.codigo" required>
            <div v-if="validationErrors.codigo" class="invalid-feedback">{{ validationErrors.codigo[0] }}</div>
          </div>
          <div class="mb-3">
            <label for="carga_horaria_total" class="form-label">Carga Horária Total (horas)</label>
            <input type="number" class="form-control" :class="{ 'is-invalid': validationErrors.carga_horaria_total }" v-model="form.carga_horaria_total" required>
            <div v-if="validationErrors.carga_horaria_total" class="invalid-feedback">{{ validationErrors.carga_horaria_total[0] }}</div>
          </div>
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Disciplinas Cadastradas</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Código</th>
          <th>Nome</th>
          <th>Carga Horária</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="5">Carregando...</td></tr>
        <tr v-for="disciplina in disciplinas" :key="disciplina.id">
          <td>{{ disciplina.id }}</td>
          <td>{{ disciplina.codigo }}</td>
          <td>{{ disciplina.nome }}</td>
          <td>{{ disciplina.carga_horaria_total }}h</td>
          <td>
            <button @click="showEditForm(disciplina)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteDisciplina(disciplina.id)" class="btn btn-sm btn-danger">Excluir</button>
            <router-link :to="{ name: 'admin.disciplinas.ementas', params: { id: disciplina.id } }" class="btn btn-sm btn-info text-white">
              Gerenciar Ementas
            </router-link>
            </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const disciplinas = ref([]);
const loading = ref(true);
const form = ref({ nome: '', codigo: '', carga_horaria_total: null });
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

const fetchDisciplinas = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/disciplinas');
    disciplinas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar disciplinas:", error);
  } finally {
    loading.value = false;
  }
};

const handleApiError = (error) => {
  validationErrors.value = {};
  if (error.response && error.response.status === 422) {
    validationErrors.value = error.response.data.errors;
  } else {
    console.error("Erro na API:", error);
    alert("Ocorreu um erro inesperado.");
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  form.value = { nome: '', codigo: '', carga_horaria_total: null };
  validationErrors.value = {};
  showForm.value = true;
};

const showEditForm = (disciplina) => {
  isEditing.value = true;
  editingId.value = disciplina.id;
  form.value = { ...disciplina };
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createDisciplina = async () => {
  try {
    await axios.post('/api/v1/disciplinas', form.value);
    await fetchDisciplinas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateDisciplina = async () => {
  try {
    await axios.put(`/api/v1/disciplinas/${editingId.value}`, form.value);
    await fetchDisciplinas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteDisciplina = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta disciplina?")) {
    try {
      await axios.delete(`/api/v1/disciplinas/${id}`);
      await fetchDisciplinas();
    } catch (error) {
      handleApiError(error);
    }
  }
};

onMounted(fetchDisciplinas);
</script>