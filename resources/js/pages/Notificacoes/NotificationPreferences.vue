<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>Preferências de Notificações</h3>
        <button class="btn-close" @click="$emit('close')">
          <i class="bi bi-x"></i>
        </button>
      </div>

      <div class="modal-body">
        <p class="description">
          Configure quais tipos de notificações você deseja receber
        </p>

        <div 
          v-for="(pref, index) in preferences" 
          :key="pref.category"
          class="preference-item"
        >
          <div class="preference-header">
            <h5>{{ pref.label }}</h5>
            <label class="switch">
              <input 
                type="checkbox" 
                v-model="preferences[index].enabled"
              >
              <span class="slider"></span>
            </label>
          </div>

          <div v-if="pref.enabled" class="preference-options">
            <label class="checkbox-label">
              <input 
                type="checkbox" 
                v-model="preferences[index].push_enabled"
              >
              <i class="bi bi-bell"></i>
              Notificações Push
            </label>
            <label class="checkbox-label">
              <input 
                type="checkbox" 
                v-model="preferences[index].email_enabled"
              >
              <i class="bi bi-envelope"></i>
              Notificações por E-mail
            </label>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" @click="$emit('close')">
          Cancelar
        </button>
        <button class="btn btn-primary" @click="savePreferences" :disabled="isSaving">
          {{ isSaving ? 'Salvando...' : 'Salvar Preferências' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'updated']);

const preferences = ref([]);
const isSaving = ref(false);

const fetchPreferences = async () => {
  try {
    const response = await axios.get('/api/v1/notifications/preferences');
    preferences.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar preferências:', error);
  }
};

const savePreferences = async () => {
  isSaving.value = true;
  try {
    await axios.post('/api/v1/notifications/preferences', {
      preferences: preferences.value
    });
    
    alert('Preferências salvas com sucesso!');
    emit('updated');
    emit('close');
  } catch (error) {
    console.error('Erro ao salvar preferências:', error);
    alert('Erro ao salvar preferências');
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  fetchPreferences();
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: rgba(30, 30, 30, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: white;
}

.btn-close {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.description {
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 1.5rem;
}

.preference-item {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.preference-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.preference-header h5 {
  margin: 0;
  color: white;
  font-size: 1rem;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.2);
  transition: 0.3s;
  border-radius: 28px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #667eea;
}

input:checked + .slider:before {
  transform: translateX(22px);
}

.preference-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: rgba(102, 126, 234, 0.8);
  color: white;
}

.btn-primary:hover {
  background: rgba(102, 126, 234, 1);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}
</style>
