<template>
  <div>
    <PageHeader 
      :title="`Atos Regulatórios - ${instituicaoNome || 'Carregando...'}`"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional', path: '/admin/institucional' },
        { label: 'Instituições', path: '/admin/institucional/instituicoes' },
        { label: instituicaoNome || 'Atos Regulatórios' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por tipo ou número..."
      v-model="buscaRapida"
    >
      <template #actions>
        <ExportButton 
          v-if="!showForm"
          :data="atosFiltrados"
          :columns="exportColumns"
          :fileName="`atos-regulatorios-${instituicaoNome || 'instituicao'}`"
          class="me-2"
        />
        <router-link to="/admin/institucional/instituicoes" class="btn btn-secondary me-2">
          <i class="bi bi-arrow-left"></i> Voltar
        </router-link>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Ato
        </button>
      </template>
    </PageHeader>

    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Ato Regulatório' : 'Adicionar Novo Ato Regulatório' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateAto() : createAto()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tipo_ato" class="form-label">Tipo do Ato</label>
              <select class="form-select" v-model="form.tipo_ato" required>
                <option value="Credenciamento">Credenciamento</option>
                <option value="Recredenciamento">Recredenciamento</option>
                <option value="Descredenciamento">Descredenciamento</option>
                <option value="Renovação de Reconhecimento">Renovação de Reconhecimento</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="numero_portaria" class="form-label">Número da Portaria</label>
              <input type="text" class="form-control" v-model="form.numero_portaria" required placeholder="Ex: Portaria MEC nº 123/2024">
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
            <input type="url" class="form-control" v-model="form.link_publicacao" placeholder="https://www.in.gov.br/...">
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> {{ isEditing ? 'Atualizar' : 'Salvar' }}
          </button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
      <div class="card-header">
        <h4>Histórico de Atos Regulatórios</h4>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Tipo</th>
              <th>Nº Portaria</th>
              <th>Publicação (D.O.U.)</th>
              <th>Validade</th>
              <th>Status</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading" :columns="6" :rows="5" />
          <tbody v-else>
            <tr v-for="ato in atosFiltrados" :key="ato.id">
              <td class="ps-4">
                <strong>{{ ato.tipo_ato }}</strong>
              </td>
              <td>
                <a v-if="ato.link_publicacao" :href="ato.link_publicacao" target="_blank" class="text-white text-decoration-none">
                  {{ ato.numero_portaria }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
                <span v-else>{{ ato.numero_portaria }}</span>
              </td>
              <td>{{ formatDate(ato.data_publicacao_dou) }}</td>
              <td>{{ formatDate(ato.data_validade_ato) }}</td>
              <td>
                <StatusBadge :status="getStatusAto(ato)" />
              </td>
              <td class="text-center">
                <button @click="showEditForm(ato)" class="btn btn-sm btn-primary me-2" title="Editar Ato">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(ato)" class="btn btn-sm btn-danger" title="Excluir Ato">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="atosFiltrados.length === 0">
              <td colspan="6" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum ato regulatório encontrado.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir o ato ${itemToDelete?.numero_portaria}?`"
      confirm-text="Excluir"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { Modal } from 'bootstrap';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';

const route = useRoute();
const instituicaoId = ref(route.params.id);
const instituicaoNome = ref('');
const atos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const atosFiltrados = computed(() => {
  if (!buscaRapida.value) return atos.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return atos.value.filter(ato => 
    ato.tipo_ato?.toLowerCase().includes(termo) ||
    ato.numero_portaria?.toLowerCase().includes(termo)
  );
});

const exportColumns = [
  { key: 'tipo_ato', label: 'Tipo do Ato' },
  { key: 'numero_portaria', label: 'Número da Portaria' },
  { key: 'data_publicacao_dou', label: 'Data de Publicação (D.O.U.)' },
  { key: 'data_validade_ato', label: 'Data de Validade' },
  { key: 'link_publicacao', label: 'Link da Publicação' }
];

const resetForm = () => {
  form.value = {
    instituicao_id: instituicaoId.value,
    tipo_ato: 'Credenciamento',
    numero_portaria: '',
    data_publicacao_dou: '',
    link_publicacao: '',
    data_validade_ato: '',
  };
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
  return date.toLocaleDateString('pt-BR');
};

const getStatusAto = (ato) => {
  if (!ato.data_validade_ato) return 'Ativo';
  
  const hoje = new Date();
  const validade = new Date(ato.data_validade_ato);
  
  if (validade < hoje) return 'Inativo';
  
  const diasRestantes = Math.floor((validade - hoje) / (1000 * 60 * 60 * 24));
  if (diasRestantes <= 90) return 'Em Extinção';
  
  return 'Ativo';
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (ato) => {
  isEditing.value = true;
  editingId.value = ato.id;
  form.value = {
    instituicao_id: instituicaoId.value,
    tipo_ato: ato.tipo_ato,
    numero_portaria: ato.numero_portaria,
    data_publicacao_dou: ato.data_publicacao_dou,
    link_publicacao: ato.link_publicacao || '',
    data_validade_ato: ato.data_validade_ato || '',
  };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
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
    alert('Erro ao criar ato. Verifique os dados e tente novamente.');
  }
};

const updateAto = async () => {
  try {
    await axios.put(`/api/v1/instituicao-atos-regulatorios/${editingId.value}`, form.value);
    await fetchAtos();
    hideForm();
  } catch (error) {
    console.error("Erro ao atualizar ato regulatório:", error);
    alert('Erro ao atualizar ato. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (ato) => {
  itemToDelete.value = ato;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/instituicao-atos-regulatorios/${itemToDelete.value.id}`);
    await fetchAtos();
    itemToDelete.value = null;
  } catch (error) {
    console.error("Erro ao excluir o ato:", error);
    alert('Erro ao excluir ato regulatório.');
  }
};

onMounted(() => {
  resetForm();
  fetchInstituicao();
  fetchAtos();

  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>
