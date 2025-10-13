<template>
  <div class="view-toggle">
    <button 
      @click="setView('table')"
      class="view-btn"
      :class="{ active: modelValue === 'table' }"
      title="Visualização em Tabela"
    >
      <i class="bi bi-table"></i>
    </button>
    <button 
      @click="setView('cards')"
      class="view-btn"
      :class="{ active: modelValue === 'cards' }"
      title="Visualização em Cards"
    >
      <i class="bi bi-grid-3x3-gap"></i>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: 'table'
  }
});

const emit = defineEmits(['update:modelValue']);

const setView = (view) => {
  emit('update:modelValue', view);
};
</script>

<style scoped>
.view-toggle {
  display: inline-flex;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  padding: 0.25rem;
  gap: 0.25rem;
}

.view-btn {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  padding: 0.5rem 0.875rem;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
}

.view-btn:hover {
  color: rgba(255, 255, 255, 0.9);
  background: rgba(255, 255, 255, 0.05);
}

.view-btn.active {
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
}
</style>
