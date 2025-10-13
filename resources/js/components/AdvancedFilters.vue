<template>
  <div>
    <!-- Botão de Toggle -->
    <button 
      @click="toggle" 
      class="btn btn-filter-toggle"
      :class="{ 'has-filters': activeFiltersCount > 0 }"
    >
      <i class="bi bi-funnel-fill me-2"></i>
      Filtros
      <span v-if="activeFiltersCount > 0" class="badge bg-primary ms-2">
        {{ activeFiltersCount }}
      </span>
    </button>

    <!-- Painel de Filtros -->
    <transition name="slide-fade">
      <div v-if="isOpen" class="filters-panel">
        <div class="filters-header">
          <div class="d-flex align-items-center">
            <i class="bi bi-funnel me-2"></i>
            <h5 class="mb-0">Filtros Avançados</h5>
          </div>
          <button @click="clearAllFilters" class="btn btn-sm btn-clear">
            <i class="bi bi-x-circle me-1"></i>
            Limpar Filtros
          </button>
        </div>

        <div class="filters-body">
          <slot></slot>
        </div>

        <div class="filters-footer">
          <div class="filter-count" v-if="activeFiltersCount > 0">
            <span class="badge-filters">
              {{ activeFiltersCount }} filtro{{ activeFiltersCount > 1 ? 's' : '' }} ativo{{ activeFiltersCount > 1 ? 's' : '' }}
            </span>
          </div>
          <button @click="close" class="btn btn-sm btn-apply">
            <i class="bi bi-check-lg me-1"></i>
            Aplicar Filtros
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  activeFiltersCount: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['update:modelValue', 'clear']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const toggle = () => {
  isOpen.value = !isOpen.value;
};

const close = () => {
  isOpen.value = false;
};

const clearAllFilters = () => {
  emit('clear');
};
</script>

<style scoped>
/* Botão de Toggle */
.btn-filter-toggle {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  font-size: 0.875rem;
  font-weight: 500;
}

.btn-filter-toggle:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  color: white;
  transform: translateY(-1px);
}

.btn-filter-toggle.has-filters {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
  border-color: rgba(102, 126, 234, 0.5);
  color: white;
}

.btn-filter-toggle .badge {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

/* Painel de Filtros - Estilo Liquid Glass */
.filters-panel {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 0;
  margin-top: 1rem;
  margin-bottom: 1.5rem;
  overflow: hidden;
  box-shadow: 
    0 8px 32px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* Header do Painel */
.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.filters-header h5 {
  color: white;
  font-weight: 600;
  font-size: 1rem;
  margin: 0;
}

.filters-header i {
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.1rem;
}

/* Botão Limpar */
.btn-clear {
  background: rgba(220, 53, 69, 0.1);
  border: 1px solid rgba(220, 53, 69, 0.3);
  color: #dc3545;
  padding: 0.4rem 0.9rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  transition: all 0.2s ease;
}

.btn-clear:hover {
  background: rgba(220, 53, 69, 0.2);
  border-color: rgba(220, 53, 69, 0.5);
  color: #ff4757;
  transform: translateY(-1px);
}

/* Body do Painel */
.filters-body {
  padding: 1.5rem;
}

.filters-body .form-label {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.filters-body .form-control,
.filters-body .form-select {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  padding: 0.625rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.filters-body .form-control:focus,
.filters-body .form-select:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(102, 126, 234, 0.5);
  color: white;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
  outline: none;
}

.filters-body .form-control::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.filters-body .form-select option {
  background-color: #1e1e2e;
  color: white;
}

/* Footer do Painel */
.filters-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.filter-count {
  display: flex;
  align-items: center;
}

.badge-filters {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* Botão Aplicar */
.btn-apply {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  padding: 0.5rem 1.25rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  transition: all 0.2s ease;
}

.btn-apply:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

/* Animações */
.slide-fade-enter-active {
  animation: slideDown 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.slide-fade-leave-active {
  animation: slideUp 0.2s cubic-bezier(0.55, 0.085, 0.68, 0.53);
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
  }
  to {
    opacity: 1;
    transform: translateY(0);
    max-height: 500px;
  }
}

@keyframes slideUp {
  from {
    opacity: 1;
    transform: translateY(0);
    max-height: 500px;
  }
  to {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
  }
}

/* Responsividade */
@media (max-width: 768px) {
  .filters-panel {
    border-radius: 0.75rem;
  }

  .filters-body {
    padding: 1rem;
  }

  .filters-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .btn-apply {
    width: 100%;
  }
}
</style>
