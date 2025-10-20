<template>
  <div class="schedule-config card glass-effect">
    <div class="card-body">
      <h5 class="mb-4">
        <i class="bi bi-clock-history me-2"></i>
        Configurar Agendamento
      </h5>

      <!-- Enable/Disable -->
      <div class="form-check form-switch mb-4">
        <input 
          v-model="config.auto_sync_enabled" 
          class="form-check-input" 
          type="checkbox"
          id="autoSyncEnabled"
        />
        <label class="form-check-label" for="autoSyncEnabled">
          <strong>Habilitar sincronização automática</strong>
        </label>
      </div>

      <div v-if="config.auto_sync_enabled">
        <!-- Frequência -->
        <div class="mb-4">
          <label class="form-label">Frequência</label>
          <select v-model="config.sync_frequency" class="form-select">
            <option value="hourly">A cada hora</option>
            <option value="daily">Diariamente</option>
            <option value="weekly">Semanalmente</option>
            <option value="monthly">Mensalmente</option>
          </select>
        </div>

        <!-- Horário -->
        <div v-if="config.sync_frequency !== 'hourly'" class="mb-4">
          <label class="form-label">Horário</label>
          <input 
            v-model="config.sync_time" 
            type="time" 
            class="form-control"
          />
        </div>

        <!-- Dia da Semana (para weekly) -->
        <div v-if="config.sync_frequency === 'weekly'" class="mb-4">
          <label class="form-label">Dia da Semana</label>
          <select v-model="config.sync_day" class="form-select">
            <option :value="0">Domingo</option>
            <option :value="1">Segunda-feira</option>
            <option :value="2">Terça-feira</option>
            <option :value="3">Quarta-feira</option>
            <option :value="4">Quinta-feira</option>
            <option :value="5">Sexta-feira</option>
            <option :value="6">Sábado</option>
          </select>
        </div>

        <!-- Próxima Execução -->
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Próxima execução:</strong> {{ nextSync }}
        </div>
      </div>

      <!-- Botões -->
      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-outline-secondary" @click="$emit('cancel')">
          Cancelar
        </button>
        <button class="btn btn-primary" @click="saveSchedule">
          <i class="bi bi-check-circle me-2"></i>
          Salvar Agendamento
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  integrationId: {
    type: Number,
    required: true
  },
  initialConfig: {
    type: Object,
    default: () => ({
      auto_sync_enabled: false,
      sync_frequency: 'daily',
      sync_time: '02:00',
      sync_day: 0
    })
  }
});

const emit = defineEmits(['saved', 'cancel']);

const config = ref({ ...props.initialConfig });

const nextSync = computed(() => {
  if (!config.value.auto_sync_enabled) return 'Desabilitado';
  
  // Calcular próxima execução (simplificado)
  const now = new Date();
  let next = new Date(now);
  
  switch (config.value.sync_frequency) {
    case 'hourly':
      next.setHours(next.getHours() + 1);
      break;
    case 'daily':
      const [hours, minutes] = config.value.sync_time.split(':');
      next.setHours(hours, minutes, 0, 0);
      if (next <= now) next.setDate(next.getDate() + 1);
      break;
    case 'weekly':
      // Implementar lógica semanal
      break;
  }
  
  return next.toLocaleString('pt-BR');
});

const saveSchedule = async () => {
  try {
    await axios.put(`/api/v1/hr/integrations/${props.integrationId}/schedule`, config.value);
    
    emit('saved');
    alert('✅ Agendamento salvo com sucesso!');
  } catch (error) {
    console.error('Erro ao salvar agendamento:', error);
    alert('❌ Erro ao salvar agendamento');
  }
};
</script>

<style scoped>
.schedule-config {
  max-width: 600px;
  margin: 0 auto;
}
</style>
