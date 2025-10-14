<template>
  <div class="card card-glass">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Alertas Regulatórios</strong>
      </div>
      <router-link to="/admin/institucional/alertas-regulatorios" class="btn btn-sm btn-outline-light">
        Ver detalhes <i class="bi bi-arrow-right ms-1"></i>
      </router-link>
    </div>
    <div class="card-body">
      <div v-if="loading" class="text-center py-3">
        <div class="spinner-border spinner-border-sm" role="status"></div>
      </div>
      <div v-else>
        <div class="alert-item mb-3" @click="navegarAlertas('vencidos')">
          <div class="alert-icon vencidos">
            <i class="bi bi-x-circle-fill"></i>
          </div>
          <div class="alert-content">
            <div class="alert-number">{{ alertas.vencidos?.length || 0 }}</div>
            <div class="alert-label">Atos Vencidos</div>
          </div>
        </div>

        <div class="alert-item mb-3" @click="navegarAlertas('a-vencer')">
          <div class="alert-icon a-vencer">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div class="alert-content">
            <div class="alert-number">{{ alertas.a_vencer_30_dias?.length || 0 }}</div>
            <div class="alert-label">Vencem em 30 dias</div>
          </div>
        </div>

        <div class="alert-item" @click="navegarAlertas('vigentes')">
          <div class="alert-icon vigentes">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <div class="alert-content">
            <div class="alert-number">{{ alertas.vigentes_count || 0 }}</div>
            <div class="alert-label">Atos Vigentes</div>
          </div>
        </div>

        <div class="mt-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
          <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Total de {{ alertas.total || 0 }} atos cadastrados
          </small>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const alertas = ref({});
const loading = ref(true);

const fetchAlertas = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/atos-regulatorios/alertas');
    alertas.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar alertas:', error);
  } finally {
    loading.value = false;
  }
};

const navegarAlertas = (tipo) => {
  router.push(`/admin/institucional/alertas-regulatorios?filtro=${tipo}`);
};

onMounted(() => {
  fetchAlertas();
});
</script>

<style scoped>
.alert-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.alert-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(5px);
}

.alert-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-right: 1rem;
  flex-shrink: 0;
}

.alert-icon.vencidos {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.alert-icon.a-vencer {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.alert-icon.vigentes {
  background: rgba(25, 135, 84, 0.2);
  color: #198754;
}

.alert-content {
  flex: 1;
}

.alert-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.95);
  line-height: 1;
}

.alert-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 0.25rem;
}
</style>
