<template>
  <div>
    <PageHeader 
      title="Catálogo de Setores"
      :breadcrumbs="[
        { label: 'Gestão de Setores' },
        { label: 'Catálogo de Setores' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por nome, sigla ou tipo..."
      v-model="buscaRapida"
    >
      <template #actions>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Setor
        </button>
      </template>
    </PageHeader>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Setor' : 'Adicionar Novo Setor' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateSetor() : createSetor()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nome do Setor</label>
              <input type="text" class="form-control" v-model="form.nome" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Sigla (Opcional)</label>
              <input type="text" class="form-control" v-model="form.sigla">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Tipo</label>
              <select class="form-select" v-model="form.tipo" required>
                <option value="Corporativo">Corporativo</option>
                <option value="Institucional">Institucional</option>
                <option value="Operacional">Operacional</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header"><h4>Setores Cadastrados</h4></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Nome</th>
                  <th>Sigla</th>
                  <th>Tipo</th>
                  <th class="text-center">Ações</th>
                </tr>
            </thead>
            <TableSkeleton v-if="loading" :columns="4" :rows="5" />
            <tbody v-else>
                <tr v-for="setor in setoresFiltrados" :key="setor.id">
                  <td class="ps-4">{{ setor.nome }}</td>
                  <td>{{ setor.sigla || 'N/A' }}</td>
                  <td><StatusBadge :status="setor.tipo" type="tipo" /></td>
                  <td class="text-center">
                      <button @click="showEditForm(setor)" class="btn btn-sm btn-primary me-2" title="Editar Setor"><i class="bi bi-pencil"></i></button>
                      <button @click="prepareDelete(setor)" class="btn btn-sm btn-danger" title="Excluir Setor"><i class="bi bi-trash"></i></button>
                  </td>
                </tr>
                 <tr v-if="setoresFiltrados.length === 0">
                    <td colspan="4" class="text-center text-muted py-4">
                      {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum setor encontrado.' }}
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
      :message="`Tem certeza que deseja excluir o setor ${itemToDelete?.nome}?`"
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
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const setores = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const setoresFiltrados = computed(() => {
  if (!buscaRapida.value) return setores.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return setores.value.filter(setor => 
    setor.nome?.toLowerCase().includes(termo) ||
    setor.sigla?.toLowerCase().includes(termo) ||
    setor.tipo?.toLowerCase().includes(termo)
  );
});

const resetForm = () => {
    form.value = { nome: '', sigla: '', tipo: 'Corporativo' };
};

const fetchSetores = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/setores');
    setores.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar setores:", error); 
  } finally { 
    loading.value = false; 
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (setor) => {
  isEditing.value = true;
  editingId.value = setor.id;
  form.value = { ...setor };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createSetor = async () => {
  try {
    await axios.post('/api/v1/setores', form.value);
    await fetchSetores();
    hideForm();
  } catch (error) { 
    console.error("Erro ao criar setor:", error);
    alert('Erro ao criar setor. Verifique os dados e tente novamente.');
  }
};

const updateSetor = async () => {
  try {
    await axios.put(`/api/v1/setores/${editingId.value}`, form.value);
    await fetchSetores();
    hideForm();
  } catch (error) { 
    console.error("Erro ao atualizar setor:", error);
    alert('Erro ao atualizar setor. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (setor) => {
  itemToDelete.value = setor;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/setores/${itemToDelete.value.id}`);
    await fetchSetores();
    itemToDelete.value = null;
  } catch (error) { 
    console.error("Erro ao excluir setor:", error); 
    alert('Erro ao excluir setor. Este setor pode estar vinculado a outras entidades.');
  }
};

onMounted(() => {
  fetchSetores();
  
  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>
