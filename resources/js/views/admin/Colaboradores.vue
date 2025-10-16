<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gestão de Colaboradores</h2>
      <router-link to="/admin/pessoas-acessos/colaboradores/novo" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Colaborador
      </router-link>
    </div>

    <!-- Barra de Busca e Filtros -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-dark border-secondary">
                <i class="bi bi-search text-white"></i>
              </span>
              <input 
                type="text" 
                class="form-control bg-dark text-white border-secondary" 
                v-model="searchQuery"
                @input="debouncedSearch"
                placeholder="Buscar por nome ou matrícula..."
              >
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select bg-dark text-white border-secondary" v-model="statusFilter" @change="fetchColaboradores">
              <option value="">Todos os Status</option>
              <option value="Ativo">Ativo</option>
              <option value="Afastado">Afastado</option>
              <option value="Desligado">Desligado</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select bg-dark text-white border-secondary" v-model="perPage" @change="fetchColaboradores">
              <option :value="10">10 por página</option>
              <option :value="15">15 por página</option>
              <option :value="25">25 por página</option>
              <option :value="50">50 por página</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-glass">
      <div class="card-header"><h4>Colaboradores Cadastrados</h4></div>
      <div class="card-body p-0">
        <!-- Skeleton Loading -->
        <div v-if="loading">
          <table class="table table-hover mb-0 text-white">
            <thead>
              <tr>
                <th class="ps-4">Nome</th>
                <th>Matrícula</th>
                <th>Cargo</th>
                <th>Status</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="n in 5" :key="n">
                <td class="ps-4"><div class="skeleton skeleton-text"></div></td>
                <td><div class="skeleton skeleton-text" style="width: 80px;"></div></td>
                <td><div class="skeleton skeleton-text"></div></td>
                <td><div class="skeleton skeleton-badge"></div></td>
                <td class="text-center"><div class="skeleton skeleton-button"></div></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Tabela de Colaboradores -->
        <table v-else class="table table-hover mb-0 text-white">
          <thead>
            <tr>
              <th class="ps-4">Nome</th>
              <th>Matrícula</th>
              <th>Cargo</th>
              <th>Status</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="colaboradores.length === 0">
              <td colspan="5" class="text-center py-4">
                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-2 mb-0">Nenhum colaborador encontrado</p>
              </td>
            </tr>
            <tr v-for="colaborador in colaboradores" :key="colaborador.id">
              <td class="ps-4">{{ colaborador.usuario.name }}</td>
              <td>{{ colaborador.matricula_funcional }}</td>
              <td>{{ colaborador.cargo }}</td>
              <td><span class="badge" :class="getStatusClass(colaborador.status)">{{ colaborador.status }}</span></td>
              <td class="text-center">
                <router-link :to="`/admin/pessoas-acessos/colaboradores/${colaborador.id}`" class="btn btn-sm btn-info me-2" title="Visualizar Cadastro">
                  <i class="bi bi-eye"></i>
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginação -->
        <div v-if="!loading && pagination.total > 0" class="d-flex justify-content-between align-items-center p-3">
          <div class="text-white">
            Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
          </div>
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <button class="page-link" @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
                  Anterior
                </button>
              </li>
              <li 
                v-for="page in paginationPages" 
                :key="page" 
                class="page-item" 
                :class="{ active: page === pagination.current_page, disabled: page === '...' }"
              >
                <button class="page-link" @click="goToPage(page)" :disabled="page === '...'">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <button class="page-link" @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">
                  Próxima
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const colaboradores = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');
const perPage = ref(15);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0
});

let searchTimeout = null;

const fetchColaboradores = async (page = 1) => {
  try {
    loading.value = true;
    const params = {
      page,
      per_page: perPage.value,
      search: searchQuery.value,
      status: statusFilter.value
    };
    
    const response = await axios.get('/api/v1/colaboradores', { params });
    colaboradores.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      from: response.data.from,
      to: response.data.to,
      total: response.data.total
    };
  } catch (error) { 
    console.error("Erro ao buscar colaboradores:", error); 
  } finally { 
    loading.value = false; 
  }
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchColaboradores();
  }, 500);
};

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== '...') {
    fetchColaboradores(page);
  }
};

const paginationPages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i);
      pages.push('...');
      pages.push(last);
    } else if (current >= last - 3) {
      pages.push(1);
      pages.push('...');
      for (let i = last - 4; i <= last; i++) pages.push(i);
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) pages.push(i);
      pages.push('...');
      pages.push(last);
    }
  }
  
  return pages;
});

const getStatusClass = (status) => {
  if (status === 'Ativo') return 'bg-success';
  if (status === 'Afastado') return 'bg-warning';
  if (status === 'Desligado') return 'bg-danger';
  return 'bg-secondary';
};

onMounted(() => {
  fetchColaboradores();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: white;
}

th {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

td {
  color: rgba(255, 255, 255, 0.8);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Skeleton Loading */
.skeleton {
  background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 4px;
}

.skeleton-text {
  height: 16px;
  width: 100%;
}

.skeleton-badge {
  height: 20px;
  width: 60px;
  display: inline-block;
}

.skeleton-button {
  height: 30px;
  width: 70px;
  display: inline-block;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Pagination */
.pagination .page-link {
  background-color: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.pagination .page-link:hover {
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
}

.pagination .page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.pagination .page-item.disabled .page-link {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Inputs e Selects */
.form-control:focus,
.form-select:focus {
  background-color: #2d2d2d;
  border-color: #0d6efd;
  color: white;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group-text {
  background-color: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
