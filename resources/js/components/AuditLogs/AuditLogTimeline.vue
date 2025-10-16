<template>
  <div class="timeline">
    <div 
      v-for="log in logs" 
      :key="log.id" 
      class="timeline-item"
      @click="$emit('view-details', log)"
    >
      <div class="timeline-marker" :class="getMarkerClass(log.action)">
        <i :class="getActionIcon(log.action)"></i>
      </div>
      <div class="timeline-content">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h6 class="mb-1">
              <span class="badge" :class="getActionBadgeClass(log.action)">
                {{ getActionName(log.action) }}
              </span>
              <span class="badge badge-module ms-2">
                {{ getModuleName(log.module) }}
              </span>
            </h6>
            <p class="mb-1 text-white">{{ log.description }}</p>
            <small class="text-white-50">
              <i class="fas fa-user me-1"></i>{{ log.user?.name || 'Sistema' }}
              <span class="mx-2">|</span>
              <i class="fas fa-clock me-1"></i>{{ formatDate(log.created_at) }}
            </small>
          </div>
          <button 
            class="btn btn-sm btn-outline-light" 
            @click.stop="$emit('view-details', log)"
            title="Ver detalhes"
          >
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  logs: {
    type: Array,
    required: true
  }
});

defineEmits(['view-details']);

const getActionName = (action) => {
  const actions = {
    created: 'Criação',
    updated: 'Atualização',
    deleted: 'Exclusão',
    restored: 'Restauração'
  };
  return actions[action] || action;
};

const getModuleName = (module) => {
  const modules = {
    institucional: 'Institucional',
    pessoas_acessos: 'Pessoas & Acessos',
    academico: 'Acadêmico',
    financeiro: 'Financeiro',
    estagios: 'Estágios',
    sistema: 'Sistema'
  };
  return modules[module] || module;
};

const getActionIcon = (action) => {
  const icons = {
    created: 'fas fa-plus',
    updated: 'fas fa-edit',
    deleted: 'fas fa-trash',
    restored: 'fas fa-undo'
  };
  return icons[action] || 'fas fa-circle';
};

const getActionBadgeClass = (action) => {
  const classes = {
    created: 'bg-success',
    updated: 'bg-info',
    deleted: 'bg-danger',
    restored: 'bg-warning'
  };
  return classes[action] || 'bg-secondary';
};

const getMarkerClass = (action) => {
  const classes = {
    created: 'marker-success',
    updated: 'marker-info',
    deleted: 'marker-danger',
    restored: 'marker-warning'
  };
  return classes[action] || 'marker-secondary';
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
/* Timeline */
.timeline {
  position: relative;
  padding-left: 50px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 20px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 100%);
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
  cursor: pointer;
  transition: transform 0.3s;
}

.timeline-item:hover {
  transform: translateX(5px);
}

.timeline-marker {
  position: absolute;
  left: -30px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  z-index: 1;
}

.marker-success {
  background: linear-gradient(135deg, #28a745, #20c997);
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.marker-info {
  background: linear-gradient(135deg, #17a2b8, #0dcaf0);
  box-shadow: 0 4px 15px rgba(13, 202, 240, 0.4);
}

.marker-danger {
  background: linear-gradient(135deg, #dc3545, #c82333);
  box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
}

.marker-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
  box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
}

.marker-secondary {
  background: linear-gradient(135deg, #6c757d, #5a6268);
  box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
}

.timeline-content {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
  transition: all 0.3s;
}

.timeline-content:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.badge-module {
  background: rgba(108, 117, 125, 0.3);
  color: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(108, 117, 125, 0.5);
}

.btn-outline-light {
  border-color: rgba(255, 255, 255, 0.3);
  color: white;
}

.btn-outline-light:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
  color: white;
}
</style>
