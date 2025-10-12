<template>
  <span class="badge-glass d-inline-flex align-items-center gap-1" :class="statusClass">
    <i :class="statusIcon"></i>
    {{ status }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'status', // 'status' ou 'tipo'
  }
});

const statusClass = computed(() => {
  if (props.type === 'tipo') {
    const classes = {
      'Corporativo': 'badge-primary',
      'Institucional': 'badge-info',
      'Operacional': 'badge-warning'
    };
    return classes[props.status] || 'badge-secondary';
  }
  
  // Status padrão
  const classes = {
    'Ativo': 'badge-success',
    'Inativo': 'badge-danger',
    'Em Extinção': 'badge-warning',
    'Em Implantação': 'badge-info',
    'Em Desativação': 'badge-warning'
  };
  return classes[props.status] || 'badge-secondary';
});

const statusIcon = computed(() => {
  if (props.type === 'tipo') {
    const icons = {
      'Corporativo': 'bi bi-building',
      'Institucional': 'bi bi-bank2',
      'Operacional': 'bi bi-gear'
    };
    return icons[props.status] || 'bi bi-circle-fill';
  }
  
  // Ícones de status
  const icons = {
    'Ativo': 'bi bi-check-circle-fill',
    'Inativo': 'bi bi-x-circle-fill',
    'Em Extinção': 'bi bi-exclamation-triangle-fill',
    'Em Implantação': 'bi bi-hourglass-split',
    'Em Desativação': 'bi bi-arrow-down-circle-fill'
  };
  return icons[props.status] || 'bi bi-circle-fill';
});
</script>

<style scoped>
.badge-glass {
  padding: 0.35rem 0.75rem;
  font-size: 0.8125rem;
  font-weight: 600;
  border-radius: 8px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Status Badges */
.badge-success {
  background: rgba(25, 135, 84, 0.3);
  color: #a3f7bf;
  border-color: rgba(25, 135, 84, 0.4);
}

.badge-danger {
  background: rgba(220, 53, 69, 0.3);
  color: #ff8fa3;
  border-color: rgba(220, 53, 69, 0.4);
}

.badge-warning {
  background: rgba(255, 193, 7, 0.25);
  color: #ffd43b;
  border-color: rgba(255, 193, 7, 0.35);
}

.badge-info {
  background: rgba(13, 202, 240, 0.25);
  color: #9fecfb;
  border-color: rgba(13, 202, 240, 0.35);
}

/* Tipo Badges - Cores Mais Vibrantes */
.badge-primary {
  background: rgba(13, 110, 253, 0.3);
  color: #a6c1ff;
  border-color: rgba(13, 110, 253, 0.4);
}

.badge-secondary {
  background: rgba(108, 117, 125, 0.3);
  color: #cbd5e0;
  border-color: rgba(108, 117, 125, 0.4);
}

/* Hover Effect */
.badge-glass:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
}
</style>
