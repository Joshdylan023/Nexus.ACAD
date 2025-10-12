<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Atos Regulatórios para: {{ cursoNome }}</h2>
      <button @click="router.go(-1)" class="btn btn-secondary">Voltar</button>
    </div>

    <div class="mb-4">
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Novo Ato Regulatório
        </button>
    </div>

    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Ato Existente' : 'Adicionar Novo Ato' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateAto() : createAto()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tipo_ato" class="form-label">Tipo do Ato</label>
              <select class="form-select" :class="{ 'is-invalid': validationErrors.tipo_ato }" id="tipo_ato" v-model="form.tipo_ato" required>
                <option :value="null">-- Selecione --</option>
                <option value="Autorização">Autorização</option>
                <option value="Reconhecimento">Reconhecimento</option>
                <option value="Renovação de Reconhecimento">Renovação de Reconhecimento</option>
              </select>
              <div v-if="validationErrors.tipo_ato" class="invalid-feedback">{{ validationErrors.tipo_ato[0] }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="numero_portaria" class="form-label">Número da Portaria</label>
              <input type="text" class="form-control" :class="{ 'is-invalid': validationErrors.numero_portaria }" id="numero_portaria" v-model="form.numero_portaria" required>
              <div v-if="validationErrors.numero_portaria" class="invalid-feedback">{{ validationErrors.numero_portaria[0] }}</div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="codigo_mec" class="form-label">Código MEC</label>
              <input type="text" class="form-control" :class="{ 'is-invalid': validationErrors.codigo_mec }" id="codigo_mec" v-model="form.codigo_mec" required>
              <div v-if="validationErrors.codigo_mec" class="invalid-feedback">{{ validationErrors.codigo_mec[0] }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="codigo_emec" class="form-label">Código e-MEC (Opcional)</label>
              <input type="text" class="form-control" :class="{ 'is-invalid': validationErrors.codigo_emec }" id="codigo_emec" v-model="form.codigo_emec">
              <div v-if="validationErrors.codigo_emec" class="invalid-feedback">{{ validationErrors.codigo_emec[0] }}</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="data_publicacao_dou" class="form-label">Data de Publicação no D.O.U.</label>
              <input type="date" class="form-control" :class="{ 'is-invalid': validationErrors.data_publicacao_dou }" id="data_publicacao_dou" v-model="form.data_publicacao_dou" required>
              <div v-if="validationErrors.data_publicacao_dou" class="invalid-feedback">{{ validationErrors.data_publicacao_dou[0] }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="data_validade_ato" class="form-label">Data de Validade (Opcional)</label>
              <input type="date" class="form-control" :class="{ 'is-invalid': validationErrors.data_validade_ato }" id="data_validade_ato" v-model="form.data_validade_ato">
              <div v-if="validationErrors.data_validade_ato" class="invalid-feedback">{{ validationErrors.data_validade_ato[0] }}</div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="link_publicacao" class="form-label">Link para a Publicação (Opcional)</label>
            <input type="url" class="form-control" :class="{ 'is-invalid': validationErrors.link_publicacao }" id="link_publicacao" v-model="form.link_publicacao">
            <div v-if="validationErrors.link_publicacao" class="invalid-feedback">{{ validationErrors.link_publicacao[0] }}</div>
          </div>
          
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Histórico de Atos</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          <th>Cód. MEC / e-MEC</th>
          <th>Nº Portaria</th>
          <th>Publicação</th>
          <th>Validade</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="7">Carregando...</td>
        </tr>
        <tr v-for="ato in atosRegulatorios" :key="ato.id">
          <td>{{ ato.id }}</td>
          <td>{{ ato.tipo_ato }}</td>
          <td>{{ ato.codigo_mec }} <span v-if="ato.codigo_emec">/ {{ ato.codigo_emec }}</span></td>
          <td>{{ ato.numero_portaria }}</td>
          <td>{{ ato.data_publicacao_dou }}</td>
          <td>{{ ato.data_validade_ato || 'N/A' }}</td>
          <td>
            <button @click="showEditForm(ato)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteAto(ato.id)" class="btn btn-sm btn-danger">Excluir</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const cursoId = ref(route.params.id);
const cursoNome = ref('');
const atosRegulatorios = ref([]);
const loading = ref(true);

const form = ref({
  curso_id: cursoId.value,
  tipo_ato: null,
  codigo_mec: '',
  codigo_emec: '',
  numero_portaria: '',
  data_publicacao_dou: '',
  link_publicacao: '',
  data_validade_ato: '',
});

const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

const fetchCurso = async () => {
    try {
        const response = await axios.get(`/api/v1/cursos/${cursoId.value}`);
        cursoNome.value = response.data.nome;
    } catch (error) {
        console.error("Erro ao buscar nome do curso:", error);
        if (error.response && error.response.status === 404) {
            cursoNome.value = "Curso não encontrado";
        }
    }
};

const fetchAtos = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/cursos-atos-regulatorios?curso_id=${cursoId.value}`);
    atosRegulatorios.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar atos regulatórios:", error);
  } finally {
    loading.value = false;
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
  form.value = {
    curso_id: cursoId.value,
    tipo_ato: null,
    codigo_mec: '',
    codigo_emec: '',
    numero_portaria: '',
    data_publicacao_dou: '',
    link_publicacao: '',
    data_validade_ato: '',
  };
  validationErrors.value = {};
  showForm.value = true;
};

const showEditForm = (ato) => {
  isEditing.value = true;
  editingId.value = ato.id;
  form.value = { ...ato };
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createAto = async () => {
  try {
    await axios.post('/api/v1/cursos-atos-regulatorios', form.value);
    await fetchAtos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateAto = async () => {
  try {
    await axios.put(`/api/v1/cursos-atos-regulatorios/${editingId.value}`, form.value);
    await fetchAtos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteAto = async (id) => {
  if (confirm("Tem certeza que deseja excluir este ato regulatório?")) {
    try {
      await axios.delete(`/api/v1/cursos-atos-regulatorios/${id}`);
      await fetchAtos();
    } catch (error) {
      handleApiError(error);
    }
  }
};

onMounted(() => {
    fetchCurso();
    fetchAtos();
});
</script>