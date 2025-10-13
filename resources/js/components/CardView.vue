<template>
  <div class="card-view-grid">
    <div 
      v-for="item in items" 
      :key="item.id" 
      class="card-item"
    >
      <div class="card-content">
        <div class="card-header-section">
          <slot name="header" :item="item">
            <h5 class="card-title">{{ item.nome || item.name }}</h5>
          </slot>
        </div>

        <div class="card-body-section">
          <slot name="body" :item="item"></slot>
        </div>

        <div class="card-footer-section">
          <slot name="footer" :item="item"></slot>
        </div>
      </div>
    </div>

    <div v-if="items.length === 0" class="no-results">
      <i class="bi bi-inbox"></i>
      <p>{{ emptyMessage }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    required: true
  },
  emptyMessage: {
    type: String,
    default: 'Nenhum item encontrado'
  }
});
</script>

<style scoped>
.card-view-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  padding: 0;
}

.card-item {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  box-shadow: 
    0 4px 20px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.card-item:hover {
  transform: translateY(-4px);
  box-shadow: 
    0 12px 40px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
  border-color: rgba(102, 126, 234, 0.3);
}

.card-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.card-header-section {
  padding: 1.5rem 1.5rem 1rem;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.card-title {
  color: white;
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
  line-height: 1.4;
}

.card-body-section {
  flex: 1;
  padding: 1.25rem 1.5rem;
}

.card-footer-section {
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  color: rgba(255, 255, 255, 0.5);
}

.no-results i {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.no-results p {
  font-size: 1.125rem;
  margin: 0;
}

/* Responsividade */
@media (max-width: 768px) {
  .card-view-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>
