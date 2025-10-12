<template>
  <div>
    <PageHeader 
      title="Gestão de Grupos Educacionais"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Grupos Educacionais' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por nome, CNPJ ou representante..."
      v-model="buscaRapida"
    >
      <template #actions>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Grupo
        </button>
      </template>
    </PageHeader>
    
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Grupo Educacional' : 'Adicionar Novo Grupo Educacional' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateGrupo() : createGrupo()">
          <div class="mb-3">
            <label class="form-label">Nome do Grupo</label>
            <input type="text" class="form-control" v-model="form.nome" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">CNPJ (Opcional)</label>
              <input type="text" class="form-control" v-model="form.cnpj">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Representante Legal (Opcional)</label>
              <input type="text" class="form-control" v-model="form.representante_legal">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Endereço Completo (Opcional)</label>
            <input type="text" class="form-control" v-model="form.endereco_completo">
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
        <div class="card-header"><h4>Grupos Educacionais Cadastrados</h4></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
            <thead>
                <tr>
                  <th class="ps-4">Nome</th>
                  <th>CNPJ</th>
                  <th>Representante Legal</th>
                  <th class="text-center">Ações</th>
                </tr>
            </thead>
            <TableSkeleton v-if="loading" :columns="4" :rows="5" />
            <tbody v-else>
                <tr v-for="grupo in gruposFiltrados" :key="grupo.id">
                  <td class="ps-4">{{ grupo.nome }}</td>
                  <td>{{ grupo.cnpj || 'N/A' }}</td>
                  <td>{{ grupo.representante_legal || 'N/A' }}</td>
                  <td class="text-center">
                      <button @click="showEditForm(grupo)" class="btn btn-sm btn-primary me-2" title="Editar Grupo"><i class="bi bi-pencil"></i></button>
                      <button @click="prepareDelete(grupo)" class="btn btn-sm btn-danger me-2" title="Excluir Grupo"><i class="bi bi-trash"></i></button>
                      <router-link :to="`/admin/institucional/grupos-educacionais/${grupo.id}/setores`" class="btn btn-sm btn-secondary" title="Gerir Setores">
                        <i class="bi bi-diagram-3"></i>
                      </router-link>
                  </td>
                </tr>
                 <tr v-if="gruposFiltrados.length === 0">
                    <td colspan="4" class="text-center text-muted py-4">
                      {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum grupo educacional encontrado.' }}
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
      :message="`Tem certeza que deseja excluir o grupo ${itemToDelete?.nome}?`"
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

const grupos = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const gruposFiltrados = computed(() => {
  if (!buscaRapida.value) return grupos.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return grupos.value.filter(grupo => 
    grupo.nome?.toLowerCase().includes(termo) ||
    grupo.cnpj?.includes(termo) ||
    grupo.representante_legal?.toLowerCase().includes(termo)
  );
});

const resetForm = () => {
    form.value = { 
      nome: '', 
      cnpj: '', 
      endereco_completo: '',
      representante_legal: ''
    };
};

const fetchGrupos = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar grupos:", error); 
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

const showEditForm = (grupo) => {
  isEditing.value = true;
  editingId.value = grupo.id;
  form.value = { ...grupo };
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createGrupo = async () => {
  try {
    await axios.post('/api/v1/grupos-educacionais', form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) { 
    console.error("Erro ao criar grupo:", error); 
    alert('Erro ao criar grupo. Verifique os dados e tente novamente.');
  }
};

const updateGrupo = async () => {
  try {
    await axios.put(`/api/v1/grupos-educacionais/${editingId.value}`, form.value);
    await fetchGrupos();
    hideForm();
  } catch (error) { 
    console.error("Erro ao atualizar grupo:", error);
    alert('Erro ao atualizar grupo. Verifique os dados e tente novamente.');
  }
};

const prepareDelete = (grupo) => {
  itemToDelete.value = grupo;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/grupos-educacionais/${itemToDelete.value.id}`);
    await fetchGrupos();
    itemToDelete.value = null;
  } catch (error) { 
    console.error("Erro ao excluir grupo:", error); 
    alert('Erro ao excluir grupo. Pode haver registros vinculados (mantenedoras, instituições).');
  }
};

onMounted(() => {
  fetchGrupos();
  
  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>

