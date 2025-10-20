<template>
  <div class="container-fluid">
    <!-- ⭐ HEADER COM BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 text-white">Catálogo de Cursos</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <router-link to="/admin/dashboard">Dashboard</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/admin/academico">Gestão Acadêmica</router-link>
            </li>
            <li class="breadcrumb-item active">Catálogo de Cursos</li>
          </ol>
        </nav>
      </div>
      <button 
        v-if="!showForm" 
        @click="showCreateForm" 
        class="btn btn-primary"
      >
        <i class="bi bi-plus-circle me-2"></i>Novo Curso no Catálogo
      </button>
    </div>

    <!-- ⭐ FORMULÁRIO (SLIDE DOWN) -->
    <transition name="slide-fade">
      <div v-if="showForm" class="card card-glass mb-4">
        <div class="card-header bg-glass border-glass">
          <h5 class="mb-0 text-white">
            <i class="bi bi-bookmark-star-fill me-2"></i>
            {{ isEditing ? 'Editar Curso do Catálogo' : 'Novo Curso no Catálogo' }}
          </h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="isEditing ? updateCurso() : createCurso()">
            <div class="row">
              <!-- Grupo Educacional -->
              <div class="col-md-6 mb-3">
                <label class="form-label text-white-50">
                  Grupo Educacional <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.grupo_educacional_id" 
                  class="form-select form-select-glass"
                  :class="{ 'is-invalid': validationErrors.grupo_educacional_id }"
                  required
                >
                  <option value="">-- Selecione --</option>
                  <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                    {{ grupo.nome }}
                  </option>
                </select>
                <div v-if="validationErrors.grupo_educacional_id" class="invalid-feedback d-block">
                  {{ validationErrors.grupo_educacional_id[0] }}
                </div>
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
                  <option value="">-- Selecione --</option>
                  <option v-for="area in areas" :key="area.id" :value="area.id">
                    {{ area.nome }}
                  </option>
                </select>
                <div v-if="validationErrors.area_conhecimento_id" class="invalid-feedback d-block">
                  {{ validationErrors.area_conhecimento_id[0] }}
                </div>
              </div>

              <!-- Código -->
              <div class="col-md-3 mb-3">
                <label class="form-label text-white-50">
                  Código <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="form.codigo" 
                  class="form-control form-control-glass text-uppercase"
                  :class="{ 'is-invalid': validationErrors.codigo }"
                  placeholder="Ex: ENG-001"
                  required
                  maxlength="20"
                >
                <div v-if="validationErrors.codigo" class="invalid-feedback d-block">
                  {{ validationErrors.codigo[0] }}
                </div>
              </div>

              <!-- Sigla -->
              <div class="col-md-3 mb-3">
                <label class="form-label text-white-50">Sigla</label>
                <input 
                  type="text" 
                  v-model="form.sigla" 
                  class="form-control form-control-glass text-uppercase"
                  placeholder="Ex: ENG"
                  maxlength="10"
                >
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
                  placeholder="Ex: Engenharia Civil"
                  required
                >
                <div v-if="validationErrors.nome" class="invalid-feedback d-block">
                  {{ validationErrors.nome[0] }}
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
                  <option value="">-- Selecione --</option>
                  <option value="Ensino Médio">Ensino Médio</option>
                  <option value="Técnico">Técnico</option>
                  <option value="Graduação">Graduação</option>
                  <option value="Pós-Graduação">Pós-Graduação</option>
                  <option value="Especialização">Especialização</option>
                  <option value="Mestrado">Mestrado</option>
                  <option value="Doutorado">Doutorado</option>
                  <option value="Extensão">Extensão</option>
                  <option value="Livre">Livre</option>
                </select>
              </div>

              <!-- Grau -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Grau Acadêmico</label>
                <select v-model="form.grau" class="form-select form-select-glass">
                  <option value="">-- Selecione --</option>
                  <option value="Bacharelado">Bacharelado</option>
                  <option value="Licenciatura">Licenciatura</option>
                  <option value="Tecnólogo">Tecnólogo</option>
                  <option value="Técnico">Técnico</option>
                  <option value="Especialista">Especialista</option>
                  <option value="Mestre">Mestre</option>
                  <option value="Doutor">Doutor</option>
                  <option value="Não se aplica">Não se aplica</option>
                </select>
              </div>

              <!-- Modalidade -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">
                  Modalidade <span class="text-danger">*</span>
                </label>
                <select v-model="form.modalidade" class="form-select form-select-glass" required>
                  <option value="presencial">Presencial</option>
                  <option value="ead">EAD</option>
                  <option value="semipresencial">Semipresencial</option>
                </select>
              </div>

              <!-- Duração Padrão -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Duração Padrão (semestres)</label>
                <input 
                  type="number" 
                  v-model="form.duracao_padrao_semestres" 
                  class="form-control form-control-glass"
                  min="1"
                  max="20"
                >
              </div>

              <!-- Prazo Máximo -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Prazo Máximo (semestres)</label>
                <input 
                  type="number" 
                  v-model="form.prazo_maximo_semestres" 
                  class="form-control form-control-glass"
                  min="1"
                  max="30"
                >
              </div>

              <!-- Carga Horária -->
              <div class="col-md-4 mb-3">
                <label class="form-label text-white-50">Carga Horária Total (horas)</label>
                <input 
                  type="number" 
                  v-model="form.carga_horaria_total" 
                  class="form-control form-control-glass"
                  min="1"
                >
              </div>

              <!-- Status -->
              <div class="col-md-12 mb-3">
                <label class="form-label text-white-50">
                  Status <span class="text-danger">*</span>
                </label>
                <select v-model="form.status" class="form-select form-select-glass" required>
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
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
            <label class="form-label text-white-50">Grupo Educacional</label>
            <select v-model="filters.grupo_educacional_id" @change="aplicarFiltros" class="form-select form-select-glass">
              <option value="">Todos</option>
              <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
                {{ grupo.nome }}
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
              placeholder="Código ou nome..."
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
            Cursos Cadastrados no Catálogo
          </h5>
          <span class="badge bg-primary">{{ pagination.total }} cursos</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4" style="width: 100px">Código</th>
                <th>Nome do Curso</th>
                <th style="width: 200px">Grupo</th>
                <th style="width: 120px">Nível</th>
                <th style="width: 120px">Modalidade</th>
                <th class="text-center" style="width: 100px">Uso</th>
                <th class="text-center" style="width: 150px">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cursos.length === 0">
                <td colspan="7" class="text-center text-white-50 py-5">
                  <i class="bi bi-inbox display-4 d-block mb-3"></i>
                  Nenhum curso encontrado no catálogo.
                </td>
              </tr>
              <tr v-for="curso in cursos" :key="curso.id" class="table-row-hover">
                <td class="ps-4">
                  <code class="badge bg-info">{{ curso.codigo }}</code>
                </td>
                <td>
                  <strong class="text-white">{{ curso.nome }}</strong>
                  <br>
                  <small class="text-white-50">{{ curso.area_conhecimento?.nome }}</small>
                </td>
                <td>
                  <small class="text-white-50">{{ curso.grupo_educacional?.nome }}</small>
                </td>
                <td>
                  <span class="badge bg-secondary">{{ curso.nivel }}</span>
                </td>
                <td>
                  <span class="badge" :class="{
                    'bg-primary': curso.modalidade === 'presencial',
                    'bg-info': curso.modalidade === 'ead',
                    'bg-warning': curso.modalidade === 'semipresencial'
                  }">
                    {{ curso.modalidade }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge bg-success">{{ curso.total_instituicoes || 0 }} IES</span>
                </td>
                <td class="text-center">
                  <button 
                    @click="showEditForm(curso)" 
                    class="btn btn-sm btn-outline-light me-1" 
                    title="Editar"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button 
                    @click="confirmDelete(curso)" 
                    class="btn btn-sm btn-outline-danger" 
                    title="Excluir"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { debounce } from 'lodash';

// Estados
const loading = ref(true);
const saving = ref(false);
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const cursos = ref([]);
const grupos = ref([]);
const areas = ref([]);

const form = ref({
  grupo_educacional_id: '',
  area_conhecimento_id: '',
  codigo: '',
  sigla: '',
  nome: '',
  nivel: 'Graduação',
  grau: '',
  modalidade: 'presencial',
  duracao_padrao_semestres: null,
  prazo_maximo_semestres: null,
  carga_horaria_total: null,
  status: 'ativo'
});

const filters = ref({
  grupo_educacional_id: '',
  nivel: '',
  modalidade: '',
  search: ''
});

const validationErrors = ref({});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Páginas visíveis na paginação
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

// Carregar dados
const fetchCursos = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/catalogo-cursos', {
      params: {
        ...filters.value,
        with_stats: 1,
        page
      }
    });
    
    cursos.value = response.data.data;
    pagination.value = response.data.meta || response.data;
  } catch (error) {
    console.error('Erro ao carregar cursos:', error);
    Swal.fire('Erro!', 'Não foi possível carregar os cursos.', 'error');
  } finally {
    loading.value = false;
  }
};

const fetchGrupos = async () => {
  try {
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar grupos:', error);
  }
};

const fetchAreas = async () => {
  try {
    const response = await axios.get('/api/v1/areas-conhecimento');
    areas.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar áreas:', error);
  }
};

// Funções do formulário
const resetForm = () => {
  form.value = {
    grupo_educacional_id: '',
    area_conhecimento_id: '',
    codigo: '',
    sigla: '',
    nome: '',
    nivel: 'Graduação',
    grau: '',
    modalidade: 'presencial',
    duracao_padrao_semestres: null,
    prazo_maximo_semestres: null,
    carga_horaria_total: null,
    status: 'ativo'
  };
  validationErrors.value = {};
};

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (curso) => {
  isEditing.value = true;
  editingId.value = curso.id;
  form.value = { ...curso };
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
    await axios.post('/api/v1/catalogo-cursos', form.value);
    
    Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Curso adicionado ao catálogo com sucesso.',
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
    await axios.put(`/api/v1/catalogo-cursos/${editingId.value}`, form.value);
    
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

const confirmDelete = (curso) => {
  Swal.fire({
    title: 'Tem certeza?',
    html: `Deseja excluir o curso <strong>"${curso.nome}"</strong> do catálogo?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then(async (result) => {
    if (result.isConfirmed) {
      await deleteCurso(curso.id);
    }
  });
};

const deleteCurso = async (id) => {
  try {
    await axios.delete(`/api/v1/catalogo-cursos/${id}`);
    
    Swal.fire({
      icon: 'success',
      title: 'Excluído!',
      text: 'Curso excluído do catálogo com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchCursos();
  } catch (error) {
    if (error.response?.status === 422) {
      Swal.fire({
        icon: 'error',
        title: 'Não é possível excluir',
        html: error.response.data.message,
        confirmButtonColor: '#667eea'
      });
    } else {
      Swal.fire('Erro!', 'Não foi possível excluir o curso.', 'error');
    }
  }
};

// Filtros
const aplicarFiltros = () => {
  fetchCursos(1);
};

const debouncedSearch = debounce(() => {
  aplicarFiltros();
}, 500);

// Paginação
const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return;
  fetchCursos(page);
};

// Inicialização
onMounted(() => {
  fetchCursos();
  fetchGrupos();
  fetchAreas();
});
</script>

<style scoped>
@import '@/styles/BaseListViewStyles.css';
</style>
