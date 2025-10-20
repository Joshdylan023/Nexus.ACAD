<template>
  <div class="card card-glass report-card h-100 placeholder-glow">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <span class="placeholder col-4 mb-2 bg-secondary"></span>
          <h5 class="card-title placeholder col-6 bg-secondary"></h5>
          <p class="card-text placeholder col-7 bg-secondary"></p>
        </div>
      </div>
      <div class="report-meta">
        <div class="d-flex align-items-center text-white-50 small mb-2">
          <span class="placeholder col-1 me-2 bg-secondary"></span>
          <span class="placeholder col-4 bg-secondary"></span>
        </div>
        <div class="d-flex align-items-center text-white-50 small mb-2">
          <span class="placeholder col-1 me-2 bg-secondary"></span>
          <span class="placeholder col-5 bg-secondary"></span>
        </div>
        <div class="d-flex align-items-center text-white-50 small mb-2">
          <span class="placeholder col-1 me-2 bg-secondary"></span>
          <span class="placeholder col-3 bg-secondary"></span>
        </div>
        <div class="d-flex align-items-center text-success small">
          <span class="placeholder col-1 me-2 bg-secondary"></span>
          <span class="placeholder col-2 bg-secondary"></span>
        </div>
      </div>
    </div>
    <div class="card-footer bg-transparent border-0">
      <a href="#" tabindex="-1" class="btn btn-outline-primary disabled placeholder col-12"></a>
    </div>
  </div>
</template>

<script setup>
// No script needed for the skeleton
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}
.placeholder {
  border-radius: 4px;
}
.placeholder-glow .placeholder {
  animation: placeholder-glow 2s ease-in-out infinite;
}
@keyframes placeholder-glow {
  50% {
    opacity: 0.4;
  }
}
</style>
