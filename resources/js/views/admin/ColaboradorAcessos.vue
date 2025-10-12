<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Gerir Acessos de: {{ colaborador?.usuario?.name }}</h2>
      <router-link :to="`/admin/pessoas-acessos/colaboradores/${id}`" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar aos Detalhes
      </router-link>
    </div>

    <div class="card card-glass">
      <div class="card-body">
        <h5 class="text-white">Instituições com Acesso</h5>
        <!-- Adicionar lógica para listar e adicionar/remover instituições -->
        <p class="text-white-50">Funcionalidade para associar instituições ao colaborador.</p>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">

        <h5 class="text-white">Perfis (Roles)</h5>
        <!-- Adicionar lógica para listar e associar perfis -->
        <p class="text-white-50">Funcionalidade para associar perfis (roles) de permissão.</p>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">

        <h5 class="text-white">Permissões Avulsas</h5>
        <!-- Adicionar lógica para listar e associar permissões individuais -->
        <p class="text-white-50">Funcionalidade para conceder permissões específicas que não fazem parte de um perfil.</p>

        <div class="mt-4">
            <button class="btn btn-primary">Salvar Alterações de Acesso</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue';
import axios from 'axios';

const props = defineProps({ id: String });
const colaborador = ref(null);

const fetchColaborador = async () => {
  try {
    const response = await axios.get(`/api/v1/colaboradores/${props.id}`);
    colaborador.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar dados do colaborador:", error);
  }
};

onMounted(() => {
  fetchColaborador();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
