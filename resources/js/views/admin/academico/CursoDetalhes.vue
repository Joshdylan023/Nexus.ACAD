<template>
  <div class="container-fluid py-4">
    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loading" class="skeleton-container">
      <div class="skeleton skeleton-title mb-3"></div>
      <div class="skeleton skeleton-card mb-3"></div>
      <div class="skeleton skeleton-card mb-3"></div>
    </div>

    <!-- ⭐ CONTEÚDO -->
    <div v-else>
      <!-- ✅ BREADCRUMB -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-glass border-glass p-3">
          <li class="breadcrumb-item">
            <router-link to="/admin/dashboard" class="text-white">
              <i class="bi bi-house-door me-1"></i>Dashboard
            </router-link>
          </li>
          <li class="breadcrumb-item">
            <router-link to="/admin/academico" class="text-white">Gestão Acadêmica</router-link>
          </li>
          <li class="breadcrumb-item">
            <router-link to="/admin/academico/cursos" class="text-white">Cursos</router-link>
          </li>
          <li class="breadcrumb-item active text-white-50">{{ curso.nome }}</li>
        </ol>
      </nav>

      <!-- ✅ CABEÇALHO COM AÇÕES -->
      <div class="card bg-glass border-glass mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="flex-grow-1">
              <h2 class="text-white mb-2">
                <i class="bi bi-mortarboard me-2"></i>
                {{ curso.nome }}
              </h2>
              <p class="text-white-50 mb-3">{{ curso.codigo_ies }}</p>
              
              <!-- Badges -->
              <div class="d-flex flex-wrap gap-2">
                <span v-if="curso.instituicao" class="badge bg-primary">
                  <i class="bi bi-building me-1"></i>
                  {{ curso.instituicao.nome_fantasia }}
                </span>
                <span v-if="curso.campus" class="badge bg-info">
                  <i class="bi bi-geo-alt me-1"></i>
                  {{ curso.campus.nome }}
                </span>
                <span class="badge bg-success">
                  <i class="bi bi-mortarboard me-1"></i>
                  {{ curso.nivel }}
                </span>
                <span class="badge bg-warning text-dark">
                  <i class="bi bi-laptop me-1"></i>
                  {{ curso.modalidade }}
                </span>
                <span class="badge" :class="getStatusClass(curso.status)">
                  {{ curso.status }}
                </span>
              </div>
            </div>

            <!-- Botões -->
            <div class="d-flex gap-2 flex-wrap">
              <router-link 
                :to="{ name: 'admin.academico.cursos.atos', params: { id: curso.id } }"
                class="btn btn-info"
              >
                <i class="bi bi-file-earmark-text me-2"></i>
                Atos Regulatórios
              </router-link>
              <button @click="$router.go(-1)" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Voltar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ GRID DE INFORMAÇÕES -->
      <div class="row">
        <!-- Card 1: Instituição -->
        <div class="col-lg-6 mb-4">
          <div class="card bg-glass border-glass h-100">
            <div class="card-header bg-glass-dark border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-building me-2"></i>Instituição e Campus
              </h5>
            </div>
            <div class="card-body">
              <div class="info-item">
                <span class="info-label">Instituição:</span>
                <span class="info-value">{{ curso.instituicao?.nome_fantasia || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Campus:</span>
                <span class="info-value">{{ curso.campus?.nome || 'Todos os campi' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Código IES:</span>
                <span class="info-value">{{ curso.codigo_ies }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Classificação -->
        <div class="col-lg-6 mb-4">
          <div class="card bg-glass border-glass h-100">
            <div class="card-header bg-glass-dark border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-bookmark me-2"></i>Classificação Acadêmica
              </h5>
            </div>
            <div class="card-body">
              <div class="info-item">
                <span class="info-label">Catálogo:</span>
                <span class="info-value">{{ curso.catalogo_curso?.nome || 'Não vinculado' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Área de Conhecimento:</span>
                <span class="info-value">{{ curso.area_conhecimento?.nome || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Grande Área:</span>
                <span class="info-value">{{ curso.area_conhecimento?.grande_area?.nome || 'N/A' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Configurações -->
        <div class="col-lg-6 mb-4">
          <div class="card bg-glass border-glass h-100">
            <div class="card-header bg-glass-dark border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-gear me-2"></i>Configurações do Curso
              </h5>
            </div>
            <div class="card-body">
              <div class="info-item">
                <span class="info-label">Nível:</span>
                <span class="info-value">{{ curso.nivel }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Modalidade:</span>
                <span class="info-value">{{ curso.modalidade }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Status:</span>
                <span>
                  <span class="badge" :class="getStatusClass(curso.status)">
                    {{ curso.status }}
                  </span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Duração e Vagas -->
        <div class="col-lg-6 mb-4">
          <div class="card bg-glass border-glass h-100">
            <div class="card-header bg-glass-dark border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-calendar-range me-2"></i>Duração e Vagas
              </h5>
            </div>
            <div class="card-body">
              <div class="info-item">
                <span class="info-label">Duração Padrão:</span>
                <span class="info-value">{{ curso.duracao_padrao_semestres }} semestres</span>
              </div>
              <div class="info-item">
                <span class="info-label">Prazo Máximo:</span>
                <span class="info-value">{{ curso.prazo_maximo_semestres }} semestres</span>
              </div>
              <div class="info-item">
                <span class="info-label">Vagas Anuais:</span>
                <span class="info-value">{{ curso.vagas_anuais || 'Não definido' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 5: Coordenação -->
        <div class="col-lg-6 mb-4">
          <div class="card bg-glass border-glass h-100">
            <div class="card-header bg-glass-dark border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-person-badge me-2"></i>Coordenação
              </h5>
            </div>
            <div class="card-body">
              <div v-if="curso.coordenador">
                <div class="d-flex align-items-center">
                  <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                    <i class="bi bi-person-fill"></i>
                  </div>
                  <div>
                    <h6 class="text-white mb-1">
                      {{ curso.coordenador.usuario?.name || curso.coordenador.name }}
                    </h6>
                    <small class="text-white-50">
                      Mat: {{ curso.coordenador.matricula_funcional }}
                    </small>
                  </div>
                </div>
              </div>
              <div v-else>
                <p class="text-white-50 mb-0">
                  <i class="bi bi-exclamation-circle me-2"></i>
                  Sem coordenador vinculado
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 6: Histórico -->
        <div class="col-lg-6 mb-4">
          <div class="card bg-glass border-glass h-100">
            <div class="card-header bg-glass-dark border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-clock-history me-2"></i>Histórico
              </h5>
            </div>
            <div class="card-body">
              <div class="info-item">
                <span class="info-label">Criado em:</span>
                <span class="info-value">{{ formatDate(curso.created_at) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Última atualização:</span>
                <span class="info-value">{{ formatDate(curso.updated_at) }}</span>
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
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const cursoId = ref(route.params.id);
const curso = ref({});
const loading = ref(true);

const fetchCurso = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/cursos/${cursoId.value}`);
    curso.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar curso:', error);
    alert('Erro ao carregar os dados do curso.');
    router.push('/admin/academico/cursos');
  } finally {
    loading.value = false;
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'Ativo': return 'bg-success';
    case 'Em Extinção': return 'bg-warning text-dark';
    case 'Extinto': return 'bg-danger';
    default: return 'bg-secondary';
  }
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('pt-BR');
};

onMounted(() => {
  fetchCurso();
});
</script>

<style scoped>
@import '@/styles/BaseListViewStyles.css';

/* ✅ INFO ITEMS */
.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 500;
  font-size: 0.9rem;
}

.info-value {
  color: #fff;
  font-weight: 500;
  text-align: right;
}

/* ✅ AVATAR */
.avatar {
  width: 50px;
  height: 50px;
  font-size: 1.5rem;
}

/* ✅ SKELETON */
.skeleton-container {
  padding: 2rem;
}

.skeleton {
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: 8px;
}

.skeleton-title {
  height: 40px;
  width: 60%;
}

.skeleton-card {
  height: 200px;
  width: 100%;
}

@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
