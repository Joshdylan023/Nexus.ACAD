<template>
  <div class="btn-group">
    <button 
      type="button" 
      class="btn btn-outline-light dropdown-toggle" 
      data-bs-toggle="dropdown" 
      aria-expanded="false"
    >
      <i class="bi bi-download me-2"></i>
      Exportar
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li>
        <a class="dropdown-item" href="#" @click.prevent="exportSimple">
          <i class="bi bi-file-earmark-spreadsheet me-2"></i>
          Excel Simples (CSV)
        </a>
      </li>
      <li>
        <a class="dropdown-item" href="#" @click.prevent="exportAdvanced">
          <i class="bi bi-file-earmark-excel me-2"></i>
          <strong>Excel Avançado</strong>
          <small class="d-block text-muted">Com logo e formatação</small>
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item disabled" href="#">
          <i class="bi bi-file-earmark-pdf me-2"></i>
          PDF <span class="badge bg-secondary ms-2">Em breve</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import axios from 'axios';
import Papa from 'papaparse';

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  fileName: {
    type: String,
    default: 'export'
  },
  apiEndpoint: {
    type: String,
    default: null
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['export-start', 'export-complete', 'export-error']);

/**
 * Export Simples (CSV) - Mantém o comportamento atual
 */
const exportSimple = () => {
  try {
    emit('export-start');
    
    const formattedData = props.data.map(item => {
      const row = {};
      props.columns.forEach(col => {
        row[col.label] = item[col.key] || '-';
      });
      return row;
    });

    const csv = Papa.unparse(formattedData);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `${props.fileName}_${Date.now()}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    emit('export-complete');
  } catch (error) {
    console.error('Erro ao exportar CSV:', error);
    emit('export-error', error);
  }
};

/**
 * Export Avançado (XLSX) - Novo com identidade visual
 */
const exportAdvanced = async () => {
  try {
    emit('export-start');
    
    if (!props.apiEndpoint) {
      throw new Error('API endpoint não configurado para export avançado');
    }
    
    const response = await axios.get(props.apiEndpoint, {
      params: props.filters,
      responseType: 'blob'
    });
    
    const blob = new Blob([response.data], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    });
    
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `${props.fileName}_advanced_${Date.now()}.xlsx`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    emit('export-complete');
  } catch (error) {
    console.error('Erro ao exportar Excel avançado:', error);
    emit('export-error', error);
  }
};
</script>

<style scoped>
.dropdown-menu {
  min-width: 280px;
}

.dropdown-item strong {
  color: #667EEA;
}

.dropdown-item small {
  font-size: 0.75rem;
  margin-top: 0.125rem;
}

.dropdown-item i {
  width: 20px;
  text-align: center;
}
</style>
