<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Catálogo de Setores</h2>
      <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Tipo de Setor
      </button>
    </div>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Tipo de Setor' : 'Adicionar Novo Tipo' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateSetor() : createSetor()">
          
          <div class="mb-3">
            <label for="tipo" class="form-label">Nível Hierárquico do Setor</label>
            <select class="form-select" v-model="form.tipo" required>
              <option :value="null" disabled>-- Selecione o nível --</option>
              <option value="Corporativo">Corporativo (Nível Grupo/Mantenedora)</option>
              <option value="Institucional">Institucional (Nível IES)</option>
              <option value="Operacional">Operacional (Nível Campus)</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="setor_pai_id" class="form-label">Setor Pai (Opcional, no catálogo)</label>
            <select class="form-select" v-model="form.setor_pai_id">
              <option :value="null">-- Nenhum (Setor de Topo) --</option>
              <option v-for="setor in setores.filter(s => s.id !== editingId)" :key="setor.id" :value="setor.id">
                {{ setor.nome }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label for="nome" class="form-label">Nome do Setor</label>
            <input type="text" class="form-control" v-model="form.nome" required>
          </div>

          <div class="mb-3">
            <label for="sigla" class="form-label">Sigla</label>
            <input type="text" class="form-control" v-model="form.sigla">
          </div>

          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header">
            <h4>Tipos de Setores Cadastrados</h4>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Nome</th>
                  <th>Sigla</th>
                  <th>Tipo</th>
                  <th>Setor Pai</th>
                  <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading"><td colspan="5" class="text-center">A carregar...</td></tr>
                <tr v-for="setor in setores" :key="setor.id">
                  <td class="ps-4">{{ setor.nome }}</td>
                  <td>{{ setor.sigla }}</td>
                  <td><span class="badge bg-info text-dark">{{ setor.tipo }}</span></td>
                  <td>{{ setor.pai ? setor.pai.nome : 'N/A' }}</td>
                  <td class="text-center">
                    <button @click="showEditForm(setor)" class="btn btn-sm btn-primary me-2" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button @click="deleteSetor(setor.id)" class="btn btn-sm btn-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                <tr v-if="!loading && setores.length === 0">
                    <td colspan="5" class="text-center text-muted">Nenhum tipo de setor encontrado.</td>
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

const setores = ref([]);
const loading = ref(true);
const form = ref({ nome: '', sigla: '', tipo: 'Operacional', setor_pai_id: null });
const isEditing = ref(false);
const editingId = ref(null);
const showForm = ref(false);
const validationErrors = ref({});

const fetchSetores = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/v1/setores');
        setores.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar setores:", error);
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
    editingId.value = null;
    form.value = { nome: '', sigla: '', tipo: 'Operacional', setor_pai_id: null };
    validationErrors.value = {};
    showForm.value = true;
};

const showEditForm = (setor) => {
    isEditing.value = true;
    editingId.value = setor.id;
    form.value = { ...setor };
    validationErrors.value = {};
    showForm.value = true;
};

const hideForm = () => {
    showForm.value = false;
    isEditing.value = false;
};

const createSetor = async () => {
    try {
        await axios.post('/api/v1/setores', form.value);
        await fetchSetores();
        hideForm();
    } catch (error) {
        handleApiError(error);
    }
};

const updateSetor = async () => {
    try {
        await axios.put(`/api/v1/setores/${editingId.value}`, form.value);
        await fetchSetores();
        hideForm();
    } catch (error) {
        handleApiError(error);
    }
};

const deleteSetor = async (id) => {
    if (confirm("Tem certeza que deseja excluir este tipo de setor? A exclusão removerá todos os seus vínculos em todos os campi e instituições.")) {
        try {
            await axios.delete(`/api/v1/setores/${id}`);
            await fetchSetores();
        } catch (error) {
            console.error("Erro ao excluir setor:", error);
        }
    }
};

onMounted(fetchSetores);
</script>