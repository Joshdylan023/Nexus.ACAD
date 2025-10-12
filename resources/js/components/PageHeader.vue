<template>
  <div class="mb-4">
    <!-- Breadcrumbs com Glass Effect -->
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb breadcrumb-glass mb-0 px-3 py-2">
        <li class="breadcrumb-item">
          <router-link :to="moduleBasePath" class="text-white-50 text-decoration-none">
            <i class="bi bi-house-door"></i>
          </router-link>
        </li>
        <li v-for="(crumb, index) in breadcrumbs" 
            :key="index" 
            class="breadcrumb-item"
            :class="{ 'active text-white': index === breadcrumbs.length - 1 }">
          <router-link 
            v-if="index < breadcrumbs.length - 1 && crumb.path" 
            :to="crumb.path" 
            class="text-white-50 text-decoration-none">
            {{ crumb.label }}
          </router-link>
          <span v-else>{{ crumb.label }}</span>
        </li>
      </ol>
    </nav>

    <!-- Cabeçalho com Título e Ações -->
    <div class="d-flex justify-content-between align-items-center gap-3">
      <h2 class="text-white mb-0">{{ title }}</h2>
      
      <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">
        <!-- Busca Rápida com Glass Effect -->
        <div v-if="showSearch" class="search-glass-container" style="max-width: 350px; width: 100%;">
          <i class="bi bi-search search-icon"></i>
          <input 
            type="text" 
            class="form-control form-control-glass"
            :placeholder="searchPlaceholder"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
          >
        </div>

        <!-- Slot para botões de ação -->
        <slot name="actions"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  moduleBasePath: {
    type: String,
    default: '/admin/institucional'
  },
  showSearch: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Buscar...'
  },
  modelValue: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
/* Breadcrumb Glass Effect */
.breadcrumb-glass {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.breadcrumb-item + .breadcrumb-item::before {
  color: rgba(255, 255, 255, 0.4);
  content: "›";
  font-size: 1.2em;
  padding: 0 0.5rem;
}

.breadcrumb-item a:hover {
  color: rgba(255, 255, 255, 0.9) !important;
  transition: color 0.2s ease;
}

/* Search Glass Container */
.search-glass-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: rgba(255, 255, 255, 0.5);
  pointer-events: none;
  z-index: 1;
}

/* Form Control Glass Effect */
.form-control-glass {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: #fff;
  padding: 0.5rem 0.75rem 0.5rem 2.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.form-control-glass::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.form-control-glass:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 3px rgba(255, 255, 255, 0.05);
  outline: none;
  color: #fff;
}

.form-control-glass:hover {
  border-color: rgba(255, 255, 255, 0.15);
}

/* Remove autofill background amarelo */
.form-control-glass:-webkit-autofill,
.form-control-glass:-webkit-autofill:hover,
.form-control-glass:-webkit-autofill:focus {
  -webkit-text-fill-color: #fff;
  -webkit-box-shadow: 0 0 0 1000px rgba(255, 255, 255, 0.05) inset;
  transition: background-color 5000s ease-in-out 0s;
}
</style>
