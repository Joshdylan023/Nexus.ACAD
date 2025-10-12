<template>
  <div>
    <div v-if="loading" class="text-center">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>
    <div v-if="colaborador && !loading">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Detalhes do Colaborador</h2>
        <div>
          <router-link to="/admin/pessoas-acessos/colaboradores" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left"></i> Voltar
          </router-link>
          <router-link :to="`/admin/pessoas-acessos/colaboradores/${colaborador.id}/editar`" class="btn btn-primary me-2">
            <i class="bi bi-pencil-square"></i> Editar Cadastro
          </router-link>
          <router-link :to="`/admin/pessoas-acessos/colaboradores/${colaborador.id}/acessos`" class="btn btn-info">
            <i class="bi bi-shield-lock"></i> Gerir Acessos
          </router-link>
        </div>
      </div>

      <div class="card card-glass">
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 text-center">
              <img :src="getPhotoUrl(colaborador.foto_perfil_sistema)" @error="usePlaceholder" alt="Foto Perfil" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
              <h4>{{ colaborador.usuario.name }}</h4>
              <p class="text-white-50">{{ colaborador.cargo }}</p>
            </div>
            <div class="col-md-9">
              <h5>Dados do Vínculo</h5>
              <p><strong>Matrícula:</strong> {{ colaborador.matricula_funcional }}</p>
              <p><strong>Email Funcional:</strong> {{ colaborador.email_funcional }}</p>
              <p><strong>Data de Admissão:</strong> {{ new Date(colaborador.data_admissao).toLocaleDateString() }}</p>
              <p><strong>Status:</strong> <span class="badge" :class="getStatusClass(colaborador.status)">{{ colaborador.status }}</span></p>
              <p v-if="colaborador.status === 'Desligado'"><strong>Data de Desligamento:</strong> {{ new Date(colaborador.data_desligamento).toLocaleDateString() }}</p>
              <p><strong>É Gestor:</strong> {{ colaborador.is_gestor ? 'Sim' : 'Não' }}</p>
              <p><strong>Unidade Organizacional:</strong> {{ colaborador.unidade_organizacional ? colaborador.unidade_organizacional.nome : 'Não informado' }}</p>
              <p><strong>Unidade de Lotação:</strong> {{ colaborador.unidade_lotacao ? colaborador.unidade_lotacao.nome : 'Não informado' }}</p>
              <p><strong>Setor de Vínculo:</strong> {{ colaborador.setor ? colaborador.setor.nome : 'Não informado' }}</p>
              
              <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">

              <h5>Dados Pessoais</h5>
              <div v-if="colaborador.usuario" class="row">
                <div class="col-md-6">
                    <p><strong>Nome Social:</strong> {{ colaborador.usuario.nome_social || 'Não informado' }}</p>
                    <p><strong>CPF:</strong> {{ colaborador.usuario.cpf }}</p>
                    <p><strong>Data de Nascimento:</strong> {{ colaborador.usuario.data_nascimento ? new Date(colaborador.usuario.data_nascimento).toLocaleDateString() : 'Não informado' }}</p>
                    <hr>
                    <p><strong>RG:</strong> {{ colaborador.usuario.rg || 'Não informado' }}</p>
                    <p><strong>Órgão Expedidor:</strong> {{ colaborador.usuario.rg_orgao_expedidor || 'Não informado' }}</p>
                    <p><strong>Data de Expedição:</strong> {{ colaborador.usuario.rg_data_expedicao ? new Date(colaborador.usuario.rg_data_expedicao).toLocaleDateString() : 'Não informado' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email Pessoal:</strong> {{ colaborador.usuario.email }}</p>
                    <p><strong>Endereço:</strong> {{ colaborador.usuario.endereco_completo || 'Não informado' }}</p>
                    <p><strong>Telefone Principal:</strong> {{ colaborador.usuario.telefone_principal || 'Não informado' }}</p>
                    <p><strong>Telefone Secundário:</strong> {{ colaborador.usuario.telefone_secundario || 'Não informado' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const colaborador = ref(null);
const loading = ref(true);

const fetchColaborador = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/colaboradores/${route.params.id}`, {
      params: {
        '_': new Date().getTime()
      }
    });
    colaborador.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar detalhes do colaborador:", error);
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

const getPhotoUrl = (path) => {
    if (path) {
        return `/storage/${path}`;
    }
    return 'https://via.placeholder.com/150';
};

const usePlaceholder = (event) => {
    event.target.src = 'https://via.placeholder.com/150';
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
  color: white;
}
p {
  margin-bottom: 0.5rem;
}
</style>