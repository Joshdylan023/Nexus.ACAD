import { ref, computed, isRef } from 'vue';

export function useFilters(items, filterConfig) {
  const activeFilters = ref({});
  const showFilters = ref(false);

  // Aplicar filtros
  const filteredItems = computed(() => {
    let result = items.value || [];

    Object.keys(activeFilters.value).forEach(filterKey => {
      const filterValue = activeFilters.value[filterKey];
      
      if (!filterValue || (Array.isArray(filterValue) && filterValue.length === 0)) {
        return;
      }

      const configArray = isRef(filterConfig) ? filterConfig.value : filterConfig;
      const config = (configArray || []).find(f => f.key === filterKey);
      if (!config) return;

      result = result.filter(item => {
        const itemValue = getNestedValue(item, config.key);

        if (Array.isArray(filterValue)) {
          // Filtro multi-select
          return filterValue.includes(itemValue);
        } else if (typeof filterValue === 'string') {
          // Filtro texto
          return itemValue?.toLowerCase().includes(filterValue.toLowerCase());
        } else if (typeof filterValue === 'object' && filterValue.min !== undefined) {
          // Filtro range
          const val = parseFloat(itemValue);
          return val >= filterValue.min && val <= filterValue.max;
        }
        
        return true;
      });
    });

    return result;
  });

  // Helper para acessar valores aninhados (ex: "instituicao.status")
  const getNestedValue = (obj, path) => {
    return path.split('.').reduce((acc, part) => acc?.[part], obj);
  };

  // Aplicar filtro
  const applyFilter = (key, value) => {
    activeFilters.value[key] = value;
  };

  // Limpar um filtro específico
  const clearFilter = (key) => {
    delete activeFilters.value[key];
  };

  // Limpar todos os filtros
  const clearAllFilters = () => {
    activeFilters.value = {};
  };

  // Contar filtros ativos
  const activeFilterCount = computed(() => {
    return Object.keys(activeFilters.value).filter(key => {
      const value = activeFilters.value[key];
      return value && (!Array.isArray(value) || value.length > 0);
    }).length;
  });

  // Toggle painel de filtros
  const toggleFilters = () => {
    showFilters.value = !showFilters.value;
  };

  return {
    activeFilters,
    filteredItems,
    showFilters,
    applyFilter,
    clearFilter,
    clearAllFilters,
    activeFilterCount,
    toggleFilters
  };
}
