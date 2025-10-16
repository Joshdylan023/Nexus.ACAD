<template>
  <div class="card card-glass mb-4">
    <div class="card-body">
      <h5 class="mb-3">
        <i class="fas fa-filter me-2"></i>Filtros
      </h5>
      
      <div class="row g-3">
        <!-- Módulo -->
        <div class="col-md-3">
          <label class="form-label">Módulo</label>
          <select v-model="localFilters.module" @change="applyFilters" class="form-select">
            <option value="">Todos os Módulos</option>
            <option value="institucional">Institucional</option>
            <option value="pessoas_acessos">Pessoas & Acessos</option>
            <option value="academico">Acadêmico</option>
            <option value="financeiro">Financeiro</option>
            <option value="estagios">Estágios</option>
            <option value="sistema">Sistema</option>
          </select>
        </div>

        <!-- Ação -->
        <div class="col-md-3">
          <label class="form-label">Ação</label>
          <select v-model="localFilters.action" @change="applyFilters" class="form-select">
            <option value="">Todas as Ações</option>
            <option value="created">Criação</option>
            <option value="updated">Atualização</option>
            <option value="deleted">Exclusão</option>
            <option value="restored">Restauração</option>
          </select>
        </div>

        <!-- Data Inicial -->
        <div class="col-md-3">
          <label class="form-label">Data Inicial</label>
          <input 
            type="date" 
            v-model="localFilters.date_from" 
            @change="applyFilters"
            class="form-control"
          />
        </div>

        <!-- Data Final -->
        <div class="col-md-3">
          <label class="form-label">Data Final</label>
          <input 
            type="date" 
            v-model="localFilters.date_to" 
            @change="applyFilters"
            class="form-control"
          />
        </div>
      </div>

      <!-- Busca -->
      <div class="row g-3 mt-2">
        <div class="col-md-10">
          <label class="form-label">Buscar</label>
          <input 
            type="text" 
            v-model="searchTerm" 
            @input="debounceSearch"
            placeholder="Buscar por descrição ou usuário..."
            class="form-control"
          />
        </div>

        <!-- Botão Limpar -->
        <div class="col-md-2 d-flex align-items-end">
          <button @click="clearFilters" class="btn btn-secondary w-100">
            <i class="fas fa-eraser me-2"></i>Limpar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['filter', 'search']);

// ⭐ Estado local dos filtros
const localFilters = ref({
  module: '',
  action: '',
  date_from: '',
  date_to: '',
});

const searchTerm = ref('');
let searchTimeout = null;

// ⭐ Aplicar filtros
const applyFilters = () => {
  emit('filter', { ...localFilters.value });
};

// ⭐ Busca com debounce (aguarda 500ms após parar de digitar)
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit('search', searchTerm.value);
  }, 500);
};

// ⭐ Limpar todos os filtros
const clearFilters = () => {
  localFilters.value = {
    module: '',
    action: '',
    date_from: '',
    date_to: '',
  };
  searchTerm.value = '';
  
  emit('filter', { ...localFilters.value });
  emit('search', '');
};

// ⭐ Inicializar com data atual como sugestão (opcional)
onMounted(() => {
  // Você pode inicializar com alguma data padrão se quiser
  // const today = new Date().toISOString().split('T')[0];
  // localFilters.value.date_to = today;
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.card-body h5 {
  color: white;
  font-weight: 600;
}

.form-label {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-select,
.form-control {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  transition: all 0.3s ease;
}

.form-select:focus,
.form-control:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(102, 126, 234, 0.5);
  color: white;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
}

.form-select option {
  background: #1a1a2e;
  color: white;
}

.btn-secondary {
  background: rgba(108, 117, 125, 0.3);
  border: 1px solid rgba(108, 117, 125, 0.5);
  color: white;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: rgba(108, 117, 125, 0.5);
  border-color: rgba(108, 117, 125, 0.7);
  transform: translateY(-2px);
}

/* Placeholder branco */
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}
</style>
