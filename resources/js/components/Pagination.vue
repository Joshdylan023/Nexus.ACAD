<template>
  <div v-if="totalPages > 1" class="pagination-container">
    <div class="pagination-info">
      Mostrando <strong>{{ startItem }}</strong> a <strong>{{ endItem }}</strong> de <strong>{{ totalItems }}</strong> registros
    </div>
    
    <nav>
      <ul class="pagination pagination-glass mb-0">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(1)" aria-label="Primeira">
            <i class="bi bi-chevron-double-left"></i>
          </a>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)" aria-label="Anterior">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>

        <li 
          v-for="page in visiblePages" 
          :key="page" 
          class="page-item" 
          :class="{ active: page === currentPage }"
        >
          <a class="page-link" href="#" @click.prevent="changePage(page)">
            {{ page }}
          </a>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)" aria-label="Próxima">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(totalPages)" aria-label="Última">
            <i class="bi bi-chevron-double-right"></i>
          </a>
        </li>
      </ul>
    </nav>

    <div class="pagination-perpage">
      <label class="mb-0">Itens por página:</label>
      <select 
        class="form-select-glass"
        :value="perPage"
        @change="changePerPage($event.target.value)"
      >
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalItems: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 25
  },
  maxVisiblePages: {
    type: Number,
    default: 5
  }
});

const emit = defineEmits(['page-changed', 'per-page-changed']);

const totalPages = computed(() => {
  return Math.ceil(props.totalItems / props.perPage);
});

const startItem = computed(() => {
  return props.totalItems === 0 ? 0 : (props.currentPage - 1) * props.perPage + 1;
});

const endItem = computed(() => {
  const end = props.currentPage * props.perPage;
  return end > props.totalItems ? props.totalItems : end;
});

const visiblePages = computed(() => {
  const pages = [];
  const half = Math.floor(props.maxVisiblePages / 2);
  
  let start = Math.max(1, props.currentPage - half);
  let end = Math.min(totalPages.value, start + props.maxVisiblePages - 1);
  
  if (end - start + 1 < props.maxVisiblePages) {
    start = Math.max(1, end - props.maxVisiblePages + 1);
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== props.currentPage) {
    emit('page-changed', page);
  }
};

const changePerPage = (value) => {
  const newPerPage = parseInt(value);
  emit('per-page-changed', newPerPage);
};
</script>

<style scoped>
/* Container principal */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
}

/* Informação de registros */
.pagination-info {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.875rem;
  font-weight: 400;
}

.pagination-info strong {
  color: rgba(255, 255, 255, 0.95);
  font-weight: 600;
}

/* Paginação Glass */
.pagination-glass {
  display: flex;
  gap: 0.25rem;
}

.pagination-glass .page-item {
  list-style: none;
}

.pagination-glass .page-link {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 2.25rem;
  height: 2.25rem;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  text-decoration: none;
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

.pagination-glass .page-link:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

.pagination-glass .page-item.active .page-link {
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: transparent;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  font-weight: 600;
}

.pagination-glass .page-item.disabled .page-link {
  color: rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.02);
  border-color: rgba(255, 255, 255, 0.05);
  cursor: not-allowed;
  pointer-events: none;
}

/* Seletor de itens por página */
.pagination-perpage {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-perpage label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.875rem;
  font-weight: 400;
  white-space: nowrap;
}

.form-select-glass {
  width: 5rem;
  padding: 0.375rem 2rem 0.375rem 0.75rem;
  font-size: 0.875rem;
  color: white;
  background: rgba(255, 255, 255, 0.05) url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba(255,255,255,0.6)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") no-repeat right 0.5rem center/12px 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  backdrop-filter: blur(10px);
  appearance: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.form-select-glass:hover {
  background-color: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.2);
}

.form-select-glass:focus {
  outline: none;
  background-color: rgba(255, 255, 255, 0.1);
  border-color: rgba(102, 126, 234, 0.5);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.form-select-glass option {
  background-color: #1e1e2e;
  color: white;
}

/* Responsividade */
@media (max-width: 768px) {
  .pagination-container {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }

  .pagination-info {
    order: 1;
    text-align: center;
  }

  .pagination-glass {
    order: 2;
  }

  .pagination-perpage {
    order: 3;
  }
}
</style>
