<template>
  <div>
    <PageHeader 
      title="Alertas Regulatórios"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Alertas Regulatórios' }
      ]"
    >
      <template #actions>
        <button @click="fetchAlertas" class="btn btn-secondary">
          <i class="bi bi-arrow-clockwise me-2"></i>
          Atualizar
        </button>
      </template>
    </PageHeader>

    <TableSkeleton v-if="loading" :columns="1" :rows="3" />

    <div v-else>
      <!-- ATOS VENCIDOS -->
      <div v-if="alertas.vencidos?.length > 0" class="card card-glass mb-4">
        <div class="card-header bg-danger">
          <i class="bi bi-x-circle-fill me-2"></i>
          <strong>ATOS VENCIDOS ({{ alertas.vencidos.length }})</strong>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4">Tipo</th>
                <th>Número</th>
                <th>Instituição</th>
                <th>Data Validade</th>
                <th>Vencido há</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ato in alertas.vencidos" :key="ato.id">
                <td class="ps-4">
                  <span class="badge bg-danger">{{ ato.tipo_ato }}</span>
                </td>
                <td>{{ ato.numero_portaria }}</td>
                <td>{{ ato.instituicao?.razao_social }}</td>
                <td>{{ formatDate(ato.data_validade_ato) }}</td>
                <td>
                  <span class="text-danger fw-bold">
                    {{ calcularDiasVencidos(ato.data_validade_ato) }}
                  </span>
                </td>
                <td class="text-center">
                  <router-link 
                    :to="`/admin/institucional/instituicoes/${ato.instituicao_id}/atos-regulatorios`" 
                    class="btn btn-sm btn-primary"
                    title="Ver Atos"
                  >
                    <i class="bi bi-eye"></i>
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ATOS A VENCER -->
      <div v-if="alertas.a_vencer_30_dias?.length > 0" class="card card-glass mb-4">
        <div class="card-header bg-warning text-dark">
          <i class="bi bi-clock-fill me-2"></i>
          <strong>ATOS A VENCER EM 30 DIAS ({{ alertas.a_vencer_30_dias.length }})</strong>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4">Tipo</th>
                <th>Número</th>
                <th>Instituição</th>
                <th>Data Validade</th>
                <th>Vence em</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ato in alertas.a_vencer_30_dias" :key="ato.id">
                <td class="ps-4">
                  <span class="badge bg-warning text-dark">{{ ato.tipo_ato }}</span>
                </td>
                <td>{{ ato.numero_portaria }}</td>
                <td>{{ ato.instituicao?.razao_social }}</td>
                <td>{{ formatDate(ato.data_validade_ato) }}</td>
                <td>
                  <span class="text-warning fw-bold">
                    {{ calcularDiasRestantes(ato.data_validade_ato) }}
                  </span>
                </td>
                <td class="text-center">
                  <router-link 
                    :to="`/admin/institucional/instituicoes/${ato.instituicao_id}/atos-regulatorios`" 
                    class="btn btn-sm btn-primary"
                    title="Ver Atos"
                  >
                    <i class="bi bi-eye"></i>
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- MENSAGEM SE TUDO OK -->
      <div v-if="alertas.vencidos?.length === 0 && alertas.a_vencer_30_dias?.length === 0" class="card card-glass">
        <div class="card-body text-center py-5">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
          <h4 class="mt-3 text-success">Todos os Atos estão em dia!</h4>
          <p class="text-muted">Não há atos vencidos ou próximos do vencimento.</p>
          <p class="mb-0">
            <strong>{{ alertas.vigentes_count }}</strong> atos vigentes | 
            <strong>{{ alertas.total }}</strong> total
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { differenceInDays, parseISO } from 'date-fns';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';

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

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('pt-BR');
};

const calcularDiasVencidos = (dataValidade) => {
  if (!dataValidade) return 'N/A';
  
  try {
    const dias = Math.abs(differenceInDays(parseISO(dataValidade), new Date()));
    return `${dias} dia${dias !== 1 ? 's' : ''}`;
  } catch (error) {
    console.error('Erro ao calcular dias vencidos:', error);
    return 'N/A';
  }
};

const calcularDiasRestantes = (dataValidade) => {
  if (!dataValidade) return 'N/A';
  
  try {
    const dias = differenceInDays(parseISO(dataValidade), new Date());
    return `${dias} dia${dias !== 1 ? 's' : ''}`;
  } catch (error) {
    console.error('Erro ao calcular dias restantes:', error);
    return 'N/A';
  }
};

onMounted(() => {
  fetchAlertas();
});
</script>

<style scoped>
.bg-danger {
  background-color: rgba(220, 53, 69, 0.2) !important;
  border-bottom: 2px solid #dc3545;
}

.bg-warning {
  background-color: rgba(255, 193, 7, 0.2) !important;
  border-bottom: 2px solid #ffc107;
}
</style>
