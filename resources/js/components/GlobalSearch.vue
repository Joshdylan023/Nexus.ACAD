<template>
  <!-- Overlay -->
  <Transition name="fade">
    <div v-if="isOpen" class="global-search-overlay" @click="close"></div>
  </Transition>

  <!-- Modal -->
  <Transition name="slide-up">
    <div v-if="isOpen" class="global-search-modal" @click.stop>
      <!-- Input de Busca -->
      <div class="search-input-wrapper">
        <i class="bi bi-search search-icon"></i>
        <input
          ref="searchInput"
          type="text"
          class="search-input"
          placeholder="Buscar em todo sistema..."
          v-model="searchQuery"
          @input="handleSearch"
          @keydown.esc="close"
          @keydown.down.prevent="navigateDown"
          @keydown.up.prevent="navigateUp"
          @keydown.enter.prevent="selectResult"
        >
        <kbd class="search-kbd">ESC</kbd>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="search-loading">
        <div class="spinner-border spinner-border-sm me-2"></div>
        Buscando...
      </div>

      <!-- Resultados -->
      <div v-else-if="hasResults" class="search-results">
        <!-- Grupos -->
        <div v-if="results.grupos?.length > 0" class="result-group">
          <div class="result-group-title">
            <i class="bi bi-collection me-2"></i>
            GRUPOS EDUCACIONAIS ({{ results.grupos.length }})
          </div>
          <div
            v-for="(item, index) in results.grupos"
            :key="`grupo-${item.id}`"
            class="result-item"
            :class="{ 'active': selectedIndex === getGlobalIndex('grupos', index) }"
            @click="showDetail('grupos', item)"
            @mouseenter="selectedIndex = getGlobalIndex('grupos', index)"
          >
            <i class="bi bi-collection result-icon"></i>
            <div class="result-content">
              <div class="result-title">{{ item.nome }}</div>
              <div class="result-subtitle">{{ formatCnpj(item.cnpj) }}</div>
            </div>
            <i class="bi bi-arrow-right result-arrow"></i>
          </div>
        </div>

        <!-- Mantenedoras -->
        <div v-if="results.mantenedoras?.length > 0" class="result-group">
          <div class="result-group-title">
            <i class="bi bi-building me-2"></i>
            MANTENEDORAS ({{ results.mantenedoras.length }})
          </div>
          <div
            v-for="(item, index) in results.mantenedoras"
            :key="`mantenedora-${item.id}`"
            class="result-item"
            :class="{ 'active': selectedIndex === getGlobalIndex('mantenedoras', index) }"
            @click="showDetail('mantenedoras', item)"
            @mouseenter="selectedIndex = getGlobalIndex('mantenedoras', index)"
          >
            <i class="bi bi-building result-icon"></i>
            <div class="result-content">
              <div class="result-title">{{ item.razao_social }}</div>
              <div class="result-subtitle">{{ item.nome_fantasia || 'Sem nome fantasia' }}</div>
            </div>
            <i class="bi bi-arrow-right result-arrow"></i>
          </div>
        </div>

        <!-- Instituições -->
        <div v-if="results.instituicoes?.length > 0" class="result-group">
          <div class="result-group-title">
            <i class="bi bi-bank me-2"></i>
            INSTITUIÇÕES ({{ results.instituicoes.length }})
          </div>
          <div
            v-for="(item, index) in results.instituicoes"
            :key="`instituicao-${item.id}`"
            class="result-item"
            :class="{ 'active': selectedIndex === getGlobalIndex('instituicoes', index) }"
            @click="showDetail('instituicoes', item)"
            @mouseenter="selectedIndex = getGlobalIndex('instituicoes', index)"
          >
            <i class="bi bi-bank result-icon"></i>
            <div class="result-content">
              <div class="result-title">{{ item.razao_social }}</div>
              <div class="result-subtitle">{{ item.cidade }}/{{ item.estado }}</div>
            </div>
            <i class="bi bi-arrow-right result-arrow"></i>
          </div>
        </div>

        <!-- Campi -->
        <div v-if="results.campi?.length > 0" class="result-group">
          <div class="result-group-title">
            <i class="bi bi-pin-map me-2"></i>
            CAMPI ({{ results.campi.length }})
          </div>
          <div
            v-for="(item, index) in results.campi"
            :key="`campus-${item.id}`"
            class="result-item"
            :class="{ 'active': selectedIndex === getGlobalIndex('campi', index) }"
            @click="showDetail('campi', item)"
            @mouseenter="selectedIndex = getGlobalIndex('campi', index)"
          >
            <i class="bi bi-pin-map result-icon"></i>
            <div class="result-content">
              <div class="result-title">{{ item.nome }}</div>
              <div class="result-subtitle">{{ item.endereco_completo }}</div>
            </div>
            <i class="bi bi-arrow-right result-arrow"></i>
          </div>
        </div>

        <!-- Setores -->
        <div v-if="results.setores?.length > 0" class="result-group">
          <div class="result-group-title">
            <i class="bi bi-diagram-3 me-2"></i>
            SETORES ({{ results.setores.length }})
          </div>
          <div
            v-for="(item, index) in results.setores"
            :key="`setor-${item.id}`"
            class="result-item"
            :class="{ 'active': selectedIndex === getGlobalIndex('setores', index) }"
            @click="showDetail('setores', item)"
            @mouseenter="selectedIndex = getGlobalIndex('setores', index)"
          >
            <i class="bi bi-diagram-3 result-icon"></i>
            <div class="result-content">
              <div class="result-title">{{ item.nome }}</div>
              <div class="result-subtitle">Setor</div>
            </div>
            <i class="bi bi-arrow-right result-arrow"></i>
          </div>
        </div>

        <!-- Usuários -->
        <div v-if="results.users?.length > 0" class="result-group">
          <div class="result-group-title">
            <i class="bi bi-person me-2"></i>
            USUÁRIOS ({{ results.users.length }})
          </div>
          <div
            v-for="(item, index) in results.users"
            :key="`user-${item.id}`"
            class="result-item"
            :class="{ 'active': selectedIndex === getGlobalIndex('users', index) }"
            @click="showDetail('users', item)"
            @mouseenter="selectedIndex = getGlobalIndex('users', index)"
          >
            <i class="bi bi-person result-icon"></i>
            <div class="result-content">
              <div class="result-title">{{ item.name }}</div>
              <div class="result-subtitle">{{ item.email }}</div>
            </div>
            <i class="bi bi-arrow-right result-arrow"></i>
          </div>
        </div>
      </div>

      <!-- Sem Resultados -->
      <div v-else-if="searchQuery && !loading" class="search-empty">
        <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
        <p class="mt-3 mb-0">Nenhum resultado encontrado para "{{ searchQuery }}"</p>
      </div>

      <!-- Estado Inicial -->
      <div v-else class="search-empty">
        <i class="bi bi-lightning" style="font-size: 3rem; opacity: 0.3;"></i>
        <p class="mt-3 mb-0">Digite para buscar em todo o sistema</p>
        <p class="text-muted" style="font-size: 0.875rem;">Grupos, Instituições, Campi, Usuários...</p>
      </div>

      <!-- Footer -->
      <div class="search-footer">
        <div class="search-hints">
          <span><kbd>↑</kbd><kbd>↓</kbd> Navegar</span>
          <span><kbd>↵</kbd> Selecionar</span>
          <span><kbd>ESC</kbd> Fechar</span>
        </div>
      </div>
    </div>
  </Transition>

  <!-- ⭐ MODAL DE DETALHES -->
  <Transition name="fade">
    <div v-if="detailModal.open" class="detail-modal-wrapper">
      <div class="detail-modal-overlay" @click="closeDetail"></div>
      <div class="detail-modal-content">
        <button class="detail-modal-close" @click="closeDetail">
          <i class="bi bi-x-lg"></i>
        </button>

        <!-- DETALHES DE GRUPOS -->
        <div v-if="detailModal.type === 'grupos'" class="detail-content">
          <div class="detail-header">
            <i class="bi bi-collection detail-icon"></i>
            <div>
              <h4 class="detail-title">{{ detailModal.item.nome }}</h4>
              <span class="badge bg-primary">Grupo Educacional</span>
            </div>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <span class="detail-label">CNPJ:</span>
              <span class="detail-value">{{ formatCnpj(detailModal.item.cnpj) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">ID:</span>
              <span class="detail-value">#{{ detailModal.item.id }}</span>
            </div>
          </div>
          <div class="detail-actions">
            <button @click="irParaListagem('grupos', detailModal.item)" class="btn btn-outline-light btn-sm me-2">
              <i class="bi bi-list-ul me-2"></i>Ver na listagem
            </button>
            <button @click="editarItem('grupos', detailModal.item)" class="btn btn-primary btn-sm">
              <i class="bi bi-pencil me-2"></i>Editar
            </button>
          </div>
        </div>

        <!-- DETALHES DE MANTENEDORAS -->
        <div v-if="detailModal.type === 'mantenedoras'" class="detail-content">
          <div class="detail-header">
            <i class="bi bi-building detail-icon"></i>
            <div>
              <h4 class="detail-title">{{ detailModal.item.razao_social }}</h4>
              <span class="badge bg-success">Mantenedora</span>
            </div>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <span class="detail-label">Nome Fantasia:</span>
              <span class="detail-value">{{ detailModal.item.nome_fantasia || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">CNPJ:</span>
              <span class="detail-value">{{ formatCnpj(detailModal.item.cnpj) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">ID:</span>
              <span class="detail-value">#{{ detailModal.item.id }}</span>
            </div>
          </div>
          <div class="detail-actions">
            <button @click="irParaListagem('mantenedoras', detailModal.item)" class="btn btn-outline-light btn-sm me-2">
              <i class="bi bi-list-ul me-2"></i>Ver na listagem
            </button>
            <button @click="editarItem('mantenedoras', detailModal.item)" class="btn btn-primary btn-sm">
              <i class="bi bi-pencil me-2"></i>Editar
            </button>
          </div>
        </div>

        <!-- DETALHES DE INSTITUIÇÕES -->
        <div v-if="detailModal.type === 'instituicoes'" class="detail-content">
          <div class="detail-header">
            <i class="bi bi-bank detail-icon"></i>
            <div>
              <h4 class="detail-title">{{ detailModal.item.razao_social }}</h4>
              <span class="badge bg-info">Instituição</span>
            </div>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <span class="detail-label">Nome Fantasia:</span>
              <span class="detail-value">{{ detailModal.item.nome_fantasia || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Sigla:</span>
              <span class="detail-value">{{ detailModal.item.sigla || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Localização:</span>
              <span class="detail-value">{{ detailModal.item.cidade || 'N/A' }}/{{ detailModal.item.estado || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">ID:</span>
              <span class="detail-value">#{{ detailModal.item.id }}</span>
            </div>
          </div>
          <div class="detail-actions">
            <button @click="irParaListagem('instituicoes', detailModal.item)" class="btn btn-outline-light btn-sm me-2">
              <i class="bi bi-list-ul me-2"></i>Ver na listagem
            </button>
            <button @click="editarItem('instituicoes', detailModal.item)" class="btn btn-primary btn-sm">
              <i class="bi bi-pencil me-2"></i>Editar
            </button>
          </div>
        </div>

        <!-- DETALHES DE CAMPI -->
        <div v-if="detailModal.type === 'campi'" class="detail-content">
          <div class="detail-header">
            <i class="bi bi-pin-map detail-icon"></i>
            <div>
              <h4 class="detail-title">{{ detailModal.item.nome }}</h4>
              <span class="badge bg-warning text-dark">Campus</span>
            </div>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <span class="detail-label">Endereço:</span>
              <span class="detail-value">{{ detailModal.item.endereco_completo || 'N/A' }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">ID:</span>
              <span class="detail-value">#{{ detailModal.item.id }}</span>
            </div>
          </div>
          <div class="detail-actions">
            <button @click="irParaListagem('campi', detailModal.item)" class="btn btn-outline-light btn-sm me-2">
              <i class="bi bi-list-ul me-2"></i>Ver na listagem
            </button>
            <button @click="editarItem('campi', detailModal.item)" class="btn btn-primary btn-sm">
              <i class="bi bi-pencil me-2"></i>Editar
            </button>
          </div>
        </div>

        <!-- DETALHES DE SETORES -->
        <div v-if="detailModal.type === 'setores'" class="detail-content">
          <div class="detail-header">
            <i class="bi bi-diagram-3 detail-icon"></i>
            <div>
              <h4 class="detail-title">{{ detailModal.item.nome }}</h4>
              <span class="badge bg-secondary">Setor</span>
            </div>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <span class="detail-label">ID:</span>
              <span class="detail-value">#{{ detailModal.item.id }}</span>
            </div>
          </div>
          <div class="detail-actions">
            <button @click="irParaListagem('setores', detailModal.item)" class="btn btn-outline-light btn-sm me-2">
              <i class="bi bi-list-ul me-2"></i>Ver na listagem
            </button>
          </div>
        </div>

        <!-- DETALHES DE USUÁRIOS -->
        <div v-if="detailModal.type === 'users'" class="detail-content">
          <div class="detail-header">
            <i class="bi bi-person detail-icon"></i>
            <div>
              <h4 class="detail-title">{{ detailModal.item.name }}</h4>
              <span class="badge bg-dark">Usuário</span>
            </div>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <span class="detail-label">Email:</span>
              <span class="detail-value">{{ detailModal.item.email }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">ID:</span>
              <span class="detail-value">#{{ detailModal.item.id }}</span>
            </div>
          </div>
          <div class="detail-actions">
            <button @click="irParaListagem('users', detailModal.item)" class="btn btn-outline-light btn-sm me-2">
              <i class="bi bi-list-ul me-2"></i>Ver perfil
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const isOpen = ref(false);
const searchQuery = ref('');
const results = ref({});
const loading = ref(false);
const selectedIndex = ref(0);
const searchInput = ref(null);
let searchTimeout = null;

// ⭐ MODAL DE DETALHES
const detailModal = ref({
  open: false,
  type: null,
  item: null,
});

const hasResults = computed(() => {
  return Object.values(results.value).some(arr => arr?.length > 0);
});

const totalResults = computed(() => {
  let count = 0;
  Object.values(results.value).forEach(arr => {
    if (arr?.length) count += arr.length;
  });
  return count;
});

const getGlobalIndex = (type, index) => {
  let globalIndex = 0;
  const order = ['grupos', 'mantenedoras', 'instituicoes', 'campi', 'setores', 'users'];
  
  for (const t of order) {
    if (t === type) {
      return globalIndex + index;
    }
    globalIndex += results.value[t]?.length || 0;
  }
  return globalIndex;
};

const open = () => {
  isOpen.value = true;
  nextTick(() => {
    searchInput.value?.focus();
  });
};

const close = () => {
  isOpen.value = false;
  searchQuery.value = '';
  results.value = {};
  selectedIndex.value = 0;
};

// ⭐ MOSTRAR MODAL DE DETALHES
const showDetail = (type, item) => {
  detailModal.value = {
    open: true,
    type: type,
    item: item
  };
};

const closeDetail = () => {
  detailModal.value = {
    open: false,
    type: null,
    item: null
  };
};

// ⭐ IR PARA LISTAGEM
const irParaListagem = (type, item) => {
  const routes = {
    grupos: `/admin/institucional/grupos-educacionais`,
    mantenedoras: `/admin/institucional/mantenedoras`,
    instituicoes: `/admin/institucional/instituicoes`,
    campi: `/admin/institucional/campi`,
    setores: `/admin/institucional/grupos-educacionais`,
    users: `/admin/pessoas-acessos/colaboradores`,
  };
  
  router.push({ path: routes[type], query: { highlight: item.id } });
  closeDetail();
  close();
};

// ⭐ EDITAR ITEM (ABRE FORMULÁRIO NA LISTAGEM)
const editarItem = (type, item) => {
  router.push({ 
    path: {
      grupos: `/admin/institucional/grupos-educacionais`,
      mantenedoras: `/admin/institucional/mantenedoras`,
      instituicoes: `/admin/institucional/instituicoes`,
      campi: `/admin/institucional/campi`,
    }[type],
    query: { edit: item.id }
  });
  closeDetail();
  close();
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  
  if (searchQuery.value.length < 2) {
    results.value = {};
    return;
  }

  loading.value = true;
  
  searchTimeout = setTimeout(async () => {
    try {
      const response = await axios.get('/api/v1/search', {
        params: { q: searchQuery.value }
      });
      results.value = response.data;
      selectedIndex.value = 0;
    } catch (error) {
      console.error('Erro na busca global:', error);
    } finally {
      loading.value = false;
    }
  }, 300);
};

const navigateDown = () => {
  if (selectedIndex.value < totalResults.value - 1) {
    selectedIndex.value++;
  }
};

const navigateUp = () => {
  if (selectedIndex.value > 0) {
    selectedIndex.value--;
  }
};

const selectResult = () => {
  let currentIndex = 0;
  const order = ['grupos', 'mantenedoras', 'instituicoes', 'campi', 'setores', 'users'];
  
  for (const type of order) {
    const items = results.value[type] || [];
    if (selectedIndex.value < currentIndex + items.length) {
      const item = items[selectedIndex.value - currentIndex];
      showDetail(type, item);
      return;
    }
    currentIndex += items.length;
  }
};

const formatCnpj = (cnpj) => {
  if (!cnpj) return '';
  const cleaned = cnpj.replace(/\D/g, '');
  if (cleaned.length !== 14) return cnpj;
  return cleaned.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
};

const handleKeyDown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    if (isOpen.value) {
      close();
    } else {
      open();
    }
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

defineExpose({ open, close });
</script>

<style scoped>
/* Overlay */
.global-search-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  z-index: 9998;
}

/* Modal */
.global-search-modal {
  position: fixed;
  top: 15%;
  left: 50%;
  transform: translateX(-50%);
  width: 90%;
  max-width: 700px;
  max-height: 70vh;
  
  background: rgba(28, 28, 35, 0.95);
  backdrop-filter: blur(40px) saturate(180%);
  -webkit-backdrop-filter: blur(40px) saturate(180%);
  
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  box-shadow: 
    0 24px 48px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  
  z-index: 9999;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Input de Busca */
.search-input-wrapper {
  position: relative;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-icon {
  color: rgba(102, 126, 234, 0.9);
  font-size: 1.5rem;
}

.search-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: rgba(255, 255, 255, 0.95);
  font-size: 1.125rem;
  font-weight: 500;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.search-kbd {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  font-family: monospace;
}

/* Loading */
.search-loading {
  padding: 2rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
}

/* Resultados */
.search-results {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem;
}

.result-group {
  margin-bottom: 1rem;
}

.result-group-title {
  padding: 0.75rem 1rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  margin: 0.25rem 0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.result-item:hover,
.result-item.active {
  background: rgba(102, 126, 234, 0.15);
}

.result-icon {
  color: rgba(102, 126, 234, 0.9);
  font-size: 1.25rem;
}

.result-content {
  flex: 1;
}

.result-title {
  color: rgba(255, 255, 255, 0.95);
  font-weight: 500;
  font-size: 0.95rem;
}

.result-subtitle {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.8rem;
  margin-top: 0.125rem;
}

.result-arrow {
  color: rgba(255, 255, 255, 0.3);
  font-size: 0.875rem;
}

/* Empty State */
.search-empty {
  padding: 3rem 2rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
}

/* Footer */
.search-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.2);
}

.search-hints {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.search-hints kbd {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 3px;
  padding: 0.125rem 0.375rem;
  margin: 0 0.125rem;
  font-family: monospace;
}

/* Scrollbar */
.search-results::-webkit-scrollbar {
  width: 6px;
}

.search-results::-webkit-scrollbar-track {
  background: transparent;
}

.search-results::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.3);
  border-radius: 10px;
}

/* ⭐ MODAL DE DETALHES */
.detail-modal-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.detail-modal-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
}

.detail-modal-content {
  position: relative;
  z-index: 10001;
  width: 90%;
  max-width: 600px;
  
  background: rgba(28, 28, 35, 0.98);
  backdrop-filter: blur(40px) saturate(180%);
  -webkit-backdrop-filter: blur(40px) saturate(180%);
  
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  box-shadow: 
    0 24px 48px rgba(0, 0, 0, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  
  padding: 2rem;
}

.detail-modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.detail-modal-close:hover {
  background: rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 1);
}

.detail-content {
  color: rgba(255, 255, 255, 0.9);
}

.detail-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-icon {
  font-size: 3rem;
  color: rgba(102, 126, 234, 0.9);
}

.detail-title {
  color: rgba(255, 255, 255, 0.95);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.detail-body {
  margin-bottom: 2rem;
}

.detail-row {
  display: flex;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-label {
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  min-width: 150px;
}

.detail-value {
  color: rgba(255, 255, 255, 0.9);
  flex: 1;
}

.detail-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

/* Animações */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
}

.slide-up-enter-from {
  transform: translateX(-50%) translateY(-20px);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateX(-50%) translateY(-20px);
  opacity: 0;
}
</style>
