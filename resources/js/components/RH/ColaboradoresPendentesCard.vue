<template>
  <div class="card-alerta" :class="{ 'tem-pendencias': total > 0 }">
    <!-- Header -->
    <div class="card-header">
      <div class="header-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div class="header-content">
        <h3 class="card-title">Colaboradores Pendentes</h3>
        <p class="card-subtitle">Informações incompletas</p>
      </div>
      <div class="badge-total" v-if="total > 0">
        {{ total }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner-border spinner-border-sm" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
      <span class="ms-2">Carregando...</span>
    </div>

    <!-- Conteúdo -->
    <div v-else class="card-body">
      <!-- Nenhuma pendência -->
      <div v-if="total === 0" class="sem-pendencias">
        <i class="bi bi-check-circle-fill text-success"></i>
        <p class="mb-0">Todos os colaboradores estão com informações completas!</p>
      </div>

      <!-- Lista de pendências -->
      <div v-else>
        <div class="alert alert-warning mb-3">
          <strong>{{ total }} colaborador{{ total > 1 ? 'es' : '' }}</strong> 
          aguardando preenchimento de informações
        </div>

        <!-- Tabela compacta -->
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th>Colaborador</th>
                <th>Matrícula</th>
                <th>Pendências</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="colab in colaboradoresPendentes.slice(0, 5)" :key="colab.id">
                <td>
                  <div class="d-flex flex-column">
                    <span class="fw-bold">{{ colab.nome }}</span>
                    <small class="text-muted">{{ colab.cargo }}</small>
                  </div>
                </td>
                <td>
                  <span class="badge bg-light text-dark">{{ colab.matricula }}</span>
                </td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <span 
                      v-for="campo in colab.campos_pendentes" 
                      :key="campo"
                      class="badge bg-warning text-dark"
                      style="font-size: 0.7rem;"
                    >
                      {{ campo }}
                    </span>
                  </div>
                </td>
                <td class="text-end">
                  <button 
                    class="btn btn-sm btn-primary"
                    @click="editarColaborador(colab.id)"
                  >
                    <i class="bi bi-pencil-fill"></i>
                    Editar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Link para ver todos -->
        <div v-if="total > 5" class="text-center mt-3">
          <button class="btn btn-outline-primary btn-sm" @click="verTodosPendentes">
            Ver todos os {{ total }} colaboradores pendentes
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const loading = ref(true);
const total = ref(0);
const colaboradoresPendentes = ref([]);

const carregarPendentes = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/relatorios/colaboradores-pendentes');
    total.value = response.data.total;
    colaboradoresPendentes.value = response.data.colaboradores;
  } catch (error) {
    console.error('Erro ao carregar colaboradores pendentes:', error);
  } finally {
    loading.value = false;
  }
};

const editarColaborador = (id) => {
  router.push(`/admin/pessoas-acessos/colaboradores/${id}/editar`);
};

const verTodosPendentes = () => {
  router.push('/colaboradores?filtro=pendentes');
};

onMounted(() => {
  carregarPendentes();
});
</script>

<style scoped>
.card-alerta {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.card-alerta.tem-pendencias {
  border-left: 4px solid #ffc107;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 193, 7, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #ffc107;
}

.tem-pendencias .header-icon {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.header-content {
  flex: 1;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: var(--text-primary);
}

.card-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

.badge-total {
  width: 40px;
  height: 40px;
  background: #ffc107;
  color: #000;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
}

.loading-state {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.sem-pendencias {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.sem-pendencias i {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
}

.table {
  margin-bottom: 0;
}

.table th {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  color: var(--text-secondary);
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.table td {
  vertical-align: middle;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
