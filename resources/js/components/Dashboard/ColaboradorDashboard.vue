<template>
  <div class="colaborador-dashboard">
    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loading">
      <!-- Header Skeleton -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <div class="skeleton skeleton-text skeleton-title mb-3"></div>
              <div class="skeleton skeleton-text skeleton-line mb-2"></div>
              <div class="skeleton skeleton-text skeleton-line mb-2"></div>
              <div class="skeleton skeleton-text skeleton-line"></div>
            </div>
            <div class="col-md-4 text-end">
              <div class="skeleton skeleton-circle mx-auto"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Estatísticas Rápidas Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-4" v-for="n in 3" :key="n">
          <div class="card card-glass card-stat">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                  <div class="skeleton skeleton-text skeleton-line mb-2"></div>
                  <div class="skeleton skeleton-text skeleton-title"></div>
                </div>
                <div class="skeleton skeleton-icon"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Widgets Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-3" v-for="n in 4" :key="n">
          <div class="card card-glass card-widget">
            <div class="card-body">
              <div class="skeleton skeleton-text skeleton-line mb-2"></div>
              <div class="skeleton skeleton-text skeleton-title"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Indicadores Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-6" v-for="n in 2" :key="n">
          <div class="card card-glass">
            <div class="card-header">
              <div class="skeleton skeleton-text skeleton-line"></div>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-6" v-for="m in 4" :key="m">
                  <div class="skeleton skeleton-text skeleton-line mb-1"></div>
                  <div class="skeleton skeleton-text skeleton-title"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações Rápidas Skeleton -->
      <div class="card card-glass mb-4">
        <div class="card-header">
          <div class="skeleton skeleton-text skeleton-line"></div>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-2" v-for="n in 6" :key="n">
              <div class="skeleton skeleton-action-card"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Atividades Skeleton -->
      <div class="card card-glass">
        <div class="card-header">
          <div class="skeleton skeleton-text skeleton-line"></div>
        </div>
        <div class="card-body">
          <div class="timeline">
            <div class="timeline-item" v-for="n in 5" :key="n">
              <div class="timeline-marker skeleton-pulse"></div>
              <div class="timeline-content">
                <div class="skeleton skeleton-text skeleton-line mb-2"></div>
                <div class="skeleton skeleton-text skeleton-line"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Header: Saudação -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h2 class="text-white mb-2">{{ dashboard.saudacao }}</h2>
              <div class="info-colaborador" v-if="dashboard.info_colaborador">
                <p class="text-white-50 mb-1">
                  <i class="fas fa-briefcase me-2"></i>{{ dashboard.info_colaborador.cargo }}
                  <span v-if="dashboard.info_colaborador.is_gestor" class="badge bg-primary ms-2">
                    <i class="fas fa-crown me-1"></i>Gestor
                  </span>
                </p>
                <p class="text-white-50 mb-1">
                  <i class="fas fa-building me-2"></i>{{ dashboard.info_colaborador.setor }}
                </p>
                <p class="text-white-50 mb-1">
                  <i class="fas fa-map-marker-alt me-2"></i>{{ dashboard.info_colaborador.unidade_lotacao }}
                </p>
                <p class="text-white-50 mb-0" v-if="dashboard.info_colaborador.tempo_empresa">
                  <i class="fas fa-calendar-alt me-2"></i>Na empresa {{ dashboard.info_colaborador.tempo_empresa }}
                </p>
              </div>
            </div>
            <div class="col-md-4 text-end">
              <img 
                v-if="dashboard.info_colaborador?.foto" 
                :src="dashboard.info_colaborador.foto" 
                alt="Foto"
                class="rounded-circle foto-colaborador"
              >
              <div v-else class="rounded-circle foto-placeholder d-inline-flex align-items-center justify-content-center">
                <i class="fas fa-user fa-3x text-white-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Estatísticas Rápidas -->
      <div v-if="dashboard.estatisticas_rapidas" class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card card-glass card-stat">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Total de Registros</p>
                  <h3 class="text-white mb-0">{{ dashboard.estatisticas_rapidas.total_registros }}</h3>
                </div>
                <div class="stat-icon bg-primary">
                  <i class="fas fa-database"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-glass card-stat">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Minhas Ações Hoje</p>
                  <h3 class="text-white mb-0">{{ dashboard.estatisticas_rapidas.minhas_acoes_hoje }}</h3>
                </div>
                <div class="stat-icon bg-success">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-glass card-stat">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Ações Esta Semana</p>
                  <h3 class="text-white mb-0">{{ dashboard.estatisticas_rapidas.minhas_acoes_semana }}</h3>
                </div>
                <div class="stat-icon bg-warning">
                  <i class="fas fa-chart-line"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pendências & Alertas -->
      <div v-if="dashboard.pendencias?.lista.length > 0" class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="text-white mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>Pendências & Alertas
          </h5>
          <span class="badge bg-danger">{{ dashboard.pendencias.total }}</span>
        </div>
        
        <div class="row g-3">
          <div 
            v-for="pendencia in dashboard.pendencias.lista" 
            :key="pendencia.titulo"
            class="col-md-4"
          >
            <div class="card card-glass card-danger h-100">
              <div class="card-body">
                <div class="d-flex align-items-start">
                  <div class="pendencia-icon me-3">
                    <i :class="pendencia.icone"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-2 text-white">{{ pendencia.titulo }}</h6>
                    <p class="mb-3 text-white-50 small">{{ pendencia.descricao }}</p>
                    <router-link v-if="pendencia.link" :to="pendencia.link" class="btn btn-sm btn-outline-light">
                      Ver Detalhes <i class="fas fa-arrow-right ms-1"></i>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Widgets Contextuais -->
      <div v-if="dashboard.widgets_contextuais?.length > 0" class="row g-3 mb-4">
        <div 
          v-for="widget in dashboard.widgets_contextuais" 
          :key="widget.tipo"
          :class="dashboard.widgets_contextuais.length === 3 ? 'col-md-4' : 'col-md-3'"
        >
          <div class="card card-glass card-widget" :class="`widget-${widget.cor}`">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">{{ widget.titulo }}</p>
                  <h3 class="text-white mb-0">{{ widget.valor }}</h3>
                </div>
                <div class="widget-icon">
                  <i :class="widget.icone"></i>
                </div>
              </div>
              <router-link 
                v-if="widget.link" 
                :to="widget.link" 
                class="btn btn-sm btn-link text-white p-0 mt-2"
              >
                Ver mais <i class="fas fa-arrow-right ms-1"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Indicadores por Módulo -->
      <div v-if="dashboard.indicadores && Object.keys(dashboard.indicadores).length > 0" class="row g-3 mb-4">
        <!-- Institucional -->
        <div v-if="dashboard.indicadores.institucional" class="col-md-6">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="mb-0 text-white">
                <i class="fas fa-building me-2"></i>Módulo Institucional
              </h5>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-6">
                  <div class="stat-item">
                    <div class="stat-value text-white">{{ dashboard.indicadores.institucional.grupos_educacionais }}</div>
                    <div class="stat-label text-white-50">Grupos Educacionais</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="stat-item">
                    <div class="stat-value text-white">{{ dashboard.indicadores.institucional.mantenedoras }}</div>
                    <div class="stat-label text-white-50">Mantenedoras</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="stat-item">
                    <div class="stat-value text-white">
                      {{ dashboard.indicadores.institucional.instituicoes_ativas }}/{{ dashboard.indicadores.institucional.instituicoes }}
                    </div>
                    <div class="stat-label text-white-50">Instituições Ativas</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="stat-item">
                    <div class="stat-value text-white">{{ dashboard.indicadores.institucional.campi }}</div>
                    <div class="stat-label text-white-50">Campi</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RH -->
        <div v-if="dashboard.indicadores.rh" class="col-md-6">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="mb-0 text-white">
                <i class="fas fa-users me-2"></i>Recursos Humanos
              </h5>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-4">
                  <div class="stat-item">
                    <div class="stat-value text-success">{{ dashboard.indicadores.rh.colaboradores_ativos }}</div>
                    <div class="stat-label text-white-50">Ativos</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="stat-item">
                    <div class="stat-value text-warning">{{ dashboard.indicadores.rh.colaboradores_afastados }}</div>
                    <div class="stat-label text-white-50">Afastados</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="stat-item">
                    <div class="stat-value text-danger">{{ dashboard.indicadores.rh.colaboradores_desligados }}</div>
                    <div class="stat-label text-white-50">Desligados</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="stat-item">
                    <div class="stat-value text-white">{{ dashboard.indicadores.rh.aniversariantes_mes }}</div>
                    <div class="stat-label text-white-50">
                      <i class="fas fa-birthday-cake me-1"></i>Aniversariantes Mês
                    </div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="stat-item">
                    <div class="stat-value text-white">{{ dashboard.indicadores.rh.gestores }}</div>
                    <div class="stat-label text-white-50">
                      <i class="fas fa-crown me-1"></i>Gestores
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações Rápidas -->
      <div v-if="dashboard.acoes_rapidas?.length > 0" class="card card-glass mb-4">
        <div class="card-header">
          <h5 class="mb-0 text-white">
            <i class="fas fa-bolt me-2"></i>Ações Rápidas
          </h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div 
              v-for="acao in dashboard.acoes_rapidas" 
              :key="acao.rota"
              class="col-md-2"
            >
              <router-link :to="acao.rota" class="acao-rapida-card" :class="`acao-${acao.cor}`">
                <div class="acao-icon">
                  <i :class="acao.icone"></i>
                </div>
                <div class="acao-content">
                  <h6 class="mb-1">{{ acao.label }}</h6>
                  <p class="mb-0 small">{{ acao.descricao }}</p>
                </div>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Atividades Recentes -->
      <div v-if="dashboard.atividades_recentes?.length > 0" class="card card-glass">
        <div class="card-header">
          <h5 class="mb-0 text-white">
            <i class="fas fa-history me-2"></i>Minhas Atividades Recentes
          </h5>
        </div>
        <div class="card-body">
          <div class="timeline">
            <div 
              v-for="atividade in dashboard.atividades_recentes" 
              :key="atividade.id"
              class="timeline-item"
            >
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-1">
                      <i :class="atividade.icone + ' me-2'"></i>
                      <h6 class="mb-0 text-white">{{ atividade.descricao }}</h6>
                    </div>
                    <p class="mb-0 text-white-50 small">
                      <span class="badge bg-secondary me-2">{{ atividade.modulo }}</span>
                      {{ atividade.data_relativa }}
                    </p>
                  </div>
                  <span class="text-white-50 small">{{ atividade.data }}</span>
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
import axios from 'axios';

const loading = ref(true);
const dashboard = ref({
  saudacao: '',
  info_colaborador: null,
  pendencias: { lista: [] },
  indicadores: {},
  acoes_rapidas: [],
  atividades_recentes: [],
  widgets_contextuais: [],
  estatisticas_rapidas: {},
});

const fetchDashboard = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/dashboard/colaborador');
    dashboard.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>

<style scoped>
.colaborador-dashboard {
  padding: 1rem;
}

.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

/* Foto do Colaborador */
.foto-colaborador {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.foto-placeholder {
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.1);
  border: 3px solid rgba(255, 255, 255, 0.3);
}

/* Estatísticas Rápidas */
.card-stat {
  transition: transform 0.3s;
}

.card-stat:hover {
  transform: translateY(-5px);
}

.stat-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.5rem;
  color: white;
}

/* Pendências - Cards Inline */
.card-danger {
  border: 1px solid rgba(220, 53, 69, 0.5);
  background: rgba(220, 53, 69, 0.1);
}

.pendencia-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(220, 53, 69, 0.2);
  border-radius: 10px;
  font-size: 1.5rem;
  color: #dc3545;
  flex-shrink: 0;
}

/* Widgets */
.card-widget {
  transition: transform 0.3s;
}

.card-widget:hover {
  transform: translateY(-5px);
}

.widget-icon {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  font-size: 2rem;
}

.widget-purple .widget-icon { color: #8e44ad; }
.widget-blue .widget-icon { color: #3498db; }
.widget-green .widget-icon { color: #27ae60; }
.widget-orange .widget-icon { color: #e67e22; }

/* Estatísticas */
.stat-item {
  padding: 1rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: bold;
}

.stat-label {
  font-size: 0.875rem;
}

/* Ações Rápidas */
.acao-rapida-card {
  display: block;
  padding: 1.5rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  text-decoration: none;
  color: white;
  border: 2px solid transparent;
  transition: all 0.3s;
  text-align: center;
  height: 100%;
}

.acao-rapida-card:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-5px);
}

.acao-primary:hover { border-color: #007bff; }
.acao-success:hover { border-color: #28a745; }
.acao-info:hover { border-color: #17a2b8; }
.acao-warning:hover { border-color: #ffc107; }
.acao-secondary:hover { border-color: #6c757d; }
.acao-danger:hover { border-color: #dc3545; }

.acao-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  opacity: 0.8;
}

.acao-content h6 {
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.acao-content p {
  font-size: 0.75rem;
  opacity: 0.7;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 30px;
}

.timeline-item {
  position: relative;
  padding-bottom: 1.5rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -30px;
  top: 5px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #007bff;
  border: 3px solid rgba(255, 255, 255, 0.2);
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -24.5px;
  top: 15px;
  width: 1px;
  height: calc(100% - 5px);
  background: rgba(255, 255, 255, 0.2);
}

.timeline-item:last-child::before {
  display: none;
}

.timeline-content {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

/* ================================
   SKELETON LOADING
   ================================ */

.skeleton {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05) 25%,
    rgba(255, 255, 255, 0.15) 50%,
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Skeleton Shapes */
.skeleton-text {
  height: 16px;
  margin-bottom: 8px;
}

.skeleton-title {
  height: 32px;
  width: 60%;
}

.skeleton-line {
  height: 14px;
  width: 80%;
}

.skeleton-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
}

.skeleton-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
}

.skeleton-action-card {
  height: 120px;
  border-radius: 10px;
}

/* Timeline Skeleton específico */
.timeline .skeleton-text {
  background: rgba(255, 255, 255, 0.08);
}
</style>
