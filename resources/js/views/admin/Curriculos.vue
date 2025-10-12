<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Currículos (Matrizes)</h2>
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Currículo
      </button>
    </div>
    
    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Currículo Existente' : 'Adicionar Novo Currículo' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateCurriculo() : createCurriculo()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="curso_id" class="form-label">Curso</label>
              <select class="form-select" v-model="form.curso_id" required>
                <option :value="null">-- Selecione um Curso --</option>
                <option v-for="curso in cursos" :key="curso.id" :value="curso.id">{{ curso.nome }}</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="nome_matriz" class="form-label">Nome da Matriz (Ex: 2025.1)</label>
              <input type="text" class="form-control" v-model="form.nome_matriz" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="tipo_matriz" class="form-label">Tipo</label>
              <select class="form-select" v-model="form.tipo_matriz" required>
                <option value="Fechada">Fechada</option>
                <option value="Aberta">Aberta</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label for="codigo_curriculo" class="form-label">Código do Currículo</label>
              <input type="text" class="form-control" v-model="form.codigo_curriculo" required>
            </div>
            <div class="col-md-4 mb-3">
              <label for="data_inicio_vigencia" class="form-label">Início da Vigência</label>
              <input type="date" class="form-control" v-model="form.data_inicio_vigencia" required>
            </div>
          </div>
          
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Currículos Cadastrados</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome da Matriz</th>
          <th>Curso</th>
          <th>Tipo</th>
          <th>Vigência</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="6">Carregando...</td></tr>
        <tr v-for="curriculo in curriculos" :key="curriculo.id">
          <td>{{ curriculo.id }}</td>
          <td>{{ curriculo.nome_matriz }} ({{ curriculo.codigo_curriculo }})</td>
          <td>{{ curriculo.curso ? curriculo.curso.nome : 'N/A' }}</td>
          <td>{{ curriculo.tipo_matriz }}</td>
          <td>{{ curriculo.data_inicio_vigencia }} até {{ curriculo.data_fim_vigencia || 'Atual' }}</td>
          <td>
            <button @click="showEditForm(curriculo)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteCurriculo(curriculo.id)" class="btn btn-sm btn-danger">Excluir</button>
            <router-link :to="{ name: 'admin.curriculos.matriz', params: { id: curriculo.id } }" class="btn btn-sm btn-info text-white">
              Editar Matriz
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

const curriculos = ref([]);
const cursos = ref([]);
const loading = ref(true);
const form = ref({
    curso_id: null,
    nome_matriz: '',
    tipo_matriz: 'Fechada',
    codigo_curriculo: '',
    data_inicio_vigencia: '',
    data_fim_vigencia: null,
});
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

const fetchCurriculos = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/v1/curriculos');
        curriculos.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar currículos:", error);
    } finally {
        loading.value = false;
    }
};

const fetchCursos = async () => {
    try {
        const response = await axios.get('/api/v1/cursos');
        cursos.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar cursos:", error);
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
    form.value = { curso_id: null, nome_matriz: '', tipo_matriz: 'Fechada', codigo_curriculo: '', data_inicio_vigencia: '', data_fim_vigencia: null };
    validationErrors.value = {};
    showForm.value = true;
};

const showEditForm = (curriculo) => {
    isEditing.value = true;
    editingId.value = curriculo.id;
    form.value = { ...curriculo };
    validationErrors.value = {};
    showForm.value = true;
};

const hideForm = () => {
    showForm.value = false;
    isEditing.value = false;
};

const createCurriculo = async () => {
    try {
        await axios.post('/api/v1/curriculos', form.value);
        await fetchCurriculos();
        hideForm();
    } catch (error) {
        handleApiError(error);
    }
};

const updateCurriculo = async () => {
    try {
        await axios.put(`/api/v1/curriculos/${editingId.value}`, form.value);
        await fetchCurriculos();
        hideForm();
    } catch (error) {
        handleApiError(error);
    }
};

const deleteCurriculo = async (id) => {
    if (confirm("Tem certeza que deseja excluir este currículo?")) {
        try {
            await axios.delete(`/api/v1/curriculos/${id}`);
            await fetchCurriculos();
        } catch (error) {
            handleApiError(error);
        }
    }
};

onMounted(() => {
    fetchCurriculos();
    fetchCursos();
});
</script>