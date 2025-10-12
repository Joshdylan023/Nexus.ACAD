<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Professores</h2>
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Professor
      </button>
    </div>
    
    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Professor' : 'Adicionar Novo Professor' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateProfessor() : createProfessor()">
          <div class="mb-3">
            <label for="user" class="form-label">Usuário (Colaborador)</label>
            <select class="form-select" v-model="form.user_id" required :disabled="isEditing">
              <option :value="null">-- Selecione um Colaborador --</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.matricula_funcional }})</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="matricula_funcional" class="form-label">Matrícula Funcional</label>
            <input type="text" class="form-control" v-model="form.matricula_funcional" required>
          </div>
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Professores Cadastrados</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Matrícula</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="4">Carregando...</td></tr>
        <tr v-for="professor in professores" :key="professor.id">
          <td>{{ professor.id }}</td>
          <td>{{ professor.usuario ? professor.usuario.name : 'Vínculo de Usuário Quebrado' }}</td>
          <td>{{ professor.matricula_funcional }}</td>
          <td>
            <button @click="showEditForm(professor)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteProfessor(professor.id)" class="btn btn-sm btn-danger">Excluir</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const professores = ref([]);
const users = ref([]);
const loading = ref(true);
const form = ref({ user_id: null, matricula_funcional: '' });
const isEditing = ref(false);
const editingId = ref(null);
const showForm = ref(false);

const fetchProfessores = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/professores');
    professores.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar professores:", error);
  } finally {
    loading.value = false;
  }
};

const fetchUsers = async () => {
  try {
    // AQUI ESTÁ A CORREÇÃO PRINCIPAL: Chamando a nova rota segura
    const response = await axios.get('/api/v1/colaboradores');
    users.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar usuários/colaboradores:", error);
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  form.value = { user_id: null, matricula_funcional: '' };
  showForm.value = true;
};

const showEditForm = (professor) => {
  isEditing.value = true;
  editingId.value = professor.id;
  form.value = { ...professor };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createProfessor = async () => {
  try {
    await axios.post('/api/v1/professores', form.value);
    await fetchProfessores();
    hideForm();
  } catch (error) {
    console.error("Erro ao criar professor:", error);
  }
};

const updateProfessor = async () => {
  try {
    await axios.put(`/api/v1/professores/${editingId.value}`, form.value);
    await fetchProfessores();
    hideForm();
  } catch (error) {
    console.error("Erro ao atualizar professor:", error);
  }
};

const deleteProfessor = async (id) => {
  if (confirm("Tem certeza que deseja excluir este professor?")) {
    try {
      await axios.delete(`/api/v1/professores/${id}`);
      await fetchProfessores();
    } catch (error) {
      console.error("Erro ao excluir professor:", error);
    }
  }
};

onMounted(() => {
    fetchProfessores();
    fetchUsers();
});
</script>