<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Cursos</h2>
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Curso
      </button>
    </div>
    
    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Curso' : 'Adicionar Novo Curso' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateCurso() : createCurso()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Instituição</label>
              <select class="form-select" v-model="form.instituicao_id" required>
                <option :value="null">-- Selecione --</option>
                <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">{{ inst.nome_fantasia }}</option>
              </select>
              <div v-if="validationErrors.instituicao_id" class="text-danger mt-1">{{ validationErrors.instituicao_id[0] }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Área de Conhecimento</label>
              <select class="form-select" v-model="form.area_conhecimento_id" required>
                <option :value="null">-- Selecione --</option>
                <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.nome }}</option>
              </select>
              <div v-if="validationErrors.area_conhecimento_id" class="text-danger mt-1">{{ validationErrors.area_conhecimento_id[0] }}</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8 mb-3">
              <label for="nome" class="form-label">Nome do Curso</label>
              <input type="text" class="form-control" v-model="form.nome" required>
              <div v-if="validationErrors.nome" class="text-danger mt-1">{{ validationErrors.nome[0] }}</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="codigo_interno" class="form-label">Código Interno</label>
              <input type="text" class="form-control" v-model="form.codigo_interno" required>
              <div v-if="validationErrors.codigo_interno" class="text-danger mt-1">{{ validationErrors.codigo_interno[0] }}</div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nível de Ensino</label>
              <select class="form-select" v-model="form.nivel" required>
                <option value="Ensino Médio">Ensino Médio</option>
                <option value="Técnico">Técnico</option>
                <option value="Graduação">Graduação</option>
                <option value="Pós-Graduação">Pós-Graduação</option>
                <option value="Mestrado">Mestrado</option>
                <option value="Doutorado">Doutorado</option>
                <option value="Extensão">Extensão</option>
                <option value="Livre">Livre</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Coordenador (Opcional)</label>
              <v-select
                  label="name"
                  :options="coordenadorOptions"
                  @search="fetchCoordenadorOptions"
                  v-model="selectedCoordenador"
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
              <label class="form-label">Duração Padrão (semestres)</label>
              <input type="number" class="form-control" v-model="form.duracao_padrao_semestres" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Prazo Máximo (semestres)</label>
              <input type="number" class="form-control" v-model="form.prazo_maximo_semestres" required>
            </div>
             <div class="col-md-4 mb-3">
              <label class="form-label">Vagas Anuais</label>
              <input type="number" class="form-control" v-model="form.vagas_anuais" required>
            </div>
          </div>

           <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" v-model="form.status" required>
                <option value="Em Planejamento">Em Planejamento</option>
                <option value="Ativo">Ativo</option>
                <option value="Em Extinção">Em Extinção</option>
                <option value="Extinto">Extinto</option>
              </select>
            </div>
          
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Cursos Cadastrados</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Instituição</th>
          <th>Área</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="6" class="text-center">A carregar...</td></tr>
        <tr v-for="curso in cursos" :key="curso.id">
          <td>{{ curso.id }}</td>
          <td>{{ curso.nome }} ({{ curso.codigo_interno }})</td>
          <td>{{ curso.instituicao ? curso.instituicao.nome_fantasia : 'N/A' }}</td>
          <td>{{ curso.area_conhecimento ? curso.area_conhecimento.nome : 'N/A' }}</td>
          <td><span class="badge" :class="getStatusClass(curso.status)">{{ curso.status }}</span></td>
          <td>
            <button @click="showEditForm(curso)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteCurso(curso.id)" class="btn btn-sm btn-danger me-2">Excluir</button>
            <router-link :to="{ name: 'admin.academico.atos-regulatorios', params: { id: curso.id } }" class="btn btn-sm btn-info text-white">
              Atos Regulatórios
            </router-link>
          </td>
        </tr>
        <tr v-if="!loading && cursos.length === 0">
            <td colspan="6" class="text-center text-muted">Nenhum curso encontrado.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const cursos = ref([]);
const instituicoes = ref([]);
const areas = ref([]);
const coordenadorOptions = ref([]);
const selectedCoordenador = ref(null);
const loading = ref(true);
const form = ref({});
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

const resetForm = () => {
    form.value = { 
        instituicao_id: null,
        area_conhecimento_id: null,
        nome: '', 
        codigo_interno: '',
        nivel: 'Graduação',
        duracao_padrao_semestres: null,
        prazo_maximo_semestres: null,
        coordenador_id: null,
        status: 'Em Planejamento',
        vagas_anuais: null
    };
    selectedCoordenador.value = null;
    validationErrors.value = {};
};

watch(selectedCoordenador, (newCoordinator) => {
    form.value.coordenador_id = newCoordinator ? newCoordinator.id : null;
});

const getStatusClass = (status) => {
    switch (status) {
        case 'Ativo': return 'bg-success';
        case 'Em Extinção': return 'bg-warning text-dark';
        case 'Extinto': return 'bg-danger';
        default: return 'bg-secondary';
    }
};

const fetchCursos = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/cursos');
    cursos.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar cursos:", error);
  } finally {
    loading.value = false;
  }
};

const fetchInstituicoes = async () => {
  try {
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar instituições:", error);
  }
};

const fetchAreas = async () => {
  try {
    const response = await axios.get('/api/v1/areas-conhecimento');
    areas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar áreas:", error);
  }
};

const fetchCoordenadorOptions = (search, loading) => {
  if (search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}`)
      .then(response => {
        coordenadorOptions.value = response.data;
      })
      .catch(error => console.error("Erro ao buscar colaboradores:", error))
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
  resetForm();
  showForm.value = true;
};

const showEditForm = (curso) => {
  isEditing.value = true;
  editingId.value = curso.id;
  form.value = { ...curso };
  
  if (curso.coordenador) {
      selectedCoordenador.value = {
          id: curso.coordenador.id,
          name: curso.coordenador.name
      };
  } else {
      selectedCoordenador.value = null;
  }
  
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createCurso = async () => {
  try {
    await axios.post('/api/v1/cursos', form.value);
    await fetchCursos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateCurso = async () => {
  try {
    await axios.put(`/api/v1/cursos/${editingId.value}`, form.value);
    await fetchCursos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteCurso = async (id) => {
  if (confirm("Tem certeza que deseja excluir este curso?")) {
    try {
      await axios.delete(`/api/v1/cursos/${id}`);
      await fetchCursos();
    } catch (error) {
      handleApiError(error);
    }
  }
};

onMounted(() => {
  fetchCursos();
  fetchInstituicoes();
  fetchAreas();
});
</script>