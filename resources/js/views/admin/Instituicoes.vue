<template>
  <div>
    <PageHeader 
      title="Gestão de Instituições de Ensino Superior (IES)"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Instituições' }
      ]"
      :show-search="!showForm"
      search-placeholder="Buscar por razão social, nome fantasia ou CNPJ..."
      v-model="buscaRapida"
    >
      <template #actions>
        <ViewToggle 
          v-if="!showForm"
          v-model="viewMode"
          class="me-2"
        />
        <ExportButton 
          v-if="!showForm"
          :data="instituicoesFiltradas"
          :columns="exportColumns"
          fileName="instituicoes"
          class="me-2"
        />
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Nova Instituição
        </button>
      </template>
    </PageHeader>
    
    <!-- Formulário -->
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">
        {{ isEditing ? 'Editar Instituição' : 'Adicionar Nova Instituição' }}
      </div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateInstituicao() : createInstituicao()">
          <!-- Dados Básicos -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Razão Social *</label>
              <input type="text" class="form-control" v-model="form.razao_social" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nome Fantasia *</label>
              <input type="text" class="form-control" v-model="form.nome_fantasia" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">CNPJ *</label>
              <input type="text" class="form-control" v-model="form.cnpj" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Sigla</label>
              <input type="text" class="form-control" v-model="form.sigla">
            </div>
            <div class="col-md-4">
              <label class="form-label">Código MEC</label>
              <input type="text" class="form-control" v-model="form.codigo_mec">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Mantenedora *</label>
              <select class="form-select" v-model="form.mantenedora_id" required>
                <option value="">Selecione...</option>
                <option v-for="m in mantenedoras" :key="m.id" :value="m.id">
                  {{ m.razao_social }}
                </option>
              </select>
            </div>
            <div class="col-md-6">
              <UserAutocomplete
                label="Reitor/Diretor"
                placeholder="Digite o nome ou matrícula..."
                v-model="form.reitor_id"
                :initial-user="selectedReitor"
              />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tipo de Organização Acadêmica</label>
              <select class="form-select" v-model="form.tipo_organizacao_academica">
                <option value="">Selecione...</option>
                <option value="Universidade">Universidade</option>
                <option value="Centro Universitário">Centro Universitário</option>
                <option value="Faculdade">Faculdade</option>
                <option value="Instituto Federal">Instituto Federal</option>
                <option value="CEFET">CEFET</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Categoria Administrativa</label>
              <select class="form-select" v-model="form.categoria_administrativa">
                <option value="">Selecione...</option>
                <option value="Pública Federal">Pública Federal</option>
                <option value="Pública Estadual">Pública Estadual</option>
                <option value="Pública Municipal">Pública Municipal</option>
                <option value="Privada com fins lucrativos">Privada com fins lucrativos</option>
                <option value="Privada sem fins lucrativos">Privada sem fins lucrativos</option>
                <option value="Privada - Particular">Privada - Particular</option>
                <option value="Privada - Comunitária">Privada - Comunitária</option>
                <option value="Privada - Confessional">Privada - Confessional</option>
                <option value="Privada - Filantrópica">Privada - Filantrópica</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" v-model="form.status">
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
                <option value="Em Extinção">Em Extinção</option>
              </select>
            </div>
          </div>

          <!-- Endereço -->
          <hr class="my-4">
          <h6 class="mb-3">Endereço da Sede</h6>

          <div class="row mb-3">
            <div class="col-md-8">
              <label class="form-label">Logradouro</label>
              <input type="text" class="form-control" v-model="form.logradouro" placeholder="Rua, Avenida, etc.">
            </div>
            <div class="col-md-4">
              <label class="form-label">Número</label>
              <input type="text" class="form-control" v-model="form.numero">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Complemento</label>
              <input type="text" class="form-control" v-model="form.complemento" placeholder="Sala, Bloco, etc.">
            </div>
            <div class="col-md-4">
              <label class="form-label">Bairro</label>
              <input type="text" class="form-control" v-model="form.bairro">
            </div>
            <div class="col-md-4">
              <label class="form-label">CEP</label>
              <input type="text" class="form-control" v-model="form.cep" placeholder="00000-000">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-8">
              <label class="form-label">Cidade</label>
              <input type="text" class="form-control" v-model="form.cidade">
            </div>
            <div class="col-md-4">
              <label class="form-label">Estado (UF)</label>
              <select class="form-select" v-model="form.estado">
                <option value="">Selecione...</option>
                <option value="AC">AC</option>
                <option value="AL">AL</option>
                <option value="AP">AP</option>
                <option value="AM">AM</option>
                <option value="BA">BA</option>
                <option value="CE">CE</option>
                <option value="DF">DF</option>
                <option value="ES">ES</option>
                <option value="GO">GO</option>
                <option value="MA">MA</option>
                <option value="MT">MT</option>
                <option value="MS">MS</option>
                <option value="MG">MG</option>
                <option value="PA">PA</option>
                <option value="PB">PB</option>
                <option value="PR">PR</option>
                <option value="PE">PE</option>
                <option value="PI">PI</option>
                <option value="RJ">RJ</option>
                <option value="RN">RN</option>
                <option value="RS">RS</option>
                <option value="RO">RO</option>
                <option value="RR">RR</option>
                <option value="SC">SC</option>
                <option value="SP">SP</option>
                <option value="SE">SE</option>
                <option value="TO">TO</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">Cancelar</button>
        </form>
      </div>
    </div>

    <!-- Visualização em Tabela -->
    <div v-if="viewMode === 'table' && !showForm" class="card card-glass">
      <div class="card-header"><h4>Instituições Cadastradas</h4></div>
      <div class="card-body p-0">
        <TableSkeleton v-if="loading" :columns="7" :rows="5" />
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4">Razão Social</th>
                <th>Nome Fantasia</th>
                <th>CNPJ</th>
                <th>Tipo</th>
                <th>Cidade/UF</th>
                <th>Status</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="instituicao in instituicoesPaginadas" :key="instituicao.id">
                <td class="ps-4">{{ instituicao.razao_social }}</td>
                <td>{{ instituicao.nome_fantasia }}</td>
                <td>{{ instituicao.cnpj }}</td>
                <td><span class="badge bg-info">{{ instituicao.tipo_organizacao_academica || 'N/A' }}</span></td>
                <td>{{ formatLocation(instituicao) }}</td>
                <td><StatusBadge :status="instituicao.status" /></td>
                <td class="text-center">
                  <button @click="showEditForm(instituicao)" class="btn btn-sm btn-primary me-2" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button @click="prepareDelete(instituicao)" class="btn btn-sm btn-danger me-2" title="Excluir">
                    <i class="bi bi-trash"></i>
                  </button>
                  <router-link :to="`/admin/institucional/instituicoes/${instituicao.id}/atos-regulatorios`" class="btn btn-sm btn-info me-2" title="Atos">
                    <i class="bi bi-file-earmark-text"></i>
                  </router-link>
                  <router-link :to="`/admin/institucional/instituicoes/${instituicao.id}/setores`" class="btn btn-sm btn-secondary" title="Setores">
                    <i class="bi bi-diagram-3"></i>
                  </router-link>
                </td>
              </tr>
              <tr v-if="instituicoesFiltradas.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  {{ buscaRapida ? 'Nenhum resultado encontrado.' : 'Nenhuma instituição encontrada.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination
          :current-page="currentPage"
          :total-items="instituicoesFiltradas.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <!-- Visualização em Cards -->
    <div v-if="viewMode === 'cards' && !showForm">
      <TableSkeleton v-if="loading" :columns="3" :rows="3" />
      <CardView 
        v-else
        :items="instituicoesPaginadas"
        empty-message="Nenhuma instituição encontrada"
      >
        <template #header="{ item }">
          <div>
            <h5 class="mb-1" style="color: white; font-size: 1.125rem; font-weight: 600;">{{ item.razao_social }}</h5>
            <div class="d-flex gap-2">
              <span class="badge bg-success">{{ item.nome_fantasia }}</span>
              <span class="badge bg-info">{{ item.tipo_organizacao_academica || 'N/A' }}</span>
            </div>
          </div>
        </template>

        <template #body="{ item }">
          <div class="info-group">
            <div class="info-item">
              <i class="bi bi-building me-2"></i>
              <span class="label">CNPJ:</span>
              <span class="value">{{ item.cnpj }}</span>
            </div>
            <div class="info-item">
              <i class="bi bi-diagram-3 me-2"></i>
              <span class="label">Mantenedora:</span>
              <span class="value">{{ item.mantenedora?.razao_social || 'N/A' }}</span>
            </div>
            <div class="info-item">
              <i class="bi bi-geo-alt me-2"></i>
              <span class="label">Localização:</span>
              <span class="value">{{ formatLocation(item) }}</span>
            </div>
            <div class="info-item">
              <i class="bi bi-toggle-on me-2"></i>
              <span class="label">Status:</span>
              <StatusBadge :status="item.status" />
            </div>
          </div>
        </template>

        <template #footer="{ item }">
          <div class="action-buttons">
            <button @click="showEditForm(item)" class="btn btn-sm btn-primary" title="Editar">
              <i class="bi bi-pencil"></i>
            </button>
            <button @click="prepareDelete(item)" class="btn btn-sm btn-danger" title="Excluir">
              <i class="bi bi-trash"></i>
            </button>
            <router-link :to="`/admin/institucional/instituicoes/${item.id}/atos-regulatorios`" class="btn btn-sm btn-info" title="Atos">
              <i class="bi bi-file-earmark-text"></i>
            </router-link>
            <router-link :to="`/admin/institucional/instituicoes/${item.id}/setores`" class="btn btn-sm btn-secondary" title="Setores">
              <i class="bi bi-diagram-3"></i>
            </router-link>
          </div>
        </template>
      </CardView>

      <div class="mt-3">
        <Pagination
          :current-page="currentPage"
          :total-items="instituicoesFiltradas.length"
          :per-page="perPage"
          @page-changed="changePage"
          @per-page-changed="changePerPage"
        />
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir a instituição ${itemToDelete?.razao_social}?`"
      confirm-text="Excluir"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportButton from '@/components/ExportButton.vue';
import Pagination from '@/components/Pagination.vue';
import CardView from '@/components/CardView.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import UserAutocomplete from '@/components/UserAutocomplete.vue';
import { usePagination } from '@/composables/usePagination';

const instituicoes = ref([]);
const mantenedoras = ref([]);
const loading = ref(true);
const form = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
const viewMode = ref('table');
const selectedReitor = ref(null);
let confirmModalInstance = null;

const { currentPage, perPage, paginateItems, changePage, changePerPage } = usePagination(25);

const instituicoesFiltradas = computed(() => {
  if (!buscaRapida.value) return instituicoes.value;
  
  const termo = buscaRapida.value.toLowerCase();
  return instituicoes.value.filter(instituicao => 
    instituicao.razao_social?.toLowerCase().includes(termo) ||
    instituicao.nome_fantasia?.toLowerCase().includes(termo) ||
    instituicao.cnpj?.includes(termo)
  );
});

const instituicoesPaginadas = computed(() => {
  return paginateItems(instituicoesFiltradas.value);
});

watch(buscaRapida, () => {
  currentPage.value = 1;
});

const exportColumns = [
  { key: 'razao_social', label: 'Razão Social' },
  { key: 'nome_fantasia', label: 'Nome Fantasia' },
  { key: 'cnpj', label: 'CNPJ' },
  { key: 'tipo_organizacao_academica', label: 'Tipo' },
  { key: 'cidade', label: 'Cidade' },
  { key: 'estado', label: 'UF' },
  { key: 'status', label: 'Status' }
];

const resetForm = () => {
  form.value = {
    mantenedora_id: '',
    reitor_id: null,
    razao_social: '',
    nome_fantasia: '',
    cnpj: '',
    sigla: '',
    tipo_organizacao_academica: '',
    categoria_administrativa: '',
    codigo_mec: '',
    logradouro: '',
    numero: '',
    complemento: '',
    bairro: '',
    cidade: '',
    estado: '',
    cep: '',
    status: 'Ativo'
  };
  selectedReitor.value = null;
};

const fetchInstituicoes = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar instituições:', error);
  } finally {
    loading.value = false;
  }
};

const fetchMantenedoras = async () => {
  try {
    const response = await axios.get('/api/v1/mantenedoras');
    mantenedoras.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar mantenedoras:', error);
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
  selectedReitor.value = instituicao.reitor || null;
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
  resetForm();
};

const createInstituicao = async () => {
  try {
    // Monta endereco_sede
    const enderecoParts = [
      form.value.logradouro,
      form.value.numero,
      form.value.complemento,
      form.value.bairro,
      form.value.cidade,
      form.value.estado,
      form.value.cep
    ].filter(Boolean);

    const payload = {
      ...form.value,
      endereco_sede: enderecoParts.length > 0 ? enderecoParts.join(', ') : 'A definir'
    };

    await axios.post('/api/v1/instituicoes', payload);
    await fetchInstituicoes();
    hideForm();
    alert('Instituição criada com sucesso!');
  } catch (error) {
    console.error('Erro ao criar instituição:', error);
    alert('Erro ao criar instituição. Verifique os dados.');
  }
};

const updateInstituicao = async () => {
  try {
    // Monta endereco_sede
    const enderecoParts = [
      form.value.logradouro,
      form.value.numero,
      form.value.complemento,
      form.value.bairro,
      form.value.cidade,
      form.value.estado,
      form.value.cep
    ].filter(Boolean);

    const payload = {
      ...form.value,
      endereco_sede: enderecoParts.length > 0 ? enderecoParts.join(', ') : 'A definir'
    };

    await axios.put(`/api/v1/instituicoes/${editingId.value}`, payload);
    await fetchInstituicoes();
    hideForm();
    alert('Instituição atualizada com sucesso!');
  } catch (error) {
    console.error('Erro ao atualizar instituição:', error);
    alert('Erro ao atualizar instituição.');
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
    alert('Instituição excluída com sucesso!');
  } catch (error) {
    console.error('Erro ao excluir instituição:', error);
    alert('Erro ao excluir instituição.');
  }
};

const formatLocation = (instituicao) => {
  if (instituicao.cidade && instituicao.estado) {
    return `${instituicao.cidade}/${instituicao.estado}`;
  } else if (instituicao.cidade) {
    return instituicao.cidade;
  } else if (instituicao.estado) {
    return instituicao.estado;
  }
  return '-';
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

<style scoped>
/* Glass Effect */
.card-glass .form-control,
.card-glass .form-select {
  background: rgba(255, 255, 255, 0.05) !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9) !important;
  transition: all 0.3s ease;
}

.card-glass .form-control:focus,
.card-glass .form-select:focus {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: rgba(13, 110, 253, 0.5);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
  color: rgba(255, 255, 255, 0.95) !important;
}

.card-glass .form-control::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.card-glass .form-label {
  color: rgba(255, 255, 255, 0.85);
  font-weight: 500;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.card-glass .form-select option {
  background: #1c1c23;
  color: rgba(255, 255, 255, 0.9);
}

.card-glass hr {
  border-color: rgba(255, 255, 255, 0.1);
  opacity: 1;
}

.card-glass h6 {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
}

/* Card View */
.info-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  align-items: flex-start;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.875rem;
  line-height: 1.5;
}

.info-item i {
  color: rgba(102, 126, 234, 0.8);
  margin-top: 0.125rem;
  flex-shrink: 0;
}

.info-item .label {
  font-weight: 600;
  margin-right: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
}

.info-item .value {
  color: rgba(255, 255, 255, 0.9);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  flex-wrap: wrap;
}
</style>
