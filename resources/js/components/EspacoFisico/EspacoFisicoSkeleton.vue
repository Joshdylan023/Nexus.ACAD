<template>
  <div class="espaco-fisico-skeleton">
    <!-- Header Skeleton -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="skeleton skeleton-title mb-3"></div>
            <div class="skeleton skeleton-text" style="width: 60%;"></div>
          </div>
          <div class="col-md-4 text-end">
            <div class="skeleton skeleton-button"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Skeleton -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3" v-for="i in 4" :key="i">
            <div class="skeleton skeleton-label mb-2"></div>
            <div class="skeleton skeleton-input"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cards Skeleton -->
    <div class="row g-3">
      <div class="col-md-4" v-for="i in 6" :key="i">
        <div class="card card-glass">
          <div class="card-body">
            <div class="skeleton skeleton-text mb-3"></div>
            <div class="skeleton skeleton-text mb-2" style="width: 80%;"></div>
            <div class="skeleton skeleton-text mb-3" style="width: 60%;"></div>
            <div class="d-flex gap-2">
              <div class="skeleton skeleton-badge"></div>
              <div class="skeleton skeleton-badge"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.skeleton {
  background: linear-gradient(90deg, 
    rgba(255, 255, 255, 0.05) 25%, 
    rgba(255, 255, 255, 0.1) 50%, 
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: loading 1.5s ease-in-out infinite;
  border-radius: 8px;
}

.skeleton-title {
  height: 32px;
  width: 200px;
}

.skeleton-text {
  height: 16px;
  width: 100%;
}

.skeleton-label {
  height: 14px;
  width: 80px;
}

.skeleton-input {
  height: 42px;
  width: 100%;
}

.skeleton-button {
  height: 38px;
  width: 120px;
}

.skeleton-badge {
  height: 24px;
  width: 60px;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>
