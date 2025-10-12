<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gestão de Instituições de Ensino (IES)</h2>
      <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nova IES
      </button>
    </div>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar IES Existente' : 'Adicionar Nova IES' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateInstituicao() : createInstituicao()">
          <div class="mb-3">
            <label class="form-label">Mantenedora</label>
            <select class="form-select" v-model="form.mantenedora_id" required>
              <option :value="null">-- Selecione --</option>
              <option v-for="mantenedora in mantenedoras" :key="mantenedora.id" :value="mantenedora.id">{{ mantenedora.razao_social }}</option>
            </select>
          </div>
          <div class="row">
             <div class="col-md-6 mb-3">
                <label class="form-label">Razão Social</label>
                <input type="text" class="form-control" v-model="form.razao_social" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Nome Fantasia</label>
                <input type="text" class="form-control" v-model="form.nome_fantasia" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">CNPJ</label>
            <input type="text" class="form-control" v-model="form.cnpj" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Endereço da Sede</label>
            <input type="text" class="form-control" v-model="form.endereco_sede" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tipo de Organização Académica</label>
                <select class="form-select" v-model="form.tipo_organizacao_academica" required>
                    <option value="Faculdade">Faculdade</option>
                    <option value="Centro Universitário">Centro Universitário</option>
                    <option value="Universidade">Universidade</option>
                </select>
            </div>
             <div class="col-md-6 mb-3">
                <label class="form-label">Reitor / Diretor Geral (Opcional)</label>
                <v-select
                    label="name"
                    :options="reitorOptions"
                    @search="fetchUsers"
                    v-model="selectedReitor"
                    placeholder="Digite a matrícula ou nome..."
                >
                    <template #option="{ name, matricula_funcional }">
                        {{ name }}<br>
                        <small class="text-muted">Matrícula: {{ matricula_funcional }}</small>
                    </template>
                    <template #selected-option="{ name }">
                        <div>{{ name }}</div>
                    </template>
                </v-select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" v-model="form.status" required>
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                    <option value="Em Extinção">Em Extinção</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Código SAP (Opcional)</label>
              <input type="text" class="form-control" v-model="form.codigo_sap">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Código e-MEC (Opcional)</label>
              <input type="text" class="form-control" v-model="form.codigo_emec">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header"><h4>Instituições Cadastradas</h4></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Nome Fantasia</th>
                  <th>Cód. e-MEC</th>
                  <th>Mantenedora</th>
                  <th>Reitor/Diretor</th>
                  <th>Status</th>
                  <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading"><td colspan="6" class="text-center">A carregar...</td></tr>
                <tr v-for="instituicao in instituicoes" :key="instituicao.id">
                  <td class="ps-4">{{ instituicao.nome_fantasia }}</td>
                  <td>{{ instituicao.codigo_emec || 'N/A' }}</td>
                  <td>{{ instituicao.mantenedora ? instituicao.mantenedora.razao_social : 'N/A' }}</td>
                  <td>{{ instituicao.reitor ? instituicao.reitor.name : 'A definir' }}</td>
                  <td><span class="badge" :class="getStatusClass(instituicao.status)">{{ instituicao.status }}</span></td>
                  <td class="text-center">
                      <button @click="showEditForm(instituicao)" class="btn btn-sm btn-primary me-2" title="Editar IES"><i class="bi bi-pencil"></i></button>
                      <button @click="deleteInstituicao(instituicao.id)" class="btn btn-sm btn-danger me-2" title="Excluir IES"><i class="bi bi-trash"></i></button>
                      <router-link :to="{ name: 'admin.institucional.atos', params: { id: instituicao.id } }" class="btn btn-sm btn-info me-2" title="Gerir Atos Regulatórios"><i class="bi bi-file-earmark-text"></i></router-link>
                      <router-link :to="{ name: 'admin.institucional.instituicao.setores', params: { id: instituicao.id } }" class="btn btn-sm btn-secondary" title="Gerir Setores"><i class="bi bi-diagram-3"></i></router-link>
                  </td>
                </tr>
                 <tr v-if="!loading && instituicoes.length === 0">
                    <td colspan="6" class="text-center text-muted">Nenhuma instituição encontrada.</td>
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

const instituicoes = ref([]);
const mantenedoras = ref([]);
const reitorOptions = ref([]);
const selectedReitor = ref(null);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});

watch(selectedReitor, (newReitor) => {
    form.value.reitor_id = newReitor ? newReitor.id : null;
});

const resetForm = () => {
    form.value = {
        mantenedora_id: null, reitor_id: null, razao_social: '',
        nome_fantasia: '', cnpj: '', endereco_sede: '',
        tipo_organizacao_academica: 'Faculdade', status: 'Ativo',
        codigo_sap: '', codigo_emec: '',
    };
    selectedReitor.value = null;
    validationErrors.value = {};
};

const getStatusClass = (status) => {
    if (status === 'Ativo') return 'bg-success';
    if (status === 'Inativo') return 'bg-danger';
    if (status === 'Em Extinção') return 'bg-warning text-dark';
    return 'bg-secondary';
};

const fetchInstituicoes = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) { console.error("Erro ao buscar instituições:", error); } 
  finally { loading.value = false; }
};

const fetchMantenedoras = async () => {
  try {
    const response = await axios.get('/api/v1/mantenedoras');
    mantenedoras.value = response.data;
  } catch (error) { console.error("Erro ao buscar mantenedoras:", error); }
};

const fetchUsers = (search, loading) => {
  if (search && search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}`)
      .then(response => {
        reitorOptions.value = response.data;
      })
      .catch(error => {
        console.error("Erro ao buscar colaboradores:", error);
        reitorOptions.value = [];
      })
      .finally(() => loading(false));
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

const showEditForm = (instituicao) => {
  isEditing.value = true;
  editingId.value = instituicao.id;
  form.value = { ...instituicao };
  if (instituicao.reitor) {
      selectedReitor.value = {
          id: instituicao.reitor.id,
          name: instituicao.reitor.name
      };
  } else {
      selectedReitor.value = null;
  }
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createInstituicao = async () => {
  try {
    await axios.post('/api/v1/instituicoes', form.value);
    await fetchInstituicoes();
    hideForm();
  } catch (error) { handleApiError(error); }
};

const updateInstituicao = async () => {
  try {
    await axios.put(`/api/v1/instituicoes/${editingId.value}`, form.value);
    await fetchInstituicoes();
    hideForm();
  } catch (error) { handleApiError(error); }
};

const deleteInstituicao = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta instituição?")) {
    try {
      await axios.delete(`/api/v1/instituicoes/${id}`);
      await fetchInstituicoes();
    } catch (error) { console.error("Erro ao excluir instituição:", error); }
  }
};

onMounted(() => {
  fetchInstituicoes();
  fetchMantenedoras();
});
</script>