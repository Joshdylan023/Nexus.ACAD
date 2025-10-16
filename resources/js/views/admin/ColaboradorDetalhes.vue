<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-white">Detalhes do Colaborador</h2>
      <div v-if="!loading">
        <router-link :to="`/admin/pessoas-acessos/colaboradores/${colaborador.id}/editar`" class="btn btn-warning me-2">
          <i class="bi bi-pencil"></i> Editar Cadastro
        </router-link>
        <router-link :to="`/admin/pessoas-acessos/colaboradores/${colaborador.id}/acessos`" class="btn btn-info me-2">
          <i class="bi bi-key"></i> Gerenciar Acessos
        </router-link>
        <router-link to="/admin/pessoas-acessos/colaboradores" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Voltar
        </router-link>
      </div>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading" class="row">
      <div class="col-md-4 mb-4">
        <div class="card card-glass">
          <div class="card-body text-center">
            <div class="skeleton skeleton-avatar mx-auto mb-3"></div>
            <div class="skeleton skeleton-text mx-auto mb-2" style="width: 60%;"></div>
            <div class="skeleton skeleton-text mx-auto mb-3" style="width: 40%;"></div>
            <div class="skeleton skeleton-badge mx-auto mb-3"></div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="skeleton skeleton-text mx-auto mb-2" style="width: 80%;"></div>
            <div class="skeleton skeleton-text mx-auto" style="width: 70%;"></div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card card-glass mb-4">
          <div class="card-header"><div class="skeleton skeleton-text" style="width: 150px;"></div></div>
          <div class="card-body">
            <div v-for="n in 8" :key="n" class="mb-3">
              <div class="skeleton skeleton-text"></div>
            </div>
          </div>
        </div>
        <div class="card card-glass mb-4">
          <div class="card-header"><div class="skeleton skeleton-text" style="width: 100px;"></div></div>
          <div class="card-body">
            <div class="skeleton skeleton-text"></div>
          </div>
        </div>
        <div class="card card-glass">
          <div class="card-header"><div class="skeleton skeleton-text" style="width: 200px;"></div></div>
          <div class="card-body">
            <div v-for="n in 6" :key="n" class="mb-3">
              <div class="skeleton skeleton-text"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Conteúdo Real -->
    <div v-else class="row">
      <!-- Card Principal -->
      <div class="col-md-4 mb-4">
        <div class="card card-glass text-center">
          <div class="card-body">
            <!-- Foto do Colaborador -->
            <div class="mb-3 d-flex justify-content-center">
              <div 
                class="profile-image-container rounded-circle d-flex align-items-center justify-content-center"
                style="width: 150px; height: 150px; border: 3px solid rgba(255,255,255,0.3); background-color: #6c757d; overflow: hidden;"
              >
                <img 
                  v-if="colaborador.foto_registro_rh"
                  :src="`/storage/${colaborador.foto_registro_rh}`" 
                  alt="Foto do Colaborador" 
                  class="img-fluid"
                  style="width: 100%; height: 100%; object-fit: cover;"
                >
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16" style="color: rgba(255,255,255,0.5);">
                  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                </svg>
              </div>
            </div>
            
            <h4 class="text-white">{{ colaborador.usuario?.name || 'Nome não informado' }}</h4>
            <p class="text-muted mb-1">{{ colaborador.cargo }}</p>
            <span class="badge" :class="getStatusClass(colaborador.status)">{{ colaborador.status }}</span>
            
            <hr style="border-color: rgba(255,255,255,0.2);">
            
            <p class="mb-1"><strong>Matrícula:</strong> {{ colaborador.matricula_funcional }}</p>
            <p class="mb-1"><strong>Email Funcional:</strong> {{ colaborador.email_funcional }}</p>
            <p class="mb-1" v-if="colaborador.is_gestor">
              <span class="badge bg-info">Gestor de Equipe</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Dados Pessoais e Vínculo -->
      <div class="col-md-8">
        <!-- Dados Pessoais -->
        <div class="card card-glass mb-4">
          <div class="card-header"><h5>Dados Pessoais</h5></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-2">
                <p><strong>Nome Completo:</strong> {{ colaborador.usuario?.name || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Nome Social:</strong> {{ colaborador.usuario?.nome_social || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>CPF:</strong> {{ colaborador.usuario?.cpf || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>RG:</strong> {{ colaborador.usuario?.rg || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Órgão Expedidor:</strong> {{ colaborador.usuario?.rg_orgao_expedidor || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Data de Expedição:</strong> {{ formatDate(colaborador.usuario?.rg_data_expedicao) }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Data de Nascimento:</strong> {{ formatDate(colaborador.usuario?.data_nascimento) }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Nacionalidade:</strong> {{ colaborador.usuario?.nacionalidade || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Naturalidade:</strong> {{ getNaturalidade() }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>E-mail Pessoal:</strong> {{ colaborador.usuario?.email || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Telefone Principal:</strong> {{ colaborador.usuario?.telefone_principal || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Telefone Secundário:</strong> {{ colaborador.usuario?.telefone_secundario || 'Não informado' }}</p>
              </div>
              <div class="col-md-12 mb-2">
                <p><strong>Endereço:</strong> {{ colaborador.usuario?.endereco_completo || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Nome do Pai:</strong> {{ colaborador.usuario?.nome_pai || 'Não informado' }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Nome da Mãe:</strong> {{ colaborador.usuario?.nome_mae || 'Não informado' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Formação -->
        <div class="card card-glass mb-4">
          <div class="card-header"><h5>Formação</h5></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 mb-2">
                <p><strong>Instituição do Ensino Médio:</strong> {{ colaborador.usuario?.ensino_medio_instituicao || 'Não informado' }}</p>
              </div>
              <div class="col-md-4 mb-2">
                <p><strong>Ano de Conclusão:</strong> {{ colaborador.usuario?.ensino_medio_ano_conclusao || 'Não informado' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Dados do Vínculo -->
        <div class="card card-glass">
          <div class="card-header"><h5>Dados do Vínculo (Colaborador)</h5></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-2">
                <p><strong>Matrícula Funcional:</strong> {{ colaborador.matricula_funcional }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Email Funcional:</strong> {{ colaborador.email_funcional }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Cargo:</strong> {{ colaborador.cargo }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Data de Admissão:</strong> {{ formatDate(colaborador.data_admissao) }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Status:</strong> <span class="badge" :class="getStatusClass(colaborador.status)">{{ colaborador.status }}</span></p>
              </div>
              <div class="col-md-6 mb-2" v-if="colaborador.status === 'Desligado'">
                <p><strong>Data de Desligamento:</strong> {{ formatDate(colaborador.data_desligamento) }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Unidade Organizacional:</strong> {{ colaborador.unidade_organizacional_nome }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Unidade de Lotação:</strong> {{ colaborador.unidade_lotacao_nome }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Setor de Vínculo:</strong> {{ colaborador.setor_nome }}</p>
              </div>
              <div class="col-md-6 mb-2">
                <p><strong>Gestor Imediato:</strong> {{ colaborador.gestor_imediato?.usuario?.name || 'Não informado' }}</p>
              </div>
              <div class="col-md-12 mb-2">
                <p><strong>É Gestor de Equipe:</strong> {{ colaborador.is_gestor ? 'Sim' : 'Não' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue';
import axios from 'axios';

const props = defineProps({
  id: {
    type: String,
    required: true
  }
});

const colaborador = ref({});
const loading = ref(true);

const fetchColaborador = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/colaboradores/${props.id}`);
    colaborador.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar colaborador:", error);
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

const formatDate = (date) => {
  if (!date) return 'Não informado';
  return new Date(date).toLocaleDateString('pt-BR');
};

const getNaturalidade = () => {
  const cidade = colaborador.value.usuario?.naturalidade_cidade;
  const uf = colaborador.value.usuario?.naturalidade_uf;
  
  if (cidade && uf) return `${cidade} - ${uf}`;
  if (cidade) return cidade;
  if (uf) return uf;
  return 'Não informado';
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

.card-header {
  background: rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

p {
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 0.5rem;
}

strong {
  color: rgba(255, 255, 255, 0.7);
}

/* Skeleton Loading */
.skeleton {
  background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 4px;
}

.skeleton-avatar {
  width: 150px;
  height: 150px;
  border-radius: 50%;
}

.skeleton-text {
  height: 16px;
  width: 100%;
  margin-bottom: 8px;
}

.skeleton-badge {
  height: 22px;
  width: 70px;
  display: inline-block;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
