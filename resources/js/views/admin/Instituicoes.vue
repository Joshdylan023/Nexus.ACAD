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
        <!-- ⭐ BOTÃO DE FILTROS -->
        <button 
          v-if="!showForm"
          @click="toggleFilters" 
          class="btn btn-outline-light me-2"
          :class="{ 'active': showFilters }"
        >
          <i class="bi bi-funnel me-2"></i>
          Filtros
          <span v-if="activeFilterCount > 0" class="badge bg-primary ms-2">
            {{ activeFilterCount }}
          </span>
        </button>

        <ViewToggle 
          v-if="!showForm"
          v-model="viewMode"
          class="me-2"
        />
        <ExportDropdown 
        v-if="!showForm"
        :data="instituicoesFiltradas"
        :columns="exportColumns"
        fileName="instituicoes"
        api-endpoint="/api/v1/instituicoes/export"
        :filters="activeFilters"
        class="me-2"
        />
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Nova Instituição
        </button>
      </template>
    </PageHeader>

    <!-- ⭐ PAINEL DE FILTROS -->
    <AdvancedFilters
      :show="showFilters"
      :filters="filterConfig"
      v-model="activeFilters"
      @close="showFilters = false"
      @clear-all="clearAllFilters"
    />
    
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
              <input 
                type="text" 
                class="form-control" 
                v-model="form.razao_social" 
                :class="{ 'is-invalid': errors.razao_social }"
                required
              >
              <div v-if="errors.razao_social" class="invalid-feedback">{{ errors.razao_social }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nome Fantasia *</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.nome_fantasia" 
                :class="{ 'is-invalid': errors.nome_fantasia }"
                required
              >
              <div v-if="errors.nome_fantasia" class="invalid-feedback">{{ errors.nome_fantasia }}</div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <MaskedInput
                v-model="form.cnpj"
                label="CNPJ *"
                mask="cnpj"
                placeholder="00.000.000/0000-00"
                required
                :error="errors.cnpj"
                hint="Informe um CNPJ válido com 14 dígitos"
              />
            </div>
            <div class="col-md-4">
              <label class="form-label">Sigla</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.sigla"
                :class="{ 'is-invalid': errors.sigla }"
              >
              <div v-if="errors.sigla" class="invalid-feedback">{{ errors.sigla }}</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Código MEC</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.codigo_mec"
                :class="{ 'is-invalid': errors.codigo_mec }"
              >
              <div v-if="errors.codigo_mec" class="invalid-feedback">{{ errors.codigo_mec }}</div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Mantenedora *</label>
              <select 
                class="form-select" 
                v-model="form.mantenedora_id" 
                :class="{ 'is-invalid': errors.mantenedora_id }"
                required
              >
                <option value="">Selecione...</option>
                <option v-for="m in mantenedoras" :key="m.id" :value="m.id">
                  {{ m.razao_social }}
                </option>
              </select>
              <div v-if="errors.mantenedora_id" class="invalid-feedback">{{ errors.mantenedora_id }}</div>
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
              <select 
                class="form-select" 
                v-model="form.tipo_organizacao_academica"
                :class="{ 'is-invalid': errors.tipo_organizacao_academica }"
              >
                <option value="">Selecione...</option>
                <option value="Universidade">Universidade</option>
                <option value="Centro Universitário">Centro Universitário</option>
                <option value="Faculdade">Faculdade</option>
                <option value="Instituto Federal">Instituto Federal</option>
                <option value="CEFET">CEFET</option>
              </select>
              <div v-if="errors.tipo_organizacao_academica" class="invalid-feedback">{{ errors.tipo_organizacao_academica }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Categoria Administrativa</label>
              <select 
                class="form-select" 
                v-model="form.categoria_administrativa"
                :class="{ 'is-invalid': errors.categoria_administrativa }"
              >
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
              <div v-if="errors.categoria_administrativa" class="invalid-feedback">{{ errors.categoria_administrativa }}</div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select 
                class="form-select" 
                v-model="form.status"
                :class="{ 'is-invalid': errors.status }"
              >
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
                <option value="Em Extinção">Em Extinção</option>
              </select>
              <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
            </div>
          </div>

          <!-- Endereço -->
          <hr class="my-4">
          <h6 class="mb-3">Endereço da Sede</h6>

          <div class="row mb-3">
            <div class="col-md-8">
              <label class="form-label">Logradouro</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.logradouro" 
                placeholder="Rua, Avenida, etc."
                :class="{ 'is-invalid': errors.logradouro }"
              >
              <div v-if="errors.logradouro" class="invalid-feedback">{{ errors.logradouro }}</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Número</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.numero"
                :class="{ 'is-invalid': errors.numero }"
              >
              <div v-if="errors.numero" class="invalid-feedback">{{ errors.numero }}</div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Complemento</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.complemento" 
                placeholder="Sala, Bloco, etc."
                :class="{ 'is-invalid': errors.complemento }"
              >
              <div v-if="errors.complemento" class="invalid-feedback">{{ errors.complemento }}</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Bairro</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.bairro"
                :class="{ 'is-invalid': errors.bairro }"
              >
              <div v-if="errors.bairro" class="invalid-feedback">{{ errors.bairro }}</div>
            </div>
            <div class="col-md-4">
              <MaskedInput
                v-model="form.cep"
                label="CEP"
                mask="cep"
                placeholder="00000-000"
                :error="errors.cep"
              />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-8">
              <label class="form-label">Cidade</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.cidade"
                :class="{ 'is-invalid': errors.cidade }"
              >
              <div v-if="errors.cidade" class="invalid-feedback">{{ errors.cidade }}</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Estado (UF)</label>
              <select 
                class="form-select" 
                v-model="form.estado"
                :class="{ 'is-invalid': errors.estado }"
              >
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
              <div v-if="errors.estado" class="invalid-feedback">{{ errors.estado }}</div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
            {{ submitting ? 'Salvando...' : 'Salvar' }}
          </button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2" :disabled="submitting">
            Cancelar
          </button>
        </form>
      </div>
    </div>

    <!-- Visualização em Tabela -->
    <div v-if="viewMode === 'table' && !showForm" class="card card-glass">
      <div class="card-header"><h4>Instituições Cadastradas</h4></div>
      <div class="card-body p-0">
        <TableSkeleton v-if="loading" :columns="8" :rows="5" />
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
                <th>Auditoria</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="instituicao in instituicoesPaginadas" :key="instituicao.id">
                <td class="ps-4">{{ instituicao.razao_social }}</td>
                <td>{{ instituicao.nome_fantasia }}</td>
                <td>{{ formatCnpj(instituicao.cnpj) }}</td>
                <td><span class="badge bg-info">{{ instituicao.tipo_organizacao_academica || 'N/A' }}</span></td>
                <td>{{ formatLocation(instituicao) }}</td>
                <td><StatusBadge :status="instituicao.status" /></td>
                <td>
                  <div class="audit-info-compact">
                    <small>
                      <i class="bi bi-person-plus text-success me-1"></i>
                      <strong>{{ instituicao.creator?.name || (instituicao.created_by ? 'ID: ' + instituicao.created_by : 'Sistema') }}</strong>
                      <br>
                      <i class="bi bi-clock text-muted me-1"></i>
                      {{ formatDate(instituicao.created_at) }}
                      <template v-if="instituicao.updated_at !== instituicao.created_at">
                        <br>
                        <i class="bi bi-pencil text-warning me-1"></i>
                        <strong>{{ instituicao.updater?.name || (instituicao.updated_by ? 'ID: ' + instituicao.updated_by : 'Sistema') }}</strong>
                        <br>
                        <i class="bi bi-clock text-muted me-1"></i>
                        {{ formatDate(instituicao.updated_at) }}
                      </template>
                    </small>
                  </div>
                </td>
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
                <td colspan="8" class="text-center text-muted py-4">
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
              <span class="value">{{ formatCnpj(item.cnpj) }}</span>
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

            <!-- AUDITORIA -->
            <div class="audit-info-card">
              <div class="audit-header">
                <i class="bi bi-shield-check me-2"></i>
                <strong>Informações de Auditoria</strong>
              </div>
              <div class="audit-details">
                <div class="audit-row">
                  <i class="bi bi-person-plus text-success me-2"></i>
                  <span class="audit-label">Criado por:</span>
                  <span class="audit-value">
                    {{ item.creator?.name || (item.created_by ? 'ID: ' + item.created_by : 'Sistema') }}
                  </span>
                </div>
                <div class="audit-row">
                  <i class="bi bi-clock text-muted me-2"></i>
                  <span class="audit-label">Em:</span>
                  <span class="audit-value">{{ formatDate(item.created_at) }}</span>
                </div>

                <template v-if="item.updated_at !== item.created_at">
                  <div class="audit-divider"></div>
                  <div class="audit-row">
                    <i class="bi bi-pencil text-warning me-2"></i>
                    <span class="audit-label">Última atualização:</span>
                    <span class="audit-value">{{ formatDate(item.updated_at) }}</span>
                  </div>
                  <div class="audit-row">
                    <i class="bi bi-person text-info me-2"></i>
                    <span class="audit-label">Por:</span>
                    <span class="audit-value">
                      {{ item.updater?.name || (item.updated_by ? 'ID: ' + item.updated_by : 'Sistema') }}
                    </span>
                  </div>
                </template>
              </div>
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
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import ExportDropdown from '@/components/ExportDropdown.vue';

import CardView from '@/components/CardView.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import UserAutocomplete from '@/components/UserAutocomplete.vue';
import MaskedInput from '@/components/MaskedInput.vue';
import AdvancedFilters from '@/components/AdvancedFilters.vue';
import { usePagination } from '@/composables/usePagination';
import { useFilters } from '@/composables/useFilters';

const instituicoes = ref([]);
const mantenedoras = ref([]);
const loading = ref(true);
const submitting = ref(false);
const form = ref({});
const errors = ref({});
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const buscaRapida = ref('');
const itemToDelete = ref(null);
const viewMode = ref('table');
const selectedReitor = ref(null);
let confirmModalInstance = null;

const { currentPage, perPage, paginateItems, changePage, changePerPage } = usePagination(25);

// ⭐ CONFIGURAÇÃO DOS FILTROS
const filterConfig = [
  {
    key: 'status',
    label: 'Status',
    type: 'multi-select',
    options: [
      { value: 'Ativo', label: 'Ativo' },
      { value: 'Inativo', label: 'Inativo' },
      { value: 'Em Extinção', label: 'Em Extinção' }
    ]
  },
  {
    key: 'tipo_organizacao_academica',
    label: 'Tipo de Organização',
    type: 'multi-select',
    options: [
      { value: 'Universidade', label: 'Universidade' },
      { value: 'Centro Universitário', label: 'Centro Universitário' },
      { value: 'Faculdade', label: 'Faculdade' },
      { value: 'Instituto Federal', label: 'Instituto Federal' },
      { value: 'CEFET', label: 'CEFET' }
    ]
  },
  {
    key: 'categoria_administrativa',
    label: 'Categoria Administrativa',
    type: 'select',
    options: [
      { value: 'Pública Federal', label: 'Pública Federal' },
      { value: 'Pública Estadual', label: 'Pública Estadual' },
      { value: 'Pública Municipal', label: 'Pública Municipal' },
      { value: 'Privada com fins lucrativos', label: 'Privada com fins lucrativos' },
      { value: 'Privada sem fins lucrativos', label: 'Privada sem fins lucrativos' }
    ]
  },
  {
    key: 'estado',
    label: 'Estado (UF)',
    type: 'multi-select',
    options: [
      { value: 'SP', label: 'São Paulo' },
      { value: 'RJ', label: 'Rio de Janeiro' },
      { value: 'MG', label: 'Minas Gerais' },
      { value: 'RS', label: 'Rio Grande do Sul' },
      { value: 'PR', label: 'Paraná' },
      { value: 'SC', label: 'Santa Catarina' },
      { value: 'BA', label: 'Bahia' },
      { value: 'PE', label: 'Pernambuco' },
      { value: 'CE', label: 'Ceará' },
      { value: 'DF', label: 'Distrito Federal' },
      { value: 'GO', label: 'Goiás' },
      { value: 'ES', label: 'Espírito Santo' },
      { value: 'PA', label: 'Pará' },
      { value: 'AM', label: 'Amazonas' },
      { value: 'MT', label: 'Mato Grosso' },
      { value: 'MS', label: 'Mato Grosso do Sul' }
    ]
  }
];

// ⭐ USAR COMPOSABLE DE FILTROS
const {
  activeFilters,
  filteredItems,
  showFilters,
  clearAllFilters,
  activeFilterCount,
  toggleFilters
} = useFilters(instituicoes, filterConfig);

// ⭐ COMPUTED ATUALIZADO PARA USAR FILTROS
const instituicoesFiltradas = computed(() => {
  let result = filteredItems.value;
  
  // Aplicar busca rápida também
  if (buscaRapida.value) {
    const termo = buscaRapida.value.toLowerCase();
    result = result.filter(inst =>
      inst.razao_social?.toLowerCase().includes(termo) ||
      inst.nome_fantasia?.toLowerCase().includes(termo) ||
      inst.cnpj?.includes(termo)
    );
  }
  
  return result;
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

const formatDate = (date) => {
  if (!date) return 'N/A';
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: ptBR });
};

const formatCnpj = (cnpj) => {
  if (!cnpj) return '';
  const cleaned = cnpj.replace(/\D/g, '');
  if (cleaned.length !== 14) return cnpj;
  return cleaned.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
};

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
  errors.value = {};
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
    submitting.value = true;
    errors.value = {};
    
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
  } catch (error) {
    console.error('Erro ao criar instituição:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro ao criar instituição. Verifique os dados.');
    }
  } finally {
    submitting.value = false;
  }
};

const updateInstituicao = async () => {
  try {
    submitting.value = true;
    errors.value = {};
    
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
  } catch (error) {
    console.error('Erro ao atualizar instituição:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Erro ao atualizar instituição.');
    }
  } finally {
    submitting.value = false;
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

.audit-info-compact {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.4;
}

.audit-info-card {
  margin-top: 1rem;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.2);
  border-left: 3px solid rgba(102, 126, 234, 0.6);
  border-radius: 6px;
}

.audit-header {
  display: flex;
  align-items: center;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.audit-header i {
  color: rgba(102, 126, 234, 0.8);
}

.audit-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.audit-row {
  display: flex;
  align-items: center;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

.audit-row i {
  flex-shrink: 0;
}

.audit-label {
  font-weight: 500;
  margin-right: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
}

.audit-value {
  color: rgba(255, 255, 255, 0.9);
}

.audit-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 0.5rem 0;
}

.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* ⭐ Botão de filtros ativo */
.btn.active {
  background-color: rgba(102, 126, 234, 0.2) !important;
  border-color: rgba(102, 126, 234, 0.5) !important;
}
</style>
