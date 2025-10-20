<template>
  <button
    @click="exportarPdf"
    :disabled="loading"
    class="btn-export-pdf"
    :class="{ 'loading': loading }"
  >
    <svg 
      v-if="!loading" 
      xmlns="http://www.w3.org/2000/svg" 
      class="icon" 
      viewBox="0 0 24 24" 
      fill="none" 
      stroke="currentColor"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
    </svg>
    
    <svg 
      v-else 
      class="icon animate-spin" 
      xmlns="http://www.w3.org/2000/svg" 
      fill="none" 
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    <span>{{ loading ? 'Gerando PDF...' : 'Exportar PDF' }}</span>
  </button>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import api from '@/services/api';

const props = defineProps({
  endpoint: {
    type: String,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  filename: {
    type: String,
    default: 'relatorio.pdf'
  }
});

const toast = useToast();
const loading = ref(false);

const exportarPdf = async () => {
  loading.value = true;
  
  try {
    // Construir query params
    const params = new URLSearchParams();
    
    Object.keys(props.filters).forEach(key => {
      if (props.filters[key] !== null && props.filters[key] !== undefined && props.filters[key] !== '') {
        params.append(key, props.filters[key]);
      }
    });
    
    const queryString = params.toString();
    const url = queryString ? `${props.endpoint}?${queryString}` : props.endpoint;
    
    // Fazer requisição
    const response = await api.get(url, {
      responseType: 'blob',
    });
    
    // Criar link de download
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = props.filename;
    link.click();
    
    // Limpar
    window.URL.revokeObjectURL(link.href);
    
    toast.success('PDF gerado com sucesso!');
    
  } catch (error) {
    console.error('Erro ao exportar PDF:', error);
    toast.error(error.response?.data?.message || 'Erro ao gerar PDF');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.btn-export-pdf {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.btn-export-pdf:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

.btn-export-pdf:active:not(:disabled) {
  transform: translateY(0);
}

.btn-export-pdf:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-export-pdf.loading {
  background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
}

.icon {
  width: 1.25rem;
  height: 1.25rem;
  stroke-width: 2;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
