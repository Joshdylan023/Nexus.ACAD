<template>
  <div class="container-fluid">
    <!-- ⭐ HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 text-white">Gestão de Cursos</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <router-link to="/admin/dashboard">Dashboard</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/admin/academico">Gestão Acadêmica</router-link>
            </li>
            <li class="breadcrumb-item active">Cursos</li>
          </ol>
        </nav>
      </div>
      <button 
        v-if="!showForm" 
        @click="showCreateForm" 
        class="btn btn-primary"
      >
        <i class="bi bi-plus-circle me-2"></i>Novo Curso
      </button>
    </div>

    <!-- ⭐ FORMULÁRIO (SLIDE DOWN) -->
    <transition name="slide-fade">
      <div v-if="showForm" class="card card-glass mb-4">
        <div class="card-header bg-glass border-glass">
          <h5 class="mb-0 text-white">
            <i class="bi bi-bookmark-star-fill me-2"></i>
            {{ isEditing ? 'Editar Curso' : 'Novo Curso' }}
          </h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="isEditing ? updateCurso() : createCurso()">
            <div class="row">
              <!-- Catálogo -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Catálogo de Cursos <span class="text-info">(opcional)</span>
                </label>
                <select 
                  v-model="form.catalogo_curso_id" 
                  class="form-select form-select-glass"
                >
                  <option value="">-- Não vinculado --</option>
                  <option v-for="cat in catalogoCursos" :key="cat.id" :value="cat.id">
                    {{ cat.codigo }} - {{ cat.nome }}
                  </option>
                </select>
              </div>

              <!-- Instituição -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Instituição <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.instituicao_id" 
                  class="form-select form-select-glass"
                  :class="{ 'is-invalid': validationErrors.instituicao_id }"
                  required
                >
                  <option :value="null">-- Selecione --</option>
                  <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                    {{ inst.nome_fantasia }}
                  </option>
                </select>
                <div v-if="validationErrors.instituicao_id" class="invalid-feedback d-block">
                  {{ validationErrors.instituicao_id[0] }}
                </div>
              </div>

              <!-- Campus -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Campus <span class="text-info">(opcional)</span>
                </label>
                <select 
                  v-model="form.campus_id" 
                  class="form-select form-select-glass"
                  :disabled="!form.instituicao_id"
                >
                  <option :value="null">
                    {{ form.instituicao_id ? '-- Todos os campi --' : '-- Selecione uma instituição primeiro --' }}
                  </option>
                  <option v-for="campus in campiFiltrados" :key="campus.id" :value="campus.id">
                    {{ campus.nome }}
                  </option>
                </select>
                <small v-if="!form.instituicao_id" class="text-white-50">
                  Selecione uma instituição para ver os campi disponíveis
                </small>
              </div>

              <!-- Área de Conhecimento -->
              <div class="col-md-6 mb-3">
                <label class="form-label text-white-50">
                  Área de Conhecimento <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.area_conhecimento_id" 
                  class="form-select form-select-glass"
                  :class="{ 'is-invalid': validationErrors.area_conhecimento_id }"
                  required
                >
                  <option :value="null">-- Selecione --</option>
                  <option v-for="area in areas" :key="area.id" :value="area.id">
                    {{ area.nome }}
                  </option>
                </select>
                <div v-if="validationErrors.area_conhecimento_id" class="invalid-feedback d-block">
                  {{ validationErrors.area_conhecimento_id[0] }}
                </div>
              </div>

              <!-- Nome -->
              <div class="col-md-6 mb-3">
                <label class="form-label text-white-50">
                  Nome do Curso <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="form.nome" 
                  class="form-control form-control-glass"
                  :class="{ 'is-invalid': validationErrors.nome }"
                  required
                >
                <div v-if="validationErrors.nome" class="invalid-feedback d-block">
                  {{ validationErrors.nome[0] }}
                </div>
              </div>

              <!-- Código IES -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Código IES <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="form.codigo_ies" 
                  class="form-control form-control-glass text-uppercase"
                  :class="{ 'is-invalid': validationErrors.codigo_ies }"
                  required
                >
                <div v-if="validationErrors.codigo_ies" class="invalid-feedback d-block">
                  {{ validationErrors.codigo_ies[0] }}
                </div>
              </div>

              <!-- Nível -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Nível <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.nivel" 
                  class="form-select form-select-glass"
                  required
                >
                  <option v-for="nivel in niveis" :key="nivel" :value="nivel">
                    {{ nivel }}
                  </option>
                </select>
              </div>

              <!-- Modalidade -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Modalidade <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.modalidade" 
                  class="form-select form-select-glass"
                  required
                >
                  <option value="presencial">Presencial</option>
                  <option value="ead">EAD</option>
                  <option value="semipresencial">Semipresencial</option>
                </select>
              </div>

              <!-- Duração -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Duração Padrão (semestres)</label>
                <input 
                  type="number" 
                  v-model="form.duracao_padrao_semestres" 
                  class="form-control form-control-glass"
                  required
                >
              </div>

              <!-- Prazo Máximo -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Prazo Máximo (semestres)</label>
                <input 
                  type="number" 
                  v-model="form.prazo_maximo_semestres" 
                  class="form-control form-control-glass"
                  required
                >
              </div>

              <!-- Vagas -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Vagas Anuais</label>
                <input 
                  type="number" 
                  v-model="form.vagas_anuais" 
                  class="form-control form-control-glass"
                  required
                >
              </div>

              <!-- Coordenador -->
              <div class="col-md-6 mb-3">
                <label class="form-label text-white-50">
                  Coordenador(a) <span class="text-info">(opcional)</span>
                </label>
                <v-select
                  v-model="selectedCoordenador"
                  :options="coordenadorOptions"
                  :filterable="false"
                  @search="fetchCoordenadorOptions"
                  placeholder="Digite nome ou matrícula..."
                  label="label"
                  :reduce="colaborador => colaborador"
                  class="v-select-glass"
                >
                  <template #no-options="{ search, searching }">
                    <div v-if="searching" class="text-white-50 p-2">
                      Digite pelo menos 3 caracteres para buscar...
                    </div>
                    <div v-else-if="search.length > 0" class="text-white-50 p-2">
                      Nenhum colaborador encontrado.
                    </div>
                    <div v-else class="text-white-50 p-2">
                      Digite para buscar colaboradores...
                    </div>
                  </template>
                  
                  <template #option="option">
                    <div class="d-flex flex-column py-1">
                      <strong class="text-white">{{ option.name }}</strong>
                      <small class="text-white-50">
                        Mat: {{ option.matricula_funcional }}
                        <span v-if="option.setor"> | {{ option.setor.nome }}</span>
                      </small>
                    </div>
                  </template>
                  
                  <template #selected-option="option">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-person-badge me-2 text-info"></i>
                      <div>
                        <strong>{{ option.name }}</strong>
                        <small class="text-white-50 ms-2">({{ option.matricula_funcional }})</small>
                      </div>
                    </div>
                  </template>
                  
                  <template #spinner="{ loading }">
                    <div v-if="loading" class="vs__spinner">Buscando...</div>
                  </template>
                </v-select>
                
                <small class="text-white-50 mt-1 d-block">
                  <i class="bi bi-info-circle me-1"></i>
                  Digite nome ou matrícula para buscar
                </small>
              </div>

              <!-- Status -->
              <div class="col-md-6 mb-3">
                <label class="form-label text-white-50">Status</label>
                <select 
                  v-model="form.status" 
                  class="form-select form-select-glass"
                  required
                >
                  <option value="Em Planejamento">Em Planejamento</option>
                  <option value="Ativo">Ativo</option>
                  <option value="Em Extinção">Em Extinção</option>
                  <option value="Extinto">Extinto</option>
                </select>
              </div>

              <!-- Botões -->
              <div class="col-12">
                <button 
                  type="submit" 
                  class="btn btn-success me-2"
                  :disabled="saving"
                >
                  <i class="bi" :class="saving ? 'bi-hourglass-split' : 'bi-check-circle'"></i>
                  {{ saving ? 'Salvando...' : 'Salvar' }}
                </button>
                <button 
                  type="button" 
                  @click="hideForm" 
                  class="btn btn-outline-light"
                  :disabled="saving"
                >
                  <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- ⭐ FILTROS -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label text-white-50">Instituição</label>
            <select v-model="filters.instituicao_id" @change="aplicarFiltros" class="form-select form-select-glass">
              <option value="">Todas</option>
              <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                {{ inst.nome_fantasia }}
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white-50">Nível</label>
            <select v-model="filters.nivel" @change="aplicarFiltros" class="form-select form-select-glass">
              <option value="">Todos</option>
              <option value="Graduação">Graduação</option>
              <option value="Pós-Graduação">Pós-Graduação</option>
              <option value="Técnico">Técnico</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white-50">Modalidade</label>
            <select v-model="filters.modalidade" @change="aplicarFiltros" class="form-select form-select-glass">
              <option value="">Todas</option>
              <option value="presencial">Presencial</option>
              <option value="ead">EAD</option>
              <option value="semipresencial">Semipresencial</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white-50">Buscar</label>
            <input 
              v-model="filters.search" 
              @input="debouncedSearch" 
              type="text" 
              class="form-control form-control-glass" 
              placeholder="Nome ou código..."
            >
          </div>
        </div>
      </div>
    </div>

    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loading" class="card card-glass">
      <div class="card-body">
        <div v-for="n in 6" :key="n" class="skeleton-item mb-3">
          <div class="skeleton-line" style="width: 15%"></div>
          <div class="skeleton-line" style="width: 50%"></div>
          <div class="skeleton-line" style="width: 30%"></div>
        </div>
      </div>
    </div>

    <!-- ⭐ LISTAGEM -->
    <div v-else class="card card-glass">
      <div class="card-header bg-glass border-glass">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-white">
            <i class="bi bi-list-ul me-2"></i>
            Cursos Cadastrados
          </h5>
          <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-primary">{{ pagination.total }} cursos</span>
            
            <!-- Botões de Exportação -->
            <div class="btn-group" role="group">
              <button 
                @click="exportarExcel" 
                class="btn btn-sm btn-success"
                :disabled="exporting"
                title="Exportar para Excel"
              >
                <i class="bi bi-file-earmark-excel me-1"></i>
                Excel
              </button>
              <button 
                @click="exportarPDF" 
                class="btn btn-sm btn-danger"
                :disabled="exporting"
                title="Exportar para PDF"
              >
                <i class="bi bi-file-earmark-pdf me-1"></i>
                PDF
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4" style="width: 80px">ID</th>
                <th>Nome do Curso</th>
                <th style="width: 200px">Instituição</th>
                <th style="width: 150px">Área</th>
                <th class="text-center" style="width: 120px">Status</th>
                <th class="text-center" style="width: 200px">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cursos.length === 0">
                <td colspan="6" class="text-center text-white-50 py-5">
                  <i class="bi bi-inbox display-4 d-block mb-3"></i>
                  Nenhum curso encontrado.
                </td>
              </tr>
              <tr v-for="curso in cursos" :key="curso.id" class="table-row-hover">
                <td class="ps-4">{{ curso.id }}</td>
                <td>
                  <strong class="text-white">{{ curso.nome }}</strong>
                  <br>
                  <small class="text-white-50">
                    {{ curso.codigo_ies }}
                    <span v-if="curso.catalogo_curso_id" class="badge bg-info ms-2">Catálogo</span>
                  </small>
                </td>
                <td>
                  <small class="text-white-50">
                    {{ curso.instituicao?.nome_fantasia || 'N/A' }}
                  </small>
                </td>
                <td>
                  <small class="text-white-50">
                    {{ curso.area_conhecimento?.nome || 'N/A' }}
                  </small>
                </td>
                <td class="text-center">
                  <span class="badge" :class="getStatusClass(curso.status)">
                    {{ curso.status }}
                  </span>
                </td>
                <td class="text-center">
                  <!-- ✅ BOTÕES COM DROPDOWN -->
                  <div class="btn-group" role="group">
                    <!-- Botão Ver Detalhes -->
                    <router-link 
                      :to="{ name: 'admin.academico.cursos.detalhes', params: { id: curso.id } }"
                      class="btn btn-sm btn-outline-info"
                      title="Ver Detalhes"
                    >
                      <i class="bi bi-eye"></i>
                    </router-link>

                    <!-- Dropdown de Ações -->
                    <div class="dropdown">
                      <button 
                        class="btn btn-sm btn-outline-light dropdown-toggle" 
                        type="button" 
                        :id="`dropdown${curso.id}`"
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                      >
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" :aria-labelledby="`dropdown${curso.id}`">
                        <!-- Atos Regulatórios -->
                        <li>
                          <router-link 
                            :to="{ name: 'admin.academico.cursos.atos', params: { id: curso.id } }"
                            class="dropdown-item"
                          >
                            <i class="bi bi-file-earmark-text me-2"></i>
                            Atos Regulatórios
                          </router-link>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <!-- Editar -->
                        <li>
                          <button @click="showEditForm(curso)" class="dropdown-item">
                            <i class="bi bi-pencil me-2"></i>
                            Editar
                          </button>
                        </li>
                        
                        <!-- Excluir -->
                        <li>
                          <button @click="confirmDelete(curso)" class="dropdown-item text-danger">
                            <i class="bi bi-trash me-2"></i>
                            Excluir
                          </button>
                        </li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="pagination.total > pagination.per_page" class="card-footer bg-glass border-glass">
        <div class="d-flex justify-content-between align-items-center">
          <div class="text-white-50">
            Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} registros
          </div>
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <a class="page-link" @click.prevent="changePage(pagination.current_page - 1)" href="#">
                  <i class="bi bi-chevron-left"></i>
                </a>
              </li>
              <li 
                v-for="page in visiblePages" 
                :key="page" 
                class="page-item" 
                :class="{ active: page === pagination.current_page }"
              >
                <a class="page-link" @click.prevent="changePage(page)" href="#">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <a class="page-link" @click.prevent="changePage(pagination.current_page + 1)" href="#">
                  <i class="bi bi-chevron-right"></i>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import Swal from 'sweetalert2';
import { debounce } from 'lodash';

const cursos = ref([]);
const instituicoes = ref([]);
const campi = ref([]);
const areas = ref([]);
const catalogoCursos = ref([]);
const coordenadorOptions = ref([]);
const selectedCoordenador = ref(null);
const isSearchingCoordenador = ref(false);

const niveis = [
  'Ensino Médio', 'Técnico', 'Graduação', 'Pós-Graduação', 
  'Especialização', 'Mestrado', 'Doutorado', 'Extensão', 'Livre'
];

const loading = ref(true);
const saving = ref(false);
const form = ref({});
const exporting = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

const filters = ref({
  instituicao_id: '',
  nivel: '',
  modalidade: '',
  search: ''
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// ✅ COMPUTED: Campi filtrados pela instituição selecionada
const campiFiltrados = computed(() => {
  if (!form.value.instituicao_id) {
    return [];
  }
  return campi.value.filter(campus => campus.instituicao_id === form.value.instituicao_id);
});

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const pages = [];

  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i);
  }

  return pages;
});

// ✅ WATCH: Limpa campus quando instituição muda
watch(() => form.value.instituicao_id, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    form.value.campus_id = null;
  }
});

// ✅ WATCH: Atualiza coordenador_id quando seleção muda
watch(selectedCoordenador, (newValue) => {
  if (newValue) {
    form.value.coordenador_id = newValue.user_id || newValue.id;
  } else {
    form.value.coordenador_id = null;
  }
});

const fetchCursos = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/cursos', {
      params: {
        ...filters.value,
        page
      }
    });
    cursos.value = response.data.data || response.data;
    pagination.value = response.data.meta || response.data;
  } catch (error) {
    console.error('Erro ao buscar cursos:', error);
    Swal.fire('Erro!', 'Não foi possível carregar os cursos.', 'error');
  } finally {
    loading.value = false;
  }
};

const fetchInstituicoes = async () => {
  try {
    const response = await axios.get('/api/v1/instituicoes');
    instituicoes.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar instituições:', error);
  }
};

const fetchCampi = async () => {
  try {
    const response = await axios.get('/api/v1/campi');
    campi.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar campi:', error);
  }
};

const fetchAreas = async () => {
  try {
    const response = await axios.get('/api/v1/areas-conhecimento');
    areas.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar áreas:', error);
  }
};

const fetchCatalogoCursos = async () => {
  try {
    const response = await axios.get('/api/v1/catalogo-cursos');
    catalogoCursos.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar cursos do catálogo:', error);
  }
};

// ✅ FUNÇÃO MELHORADA: Busca colaboradores
const fetchCoordenadorOptions = debounce(async (search, loading) => {
  if (!search || search.length < 3) {
    coordenadorOptions.value = [];
    return;
  }

  try {
    loading(true);
    isSearchingCoordenador.value = true;

    const response = await axios.get('/api/v1/colaboradores', {
      params: {
        search: search,
        limit: 20
      }
    });

    const data = response.data.data || response.data;
    
    coordenadorOptions.value = data.map(colaborador => {
      const name = colaborador.usuario?.name || 'Nome não disponível';
      return {
        id: colaborador.id,
        user_id: colaborador.user_id,
        name: name,
        matricula_funcional: colaborador.matricula_funcional,
        label: `${name} (${colaborador.matricula_funcional})`,
        setor: colaborador.setor_vinculo?.setor || null,
        email: colaborador.usuario?.email
      };
    });

  } catch (error) {
    console.error('Erro ao buscar colaboradores:', error);
    coordenadorOptions.value = [];
    
    if (error.response?.status !== 404) {
      Swal.fire({
        icon: 'error',
        title: 'Erro ao buscar',
        text: 'Não foi possível buscar os colaboradores.',
        toast: true,
        position: 'top-end',
        timer: 3000,
        showConfirmButton: false
      });
    }
  } finally {
    loading(false);
    isSearchingCoordenador.value = false;
  }
}, 500);

const resetForm = () => {
  form.value = {
    catalogo_curso_id: '',
    instituicao_id: null,
    campus_id: null,
    area_conhecimento_id: null,
    nome: '',
    codigo_ies: '',
    nivel: 'Graduação',
    modalidade: 'presencial',
    duracao_padrao_semestres: null,
    prazo_maximo_semestres: null,
    coordenador_id: null,
    status: 'Em Planejamento',
    vagas_anuais: null
  };
  selectedCoordenador.value = null;
  coordenadorOptions.value = [];
  validationErrors.value = {};
};

const getStatusClass = (status) => {
  switch (status) {
    case 'Ativo': return 'bg-success';
    case 'Em Extinção': return 'bg-warning text-dark';
    case 'Extinto': return 'bg-danger';
    default: return 'bg-secondary';
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  resetForm();
  showForm.value = true;
};

const showEditForm = (curso) => {
  isEditing.value = true;
  editingId.value = curso.id;
  form.value = { ...curso };
  
  if (curso.coordenador) {
    const coordenadorName = curso.coordenador.usuario?.name || curso.coordenador.name || 'Nome não disponível';
    selectedCoordenador.value = {
      id: curso.coordenador.id,
      user_id: curso.coordenador.user_id,
      name: coordenadorName,
      matricula_funcional: curso.coordenador.matricula_funcional,
      label: `${coordenadorName} (${curso.coordenador.matricula_funcional})`,
      setor: curso.coordenador.setor_vinculo?.setor || null
    };
    
    coordenadorOptions.value = [selectedCoordenador.value];
  } else {
    selectedCoordenador.value = null;
    coordenadorOptions.value = [];
  }
  
  validationErrors.value = {};
  showForm.value = true;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const hideForm = () => {
  showForm.value = false;
  setTimeout(() => {
    resetForm();
    isEditing.value = false;
    editingId.value = null;
  }, 300);
};

const createCurso = async () => {
  saving.value = true;
  validationErrors.value = {};
  
  try {
    await axios.post('/api/v1/cursos', form.value);
    
    Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Curso criado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchCursos();
    hideForm();
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    } else {
      Swal.fire('Erro!', 'Não foi possível criar o curso.', 'error');
    }
  } finally {
    saving.value = false;
  }
};

const updateCurso = async () => {
  saving.value = true;
  validationErrors.value = {};
  
  try {
    await axios.put(`/api/v1/cursos/${editingId.value}`, form.value);
    
    Swal.fire({
      icon: 'success',
      title: 'Atualizado!',
      text: 'Curso atualizado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchCursos();
    hideForm();
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    } else {
      Swal.fire('Erro!', 'Não foi possível atualizar o curso.', 'error');
    }
  } finally {
    saving.value = false;
  }
};

// ✅ CONFIRMAR EXCLUSÃO
const confirmDelete = async (curso) => {
  const result = await Swal.fire({
    title: 'Tem certeza?',
    text: `Deseja excluir o curso "${curso.nome}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/v1/cursos/${curso.id}`);
      
      Swal.fire({
        icon: 'success',
        title: 'Excluído!',
        text: 'Curso excluído com sucesso.',
        timer: 2000,
        showConfirmButton: false
      });
      
      await fetchCursos();
    } catch (error) {
      Swal.fire('Erro!', 'Não foi possível excluir o curso.', 'error');
    }
  }
};

// ✅ EXPORTAR PARA EXCEL
const exportarExcel = async () => {
  try {
    exporting.value = true;
    
    const response = await axios.get('/api/v1/cursos/export/excel', {
      params: filters.value,
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `cursos_${new Date().getTime()}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    Swal.fire({
      icon: 'success',
      title: 'Exportado!',
      text: 'Arquivo Excel gerado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    console.error('Erro ao exportar Excel:', error);
    Swal.fire('Erro!', 'Não foi possível exportar para Excel.', 'error');
  } finally {
    exporting.value = false;
  }
};

// ✅ EXPORTAR PARA PDF
const exportarPDF = async () => {
  try {
    exporting.value = true;
    
    const response = await axios.get('/api/v1/cursos/export/pdf', {
      params: filters.value,
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `cursos_${new Date().getTime()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    Swal.fire({
      icon: 'success',
      title: 'Exportado!',
      text: 'Arquivo PDF gerado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    console.error('Erro ao exportar PDF:', error);
    Swal.fire('Erro!', 'Não foi possível exportar para PDF.', 'error');
  } finally {
    exporting.value = false;
  }
};

const aplicarFiltros = () => {
  fetchCursos(1);
};

const debouncedSearch = debounce(() => {
  aplicarFiltros();
}, 500);

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return;
  fetchCursos(page);
};

onMounted(() => {
  fetchCursos();
  fetchInstituicoes();
  fetchCampi();
  fetchAreas();
  fetchCatalogoCursos();
});
</script>

<style scoped>
@import '@/styles/BaseListViewStyles.css';

/* ✅ FIX: Dropdown não cortado */
.table tbody tr {
  position: relative;
}

.table tbody tr .dropdown {
  position: static;
}

.table tbody tr .dropdown-menu {
  position: absolute;
  z-index: 1050;
}

.table-responsive {
  overflow: visible !important;
}

/* ✅ V-SELECT GLASS STYLING */
.v-select-glass {
  --vs-controls-color: rgba(255, 255, 255, 0.9);
  --vs-border-color: rgba(255, 255, 255, 0.1);
  --vs-border-width: 1px;
  --vs-border-style: solid;
  --vs-border-radius: 8px;
  
  --vs-dropdown-bg: rgba(0, 0, 0, 0.9);
  --vs-dropdown-color: rgba(255, 255, 255, 0.9);
  --vs-dropdown-option-color: rgba(255, 255, 255, 0.9);
  
  --vs-selected-bg: rgba(102, 126, 234, 0.3);
  --vs-selected-color: #fff;
  
  --vs-search-input-color: rgba(255, 255, 255, 0.9);
  --vs-search-input-bg: transparent;
  --vs-search-input-placeholder-color: rgba(255, 255, 255, 0.5);
  
  --vs-dropdown-option--active-bg: rgba(102, 126, 234, 0.2);
  --vs-dropdown-option--active-color: #fff;
}

.v-select-glass :deep(.vs__dropdown-toggle) {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.5rem;
}

.v-select-glass :deep(.vs__dropdown-menu) {
  background: rgba(0, 0, 0, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.v-select-glass :deep(.vs__dropdown-option) {
  padding: 0.75rem 1rem;
  transition: all 0.2s ease;
}

.v-select-glass :deep(.vs__dropdown-option:hover) {
  background: rgba(102, 126, 234, 0.3);
}

.v-select-glass :deep(.vs__dropdown-option--highlight) {
  background: rgba(102, 126, 234, 0.2);
}

.v-select-glass :deep(.vs__search),
.v-select-glass :deep(.vs__search:focus) {
  color: rgba(255, 255, 255, 0.9);
}

.v-select-glass :deep(.vs__selected) {
  color: rgba(255, 255, 255, 0.9);
}

.v-select-glass :deep(.vs__clear),
.v-select-glass :deep(.vs__open-indicator) {
  fill: rgba(255, 255, 255, 0.7);
}

.v-select-glass :deep(.vs__spinner) {
  border-left-color: rgba(102, 126, 234, 0.8);
}
</style>
