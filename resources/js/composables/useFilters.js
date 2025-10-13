import { ref, computed } from 'vue';

export function useFilters(initialFilters = {}) {
  const filters = ref({ ...initialFilters });
  const showFilters = ref(false);

  /**
   * Conta quantos filtros estão ativos
   */
  const activeFiltersCount = computed(() => {
    return Object.values(filters.value).filter(value => {
      if (Array.isArray(value)) return value.length > 0;
      if (typeof value === 'string') return value !== '';
      if (typeof value === 'number') return value !== null && value !== undefined;
      return value !== null && value !== undefined && value !== '';
    }).length;
  });

  /**
   * Aplica múltiplos filtros em um array de dados
   */
  const applyFilters = (data, filterFunctions) => {
    let filtered = [...data];

    Object.keys(filters.value).forEach(key => {
      const filterValue = filters.value[key];
      
      // Pula filtros vazios
      if (!filterValue || (Array.isArray(filterValue) && filterValue.length === 0)) {
        return;
      }

      // Aplica a função de filtro correspondente se existir
      if (filterFunctions[key]) {
        filtered = filtered.filter(item => filterFunctions[key](item, filterValue));
      }
    });

    return filtered;
  };

  /**
   * Limpa todos os filtros
   */
  const clearFilters = () => {
    Object.keys(filters.value).forEach(key => {
      if (Array.isArray(filters.value[key])) {
        filters.value[key] = [];
      } else {
        filters.value[key] = '';
      }
    });
  };

  /**
   * Define um filtro específico
   */
  const setFilter = (key, value) => {
    filters.value[key] = value;
  };

  /**
   * Obtém um filtro específico
   */
  const getFilter = (key) => {
    return filters.value[key];
  };

  /**
   * Verifica se algum filtro está ativo
   */
  const hasActiveFilters = computed(() => {
    return activeFiltersCount.value > 0;
  });

  return {
    filters,
    showFilters,
    activeFiltersCount,
    hasActiveFilters,
    applyFilters,
    clearFilters,
    setFilter,
    getFilter
  };
}
