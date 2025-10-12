<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gerir Setores do Campus: {{ campus.nome }}</h2>
      <router-link to="/admin/institucional/campi" class="btn btn-secondary">Voltar</router-link>
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="card card-glass">
          <div class="card-header">Catálogo de Setores (Institucionais e Operacionais)</div>
          <div v-if="loading.catalogo" class="card-body text-center">A carregar...</div>
          <ul v-else class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
            <li v-for="setor in setoresDisponiveis" :key="setor.id" class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
              {{ setor.nome }} ({{ setor.tipo }})
              <button @click="abrirModalVincular(setor)" class="btn btn-sm btn-primary">Vincular</button>
            </li>
             <li v-if="!setoresDisponiveis.length && !loading.catalogo" class="list-group-item text-muted bg-transparent">
              Nenhum setor disponível para vincular.
            </li>
          </ul>
        </div>
      </div>
      
      <div class="col-md-7">
        <div class="card card-glass">
          <div class="card-header">Setores Vinculados a este Campus</div>
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
                  <tr v-for="setor in setoresVinculados" :key="setor.pivot.id">
                      <td>
                        <div><strong>{{ setor.nome }}</strong></div>
                        <small class="text-muted">SAP: {{ setor.pivot.centro_custo_sap || 'N/A' }}</small>
                      </td>
                      <td>{{ setor.pivot.gestor ? setor.pivot.gestor.name : 'A definir' }}</td>
                      <td><span class="badge" :class="getStatusClass(setor.pivot.status)">{{ setor.pivot.status }}</span></td>
                      <td>
                          <button @click="abrirModalEditar(setor)" class="btn btn-sm btn-primary me-2" title="Editar Vínculo"><i class="bi bi-pencil"></i></button>
                          <button @click="desvincularSetor(setor.id)" class="btn btn-sm btn-danger" title="Desvincular Setor"><i class="bi bi-trash"></i></button>
                      </td>
                  </tr>
                  <tr v-if="!setoresVinculados.length && !loading.vinculados">
                      <td colspan="4" class="text-center text-muted">Nenhum setor vinculado a este campus.</td>
                  </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="setorModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content card-glass">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Editar Vínculo' : 'Vincular Setor' }}: {{ setorSelecionado?.nome }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="salvarVinculo">
              <div class="mb-3">
                <label for="pai_id" class="form-label">Setor Pai (Hierarquia Superior)</label>
                <select class="form-select" v-model="formVinculo.pai_id">
                  <option :value="null">-- Nenhum (Setor de Topo no Campus) --</option>                  
                  <option v-for="setorPai in setoresVinculados.filter(s => s.id !== setorSelecionado?.id)" :key="setorPai.pivot.id" :value="setorPai.pivot.id">
                    {{ setorPai.nome }}
                  </option>
                </select>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Gestor do Setor (Opcional)</label>
                  <v-select
                    label="name"
                    :options="gestorOptions"
                    @search="fetchGestores"
                    v-model="selectedGestor"
                    placeholder="Digite o nome, CPF ou matrícula..."
                  >
                    <template #option="{ name, matricula_funcional }">
                        {{ name }}<br><small class="text-muted">Matrícula: {{ matricula_funcional }}</small>
                    </template>
                  </v-select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Status do Vínculo</label>
                  <select class="form-select" v-model="formVinculo.status" required>
                    <option value="Em Implantação">Em Implantação</option>
                    <option value="Ativo">Ativo</option>
                    <option value="Em Desativação">Em Desativação</option>
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

const campusId = ref(route.params.id);
const campus = ref({});
const catalogoSetores = ref([]);
const setoresVinculados = ref([]);
const gestorOptions = ref([]);
const selectedGestor = ref(null);
const loading = ref({ catalogo: true, vinculados: true });
const isEditing = ref(false);
const setorModal = ref(null);
const setorSelecionado = ref(null);
const formVinculo = ref({});

watch(selectedGestor, (newGestor) => {
    formVinculo.value.gestor_id = newGestor ? newGestor.id : null;
});


const fetchCampus = async () => {
    try {
        const response = await axios.get(`/api/v1/campi/${campusId.value}`);
        campus.value = response.data;
    } catch (error) { console.error("Erro ao buscar dados do campus:", error); }
};

const fetchCatalogoSetores = async () => {
    try {
        loading.value.catalogo = true;
        const response = await axios.get('/api/v1/setores?tipos=Institucional,Operacional');
        catalogoSetores.value = response.data;
    } catch (error) { console.error("Erro ao buscar catálogo de setores:", error); }
    finally { loading.value.catalogo = false; }
};

const fetchSetoresVinculados = async () => {
    try {
        loading.value.vinculados = true;
        const response = await axios.get(`/api/v1/campi/${campusId.value}/setores`);
        setoresVinculados.value = response.data || [];
    } catch (error) { console.error("Erro ao buscar setores vinculados:", error); }
    finally { loading.value.vinculados = false; }
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
    if (!Array.isArray(setoresVinculados.value)) return catalogoSetores.value;
    const idsVinculados = setoresVinculados.value.map(s => s.id);
    return catalogoSetores.value.filter(s => !idsVinculados.includes(s.id));
});

const getStatusClass = (status) => {
  if (status === 'Ativo') return 'bg-success';
  if (status === 'Inativo') return 'bg-danger';
  if (status === 'Em Desativação') return 'bg-warning text-dark';
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
        pai_id: null,
    };
    selectedGestor.value = null;
    if (setorModal.value) setorModal.value.show();
};

const abrirModalEditar = (setor) => {
    isEditing.value = true;
    setorSelecionado.value = setor;
    formVinculo.value = {
        gestor_id: setor.pivot.gestor_id,
        status: setor.pivot.status,
        centro_custo_sap: setor.pivot.centro_custo_sap,
        centro_resultado_sap: setor.pivot.centro_resultado_sap,
        requer_portaria_nomeacao_gestor: setor.pivot.requer_portaria_nomeacao_gestor,
        pai_id: setor.pivot.pai_id,
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
        if(isEditing.value) {
            await axios.put(`/api/v1/campi/${campusId.value}/setores/${setorSelecionado.value.id}`, formVinculo.value);
        } else {
            await axios.post(`/api/v1/campi/${campusId.value}/setores`, formVinculo.value);
        }
        await fetchSetoresVinculados();
        if (setorModal.value) setorModal.value.hide();
    } catch (error) {
        console.error("Erro ao salvar vínculo:", error);
        alert('Ocorreu um erro ao salvar.');
    }
};

const desvincularSetor = async (setorId) => {
    if(confirm('Tem certeza que deseja desvincular este setor do campus?')){
        try {
            await axios.delete(`/api/v1/campi/${campusId.value}/setores/${setorId}`);
            await fetchSetoresVinculados();
        } catch (error) {
            console.error("Erro ao desvincular setor:", error);
            alert('Erro ao desvincular o setor.');
        }
    }
};

onMounted(() => {
    fetchCampus();
    fetchCatalogoSetores();
    fetchSetoresVinculados();
    
    const modalElement = document.getElementById('setorModal');
    if (modalElement) {
        setorModal.value = new Modal(modalElement);
    }
});
</script>