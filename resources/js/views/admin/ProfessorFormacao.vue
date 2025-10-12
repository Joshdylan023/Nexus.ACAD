<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Formação Acadêmica de: {{ professorNome || 'Professor não encontrado' }}</h2>
      <button @click="router.go(-1)" class="btn btn-secondary">Voltar</button>
    </div>

    <div v-if="!showForm" class="mb-4">
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Adicionar Nova Formação
      </button>
    </div>

    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Formação' : 'Adicionar Nova Formação' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateFormacao() : createFormacao()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nivel" class="form-label">Nível</label>
              <select class="form-select" v-model="form.nivel" required>
                <option value="Graduação">Graduação</option>
                <option value="Especialização">Especialização</option>
                <option value="Mestrado">Mestrado</option>
                <option value="Doutorado">Doutorado</option>
                <option value="Pós-Doutorado">Pós-Doutorado</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="curso" class="form-label">Nome do Curso</label>
              <input type="text" class="form-control" v-model="form.curso" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="instituicao" class="form-label">Instituição de Ensino</label>
              <input type="text" class="form-control" v-model="form.instituicao" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="ano_conclusao" class="form-label">Ano de Conclusão</label>
              <input type="number" class="form-control" v-model="form.ano_conclusao" required placeholder="AAAA">
            </div>
          </div>
          <button type="submit" class="btn btn-success">Salvar Formação</button>
          <button v-if="isEditing" type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Formações Cadastradas</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Nível</th>
          <th>Curso</th>
          <th>Instituição</th>
          <th>Ano de Conclusão</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="5">Carregando...</td></tr>
        <tr v-for="formacao in formacoes" :key="formacao.id">
          <td>{{ formacao.nivel }}</td>
          <td>{{ formacao.curso }}</td>
          <td>{{ formacao.instituicao }}</td>
          <td>{{ formacao.ano_conclusao }}</td>
          <td>
            <button @click="showEditForm(formacao)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteFormacao(formacao.id)" class="btn btn-sm btn-danger">Excluir</button>
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

const professorId = ref(route.params.id);
const professorNome = ref('');
const formacoes = ref([]);
const loading = ref(true);
const form = ref({});
const isEditing = ref(false);
const editingId = ref(null);
const showForm = ref(false);

const resetForm = () => {
    form.value = {
        professor_id: professorId.value,
        nivel: 'Graduação',
        curso: '',
        instituicao: '',
        ano_conclusao: null,
    };
};

const fetchProfessor = async () => {
    try {
        const response = await axios.get(`/api/v1/professor-vinculos/${professorId.value}`);
        professorNome.value = response.data.usuario.name;
    } catch (error) {
        console.error("Erro ao buscar nome do professor:", error);
        professorNome.value = "Professor não encontrado";
    }
};

const fetchFormacoes = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/professor-formacao?professor_id=${professorId.value}`);
    formacoes.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar formações:", error);
    alert('Não foi possível carregar as formações. Verifique o console.');
  } finally {
    loading.value = false;
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  resetForm();
  showForm.value = true;
};

const showEditForm = (formacao) => {
  isEditing.value = true;
  editingId.value = formacao.id;
  form.value = { ...formacao, professor_id: professorId.value }; // Explicitly set professor_id to professorId.value
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createFormacao = async () => {
  try {
    await axios.post('/api/v1/professor-formacao', form.value);
    await fetchFormacoes();
    hideForm();
  } catch (error) {
    console.error("Erro ao criar formação:", error);
    alert("Erro ao salvar formação. Verifique os dados.");
  }
};

const updateFormacao = async () => {
  try {
    await axios.put(`/api/v1/professor-formacao/${editingId.value}`, form.value);
    await fetchFormacoes();
    hideForm();
  } catch (error) {
    console.error("Erro ao atualizar formação:", error);
    alert("Erro ao atualizar formação. Verifique os dados.");
  }
};

const deleteFormacao = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta formação?")) {
    try {
      await axios.delete(`/api/v1/professor-formacao/${id}`);
      await fetchFormacoes();
    } catch (error) {
      console.error("Erro ao excluir formação:", error);
    }
  }
};

onMounted(() => {
    fetchProfessor();
    fetchFormacoes();
});
</script>