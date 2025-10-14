<template>
  <!-- ⭐ OVERLAY DE FUNDO -->
  <Transition name="fade">
    <div v-if="show" class="filters-overlay" @click="$emit('close')"></div>
  </Transition>

  <!-- ⭐ PAINEL LATERAL -->
  <Transition name="slide-fade">
    <div v-if="show" class="advanced-filters-panel" @click.stop>
      <div class="filters-header">
        <h6 class="mb-0">
          <i class="bi bi-funnel me-2"></i>
          Filtros Avançados
        </h6>
        <button @click="$emit('close')" class="btn-close btn-close-white"></button>
      </div>

      <div class="filters-body">
        <!-- Filtros Dinâmicos -->
        <div v-for="filter in filters" :key="filter.key" class="filter-group">
          <label class="filter-label">{{ filter.label }}</label>

          <!-- SELECT SIMPLES -->
          <select
            v-if="filter.type === 'select'"
            class="form-select form-select-sm"
            :value="modelValue[filter.key]"
            @change="handleChange(filter.key, $event.target.value)"
          >
            <option value="">Todos</option>
            <option v-for="option in filter.options" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>

          <!-- MULTI-SELECT (CHECKBOXES) -->
          <div v-else-if="filter.type === 'multi-select'" class="checkbox-group">
            <div v-for="option in filter.options" :key="option.value" class="form-check">
              <input
                type="checkbox"
                class="form-check-input"
                :id="`${filter.key}-${option.value}`"
                :value="option.value"
                :checked="(modelValue[filter.key] || []).includes(option.value)"
                @change="handleMultiSelectChange(filter.key, option.value, $event.target.checked)"
              >
              <label class="form-check-label" :for="`${filter.key}-${option.value}`">
                {{ option.label }}
              </label>
            </div>
          </div>

          <!-- INPUT TEXTO -->
          <input
            v-else-if="filter.type === 'text'"
            type="text"
            class="form-control form-control-sm"
            :placeholder="filter.placeholder || 'Filtrar...'"
            :value="modelValue[filter.key]"
            @input="handleChange(filter.key, $event.target.value)"
          >

          <!-- RANGE (MIN-MAX) -->
          <div v-else-if="filter.type === 'range'" class="range-inputs">
            <input
              type="number"
              class="form-control form-control-sm"
              placeholder="Min"
              :value="modelValue[filter.key]?.min"
              @input="handleRangeChange(filter.key, 'min', $event.target.value)"
            >
            <span class="range-separator">até</span>
            <input
              type="number"
              class="form-control form-control-sm"
              placeholder="Max"
              :value="modelValue[filter.key]?.max"
              @input="handleRangeChange(filter.key, 'max', $event.target.value)"
            >
          </div>
        </div>
      </div>

      <div class="filters-footer">
        <button @click="$emit('clear-all')" class="btn btn-sm btn-danger-glass w-100">
          <i class="bi bi-x-circle me-2"></i>
          Limpar Filtros
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  filters: Array,
  modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'close', 'clear-all']);

const handleChange = (key, value) => {
  emit('update:modelValue', { ...props.modelValue, [key]: value });
};

const handleMultiSelectChange = (key, value, checked) => {
  const current = props.modelValue[key] || [];
  let updated;

  if (checked) {
    updated = [...current, value];
  } else {
    updated = current.filter(v => v !== value);
  }

  emit('update:modelValue', { ...props.modelValue, [key]: updated });
};

const handleRangeChange = (key, type, value) => {
  const current = props.modelValue[key] || {};
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: { ...current, [type]: value }
  });
};
</script>

<style scoped>
/* ⭐ OVERLAY DE FUNDO */
.filters-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 999;
}

/* ⭐ PAINEL LATERAL COM LIQUID GLASS */
.advanced-filters-panel {
  position: fixed;
  top: 0;
  right: 0;
  width: 380px;
  height: 100vh;
  
  /* LIQUID GLASS EFFECT */
  background: rgba(28, 28, 35, 0.85);
  backdrop-filter: blur(40px) saturate(180%);
  -webkit-backdrop-filter: blur(40px) saturate(180%);
  
  border-left: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 
    -8px 0 40px rgba(0, 0, 0, 0.5),
    inset 1px 0 1px rgba(255, 255, 255, 0.08);
  
  z-index: 1001;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Gradient overlay sutil */
.advanced-filters-panel::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 250px;
  background: radial-gradient(
    circle at top right,
    rgba(102, 126, 234, 0.12) 0%,
    transparent 70%
  );
  pointer-events: none;
  z-index: 0;
}

.filters-header {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.75rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(
    to bottom,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.02) 100%
  );
}

.filters-header h6 {
  color: rgba(255, 255, 255, 0.95);
  font-weight: 600;
  font-size: 1.125rem;
  letter-spacing: -0.02em;
}

.filters-header i {
  color: rgba(102, 126, 234, 1);
  filter: drop-shadow(0 0 8px rgba(102, 126, 234, 0.4));
}

.btn-close-white {
  opacity: 0.6;
  transition: all 0.3s ease;
  filter: brightness(1.2);
}

.btn-close-white:hover {
  opacity: 1;
  transform: rotate(90deg) scale(1.1);
}

.filters-body {
  position: relative;
  z-index: 1;
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 1.5rem;
}

.filter-group {
  margin-bottom: 2rem;
  position: relative;
}

.filter-label {
  display: block;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* ⭐ SELECT COM LIQUID GLASS */
.form-select,
.form-control {
  background: rgba(255, 255, 255, 0.08) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: rgba(255, 255, 255, 0.95) !important;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 
    0 2px 8px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.form-select:focus,
.form-control:focus {
  background: rgba(102, 126, 234, 0.12) !important;
  border-color: rgba(102, 126, 234, 0.6);
  box-shadow: 
    0 0 0 4px rgba(102, 126, 234, 0.15),
    0 4px 16px rgba(102, 126, 234, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
  outline: none;
}

.form-select option {
  background: #1c1c23;
  color: rgba(255, 255, 255, 0.95);
  padding: 0.75rem;
}

/* ⭐ CHECKBOXES COM LIQUID GLASS */
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(10px);
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.form-check {
  padding-left: 2.25rem;
  position: relative;
  min-height: 1.5rem;
}

.form-check-input {
  position: absolute;
  left: 0;
  top: 0;
  width: 1.35rem;
  height: 1.35rem;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.25);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 
    0 2px 8px rgba(0, 0, 0, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.12);
  margin: 0;
}

.form-check-input:checked {
  background: linear-gradient(
    135deg,
    rgba(102, 126, 234, 1) 0%,
    rgba(102, 126, 234, 0.8) 100%
  );
  border-color: rgba(102, 126, 234, 1);
  box-shadow: 
    0 0 0 4px rgba(102, 126, 234, 0.2),
    0 4px 16px rgba(102, 126, 234, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

.form-check-input:hover {
  border-color: rgba(102, 126, 234, 0.6);
  transform: scale(1.08);
}

.form-check-label {
  color: rgba(255, 255, 255, 0.88);
  font-size: 0.9rem;
  cursor: pointer;
  transition: color 0.2s ease;
  user-select: none;
  line-height: 1.5rem;
}

.form-check-input:checked + .form-check-label {
  color: rgba(255, 255, 255, 1);
  font-weight: 500;
}

/* RANGE INPUTS */
.range-inputs {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.range-separator {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.875rem;
  font-weight: 600;
}

/* ⭐ FOOTER COM LIQUID GLASS */
.filters-footer {
  position: relative;
  z-index: 1;
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(
    to top,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.02) 100%
  );
}

.btn-danger-glass {
  background: rgba(220, 53, 69, 0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(220, 53, 69, 0.35);
  color: rgba(255, 255, 255, 0.95);
  font-weight: 600;
  padding: 0.875rem;
  border-radius: 10px;
  transition: all 0.3s ease;
  box-shadow: 
    0 2px 12px rgba(220, 53, 69, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

.btn-danger-glass:hover {
  background: rgba(220, 53, 69, 0.25);
  border-color: rgba(220, 53, 69, 0.6);
  transform: translateY(-3px);
  box-shadow: 
    0 6px 20px rgba(220, 53, 69, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
  color: #fff;
}

/* ⭐ ANIMAÇÕES */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
}

.slide-fade-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* ⭐ SCROLLBAR LIQUID GLASS */
.filters-body::-webkit-scrollbar {
  width: 6px;
}

.filters-body::-webkit-scrollbar-track {
  background: transparent;
}

.filters-body::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.3);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.filters-body::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.5);
}

/* RESPONSIVO */
@media (max-width: 768px) {
  .advanced-filters-panel {
    width: 100%;
    max-width: 400px;
  }
}
</style>
