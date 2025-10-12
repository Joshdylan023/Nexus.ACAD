<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gestão de Ementas para: {{ disciplinaNome }}</h2>
      <button @click="router.go(-1)" class="btn btn-secondary">Voltar</button>
    </div>

    <div class="mb-4">
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Nova Versão da Ementa
        </button>
    </div>

    <div v-if="showForm" class="card mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Versão da Ementa' : 'Adicionar Nova Versão' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateEmenta() : createEmenta()">
          <div class="row">
            <div class="col-md-9 mb-3">
              <label for="titulo" class="form-label">Título</label>
              <input type="text" class="form-control" v-model="form.titulo" required>
            </div>
            <div class="col-md-3 mb-3">
              <label for="versao" class="form-label">Versão</label>
              <input type="number" class="form-control" v-model="form.versao" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="ementa_resumida" class="form-label">Ementa Resumida</label>
            <textarea class="form-control" v-model="form.ementa_resumida" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="conteudo_detalhado" class="form-label">Conteúdo Detalhado</label>
            <textarea class="form-control" v-model="form.conteudo_detalhado" rows="10" required></textarea>
          </div>
          <div class="mb-3">
            <label for="bibliografia" class="form-label">Bibliografia</label>
            <textarea class="form-control" v-model="form.bibliografia" rows="5"></textarea>
          </div>
          <div class="mb-3">
            <label for="data_inicio_vigencia" class="form-label">Data de Início da Vigência</label>
            <input type="date" class="form-control" v-model="form.data_inicio_vigencia" required>
          </div>
          <div class="mb-3">
            <label for="data_fim_vigencia" class="form-label">Data de Fim da Vigência</label>
            <input type="date" class="form-control" v-model="form.data_fim_vigencia">
          </div>
          <button type="submit" class="btn btn-success">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <h3>Versões Cadastradas</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Versão</th>
          <th>Título</th>
          <th>Vigência</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading"><td colspan="5">Carregando...</td></tr>
        <tr v-for="ementa in ementas" :key="ementa.id">
          <td>{{ ementa.versao }}</td>
          <td>{{ ementa.titulo }}</td>
          <td>
            {{ ementa.data_inicio_vigencia }} até {{ ementa.data_fim_vigencia || 'Indeterminado' }}
          </td>
          <td>
            <span v-if="isVigente(ementa)" class="badge bg-success">Vigente</span>
            <span v-else class="badge bg-secondary">Expirada</span>
          </td>
          <td>
            <button @click="showEditForm(ementa)" class="btn btn-sm btn-primary me-2">Editar</button>
            <button @click="deleteEmenta(ementa.id)" class="btn btn-sm btn-danger">Excluir</button>
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

const disciplinaId = ref(route.params.id);
const disciplinaNome = ref('');
const ementas = ref([]);
const loading = ref(true);
const form = ref({});
const isEditing = ref(false);
const editingId = ref(null);
const showForm = ref(false);

const resetForm = () => {
    form.value = {
        disciplina_id: disciplinaId.value,
        titulo: '',
        versao: 1,
        ementa_resumida: '',
        conteudo_detalhado: '',
        bibliografia: '',
        data_inicio_vigencia: '',
        data_fim_vigencia: null,
    };
};

const fetchDisciplina = async () => {
    try {
        const response = await axios.get(`/api/v1/disciplinas/${disciplinaId.value}`);
        disciplinaNome.value = response.data.nome;
    } catch (error) {
        console.error("Erro ao buscar nome da disciplina:", error);
    }
};

const fetchEmentas = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/ementas?disciplina_id=${disciplinaId.value}`);
    ementas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar ementas:", error);
  } finally {
    loading.value = false;
  }
};

const isVigente = (ementa) => {
  const hoje = new Date();
  const inicio = new Date(ementa.data_inicio_vigencia);
  
  inicio.setMinutes(inicio.getMinutes() + inicio.getTimezoneOffset());
  
  if (ementa.data_fim_vigencia) {
    const fim = new Date(ementa.data_fim_vigencia);
    fim.setMinutes(fim.getMinutes() + fim.getTimezoneOffset());
    return hoje >= inicio && hoje <= fim;
  }
  
  return hoje >= inicio;
};


const showCreateForm = () => {
  isEditing.value = false;
  resetForm();
  showForm.value = true;
};

const showEditForm = (ementa) => {
  isEditing.value = true;
  editingId.value = ementa.id;
  form.value = { ...ementa };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createEmenta = async () => {
  try {
    await axios.post('/api/v1/ementas', form.value);
    await fetchEmentas();
    hideForm();
  } catch (error) {
    console.error("Erro ao criar ementa:", error);
    alert("Erro ao salvar a ementa. Verifique os dados e tente novamente.");
  }
};

const updateEmenta = async () => {
  try {
    await axios.put(`/api/v1/ementas/${editingId.value}`, form.value);
    await fetchEmentas();
    hideForm();
  } catch (error) {
    console.error("Erro ao atualizar ementa:", error);
    alert("Erro ao salvar a ementa. Verifique os dados e tente novamente.");
  }
};

const deleteEmenta = async (id) => {
  if (confirm("Tem certeza que deseja excluir esta versão da ementa?")) {
    try {
      await axios.delete(`/api/v1/ementas/${id}`);
      await fetchEmentas();
    } catch (error) {
      console.error("Erro ao excluir ementa:", error);
    }
  }
};

const cancelEdit = () => { hideForm(); };

onMounted(() => {
    fetchDisciplina();
    fetchEmentas();
});
</script>