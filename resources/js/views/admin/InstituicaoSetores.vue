<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gerir Setores da Instituição: {{ instituicao.nome_fantasia }}</h2>
      <router-link to="/admin/institucional/instituicoes" class="btn btn-secondary">Voltar</router-link>
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="card card-glass">
          <div class="card-header">Catálogo de Setores (Corporativos e Institucionais)</div>
          <div v-if="loading.catalogo" class="card-body text-center">A carregar...</div>
          <ul v-else class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
            <li v-for="setor in setoresDisponiveis" :key="setor.id" class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
              {{ setor.nome }} ({{ setor.tipo }})
              <button @click="abrirModalVincular(setor)" class="btn btn-sm btn-primary">Vincular</button>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="col-md-7">
        <div class="card card-glass">
          <div class="card-header">Setores Vinculados a esta Instituição</div>
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Setor</th>
                  <th>Gestor</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                  <tr v-if="loading.vinculados"><td colspan="4" class="text-center">A carregar...</td></tr>
                  <tr v-for="vinculo in validSetoresVinculados" :key="vinculo.id">
                      <td>
                        <div><strong>{{ vinculo.setor.nome }}</strong></div>
                        <small class="text-muted">SAP: {{ vinculo.centro_custo_sap || 'N/A' }}</small>
                      </td>
                      <td>{{ vinculo.gestor ? (vinculo.gestor.usuario ? vinculo.gestor.usuario.name : vinculo.gestor.name) : 'A definir' }}</td>
                      <td><span class="badge" :class="getStatusClass(vinculo.status)">{{ vinculo.status }}</span></td>
                      <td>
                          <button @click="abrirModalEditar(vinculo)" class="btn btn-sm btn-primary me-2" title="Editar Vínculo"><i class="bi bi-pencil"></i></button>
                          <button @click="desvincularSetor(vinculo.id)" class="btn btn-sm btn-danger" title="Desvincular Setor"><i class="bi bi-trash"></i></button>
                      </td>
                  </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="setorModalInstituicao" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content card-glass">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Editar Vínculo' : 'Vincular Setor' }}: {{ setorSelecionado?.nome || setorSelecionado?.setor?.nome }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="salvarVinculo">
              <div class="mb-3">
                <label for="setor_pai_id" class="form-label">Setor Pai (Opcional)</label>
                <v-select label="nome" :options="setorPaiOptions" :reduce="option => option.id" v-model="formVinculo.setor_pai_id"></v-select>
              </div>
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
                  <label class="form-label">Status do Vínculo</label>
                  <select class="form-select" v-model="formVinculo.status" required>
                    <option value="Em Implantação">Em Implantação</option>
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                  </select>
                </div>
              </div>
               <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="centro_custo_sap" class="form-label">Centro de Custo (SAP)</label>
                  <input type="text" class="form-control" v-model="formVinculo.centro_custo_sap">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="centro_resultado_sap" class="form-label">Centro de Resultado (SAP)</label>
                  <input type="text" class="form-control" v-model="formVinculo.centro_resultado_sap">
                </div>
              </div>
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="requer_portaria" v-model="formVinculo.requer_portaria_nomeacao_gestor">
                <label class="form-check-label" for="requer_portaria">Nomeação do gestor requer portaria oficial</label>
              </div>
              <div class="modal-footer border-0">
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

const instituicaoId = ref(route.params.id);
const instituicao = ref({});
const catalogoSetores = ref([]);
const setoresVinculados = ref([]);
const mantenedoraSetores = ref([]);
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

const validSetoresVinculados = computed(() => {
  return setoresVinculados.value.filter(vinculo => vinculo.setor);
});

const fetchInstituicao = async () => {
    try {
        const response = await axios.get(`/api/v1/instituicoes/${instituicaoId.value}?include=mantenedora.grupoEducacional`);
        instituicao.value = response.data;
        if (instituicao.value.mantenedora) {
            fetchMantenedoraSetores(instituicao.value.mantenedora_id);
            if (instituicao.value.mantenedora.grupo_educacional_id) {
                fetchGrupoEducacionalSetores(instituicao.value.mantenedora.grupo_educacional_id);
            }
        }
    } catch (error) { console.error("Erro ao buscar dados da instituição:", error); }
};

const fetchCatalogoSetores = async () => {
    try {
        loading.value.catalogo = true;
        const response = await axios.get('/api/v1/setores?tipos=Corporativo,Institucional');
        catalogoSetores.value = response.data;
    } catch (error) { console.error("Erro ao buscar catálogo de setores:", error); }
    finally { loading.value.catalogo = false; }
};

const fetchSetoresVinculados = async () => {
    try {
        loading.value.vinculados = true;
        const response = await axios.get(`/api/v1/instituicoes/${instituicaoId.value}/setores`);
        setoresVinculados.value = response.data;
    } catch (error) { console.error("Erro ao buscar setores vinculados:", error); }
    finally { loading.value.vinculados = false; }
};

const fetchMantenedoraSetores = async (mantenedoraId) => {
    try {
        const response = await axios.get(`/api/v1/mantenedoras/${mantenedoraId}/setores`);
        mantenedoraSetores.value = response.data || [];
    } catch (error) {
        console.error("Erro ao buscar setores da mantenedora:", error);
    }
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

const setoresDisponiveis = computed(() => {
    if (!Array.isArray(setoresVinculados.value)) return catalogoSetores.value;
    const idsVinculados = setoresVinculados.value.map(v => v.setor_id);
    return catalogoSetores.value.filter(s => !idsVinculados.includes(s.id));
});

const setorPaiOptions = computed(() => {
    const instituicaoSetoresFormatado = validSetoresVinculados.value
        .map(s => ({
            id: s.id,
            nome: `${s.setor.nome} (Instituição)`
        }));

    const mantenedoraSetoresFormatado = mantenedoraSetores.value
        .map(s => ({
            id: s.pivot.id,
            nome: `${s.nome} (Mantenedora)`
        }));

    const grupoSetoresFormatado = grupoEducacionalSetores.value
        .map(s => ({
            id: s.pivot.id,
            nome: `${s.nome} (Grupo)`
        }));

    const allSetores = [...instituicaoSetoresFormatado, ...mantenedoraSetoresFormatado, ...grupoSetoresFormatado];
    
    if (isEditing.value && setorSelecionado.value) {
        return allSetores.filter(s => s.id !== setorSelecionado.value.id);
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
        gestor_id: null,
        status: 'Em Implantação',
        centro_custo_sap: '',
        centro_resultado_sap: '',
        requer_portaria_nomeacao_gestor: false,
        setor_pai_id: null,
    };
    selectedGestor.value = null;
    gestorOptions.value = [];
    if (setorModal.value) setorModal.value.show();
};

const abrirModalEditar = (vinculo) => {
    isEditing.value = true;
    setorSelecionado.value = vinculo;
    formVinculo.value = {
        gestor_id: vinculo.gestor_id,
        status: vinculo.status,
        centro_custo_sap: vinculo.centro_custo_sap,
        centro_resultado_sap: vinculo.centro_resultado_sap,
        requer_portaria_nomeacao_gestor: vinculo.requer_portaria_nomeacao_gestor,
        setor_pai_id: vinculo.setor_pai_id,
    };
    if (vinculo.gestor) {
        selectedGestor.value = vinculo.gestor;
        gestorOptions.value = [vinculo.gestor];
    } else {
        selectedGestor.value = null;
        gestorOptions.value = [];
    }
    if (setorModal.value) setorModal.value.show();
};

const salvarVinculo = async () => {
    try {
        const url = `/api/v1/instituicoes/${instituicaoId.value}/setores`;
        
        if (isEditing.value) {
            await axios.put(`${url}/${setorSelecionado.value.id}`, formVinculo.value);
        } else {
            await axios.post(url, formVinculo.value);
        }

        await fetchSetoresVinculados();
        if (setorModal.value) setorModal.value.hide();
    } catch (error) {
        console.error("Erro ao salvar vínculo:", error);
        alert('Ocorreu um erro ao salvar.');
    }
};

const desvincularSetor = async (setorId) => {
    if(confirm('Tem certeza que deseja desvincular este setor da instituição?')){
        try {
            await axios.delete(`/api/v1/instituicoes/${instituicaoId.value}/setores/${setorId}`);
            await fetchSetoresVinculados();
        } catch (error) {
            console.error("Erro ao desvincular setor:", error);
            alert('Erro ao desvincular o setor.');
        }
    }
};

onMounted(() => {
    fetchInstituicao();
    fetchCatalogoSetores();
    fetchSetoresVinculados();
    
    const modalElement = document.getElementById('setorModalInstituicao');
    if (modalElement) {
        setorModal.value = new Modal(modalElement);
    }
});
</script>