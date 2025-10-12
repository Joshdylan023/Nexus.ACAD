<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gestão de Mantenedoras</h2>
      <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova Mantenedora
      </button>
    </div>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Mantenedora' : 'Adicionar Nova Mantenedora' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateMantenedora() : createMantenedora()">
          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Grupo Educacional (Opcional)</label>
                <select class="form-select" v-model="form.grupo_educacional_id">
                  <option :value="null">-- Nenhum --</option>
                  <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nome }}</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="razao_social" class="form-label">Razão Social</label>
                <input type="text" id="razao_social" class="form-control" v-model="form.razao_social" required>
            </div>
          </div>
          <div class="row">
             <div class="col-md-6 mb-3">
                <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" class="form-control" v-model="form.nome_fantasia">
            </div>
            <div class="col-md-6 mb-3">
                <label for="cnpj" class="form-label">CNPJ</label>
                <input type="text" id="cnpj" class="form-control" v-model="form.cnpj" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="endereco_completo" class="form-label">Endereço Completo</label>
            <input type="text" id="endereco_completo" class="form-control" v-model="form.endereco_completo">
          </div>
          <div class="mb-3">
            <label for="representante_legal" class="form-label">Representante Legal</label>
            <input type="text" id="representante_legal" class="form-control" v-model="form.representante_legal">
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header">
            <h4>Mantenedoras Cadastradas</h4>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">ID</th>
                  <th>Razão Social</th>
                  <th>CNPJ</th>
                  <th>Grupo</th>
                  <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading"><td colspan="5" class="text-center">A carregar...</td></tr>
                <tr v-for="mantenedora in mantenedoras" :key="mantenedora.id">
                  <td class="ps-4">{{ mantenedora.id }}</td>
                  <td>{{ mantenedora.razao_social }}</td>
                  <td>{{ mantenedora.cnpj }}</td>
                  <td>{{ mantenedora.grupo_educacional ? mantenedora.grupo_educacional.nome : 'N/A' }}</td>
                  <td>
                      <button @click="showEditForm(mantenedora)" class="btn btn-sm btn-primary me-2">Editar</button>
                      <router-link :to="`/admin/institucional/mantenedoras/${mantenedora.id}/setores`" class="btn btn-sm btn-info me-2">Gerir Setores</router-link>
                      <button @click="deleteMantenedora(mantenedora.id)" class="btn btn-sm btn-danger">Excluir</button>
                  </td>
                </tr>
                <tr v-if="!loading && mantenedoras.length === 0">
                    <td colspan="5" class="text-center text-muted">Nenhuma mantenedora encontrada.</td>
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

const mantenedoras = ref([]);
const grupos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});

const resetForm = () => {
    form.value = {
        razao_social: '',
        nome_fantasia: '',
        cnpj: '',
        grupo_educacional_id: null,
        endereco_completo: '',
        representante_legal: ''
    };
    validationErrors.value = {};
};

const fetchMantenedoras = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/mantenedoras');
    mantenedoras.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar mantenedoras:", error);
  } finally {
    loading.value = false;
  }
};

const fetchGrupos = async () => {
  try {
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar grupos:", error);
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
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (mantenedora) => {
  isEditing.value = true;
  editingId.value = mantenedora.id;
  form.value = { ...mantenedora };
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createMantenedora = async () => {
  try {
    await axios.post('/api/v1/mantenedoras', form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateMantenedora = async () => {
  try {
    await axios.put(`/api/v1/mantenedoras/${editingId.value}`, form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteMantenedora = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta mantenedora?")) {
    try {
      await axios.delete(`/api/v1/mantenedoras/${id}`);
      await fetchMantenedoras();
    } catch (error) {
      console.error("Erro ao excluir mantenedora:", error);
    }
  }
};

onMounted(() => {
  fetchMantenedoras();
  fetchGrupos();
});
</script>