<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Atos Regulatórios para: {{ instituicaoNome }}</h2>
      <div>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success me-2">
            <i class="bi bi-plus-lg"></i> Novo Ato
        </button>
        <router-link to="/admin/institucional/instituicoes" class="btn btn-secondary">Voltar</router-link>
      </div>
    </div>

    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">Adicionar Novo Ato Regulatório</div>
      <div class="card-body">
        <form @submit.prevent="createAto">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tipo_ato" class="form-label">Tipo do Ato</label>
              <select class="form-select" v-model="form.tipo_ato" required>
                <option value="Credenciamento">Credenciamento</option>
                <option value="Recredenciamento">Recredenciamento</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="numero_portaria" class="form-label">Número da Portaria</label>
              <input type="text" class="form-control" v-model="form.numero_portaria" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="data_publicacao_dou" class="form-label">Data de Publicação (D.O.U.)</label>
              <input type="date" class="form-control" v-model="form.data_publicacao_dou" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="data_validade_ato" class="form-label">Data de Validade (Opcional)</label>
              <input type="date" class="form-control" v-model="form.data_validade_ato">
            </div>
          </div>
          <div class="mb-3">
            <label for="link_publicacao" class="form-label">Link da Publicação (Opcional)</label>
            <input type="url" class="form-control" v-model="form.link_publicacao" placeholder="https://...">
          </div>
          <button type="submit" class="btn btn-primary">Salvar Ato</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header">
            <h4>Histórico de Atos</h4>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Tipo</th>
                  <th>Nº Portaria</th>
                  <th>Publicação</th>
                  <th>Validade</th>
                  <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading"><td colspan="5" class="text-center">A carregar...</td></tr>
                <tr v-for="ato in atos" :key="ato.id">
                  <td class="ps-4">{{ ato.tipo_ato }}</td>
                  <td>
                    <a v-if="ato.link_publicacao" :href="ato.link_publicacao" target="_blank" class="text-white">{{ ato.numero_portaria }} <i class="bi bi-box-arrow-up-right"></i></a>
                    <span v-else>{{ ato.numero_portaria }}</span>
                  </td>
                  <td>{{ formatDate(ato.data_publicacao_dou) }}</td>
                  <td>{{ formatDate(ato.data_validade_ato) }}</td>
                  <td>
                    <button @click="deleteAto(ato.id)" class="btn btn-sm btn-danger">Excluir</button>
                  </td>
                </tr>
                <tr v-if="!atos.length && !loading">
                    <td colspan="5" class="text-center text-muted">Nenhum ato regulatório encontrado.</td>
                </tr>
            </tbody>
            </table>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const instituicaoId = ref(route.params.id);
const instituicaoNome = ref('');
const atos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false); // Controla a visibilidade do formulário

const resetForm = () => {
    form.value = {
        instituicao_id: instituicaoId.value,
        tipo_ato: 'Credenciamento',
        numero_portaria: '',
        data_publicacao_dou: '',
        link_publicacao: '',
        data_validade_ato: null,
    };
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    return date.toLocaleDateString('pt-BR');
};

const showCreateForm = () => {
    resetForm();
    showForm.value = true;
};

const hideForm = () => {
    showForm.value = false;
};

const fetchInstituicao = async () => {
    try {
        const response = await axios.get(`/api/v1/instituicoes/${instituicaoId.value}`);
        instituicaoNome.value = response.data.nome_fantasia;
    } catch (error) {
        console.error("Erro ao buscar dados da instituição:", error);
    }
};

const fetchAtos = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/instituicao-atos-regulatorios?instituicao_id=${instituicaoId.value}`);
    atos.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar atos regulatórios:", error);
  } finally {
    loading.value = false;
  }
};

const createAto = async () => {
  try {
    await axios.post('/api/v1/instituicao-atos-regulatorios', form.value);
    await fetchAtos();
    hideForm();
  } catch (error) {
    console.error("Erro ao criar ato regulatório:", error);
  }
};

const deleteAto = async (id) => {
  if (confirm("Tem certeza que deseja excluir este ato regulatório?")) {
    try {
      await axios.delete(`/api/v1/instituicao-atos-regulatorios/${id}`);
      await fetchAtos();
    } catch (error) {
      console.error("Erro ao excluir o ato:", error);
    }
  }
};

onMounted(() => {
    resetForm();
    fetchInstituicao();
    fetchAtos();
});
</script>