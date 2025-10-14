<template>
  <transition-group name="toast-list" tag="div" class="toast-container">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="toast-notification"
      :class="`toast-${toast.type}`"
    >
      <div class="toast-icon">
        <i :class="getIconClass(toast.type)"></i>
      </div>
      <div class="toast-content">
        <h6>{{ toast.title }}</h6>
        <p>{{ toast.message }}</p>
      </div>
      <button class="toast-close" @click="removeToast(toast.id)">
        <i class="bi bi-x"></i>
      </button>
    </div>
  </transition-group>
</template>

<script setup>
import { ref } from 'vue';

const toasts = ref([]);
let toastId = 0;

const addToast = (notification) => {
  const id = toastId++;
  toasts.value.push({
    id,
    ...notification
  });

  // Remove automaticamente após 5 segundos
  setTimeout(() => {
    removeToast(id);
  }, 5000);
};

const removeToast = (id) => {
  const index = toasts.value.findIndex(t => t.id === id);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
};

const getIconClass = (type) => {
  const icons = {
    success: 'bi-check-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    error: 'bi-x-circle-fill',
    info: 'bi-info-circle-fill'
  };
  return icons[type] || 'bi-bell-fill';
};

defineExpose({ addToast });
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast-notification {
  min-width: 300px;
  max-width: 400px;
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  border-left: 4px solid;
}

.toast-success {
  border-left-color: #28a745;
}

.toast-warning {
  border-left-color: #ffc107;
}

.toast-error {
  border-left-color: #dc3545;
}

.toast-info {
  border-left-color: #007bff;
}

.toast-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.toast-success .toast-icon {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.toast-warning .toast-icon {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.toast-error .toast-icon {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.toast-info .toast-icon {
  background: rgba(0, 123, 255, 0.2);
  color: #007bff;
}

.toast-content {
  flex: 1;
}

.toast-content h6 {
  margin: 0 0 0.25rem 0;
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
}

.toast-content p {
  margin: 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.85rem;
}

.toast-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.toast-list-enter-active {
  animation: toast-in 0.3s;
}

.toast-list-leave-active {
  animation: toast-out 0.3s;
}

@keyframes toast-in {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes toast-out {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(100%);
  }
}
</style>
