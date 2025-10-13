import { ref, computed } from 'vue';

export function usePagination(initialPerPage = 25) {
  const currentPage = ref(1);
  const perPage = ref(initialPerPage);

  /**
   * Retorna os itens paginados
   */
  const paginateItems = (items) => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return items.slice(start, end);
  };

  /**
   * Reseta para a primeira página
   */
  const resetPage = () => {
    currentPage.value = 1;
  };

  /**
   * Muda a página
   */
  const changePage = (page) => {
    currentPage.value = page;
    // Scroll suave para o topo
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  /**
   * Muda itens por página e reseta para primeira página
   */
  const changePerPage = (newPerPage) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
  };

  return {
    currentPage,
    perPage,
    paginateItems,
    resetPage,
    changePage,
    changePerPage
  };
}
