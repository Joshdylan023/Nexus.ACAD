<template>
  <div class="org-chart-container">
    <div class="chart-controls">
      <button @click="zoomIn" class="btn btn-sm btn-outline-light me-2">
        <i class="bi bi-zoom-in"></i>
      </button>
      <button @click="zoomOut" class="btn btn-sm btn-outline-light me-2">
        <i class="bi bi-zoom-out"></i>
      </button>
      <button @click="resetZoom" class="btn btn-sm btn-outline-light me-2">
        <i class="bi bi-arrows-fullscreen"></i>
      </button>
      <button @click="expandAll" class="btn btn-sm btn-outline-light me-2">
        <i class="bi bi-arrows-expand"></i> Expandir Tudo
      </button>
      <button @click="collapseAll" class="btn btn-sm btn-outline-light">
        <i class="bi bi-arrows-collapse"></i> Recolher Tudo
      </button>
    </div>

    <div ref="chartContainer" class="chart-wrapper"></div>

    <div v-if="loading" class="chart-loading">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
      <p class="mt-3">Carregando estrutura organizacional...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { OrgChart } from 'd3-org-chart';

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
});

const chartContainer = ref(null);
const loading = ref(false);
let chart = null;

const initChart = () => {
  if (!chartContainer.value || props.data.length === 0) return;

  chart = new OrgChart()
    .container(chartContainer.value)
    .data(props.data)
    .nodeWidth(() => 280)
    .nodeHeight(() => 120)
    .childrenMargin(() => 60)
    .compactMarginBetween(() => 40)
    .compactMarginPair(() => 60)
    .nodeContent((d) => {
      return `
        <div class="org-node ${d.data.type}">
          <div class="org-node-header">
            <i class="bi ${getIcon(d.data.type)}"></i>
            <span class="org-node-type">${d.data.type}</span>
          </div>
          <div class="org-node-body">
            <div class="org-node-title">${d.data.name}</div>
            ${d.data.subtitle ? `<div class="org-node-subtitle">${d.data.subtitle}</div>` : ''}
          </div>
          <div class="org-node-footer">
            ${d.data.count ? `<span class="badge bg-primary">${d.data.count} ${d.data.countLabel || 'itens'}</span>` : ''}
          </div>
        </div>
      `;
    })
    .render();
};

const getIcon = (type) => {
  const icons = {
    'Grupo': 'bi-building',
    'Mantenedora': 'bi-diagram-3',
    'Instituição': 'bi-mortarboard',
    'Campus': 'bi-geo-alt'
  };
  return icons[type] || 'bi-circle';
};

const zoomIn = () => {
  if (chart) chart.zoomIn();
};

const zoomOut = () => {
  if (chart) chart.zoomOut();
};

const resetZoom = () => {
  if (chart) chart.fit();
};

const expandAll = () => {
  if (chart) chart.expandAll();
};

const collapseAll = () => {
  if (chart) chart.collapseAll();
};

onMounted(async () => {
  await nextTick();
  initChart();
});
</script>

<style scoped>
.org-chart-container {
  position: relative;
  width: 100%;
  min-height: 600px;
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  overflow: hidden;
}

.chart-controls {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 10;
  display: flex;
  gap: 0.5rem;
}

.chart-wrapper {
  width: 100%;
  height: 600px;
  overflow: hidden;
}

.chart-loading {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
}

/* Estilos dos nós da árvore */
:deep(.org-node) {
  background: rgba(30, 30, 46, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  padding: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  cursor: pointer;
}

:deep(.org-node:hover) {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
  border-color: rgba(102, 126, 234, 0.5);
}

:deep(.org-node-header) {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

:deep(.org-node-header i) {
  color: rgba(102, 126, 234, 0.8);
  font-size: 1.25rem;
}

:deep(.org-node-type) {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

:deep(.org-node-body) {
  margin-bottom: 0.75rem;
}

:deep(.org-node-title) {
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
  line-height: 1.3;
  margin-bottom: 0.25rem;
}

:deep(.org-node-subtitle) {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.75rem;
}

:deep(.org-node-footer) {
  display: flex;
  justify-content: flex-end;
}

/* Cores por tipo */
:deep(.org-node.Grupo) {
  border-left: 4px solid #667eea;
}

:deep(.org-node.Mantenedora) {
  border-left: 4px solid #17a2b8;
}

:deep(.org-node.Instituição) {
  border-left: 4px solid #28a745;
}

:deep(.org-node.Campus) {
  border-left: 4px solid #ffc107;
}

/* Linhas conectoras */
:deep(.node-button-foreign-object) {
  display: none;
}

:deep(path.link) {
  stroke: rgba(102, 126, 234, 0.3);
  stroke-width: 2px;
}
</style>
