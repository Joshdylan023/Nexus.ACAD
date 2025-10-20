<template>
  <div class="skeleton-dashboard">
    <!-- Skeleton KPIs Principais -->
    <div class="row g-3 mb-4">
      <div v-for="n in 4" :key="n" class="col-md-3">
        <div class="card card-glass">
          <div class="card-body">
            <div class="skeleton-box" style="height: 60px;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Skeleton KPIs Secundários -->
    <div class="row g-3 mb-4">
      <div v-for="n in 6" :key="n" class="col-md-2">
        <div class="card card-glass">
          <div class="card-body text-center">
            <div class="skeleton-box" style="height: 40px; width: 40px; margin: 0 auto 10px;"></div>
            <div class="skeleton-box" style="height: 20px; width: 80%; margin: 0 auto;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Skeleton Gráficos -->
    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <div class="card card-glass">
          <div class="card-body">
            <div class="skeleton-box" style="height: 300px;"></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-glass">
          <div class="card-body">
            <div class="skeleton-box" style="height: 300px;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Skeleton Tabela -->
    <div class="card card-glass">
      <div class="card-body">
        <div class="skeleton-box" style="height: 200px;"></div>
      </div>
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

.skeleton-box {
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.1) 25%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.1) 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 8px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>