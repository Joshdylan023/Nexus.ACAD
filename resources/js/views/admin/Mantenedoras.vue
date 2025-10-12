<template>
  <div>
    <PageHeader 
      title="Gestão de Mantenedoras"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Mantenedoras' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por razão social, CNPJ ou grupo..."
      v-model="buscaRapida"
    >
      <template #actions>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Nova Mantenedora
        </button>
      </template>
    </PageHeader>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Mantenedora' : 'Adicionar Nova Mantenedora' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateMantenedora() : createMantenedora()">
          <div class="mb-3">
            <label class="form-label">Grupo Educacional</label>
            <select class="form-select" v-model="form.grupo_educacional_id" required>
              <option :value="null">-- Selecione --</option>
              <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">{{ grupo.nome }}</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Razão Social</label>
              <input type="text" class="form-control" v-model="form.razao_social" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nome Fantasia (Opcional)</label>
              <input type="text" class="form-control" v-model="form.nome_fantasia">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">CNPJ</label>
              <input type="text" class="form-control" v-model="form.cnpj" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Representante Legal (Opcional)</label>
              <input type="text" class="form-control" v-model="form.representante_legal">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Endereço da Sede (Opcional)</label>
            <input type="text" class="form-control" v-model="form.endereco_sede">
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header"><h4>Mantenedoras Cadastradas</h4></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Razão Social</th>
                  <th>CNPJ</th>
                  <th>Grupo Educacional</th>
                  <th class="text-center">Ações</th>
                </tr>
            </thead>
            <TableSkeleton v-if="loading" :columns="4" :rows="5" />
            <tbody v-else>
                <tr v-for="mantenedora in mantenedorasFiltradas" :key="mantenedora.id">
                  <td class="ps-4">{{ mantenedora.razao_social }}</td>
                  <td>{{ mantenedora.cnpj }}</td>
                  <td>{{ mantenedora.grupo_educacional ? mantenedora.grupo_educacional.nome : 'N/A' }}</td>
                  <td class="text-center">
                      <button @click="showEditForm(mantenedora)" class="btn btn-sm btn-primary me-2" title="Editar Mantenedora"><i class="bi bi-pencil"></i></button>
                      <button @click="prepareDelete(mantenedora)" class="btn btn-sm btn-danger me-2" title="Excluir Mantenedora"><i class="bi bi-trash"></i></button>
                      <router-link :to="`/admin/institucional/mantenedoras/${mantenedora.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores">
                        <i class="bi bi-diagram-3"></i>
                      </router-link>
                  </td>
                </tr>
                 <tr v-if="mantenedorasFiltradas.length === 0">
                    <td colspan="4" class="text-center text-muted py-4">
                      {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhuma mantenedora encontrada.' }}
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
      :message="`Tem certeza que deseja excluir a mantenedora ${itemToDelete?.razao_social}?`"
      confirm-text="Excluir"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const mantenedoras = ref([]);
const grupos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const mantenedorasFiltradas = computed(() => {
  if (!buscaRapida.value) return mantenedoras.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return mantenedoras.value.filter(m => 
    m.razao_social?.toLowerCase().includes(termo) ||
    m.nome_fantasia?.toLowerCase().includes(termo) ||
    m.cnpj?.includes(termo) ||
    m.grupo_educacional?.nome.toLowerCase().includes(termo)
  );
});

const resetForm = () => {
    form.value = { 
      grupo_educacional_id: null, 
      razao_social: '', 
      nome_fantasia: '', 
      cnpj: '', 
      endereco_sede: '', 
      representante_legal: '' 
    };
};

const fetchMantenedoras = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/mantenedoras');
    mantenedoras.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar mantenedoras:", error); 
  } finally { 
    loading.value = false; 
  }
};

const fetchGrupos = async () => {
  try {
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar grupos:", error); 
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (mantenedora) => {
  isEditing.value = true;
  editingId.value = mantenedora.id;
  form.value = { ...mantenedora };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createMantenedora = async () => {
  try {
    await axios.post('/api/v1/mantenedoras', form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) { 
    console.error("Erro ao criar mantenedora:", error);
    alert('Erro ao criar mantenedora. Verifique os dados e tente novamente.');
  }
};

const updateMantenedora = async () => {
  try {
    await axios.put(`/api/v1/mantenedoras/${editingId.value}`, form.value);
    await fetchMantenedoras();
    hideForm();
  } catch (error) { 
    console.error("Erro ao atualizar mantenedora:", error);
    alert('Erro ao atualizar mantenedora. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (mantenedora) => {
  itemToDelete.value = mantenedora;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/mantenedoras/${itemToDelete.value.id}`);
    await fetchMantenedoras();
    itemToDelete.value = null;
  } catch (error) { 
    console.error("Erro ao excluir mantenedora:", error);
    alert('Erro ao excluir mantenedora. Pode haver registros vinculados (instituições).');
  }
};

onMounted(() => {
  fetchMantenedoras();
  fetchGrupos();
  
  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>
