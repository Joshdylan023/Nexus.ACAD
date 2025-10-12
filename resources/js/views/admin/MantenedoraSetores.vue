<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gerir Setores da Mantenedora: {{ mantenedora.nome_fantasia }}</h2>
      <router-link to="/admin/institucional/mantenedoras" class="btn btn-secondary">Voltar</router-link>
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="card card-glass">
          <div class="card-header">Catálogo de Setores (Corporativos)</div>
          <div v-if="loading.catalogo" class="text-center p-3">A carregar catálogo...</div>
          <ul v-else class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
            <li v-for="setor in setoresDisponiveis" :key="setor.id" class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
              {{ setor.nome }}
              <button @click="abrirModalVincular(setor)" class="btn btn-sm btn-primary">Vincular</button>
            </li>
            <li v-if="!setoresDisponiveis.length && !loading.catalogo" class="list-group-item text-muted bg-transparent">
              Nenhum setor corporativo disponível para vincular.
            </li>
          </ul>
        </div>
      </div>
      
      <div class="col-md-7">
        <div class="card card-glass">
          <div class="card-header">Setores Vinculados a esta Mantenedora</div>
          <table class="table table-hover table-glass mb-0">
            <thead>
              <tr>
                <th>Setor</th>
                <th>Gestor</th>
                <th>C. Custo SAP</th>
                <th>C. Resultado SAP</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
                <tr v-if="loading.vinculados"><td colspan="6" class="text-center">A carregar...</td></tr>
                <tr v-for="setor in setoresVinculados" :key="setor.pivot.id">
                    <td>{{ setor.nome }}</td>
                    <td>{{ setor.pivot.gestor ? setor.pivot.gestor.name : 'A definir' }}</td>
                    <td>{{ setor.pivot.centro_custo_sap }}</td>
                    <td>{{ setor.pivot.centro_resultado_sap }}</td>
                    <td><span class="badge" :class="getStatusClass(setor.pivot.status)">{{ setor.pivot.status }}</span></td>
                    <td>
                        <button @click="abrirModalEditar(setor)" class="btn btn-sm btn-primary me-2" title="Editar Vínculo"><i class="bi bi-pencil"></i></button>
                        <button @click="desvincularSetor(setor.id)" class="btn btn-sm btn-danger" title="Desvincular Setor"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                 <tr v-if="!setoresVinculados.length && !loading.vinculados">
                    <td colspan="6" class="text-center text-muted">Nenhum setor vinculado a esta mantenedora.</td>
                </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade" id="setorModalMantenedora" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content card-glass">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Editar Vínculo' : 'Vincular Setor' }}: {{ setorSelecionado?.nome }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="salvarVinculo">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Gestor do Setor (Opcional)</label>
                  <v-select
                    :get-option-label="option => option.usuario ? option.usuario.name : option.name"
                    :options="gestorOptions"
                    @search="fetchGestores"
                    v-model="selectedGestor"
                    placeholder="Digite o nome, CPF ou matrícula..."
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
                <div class="col-md-6 mb-3">
                  <label class="form-label">Setor Pai (Opcional)</label>
                  <v-select label="nome" :options="setorPaiOptions" :reduce="option => option.id" v-model="formVinculo.pai_id"></v-select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Status do Vínculo</label>
                  <select class="form-select" v-model="formVinculo.status" required>
                    <option value="Em Implantação">Em Implantação</option>
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                    <option value="Suspenso">Suspenso</option>
                    <option value="Concluído">Concluído</option>
                    <option value="Cancelado">Cancelado</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Centro de Custo SAP</label>
                  <input type="text" class="form-control" v-model="formVinculo.centro_custo_sap">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Centro de Resultado SAP</label>
                  <input type="text" class="form-control" v-model="formVinculo.centro_resultado_sap">
                </div>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" v-model="formVinculo.requer_portaria_nomeacao_gestor" id="requerPortaria">
                <label class="form-check-label" for="requerPortaria">
                  Requer Portaria de Nomeação do Gestor
                </label>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { Modal } from 'bootstrap';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const route = useRoute();
const router = useRouter();

const mantenedoraId = ref(route.params.id);
const mantenedora = ref({});
const catalogoSetores = ref([]);
const setoresVinculados = ref([]);
const grupoEducacionalSetores = ref([]);
const gestorOptions = ref([]);
const selectedGestor = ref(null);
const loading = ref({ catalogo: true, vinculados: true });
const isEditing = ref(false);
const setorModal = ref(null);
const setorSelecionado = ref(null);
const formVinculo = ref({});

watch(selectedGestor, (newGestor) => {
    formVinculo.value.gestor_id = newGestor ? (newGestor.user_id || newGestor.id) : null;
});

const fetchMantenedora = async () => {
    try {
        const response = await axios.get(`/api/v1/mantenedoras/${mantenedoraId.value}`);
        mantenedora.value = response.data;
        if (mantenedora.value.grupo_educacional_id) {
            fetchGrupoEducacionalSetores(mantenedora.value.grupo_educacional_id);
        }
    } catch (error) { console.error("Erro ao buscar dados da mantenedora:", error); }
};

const fetchCatalogoSetores = async () => {
    try {
        loading.value.catalogo = true;
        const response = await axios.get('/api/v1/setores?tipos=Corporativo');
        catalogoSetores.value = response.data;
    } catch (error) { console.error("Erro ao buscar catálogo de setores:", error); }
    finally { loading.value.catalogo = false; }
};

const fetchSetoresVinculados = async () => {
    try {
        loading.value.vinculados = true;
        const response = await axios.get(`/api/v1/mantenedoras/${mantenedoraId.value}/setores`);
        setoresVinculados.value = response.data || [];
    } catch (error) { console.error("Erro ao buscar setores vinculados:", error); }
    finally { loading.value.vinculados = false; }
};

const fetchGrupoEducacionalSetores = async (grupoId) => {
    try {
        const response = await axios.get(`/api/v1/grupos-educacionais/${grupoId}/setores`);
        grupoEducacionalSetores.value = response.data || [];
    } catch (error) {
        console.error("Erro ao buscar setores do grupo educacional:", error);
    }
};

const fetchGestores = (search, loading) => {
  if (search && search.length) {
    loading(true);
    axios.get(`/api/v1/colaboradores?search=${search}`)
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

const setoresDisponiveis = computed(() => {
    const idsVinculados = setoresVinculados.value.map(s => s.id);
    return catalogoSetores.value.filter(s => !idsVinculados.includes(s.id));
});

const setorPaiOptions = computed(() => {
    const mantenedoraSetores = setoresVinculados.value
        .map(s => ({
            id: s.pivot.id,
            nome: `${s.nome} (Mantenedora)`
        }));

    const grupoSetores = grupoEducacionalSetores.value
        .map(s => ({
            id: s.pivot.id,
            nome: `${s.nome} (Grupo)`
        }));

    const allSetores = [...mantenedoraSetores, ...grupoSetores];

    if (!setorSelecionado.value) {
        return allSetores;
    }

    if (setorSelecionado.value.pivot) {
        return allSetores.filter(s => s.id !== setorSelecionado.value.pivot.id);
    }

    return allSetores;
});

const getStatusClass = (status) => {
  if (status === 'Ativo') return 'bg-success';
  if (status === 'Inativo') return 'bg-danger';
  return 'bg-secondary';
};

const abrirModalVincular = (setor) => {
    isEditing.value = false;
    setorSelecionado.value = setor;
    formVinculo.value = {
        setor_id: setor.id,
        pai_id: null,
        gestor_id: null,
        status: 'Ativo',
        centro_custo_sap: '',
        centro_resultado_sap: '',
        requer_portaria_nomeacao_gestor: false
    };
    selectedGestor.value = null;
    gestorOptions.value = [];
    if (setorModal.value) setorModal.value.show();
};

const abrirModalEditar = (setor) => {
    isEditing.value = true;
    setorSelecionado.value = setor;
    formVinculo.value = {
        pai_id: setor.pivot.pai_id,
        gestor_id: setor.pivot.gestor_id,
        status: setor.pivot.status,
        centro_custo_sap: setor.pivot.centro_custo_sap,
        centro_resultado_sap: setor.pivot.centro_resultado_sap,
        requer_portaria_nomeacao_gestor: setor.pivot.requer_portaria_nomeacao_gestor
    };
    if (setor.pivot.gestor) {
        selectedGestor.value = {
            id: setor.pivot.gestor.id,
            name: setor.pivot.gestor.name,
            matricula_funcional: setor.pivot.gestor.matricula_funcional
        };
        gestorOptions.value = [selectedGestor.value];
    } else {
        selectedGestor.value = null;
        gestorOptions.value = [];
    }
    if (setorModal.value) setorModal.value.show();
};

const salvarVinculo = async () => {
    try {
        const url = `/api/v1/mantenedoras/${mantenedoraId.value}/setores`;
        const payload = isEditing.value 
            ? { ...formVinculo.value } 
            : { setor_id: setorSelecionado.value.id, ...formVinculo.value };
        
        if (isEditing.value) {
            await axios.put(`${url}/${setorSelecionado.value.id}`, payload);
        } else {
            await axios.post(url, payload);
        }

        await fetchSetoresVinculados();
        if (setorModal.value) setorModal.value.hide();
    } catch (error) {
        console.error("Erro ao salvar vínculo:", error);
        alert('Ocorreu um erro ao salvar.');
    }
};

const desvincularSetor = async (setorId) => {
    if(confirm('Tem certeza que deseja desvincular este setor da mantenedora?')){
        try {
            await axios.delete(`/api/v1/mantenedoras/${mantenedoraId.value}/setores/${setorId}`);
            await fetchSetoresVinculados();
        } catch (error) {
            console.error("Erro ao desvincular setor:", error);
            alert('Erro ao desvincular o setor.');
        }
    }
};

onMounted(() => {
    fetchMantenedora();
    fetchCatalogoSetores();
    fetchSetoresVinculados();
    
    const modalElement = document.getElementById('setorModalMantenedora');
    if (modalElement) {
        setorModal.value = new Modal(modalElement);
    }
});
</script>