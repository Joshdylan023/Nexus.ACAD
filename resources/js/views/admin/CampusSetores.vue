<template>
  <div>
    <PageHeader 
      :title="`Setores - ${campus.nome || 'Carregando...'}`"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional', path: '/admin/institucional' },
        { label: 'Campi', path: '/admin/institucional/campi' },
        { label: campus.nome || 'Setores' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar setor..."
      v-model="buscaRapida"
    >
      <template #actions>
        <router-link to="/admin/institucional/campi" class="btn btn-secondary me-2">
          <i class="bi bi-arrow-left"></i> Voltar
        </router-link>
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Adicionar Setor
        </button>
      </template>
    </PageHeader>

    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Vínculo de Setor' : 'Adicionar Novo Setor' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateSetor() : createSetor()">
          <div class="mb-3">
            <label class="form-label">Setor</label>
            <select class="form-select" v-model="form.setor_id" required :disabled="isEditing">
              <option :value="null">-- Selecione --</option>
              <option v-for="setor in setoresDisponiveis" :key="setor.id" :value="setor.id">
                {{ setor.nome }} ({{ setor.tipo }})
              </option>
            </select>
            <small class="text-white-50">Setores do tipo "Corporativo", "Institucional" ou "Operacional" podem ser vinculados</small>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Setor Pai (Opcional)</label>
              <select class="form-select" v-model="form.setor_pai_id">
                <option :value="null">-- Nenhum (Setor Raiz) --</option>
                <option v-for="setor in setorPaiOptions" :key="setor.id" :value="setor.id">
                  {{ setor.nome }}
                </option>
              </select>
              <small class="text-white-50">Setores do grupo, mantenedora, instituição ou deste campus</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Gestor do Setor (Opcional)</label>
              <v-select
                :get-option-label="option => option.usuario ? option.usuario.name : option.name"
                :options="gestorOptions"
                @search="fetchGestores"
                v-model="selectedGestor"
                placeholder="Digite o nome ou matrícula..."
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
                <option value="Em Implantação">Em Implantação</option>
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Centro de Custo SAP (Opcional)</label>
              <input type="text" class="form-control" v-model="form.centro_custo_sap">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Centro de Resultado SAP (Opcional)</label>
              <input type="text" class="form-control" v-model="form.centro_resultado_sap">
            </div>
          </div>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" v-model="form.requer_portaria_nomeacao_gestor" id="requerPortaria">
              <label class="form-check-label" for="requerPortaria">
                Requer Portaria de Nomeação do Gestor
              </label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <div class="card card-glass">
      <div class="card-header"><h4>Setores Vinculados</h4></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nome do Setor</th>
              <th>Tipo</th>
              <th>Gestor</th>
              <th>C. Custo SAP</th>
              <th>Status</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <TableSkeleton v-if="loading.vinculados" :columns="6" :rows="5" />
          <tbody v-else>
            <tr v-for="vinculo in setoresFiltrados" :key="vinculo.id">
              <td class="ps-4">{{ vinculo.setor?.nome || 'N/A' }}</td>
              <td><StatusBadge v-if="vinculo.setor" :status="vinculo.setor.tipo" type="tipo" /></td>
              <td>{{ vinculo.gestor ? (vinculo.gestor.usuario ? vinculo.gestor.usuario.name : vinculo.gestor.name) : 'Não definido' }}</td>
              <td>{{ vinculo.centro_custo_sap || 'N/A' }}</td>
              <td><StatusBadge :status="vinculo.status" /></td>
              <td class="text-center">
                <button @click="showEditForm(vinculo)" class="btn btn-sm btn-primary me-2" title="Editar Vínculo">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="prepareDelete(vinculo)" class="btn btn-sm btn-danger" title="Remover Vínculo">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="setoresFiltrados.length === 0">
              <td colspan="6" class="text-center text-muted py-4">
                {{ buscaRapida ? 'Nenhum resultado encontrado para sua busca.' : 'Nenhum setor vinculado.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Remoção"
      :message="`Tem certeza que deseja remover o vínculo do setor ${itemToDelete?.setor?.nome}?`"
      confirm-text="Remover"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { Modal } from 'bootstrap';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const route = useRoute();
const campusId = ref(route.params.id);
const campus = ref({});
const instituicaoId = ref(null);
const mantenedoraId = ref(null);
const grupoEducacionalId = ref(null);
const catalogoSetores = ref([]);
const setoresVinculados = ref([]);
const setoresInstituicao = ref([]);
const setoresMantenedora = ref([]);
const setoresGrupo = ref([]);
const gestorOptions = ref([]);
const selectedGestor = ref(null);
const loading = ref({ catalogo: true, vinculados: true });
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
let confirmModalInstance = null;

const validSetoresVinculados = computed(() => {
  return setoresVinculados.value.filter(vinculo => vinculo.setor);
});

const setoresFiltrados = computed(() => {
  if (!buscaRapida.value) return validSetoresVinculados.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return validSetoresVinculados.value.filter(vinculo => 
    vinculo.setor?.nome.toLowerCase().includes(termo) ||
    vinculo.setor?.tipo.toLowerCase().includes(termo)
  );
});

const setoresDisponiveis = computed(() => {
  if (!Array.isArray(setoresVinculados.value)) return catalogoSetores.value;
  const idsVinculados = setoresVinculados.value.map(v => v.setor_id);
  return catalogoSetores.value.filter(s => !idsVinculados.includes(s.id));
});

const setorPaiOptions = computed(() => {
  const grupoSetoresFormatado = setoresGrupo.value.map(s => ({
    id: s.pivot.id,
    nome: `${s.nome} (Grupo)`
  }));

  const mantenedoraSetoresFormatado = setoresMantenedora.value.map(s => ({
    id: s.pivot.id,
    nome: `${s.nome} (Mantenedora)`
  }));

  const instituicaoSetoresFormatado = setoresInstituicao.value.map(s => ({
    id: s.id,
    nome: `${s.setor.nome} (Instituição)`
  }));

  const campusSetoresFormatado = validSetoresVinculados.value
    .filter(v => !isEditing.value || v.id !== editingId.value)
    .map(v => ({
      id: v.id,
      nome: `${v.setor.nome} (Campus)`
    }));

  return [...grupoSetoresFormatado, ...mantenedoraSetoresFormatado, ...instituicaoSetoresFormatado, ...campusSetoresFormatado];
});

watch(selectedGestor, (newGestor) => {
  form.value.gestor_id = newGestor ? (newGestor.user_id || newGestor.id) : null;
});

const resetForm = () => {
  form.value = {
    setor_id: null,
    setor_pai_id: null,
    gestor_id: null,
    status: 'Em Implantação',
    centro_custo_sap: '',
    centro_resultado_sap: '',
    requer_portaria_nomeacao_gestor: false
  };
  selectedGestor.value = null;
  gestorOptions.value = [];
};

const fetchCampus = async () => {
  try {
    const response = await axios.get(`/api/v1/campi/${campusId.value}`);
    campus.value = response.data;
    instituicaoId.value = response.data.instituicao_id;

    if (instituicaoId.value) {
      await fetchSetoresInstituicao();
      const instituicaoResponse = await axios.get(`/api/v1/instituicoes/${instituicaoId.value}`);
      mantenedoraId.value = instituicaoResponse.data.mantenedora_id;

      if (mantenedoraId.value) {
        await fetchSetoresMantenedora();
        const mantenedoraResponse = await axios.get(`/api/v1/mantenedoras/${mantenedoraId.value}`);
        grupoEducacionalId.value = mantenedoraResponse.data.grupo_educacional_id;

        if (grupoEducacionalId.value) {
          await fetchSetoresGrupo();
        }
      }
    }
  } catch (error) {
    console.error("Erro ao buscar campus:", error);
  }
};

const fetchCatalogoSetores = async () => {
  try {
    loading.value.catalogo = true;
    const response = await axios.get('/api/v1/setores?tipos=Corporativo,Institucional,Operacional');
    catalogoSetores.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar catálogo de setores:", error);
  } finally {
    loading.value.catalogo = false;
  }
};

const fetchSetoresVinculados = async () => {
  try {
    loading.value.vinculados = true;
    const response = await axios.get(`/api/v1/campi/${campusId.value}/setores`);
    console.log('📦 Setores do Campus:', response.data); // ← DEBUG
    console.log('📊 Tipo de dado:', typeof response.data);
    console.log('📊 É Array?:', Array.isArray(response.data));
    if (Array.isArray(response.data) && response.data.length > 0) {
      console.log('📊 Primeiro item:', response.data[0]);
    }
    setoresVinculados.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar setores vinculados:", error);
  } finally {
    loading.value.vinculados = false;
  }
};

const fetchSetoresInstituicao = async () => {
  try {
    const response = await axios.get(`/api/v1/instituicoes/${instituicaoId.value}/setores`);
    setoresInstituicao.value = response.data || [];
  } catch (error) {
    console.error("Erro ao buscar setores da instituição:", error);
  }
};

const fetchSetoresMantenedora = async () => {
  try {
    const response = await axios.get(`/api/v1/mantenedoras/${mantenedoraId.value}/setores`);
    setoresMantenedora.value = response.data || [];
  } catch (error) {
    console.error("Erro ao buscar setores da mantenedora:", error);
  }
};

const fetchSetoresGrupo = async () => {
  try {
    const response = await axios.get(`/api/v1/grupos-educacionais/${grupoEducacionalId.value}/setores`);
    setoresGrupo.value = response.data || [];
  } catch (error) {
    console.error("Erro ao buscar setores do grupo:", error);
  }
};

const fetchGestores = (search, loading) => {
  if (search && search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}&include=usuario`)
      .then(response => {
        gestorOptions.value = response.data;
      })
      .catch(error => {
        console.error("Erro ao buscar colaboradores:", error);
        gestorOptions.value = [];
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

const showEditForm = (vinculo) => {
  isEditing.value = true;
  editingId.value = vinculo.id;
  form.value = {
    setor_id: vinculo.setor_id,
    setor_pai_id: vinculo.setor_pai_id,
    gestor_id: vinculo.gestor_id,
    status: vinculo.status,
    centro_custo_sap: vinculo.centro_custo_sap || '',
    centro_resultado_sap: vinculo.centro_resultado_sap || '',
    requer_portaria_nomeacao_gestor: vinculo.requer_portaria_nomeacao_gestor || false
  };

  if (vinculo.gestor) {
    selectedGestor.value = vinculo.gestor;
    gestorOptions.value = [vinculo.gestor];
  }

  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createSetor = async () => {
  try {
    await axios.post(`/api/v1/campi/${campusId.value}/setores`, form.value);
    await fetchSetoresVinculados();
    hideForm();
  } catch (error) {
    console.error('Erro ao vincular setor:', error);
    alert('Erro ao vincular setor. Verifique os dados.');
  }
};

const updateSetor = async () => {
  try {
    await axios.put(`/api/v1/campi/${campusId.value}/setores/${editingId.value}`, form.value);
    await fetchSetoresVinculados();
    hideForm();
  } catch (error) {
    console.error('Erro ao atualizar vínculo:', error);
    alert('Erro ao atualizar vínculo. Verifique os dados.');
  }
};

const prepareDelete = (vinculo) => {
  itemToDelete.value = vinculo;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/campi/${campusId.value}/setores/${itemToDelete.value.id}`);
    await fetchSetoresVinculados();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao remover vínculo:', error);
    alert('Erro ao remover vínculo do setor.');
  }
};

onMounted(() => {
  fetchCampus();
  fetchCatalogoSetores();
  fetchSetoresVinculados();

  const confirmModalEl = document.getElementById('confirmDeleteModal');
  if (confirmModalEl) {
    confirmModalInstance = new Modal(confirmModalEl);
  }
});
</script>
