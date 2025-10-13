<template>
  <div class="global-search">
    <div class="search-input-wrapper">
      <i class="bi bi-search"></i>
      <input 
        type="text" 
        class="search-input" 
        placeholder="Buscar em todo sistema... (Ctrl+K)"
        v-model="searchQuery"
        @input="handleSearch"
        @focus="showResults = true"
        @keydown.esc="closeSearch"
        ref="searchInput"
      >
      <kbd v-if="!searchQuery" class="search-shortcut">Ctrl+K</kbd>
      <button v-else class="btn-clear" @click="clearSearch">
        <i class="bi bi-x"></i>
      </button>
    </div>

    <!-- Resultados -->
    <transition name="fade">
      <div v-if="showResults && hasResults" class="search-results">
        <div v-for="(items, category) in results" :key="category">
          <div v-if="items.length > 0" class="result-category">
            <h6 class="category-title">{{ formatCategory(category) }}</h6>
            <div 
              v-for="item in items" 
              :key="item.id"
              class="result-item"
              @click="selectResult(item)"
            >
              <div class="result-icon" :class="`icon-${item.type}`">
                <i :class="getIcon(item.type)"></i>
              </div>
              <div class="result-content">
                <div class="result-title">{{ item.title }}</div>
                <div class="result-subtitle">{{ item.subtitle }}</div>
              </div>
              <i class="bi bi-arrow-right result-arrow"></i>
            </div>
          </div>
        </div>

        <div v-if="!hasAnyResults" class="no-results">
          <i class="bi bi-inbox fs-1"></i>
          <p class="mt-2">Nenhum resultado encontrado</p>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <div 
      v-if="showResults && hasResults" 
      class="search-overlay" 
      @click="closeSearch"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const searchQuery = ref('');
const searchInput = ref(null);
const showResults = ref(false);
const results = ref({});
const loading = ref(false);

let debounceTimeout = null;

const hasResults = computed(() => {
  return searchQuery.value.length >= 2;
});

const hasAnyResults = computed(() => {
  return Object.values(results.value).some(items => items.length > 0);
});

const handleSearch = () => {
  clearTimeout(debounceTimeout);
  
  if (searchQuery.value.length < 2) {
    results.value = {};
    return;
  }

  loading.value = true;

  debounceTimeout = setTimeout(async () => {
    try {
      const response = await axios.get('/api/v1/search', {
        params: { q: searchQuery.value }
      });
      results.value = response.data;
    } catch (error) {
      console.error('Erro na busca:', error);
    } finally {
      loading.value = false;
    }
  }, 300);
};

const selectResult = (item) => {
  router.push(item.url);
  closeSearch();
};

const clearSearch = () => {
  searchQuery.value = '';
  results.value = {};
  showResults.value = false;
};

const closeSearch = () => {
  showResults.value = false;
};

const formatCategory = (category) => {
  const labels = {
    'grupos_educacionais': 'Grupos Educacionais',
    'mantenedoras': 'Mantenedoras',
    'instituicoes': 'Instituições',
    'campi': 'Campi',
    'cursos': 'Cursos',
    'disciplinas': 'Disciplinas',
    'colaboradores': 'Colaboradores'
  };
  return labels[category] || category;
};

const getIcon = (type) => {
  const icons = {
    'grupo_educacional': 'bi-diagram-3',
    'mantenedora': 'bi-building',
    'instituicao': 'bi-bank',
    'campus': 'bi-geo-alt',
    'curso': 'bi-mortarboard',
    'disciplina': 'bi-journal-text',
    'colaborador': 'bi-person'
  };
  return icons[type] || 'bi-file-earmark';
};

// Atalho de teclado Ctrl+K
const handleKeydown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    searchInput.value?.focus();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.global-search {
  position: relative;
  width: 100%;
  max-width: 600px;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper > i {
  position: absolute;
  left: 1rem;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1.1rem;
}

.search-input {
  width: 100%;
  padding: 0.75rem 3rem 0.75rem 3rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.75rem;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.95rem;
  transition: all 0.3s;
}

.search-input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(102, 126, 234, 0.5);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-shortcut {
  position: absolute;
  right: 1rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
}

.btn-clear {
  position: absolute;
  right: 1rem;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.btn-clear:hover {
  color: rgba(255, 255, 255, 0.9);
}

.search-results {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: rgba(28, 28, 35, 0.98);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  max-height: 500px;
  overflow-y: auto;
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.result-category {
  padding: 1rem 0;
}

.category-title {
  padding: 0.5rem 1.5rem;
  margin: 0;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.5);
  letter-spacing: 0.05em;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  transition: background 0.2s;
}

.result-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.result-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.icon-grupo_educacional {
  background: rgba(102, 126, 234, 0.2);
  color: #667eea;
}

.icon-mantenedora {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.icon-instituicao {
  background: rgba(0, 123, 255, 0.2);
  color: #007bff;
}

.icon-campus {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.icon-curso {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.icon-disciplina {
  background: rgba(108, 117, 125, 0.2);
  color: #6c757d;
}

.icon-colaborador {
  background: rgba(23, 162, 184, 0.2);
  color: #17a2b8;
}

.result-content {
  flex: 1;
}

.result-title {
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 0.2rem;
}

.result-subtitle {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
}

.result-arrow {
  color: rgba(255, 255, 255, 0.3);
  font-size: 1rem;
}

.no-results {
  text-align: center;
  padding: 3rem 1rem;
  color: rgba(255, 255, 255, 0.5);
}

.search-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
