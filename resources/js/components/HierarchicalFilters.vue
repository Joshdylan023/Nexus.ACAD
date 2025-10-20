<template>
  <div class="row g-3">
    <!-- Filtro: Grupo Educacional -->
    <div v-if="showGrupo" class="col-md-3">
      <label class="form-label text-white-50">Grupo Educacional</label>
      <select 
        v-model="filters.grupo_educacional_id" 
        @change="onGrupoChange" 
        class="form-select form-select-glass"
      >
        <option value="">Todos os Grupos</option>
        <option v-for="grupo in grupos" :key="grupo.id" :value="grupo.id">
          {{ grupo.nome }}
        </option>
      </select>
    </div>

    <!-- Filtro: Mantenedora -->
    <div v-if="showMantenedora" class="col-md-3">
      <label class="form-label text-white-50">Mantenedora</label>
      <select 
        v-model="filters.mantenedora_id" 
        @change="onMantenedoraChange" 
        class="form-select form-select-glass"
        :disabled="!filters.grupo_educacional_id && showGrupo"
      >
        <option value="">Todas as Mantenedoras</option>
        <option v-for="mantenedora in mantenedoras" :key="mantenedora.id" :value="mantenedora.id">
          {{ mantenedora.nome }}
        </option>
      </select>
    </div>

    <!-- Filtro: Instituição -->
    <div v-if="showInstituicao" class="col-md-3">
      <label class="form-label text-white-50">Instituição (IES)</label>
      <select 
        v-model="filters.instituicao_id" 
        @change="onInstituicaoChange" 
        class="form-select form-select-glass"
        :disabled="!filters.mantenedora_id && showMantenedora"
      >
        <option value="">Todas as Instituições</option>
        <option v-for="instituicao in instituicoes" :key="instituicao.id" :value="instituicao.id">
          {{ instituicao.nome }}
        </option>
      </select>
    </div>

    <!-- Filtro: Campus -->
    <div v-if="showCampus" class="col-md-3">
      <label class="form-label text-white-50">Campus</label>
      <select 
        v-model="filters.campus_id" 
        @change="onCampusChange" 
        class="form-select form-select-glass"
        :disabled="!filters.instituicao_id && showInstituicao"
      >
        <option value="">Todos os Campi</option>
        <option v-for="campus in campi" :key="campus.id" :value="campus.id">
          {{ campus.nome }}
        </option>
      </select>
    </div>

    <!-- Filtro: Curso (se aplicável) -->
    <div v-if="showCurso" class="col-md-3">
      <label class="form-label text-white-50">Curso</label>
      <select 
        v-model="filters.curso_id" 
        @change="onCursoChange" 
        class="form-select form-select-glass"
        :disabled="!filters.campus_id && showCampus"
      >
        <option value="">Todos os Cursos</option>
        <option v-for="curso in cursos" :key="curso.id" :value="curso.id">
          {{ curso.nome }}
        </option>
      </select>
    </div>

    <!-- Slot para filtros customizados adicionais -->
    <slot :filters="filters"></slot>

    <!-- Botão Limpar Filtros -->
    <div class="col-12">
      <button @click="clearAllFilters" class="btn btn-outline-light btn-sm">
        <i class="bi bi-x-circle me-2"></i>Limpar Filtros
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  showGrupo: { type: Boolean, default: true },
  showMantenedora: { type: Boolean, default: true },
  showInstituicao: { type: Boolean, default: true },
  showCampus: { type: Boolean, default: true },
  showCurso: { type: Boolean, default: false },
  initialFilters: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['filter-change']);

// Estados dos filtros
const filters = ref({
  grupo_educacional_id: '',
  mantenedora_id: '',
  instituicao_id: '',
  campus_id: '',
  curso_id: '',
  ...props.initialFilters
});

// Dados das listas
const grupos = ref([]);
const mantenedoras = ref([]);
const instituicoes = ref([]);
const campi = ref([]);
const cursos = ref([]);

// Loading states
const loadingGrupos = ref(false);
const loadingMantenedoras = ref(false);
const loadingInstituicoes = ref(false);
const loadingCampi = ref(false);
const loadingCursos = ref(false);

// ==========================================
// CARREGAR GRUPOS EDUCACIONAIS
// ==========================================
const loadGrupos = async () => {
  if (!props.showGrupo) return;
  
  loadingGrupos.value = true;
  try {
    const response = await axios.get('/api/v1/grupos-educacionais');
    grupos.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar grupos:', error);
  } finally {
    loadingGrupos.value = false;
  }
};

// ==========================================
// CARREGAR MANTENEDORAS
// ==========================================
const loadMantenedoras = async (grupoId) => {
  if (!props.showMantenedora) return;
  
  loadingMantenedoras.value = true;
  try {
    const params = grupoId ? { grupo_educacional_id: grupoId } : {};
    const response = await axios.get('/api/v1/mantenedoras', { params });
    mantenedoras.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar mantenedoras:', error);
  } finally {
    loadingMantenedoras.value = false;
  }
};

// ==========================================
// CARREGAR INSTITUIÇÕES
// ==========================================
const loadInstituicoes = async (mantenedoraId) => {
  if (!props.showInstituicao) return;
  
  loadingInstituicoes.value = true;
  try {
    const params = mantenedoraId ? { mantenedora_id: mantenedoraId } : {};
    const response = await axios.get('/api/v1/instituicoes', { params });
    instituicoes.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  } finally {
    loadingInstituicoes.value = false;
  }
};

// ==========================================
// CARREGAR CAMPI
// ==========================================
const loadCampi = async (instituicaoId) => {
  if (!props.showCampus) return;
  
  loadingCampi.value = true;
  try {
    const params = instituicaoId ? { instituicao_id: instituicaoId } : {};
    const response = await axios.get('/api/v1/campi', { params });
    campi.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  } finally {
    loadingCampi.value = false;
  }
};

// ==========================================
// CARREGAR CURSOS
// ==========================================
const loadCursos = async (campusId) => {
  if (!props.showCurso) return;
  
  loadingCursos.value = true;
  try {
    const params = campusId ? { campus_id: campusId } : {};
    const response = await axios.get('/api/v1/cursos', { params });
    cursos.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar cursos:', error);
  } finally {
    loadingCursos.value = false;
  }
};

// ==========================================
// EVENTOS DE MUDANÇA (CASCATA)
// ==========================================
const onGrupoChange = () => {
  // Limpa filtros dependentes
  filters.value.mantenedora_id = '';
  filters.value.instituicao_id = '';
  filters.value.campus_id = '';
  filters.value.curso_id = '';
  
  mantenedoras.value = [];
  instituicoes.value = [];
  campi.value = [];
  cursos.value = [];

  if (filters.value.grupo_educacional_id) {
    loadMantenedoras(filters.value.grupo_educacional_id);
  }
  
  emitChange();
};

const onMantenedoraChange = () => {
  filters.value.instituicao_id = '';
  filters.value.campus_id = '';
  filters.value.curso_id = '';
  
  instituicoes.value = [];
  campi.value = [];
  cursos.value = [];

  if (filters.value.mantenedora_id) {
    loadInstituicoes(filters.value.mantenedora_id);
  }
  
  emitChange();
};

const onInstituicaoChange = () => {
  filters.value.campus_id = '';
  filters.value.curso_id = '';
  
  campi.value = [];
  cursos.value = [];

  if (filters.value.instituicao_id) {
    loadCampi(filters.value.instituicao_id);
  }
  
  emitChange();
};

const onCampusChange = () => {
  filters.value.curso_id = '';
  cursos.value = [];

  if (filters.value.campus_id) {
    loadCursos(filters.value.campus_id);
  }
  
  emitChange();
};

const onCursoChange = () => {
  emitChange();
};

// ==========================================
// EMITIR MUDANÇAS
// ==========================================
const emitChange = () => {
  emit('filter-change', { ...filters.value });
};

// ==========================================
// LIMPAR TODOS OS FILTROS
// ==========================================
const clearAllFilters = () => {
  filters.value = {
    grupo_educacional_id: '',
    mantenedora_id: '',
    instituicao_id: '',
    campus_id: '',
    curso_id: ''
  };
  
  mantenedoras.value = [];
  instituicoes.value = [];
  campi.value = [];
  cursos.value = [];
  
  emitChange();
};

// ==========================================
// EXPOR MÉTODOS PARA O COMPONENTE PAI
// ==========================================
defineExpose({
  clearAllFilters,
  filters
});

// ==========================================
// INICIALIZAÇÃO
// ==========================================
onMounted(() => {
  loadGrupos();
  
  // Se já tem filtros iniciais, carregar as listas correspondentes
  if (filters.value.grupo_educacional_id) {
    loadMantenedoras(filters.value.grupo_educacional_id);
  }
  if (filters.value.mantenedora_id) {
    loadInstituicoes(filters.value.mantenedora_id);
  }
  if (filters.value.instituicao_id) {
    loadCampi(filters.value.instituicao_id);
  }
  if (filters.value.campus_id) {
    loadCursos(filters.value.campus_id);
  }
});
</script>

<style scoped>
/* Estilos específicos do componente de filtros */
.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-select-glass:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: rgba(255, 255, 255, 0.02);
}

.btn-outline-light {
  transition: all 0.3s ease;
}

.btn-outline-light:hover {
  transform: translateY(-2px);
}
</style>
