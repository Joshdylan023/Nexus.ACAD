<template>
  <div class="modal fade" :id="id" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-0">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
            {{ title }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">{{ message }}</p>
          <p v-if="warningText" class="text-danger small mb-0">
            <i class="bi bi-info-circle"></i> {{ warningText }}
          </p>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            {{ cancelText }}
          </button>
          <button type="button" class="btn btn-danger" @click="confirm" data-bs-dismiss="modal">
            <i :class="confirmIcon"></i> {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  id: {
    type: String,
    default: 'confirmModal'
  },
  title: {
    type: String,
    default: 'Confirmar Ação'
  },
  message: {
    type: String,
    required: true
  },
  warningText: {
    type: String,
    default: 'Esta ação não pode ser desfeita.'
  },
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  confirmIcon: {
    type: String,
    default: 'bi bi-check-circle'
  }
});

const emit = defineEmits(['confirm']);

const confirm = () => {
  emit('confirm');
};
</script>
