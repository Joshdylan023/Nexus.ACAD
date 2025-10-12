<template>
  <div class="dropdown">
    <button 
      class="btn btn-outline-light dropdown-toggle" 
      type="button" 
      id="exportDropdown" 
      data-bs-toggle="dropdown" 
      aria-expanded="false"
      :disabled="exporting || disabled"
    >
      <span v-if="!exporting">
        <i class="bi bi-download me-2"></i>Exportar
      </span>
      <span v-else>
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        Exportando...
      </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
      <li>
        <a class="dropdown-item" href="#" @click.prevent="handleExport('excel')">
          <i class="bi bi-file-earmark-excel text-success me-2"></i>
          Excel (.xlsx)
        </a>
      </li>
      <li>
        <a class="dropdown-item" href="#" @click.prevent="handleExport('csv')">
          <i class="bi bi-file-earmark-text text-primary me-2"></i>
          CSV (.csv)
        </a>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { useExport } from '@/composables/useExport';

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  fileName: {
    type: String,
    default: 'export'
  },
  columns: {
    type: Array,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const { exporting, exportToExcel, exportToCSV } = useExport();

const handleExport = (format) => {
  if (!props.data || props.data.length === 0) {
    alert('Não há dados para exportar.');
    return;
  }

  if (format === 'excel') {
    exportToExcel(props.data, props.fileName, props.columns);
  } else if (format === 'csv') {
    exportToCSV(props.data, props.fileName, props.columns);
  }
};
</script>

<style scoped>
.dropdown-menu {
  background: rgba(30, 30, 46, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.dropdown-item {
  color: rgba(255, 255, 255, 0.9);
}

.dropdown-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}
</style>
