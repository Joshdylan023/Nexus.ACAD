<template>
  <div class="container-fluid">
    <!-- ⭐ HEADER COM BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 text-white">{{ title }}</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <router-link to="/admin/dashboard">Dashboard</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/admin/academico">Gestão Acadêmica</router-link>
            </li>
            <li 
              v-for="(crumb, index) in breadcrumbs" 
              :key="index" 
              class="breadcrumb-item" 
              :class="{ active: index === breadcrumbs.length - 1 }"
            >
              <router-link v-if="crumb.path && index !== breadcrumbs.length - 1" :to="crumb.path">
                {{ crumb.label }}
              </router-link>
              <span v-else>{{ crumb.label }}</span>
            </li>
          </ol>
        </nav>
      </div>
      
      <slot name="header-actions">
        <button 
          v-if="showCreateButton" 
          @click="$emit('create')" 
          class="btn btn-primary"
        >
          <i class="bi bi-plus-circle me-2"></i>{{ createButtonLabel }}
        </button>
      </slot>
    </div>

    <!-- ⭐ SLOT PARA FILTROS CUSTOMIZADOS -->
    <div v-if="$slots.filters" class="card card-glass mb-4">
      <div class="card-body">
        <slot name="filters"></slot>
      </div>
    </div>

    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loading" class="card card-glass">
      <div class="card-body">
        <div v-for="n in skeletonRows" :key="n" class="skeleton-item mb-3">
          <div class="skeleton-line" style="width: 60%"></div>
          <div class="skeleton-line" style="width: 40%"></div>
          <div class="skeleton-line" style="width: 80%"></div>
        </div>
      </div>
    </div>

    <!-- ⭐ CONTEÚDO PRINCIPAL -->
    <div v-else class="card card-glass">
      <div class="card-body">
        <!-- Slot para o conteúdo da tabela ou lista -->
        <slot></slot>

        <!-- Paginação -->
        <div 
          v-if="showPagination && pagination.total > pagination.per_page" 
          class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-glass"
        >
          <div class="text-white-50">
            Mostrando <strong class="text-white">{{ pagination.from }}</strong> a 
            <strong class="text-white">{{ pagination.to }}</strong> de 
            <strong class="text-white">{{ pagination.total }}</strong> registros
          </div>
          
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <a 
                  class="page-link" 
                  @click.prevent="changePage(pagination.current_page - 1)" 
                  href="#"
                >
                  <i class="bi bi-chevron-left"></i>
                </a>
              </li>
              
              <li 
                v-for="page in visiblePages" 
                :key="page" 
                class="page-item" 
                :class="{ active: page === pagination.current_page }"
              >
                <a class="page-link" @click.prevent="changePage(page)" href="#">
                  {{ page }}
                </a>
              </li>
              
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <a 
                  class="page-link" 
                  @click.prevent="changePage(pagination.current_page + 1)" 
                  href="#"
                >
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
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  breadcrumbs: { type: Array, default: () => [] },
  showCreateButton: { type: Boolean, default: true },
  createButtonLabel: { type: String, default: 'Novo' },
  loading: { type: Boolean, default: false },
  pagination: { 
    type: Object, 
    default: () => ({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    }) 
  },
  showPagination: { type: Boolean, default: true },
  skeletonRows: { type: Number, default: 5 }
});

const emit = defineEmits(['create', 'page-change']);

// Páginas visíveis na paginação (mostra 5 páginas no máximo)
const visiblePages = computed(() => {
  const current = props.pagination.current_page;
  const last = props.pagination.last_page;
  const delta = 2;
  const pages = [];

  // Adiciona primeira página
  if (current > delta + 1) {
    pages.push(1);
    if (current > delta + 2) {
      pages.push('...');
    }
  }

  // Adiciona páginas ao redor da atual
  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i);
  }

  // Adiciona última página
  if (current < last - delta) {
    if (current < last - delta - 1) {
      pages.push('...');
    }
    pages.push(last);
  }

  return pages;
});

// Mudar página
const changePage = (page) => {
  if (page === '...' || page < 1 || page > props.pagination.last_page) return;
  emit('page-change', page);
};
</script>

<style scoped>
@import '@/styles/BaseListViewStyles.css';
</style>
