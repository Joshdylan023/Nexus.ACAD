<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Vínculos de Professores</h2>
      <button @click="showCreateForm" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Vínculo
      </button>
    </div>
    
    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Vínculo' : 'Adicionar Novo Vínculo' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateVinculo() : createVinculo()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Usuário (Colaborador)</label>
              <v-select
                  label="name"
                  :options="userOptions"
                  @search="fetchUserOptions"
                  v-model="selectedUser"
                  :disabled="isEditing"
                  placeholder="Digite para buscar..."
              >
                <template #option="{ name, matricula_funcional }">
                  {{ name }}<br><small class="text-muted">{{ matricula_funcional }}</small>
                </template>
                <template #no-options>
                  Digite para iniciar a busca...
                </template>
              </v-select>
               <div v-if="validationErrors.user_id" class="text-danger mt-1">{{ validationErrors.user_id[0] }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Instituição</label>
              <select class="form-select" v-model="form.instituicao_id" required>
                <option :value="null">-- Selecione --</option>
                <option v-for="ies in instituicoes" :key="ies.id" :value="ies.id">{{ ies.nome_fantasia }}</option>
              </select>
               <div v-if="validationErrors.instituicao_id" class="text-danger mt-1">{{ validationErrors.instituicao_id[0] }}</div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="matricula_funcional" class="form-label">Matrícula Funcional</label>
              <input type="text" class="form-control" v-model="form.matricula_funcional" required>
               <div v-if="validationErrors.matricula_funcional" class="text-danger mt-1">{{ validationErrors.matricula_funcional[0] }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" v-model="form.status" required>
                <option value="Ativo">Ativo</option>
                <option value="Afastado">Afastado</option>
                <option value="Desligado">Desligado</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="regime_contratacao" class="form-label">Regime de Contratação</label>
              <select class="form-select" v-model="form.regime_contratacao" required>
                <option value="CLT">CLT</option>
                <option value="PJ">PJ</option>
                <option value="Horista">Horista</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="tipo_contrato" class="form-label">Tipo de Contrato</label>
              <input type="text" class="form-control" v-model="form.tipo_contrato" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="carga_horaria_contratual" class="form-label">Carga Horária Contratual</label>
              <input type="number" class="form-control" v-model="form.carga_horaria_contratual" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="nivel_carreira" class="form-label">Nível de Carreira</label>
              <input type="text" class="form-control" v-model="form.nivel_carreira" required>
            </div>
          </div>
           <div class="row">
            <div class="col-md-4 mb-3">
              <label for="data_contratacao" class="form-label">Data de Contratação</label>
              <input type="date" class="form-control" v-model="form.data_contratacao">
            </div>
            <div class="col-md-4 mb-3">
              <label for="data_afastamento" class="form-label">Data de Afastamento</label>
              <input type="date" class="form-control" v-model="form.data_afastamento">
            </div>
            <div class="col-md-4 mb-3">
              <label for="data_desligamento" class="form-label">Data de Desligamento</label>
              <input type="date" class="form-control" v-model="form.data_desligamento">
            </div>
          </div>
          <div class="mb-3">
            <label for="biografia" class="form-label">Biografia (Opcional)</label>
            <textarea class="form-control" v-model="form.biografia" rows="3"></textarea>
          </div>
          
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Vínculos Cadastrados</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Professor</th>
          <th>Instituição</th>
          <th>Matrícula</th>
          <th>Status</th>
          <th>Contratação</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="6">Carregando...</td></tr>
        <tr v-for="vinculo in vinculos" :key="vinculo.id">
          <td>{{ vinculo.usuario ? vinculo.usuario.name : 'N/A' }}</td>
          <td>{{ vinculo.instituicao ? vinculo.instituicao.nome_fantasia : 'N/A' }}</td>
          <td>{{ vinculo.matricula_funcional }}</td>
          <td><span class="badge" :class="getStatusClass(vinculo.status)">{{ vinculo.status }}</span></td>
          <td>{{ formatDateToInput(vinculo.data_contratacao) }}</td>
          <td>
            <button @click="showEditForm(vinculo)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteVinculo(vinculo.id)" class="btn btn-sm btn-danger">Excluir</button>
            <router-link :to="{ name: 'admin.professores.formacao', params: { id: vinculo.id } }" class="btn btn-sm btn-info text-white">
              Formação
            </router-link>
          </td>
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

const vinculos = ref([]);
const userOptions = ref([]);
const instituicoes = ref([]);
const selectedUser = ref(null);
const loading = ref(true);
const form = ref({});
const isEditing = ref(false);
const editingId = ref(null);
const showForm = ref(false);
const validationErrors = ref({});

const resetForm = () => {
    form.value = {
        user_id: null,
        instituicao_id: null,
        matricula_funcional: '',
        status: 'Ativo',
        regime_contratacao: 'CLT',
        tipo_contrato: 'Indeterminado',
        carga_horaria_contratual: 40,
        nivel_carreira: 'Professor I',
        biografia: '',
        data_contratacao: null,
        data_afastamento: null,
        data_desligamento: null,
    };
    selectedUser.value = null;
    validationErrors.value = {};
};

watch(selectedUser, (newUser) => {
    form.value.user_id = newUser ? newUser.id : null;
});

const getStatusClass = (status) => {
  if (status === 'Ativo') return 'bg-success';
  if (status === 'Afastado') return 'bg-warning text-dark';
  if (status === 'Desligado') return 'bg-danger';
  return 'bg-secondary';
};

const formatDateToInput = (dateString) => {
  if (!dateString) return null;
  return dateString.substring(0, 10);
};

const formatDateToDisplay = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    return date.toLocaleDateString('pt-BR');
}

const handleApiError = (error) => {
  validationErrors.value = {};
  if (error.response && error.response.status === 422) {
    validationErrors.value = error.response.data.errors;
  } else {
    console.error("Ocorreu um erro na API:", error);
    alert("Ocorreu um erro inesperado.");
  }
};

const fetchVinculos = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/professor-vinculos');
    vinculos.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar vínculos:", error);
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

const fetchUserOptions = (search, loading) => {
  if (search && search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}`)
      .then(response => {
        userOptions.value = response.data;
      })
      .catch(error => {
        console.error("Erro ao buscar usuários:", error);
      })
      .finally(() => loading(false));
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  resetForm();
  showForm.value = true;
};

const showEditForm = (vinculo) => {
  isEditing.value = true;
  editingId.value = vinculo.id;
  
  form.value = { 
    ...vinculo,
    data_contratacao: formatDateToInput(vinculo.data_contratacao),
    data_afastamento: formatDateToInput(vinculo.data_afastamento),
    data_desligamento: formatDateToInput(vinculo.data_desligamento),
  };

  selectedUser.value = vinculo.usuario;
  validationErrors.value = {};
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createVinculo = async () => {
  try {
    await axios.post('/api/v1/professor-vinculos', form.value);
    await fetchVinculos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const updateVinculo = async () => {
  try {
    await axios.put(`/api/v1/professor-vinculos/${editingId.value}`, form.value);
    await fetchVinculos();
    hideForm();
  } catch (error) {
    handleApiError(error);
  }
};

const deleteVinculo = async (id) => {
  if (confirm("Tem certeza que deseja excluir este vínculo?")) {
    try {
      await axios.delete(`/api/v1/professor-vinculos/${id}`);
      await fetchVinculos();
    } catch (error) {
      handleApiError(error);
    }
  }
};

onMounted(() => {
    fetchVinculos();
    fetchInstituicoes();
});
</script>