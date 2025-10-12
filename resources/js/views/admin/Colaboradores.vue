<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gestão de Colaboradores</h2>
      <router-link to="/admin/pessoas-acessos/colaboradores/novo" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Colaborador
      </router-link>
    </div>

    <div class="card card-glass">
      <div class="card-header"><h4>Colaboradores Cadastrados</h4></div>
      <div class="card-body p-0">
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const colaboradores = ref([]);
const loading = ref(true);

const fetchColaboradores = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/colaboradores');
    colaboradores.value = response.data;
  } catch (error) { 
    console.error("Erro ao buscar colaboradores:", error); 
  } finally { 
    loading.value = false; 
  }
};

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
</style>