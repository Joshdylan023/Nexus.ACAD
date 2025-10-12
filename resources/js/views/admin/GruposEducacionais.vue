<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Grupos Educacionais</h2>
      <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Grupo
      </button>
    </div>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Grupo Educacional' : 'Adicionar Novo Grupo' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateGrupo() : createGrupo()">
          <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" id="nome" class="form-control" v-model="form.nome" required>
            <div v-if="validationErrors.nome" class="text-danger mt-1">{{ validationErrors.nome[0] }}</div>
          </div>
          <div class="mb-3">
            <label for="cnpj" class="form-label">CNPJ (Opcional)</label>
            <input type="text" id="cnpj" class="form-control" v-model="form.cnpj">
             <div v-if="validationErrors.cnpj" class="text-danger mt-1">{{ validationErrors.cnpj[0] }}</div>
          </div>
           <div class="mb-3">
            <label for="representante_legal" class="form-label">Representante Legal (Opcional)</label>
            <input type="text" id="representante_legal" class="form-control" v-model="form.representante_legal">
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Grupos Cadastrados</h4>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nome</th>
              <th>CNPJ</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="3" class="text-center">A carregar...</td></tr>
            <tr v-for="grupo in grupos" :key="grupo.id">
              <td class="ps-4">{{ grupo.nome }}</td>
              <td>{{ grupo.cnpj || 'N/A' }}</td>
              <td class="text-center">
                <button @click="showEditForm(grupo)" class="btn btn-sm btn-primary me-2" title="Editar Grupo"><i class="bi bi-pencil"></i></button>
                <button @click="deleteGrupo(grupo.id)" class="btn btn-sm btn-danger me-2" title="Inativar Grupo"><i class="bi bi-trash"></i></button>
                 <router-link :to="{ name: 'admin.institucional.grupo.setores', params: { id: grupo.id } }" class="btn btn-sm btn-secondary" title="Gerir Setores">
                    <i class="bi bi-diagram-3"></i>
                </router-link>
              </td>
            </tr>
            <tr v-if="!loading && grupos.length === 0">
                <td colspan="3" class="text-center text-muted">Nenhum grupo educacional encontrado.</td>
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

const grupos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});

const resetForm = () => {
    form.value = { nome: '', cnpj: '', representante_legal: '' };
    validationErrors.value = {};
};

const fetchGrupos = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar grupos:", error);
    if (error.response?.status === 403) {
      alert("Você não tem permissão para ver os grupos educacionais.");
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

const showEditForm = (grupo) => {
  isEditing.value = true;
  editingId.value = grupo.id;
  form.value = { ...grupo };
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
  validationErrors.value = {};
};

const createGrupo = async () => {
  try {
    await axios.post('/api/v1/grupos-educacionais', form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateGrupo = async () => {
  try {
    await axios.put(`/api/v1/grupos-educacionais/${editingId.value}`, form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteGrupo = async (id) => {
  if (confirm("Esta ação irá INATIVAR o grupo. Tem certeza?")) {
    // A lógica de inativação será implementada no backend. Por agora, mantemos o delete.
    try {
      await axios.delete(`/api/v1/grupos-educacionais/${id}`);
      await fetchGrupos();
    } catch (error) {
      console.error("Erro ao excluir grupo:", error);
    }
  }
};

onMounted(fetchGrupos);
</script>