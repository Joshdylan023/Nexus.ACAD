<template>
  <div class="stats-panel">
    <div class="row g-4">
      <!-- Performance -->
      <div class="col-md-6">
        <div class="glass-card">
          <h5 class="mb-4">
            <i class="bi bi-speedometer2 me-2"></i>Performance
          </h5>
          <div class="stat-row">
            <span class="stat-label">Tempo Médio</span>
            <span class="stat-value">{{ stats.performance?.avg_duration || 0 }}s</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Tempo Mínimo</span>
            <span class="stat-value text-success">{{ stats.performance?.min_duration || 0 }}s</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Tempo Máximo</span>
            <span class="stat-value text-warning">{{ stats.performance?.max_duration || 0 }}s</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Tempo Total</span>
            <span class="stat-value">{{ formatTotalDuration(stats.performance?.total_duration || 0) }}</span>
          </div>
        </div>
      </div>

      <!-- Registros -->
      <div class="col-md-6">
        <div class="glass-card">
          <h5 class="mb-4">
            <i class="bi bi-database me-2"></i>Registros Processados
          </h5>
          <div class="stat-row">
            <span class="stat-label">Criados</span>
            <span class="stat-value text-success">
              <i class="bi bi-plus-circle me-1"></i>
              {{ stats.records?.total_created || 0 }}
            </span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Atualizados</span>
            <span class="stat-value text-info">
              <i class="bi bi-arrow-repeat me-1"></i>
              {{ stats.records?.total_updated || 0 }}
            </span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Falharam</span>
            <span class="stat-value text-danger">
              <i class="bi bi-x-circle me-1"></i>
              {{ stats.records?.total_failed || 0 }}
            </span>
          </div>
          <div class="stat-row">
            <span class="stat-label">Ignorados</span>
            <span class="stat-value text-muted">
              <i class="bi bi-dash-circle me-1"></i>
              {{ stats.records?.total_skipped || 0 }}
            </span>
          </div>
        </div>
      </div>

      <!-- Taxa de Sucesso -->
      <div class="col-12">
        <div class="glass-card">
          <h5 class="mb-4">
            <i class="bi bi-graph-up me-2"></i>Taxa de Sucesso
          </h5>
          <div class="progress-wrapper">
            <div class="d-flex justify-content-between mb-2">
              <span>Sincronizações Bem-Sucedidas</span>
              <span class="text-success fw-bold">{{ successRate }}%</span>
            </div>
            <div class="progress" style="height: 24px;">
              <div 
                class="progress-bar bg-success" 
                :style="{ width: successRate + '%' }"
                role="progressbar"
              >
                {{ stats.overview?.successful_syncs || 0 }} de {{ stats.overview?.total_syncs || 0 }}
              </div>
            </div>
          </div>

          <div class="row g-3 mt-3">
            <div class="col-md-4">
              <div class="mini-stat success">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                  <div class="mini-stat-label">Sucesso</div>
                  <div class="mini-stat-value">{{ stats.overview?.successful_syncs || 0 }}</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mini-stat danger">
                <i class="bi bi-x-circle-fill"></i>
                <div>
                  <div class="mini-stat-label">Falhas</div>
                  <div class="mini-stat-value">{{ stats.overview?.failed_syncs || 0 }}</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mini-stat info">
                <i class="bi bi-arrow-repeat"></i>
                <div>
                  <div class="mini-stat-label">Total</div>
                  <div class="mini-stat-value">{{ stats.overview?.total_syncs || 0 }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  integrationId: {
    type: Number,
    required: true
  },
  stats: {
    type: Object,
    default: () => ({})
  }
});

// Computed
const successRate = computed(() => {
  const total = props.stats.overview?.total_syncs || 0;
  const successful = props.stats.overview?.successful_syncs || 0;
  return total > 0 ? Math.round((successful / total) * 100) : 0;
});

// Methods
const formatTotalDuration = (seconds) => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  
  if (hours > 0) return `${hours}h ${minutes}m`;
  if (minutes > 0) return `${minutes}m ${secs}s`;
  return `${secs}s`;
};
</script>

<style scoped>
.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-row:last-child {
  border-bottom: none;
}

.stat-label {
  color: rgba(255, 255, 255, 0.7);
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 600;
}

.progress {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.progress-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  transition: width 0.6s ease;
}

.mini-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border-left: 4px solid;
}

.mini-stat.success {
  border-color: #10B981;
}

.mini-stat.danger {
  border-color: #EF4444;
}

.mini-stat.info {
  border-color: #3B82F6;
}

.mini-stat i {
  font-size: 2rem;
  opacity: 0.8;
}

.mini-stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.7);
}

.mini-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
}
</style>
