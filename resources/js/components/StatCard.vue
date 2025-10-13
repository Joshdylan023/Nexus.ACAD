<template>
  <div class="stat-card">
    <div class="stat-icon" :style="{ background: iconBg }">
      <i :class="icon" :style="{ color: iconColor }"></i>
    </div>
    <div class="stat-content">
      <span class="stat-label">{{ label }}</span>
      <h3 class="stat-value">
        {{ loading ? '-' : value }}
        <span v-if="trend" class="stat-trend" :class="trendClass">
          <i :class="trendIcon"></i>
          {{ trend }}
        </span>
      </h3>
      <p v-if="subtitle" class="stat-subtitle">{{ subtitle }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: String,
  value: [String, Number],
  icon: String,
  iconColor: String,
  iconBg: String,
  subtitle: String,
  trend: String,
  trendUp: Boolean,
  loading: Boolean
});

const trendClass = computed(() => {
  return props.trendUp ? 'trend-up' : 'trend-down';
});

const trendIcon = computed(() => {
  return props.trendUp ? 'bi bi-arrow-up' : 'bi bi-arrow-down';
});
</script>

<style scoped>
.stat-card {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  transition: all 0.3s ease;
  box-shadow: 
    0 4px 15px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 
    0 8px 30px rgba(0, 0, 0, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
  border-color: rgba(102, 126, 234, 0.3);
}

.stat-icon {
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  font-size: 2rem;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-label {
  display: block;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  font-weight: 500;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin: 0;
  line-height: 1.2;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-trend {
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.trend-up {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.trend-down {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.stat-subtitle {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.5);
  margin: 0.5rem 0 0;
}

@media (max-width: 768px) {
  .stat-icon {
    width: 56px;
    height: 56px;
    font-size: 1.75rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }
}
</style>
