<template>
  <div>
    <PageHeader 
      title="Gestão de Instituições de Ensino (IES)"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Instituições' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por nome, CNPJ ou código e-MEC..."
      v-model="buscaRapida"
    >
      <template #actions>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Nova IES
        </button>
      </template>
    </PageHeader>
    
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
                    :get-option-label="option => option.usuario ? option.usuario.name : option.name"
                    :options="reitorOptions"
                    @search="fetchUsers"
                    v-model="selectedReitor"
                    placeholder="Digite a matrícula ou nome..."
                >
                    <template #option="option">
                        {{ option.usuario ? option.usuario.name : option.name }}<br>
                        <small class="text-muted" v-if="option.matricula_funcional">Matrícula: {{ option.matricula_funcional }}</small>
                    </template>
                    <template #selected-option="option">
                        <div>{{ option.usuario ? option.usuario.name : option.name }}</div>
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
            <TableSkeleton v-if="loading" :columns="6" :rows="5" />
            <tbody v-else>
                <tr v-for="instituicao in instituicoesFiltradas" :key="instituicao.id">
                  <td class="ps-4">{{ instituicao.nome_fantasia }}</td>
                  <td>{{ instituicao.codigo_emec || 'N/A' }}</td>
                  <td>{{ instituicao.mantenedora ? instituicao.mantenedora.razao_social : 'N/A' }}</td>
                  <td>{{ instituicao.reitor ? instituicao.reitor.name : 'A definir' }}</td>
                  <td><StatusBadge :status="instituicao.status" /></td>
                  <td class="text-center">
                      <button @click="showEditForm(instituicao)" class="btn btn-sm btn-primary me-2" title="Editar IES"><i class="bi bi-pencil"></i></button>
                      <button @click="prepareDelete(instituicao)" class="btn btn-sm btn-danger me-2" title="Excluir IES"><i class="bi bi-trash"></i></button>
                      <router-link :to="`/admin/institucional/instituicoes/${instituicao.id}/atos-regulatorios`" class="btn btn-sm btn-info me-2" title="Gerir Atos Regulatórios"><i class="bi bi-file-earmark-text"></i></router-link>
                      <router-link :to="`/admin/institucional/instituicoes/${instituicao.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores"><i class="bi bi-diagram-3"></i></router-link>
                  </td>
                </tr>
                 <tr v-if="instituicoesFiltradas.length === 0">
                    <td colspan="6" class="text-center text-muted py-4">
                      {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhuma instituição encontrada.' }}
                    </td>
                </tr>
            </tbody>
            </table>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir a instituição ${itemToDelete?.nome_fantasia}?`"
      confirm-text="Excluir"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const instituicoes = ref([]);
const mantenedoras = ref([]);
const reitorOptions = ref([]);
const selectedReitor = ref(null);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const instituicoesFiltradas = computed(() => {
  if (!buscaRapida.value) return instituicoes.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return instituicoes.value.filter(inst => 
    inst.nome_fantasia?.toLowerCase().includes(termo) ||
    inst.razao_social?.toLowerCase().includes(termo) ||
    inst.cnpj?.includes(termo) ||
    inst.codigo_emec?.includes(termo) ||
    inst.mantenedora?.razao_social.toLowerCase().includes(termo)
  );
});

watch(selectedReitor, (newReitor) => {
    form.value.reitor_id = newReitor ? (newReitor.user_id || newReitor.id) : null;
});

const resetForm = () => {
    form.value = {
        mantenedora_id: null, reitor_id: null, razao_social: '',
        nome_fantasia: '', cnpj: '', endereco_sede: '',
        tipo_organizacao_academica: 'Faculdade', status: 'Ativo',
        codigo_sap: '', codigo_emec: '',
    };
    selectedReitor.value = null;
    reitorOptions.value = [];
};

const fetchInstituicoes = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar instituições:", error); 
  } finally { 
    loading.value = false; 
  }
};

const fetchMantenedoras = async () => {
  try {
    const response = await axios.get('/api/v1/mantenedoras');
    mantenedoras.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar mantenedoras:", error); 
  }
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
          name: instituicao.reitor.name,
          usuario: instituicao.reitor
      };
      reitorOptions.value = [selectedReitor.value];
  } else {
      selectedReitor.value = null;
      reitorOptions.value = [];
  }
  
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
  } catch (error) { 
    console.error("Erro ao criar instituição:", error);
    alert('Erro ao criar instituição. Verifique os dados e tente novamente.');
  }
};

const updateInstituicao = async () => {
  try {
    await axios.put(`/api/v1/instituicoes/${editingId.value}`, form.value);
    await fetchInstituicoes();
    hideForm();
  } catch (error) { 
    console.error("Erro ao atualizar instituição:", error);
    alert('Erro ao atualizar instituição. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (instituicao) => {
  itemToDelete.value = instituicao;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/instituicoes/${itemToDelete.value.id}`);
    await fetchInstituicoes();
    itemToDelete.value = null;
  } catch (error) { 
    console.error("Erro ao excluir instituição:", error);
    alert('Erro ao excluir instituição. Pode haver registros vinculados (campi, cursos).');
  }
};

onMounted(() => {
  fetchInstituicoes();
  fetchMantenedoras();
  
  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>
