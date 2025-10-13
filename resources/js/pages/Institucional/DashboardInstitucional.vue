<template>
  <div>
    <PageHeader 
      title="Dashboard Institucional"
      :breadcrumbs="[{ label: 'Dashboard' }]"
    >
      <template #actions>
        <div class="d-flex gap-2">
          <input 
            type="date" 
            class="form-control form-control-sm" 
            v-model="filters.startDate"
            @change="fetchStats"
          >
          <span class="align-self-center text-muted">até</span>
          <input 
            type="date" 
            class="form-control form-control-sm" 
            v-model="filters.endDate"
            @change="fetchStats"
          >
          <button class="btn btn-sm btn-primary" @click="resetFilters">
            <i class="bi bi-arrow-clockwise"></i> Resetar
          </button>
          <button class="btn btn-sm btn-success" @click="exportToPDF">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Widget de Evento Ativo -->
    <div v-if="stats.evento_ativo" class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
        <div class="flex-grow-1">
          <h5 class="alert-heading mb-1">{{ stats.evento_ativo.title }}</h5>
          <p class="mb-0">{{ stats.evento_ativo.description }}</p>
          <small class="text-muted">
            <i class="bi bi-clock"></i>
            {{ formatEventDates(stats.evento_ativo.start_at, stats.evento_ativo.end_at) }}
          </small>
        </div>
        <span :class="`badge bg-${getEventTypeColor(stats.evento_ativo.type)} fs-6`">
          {{ stats.evento_ativo.type }}
        </span>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <h6 class="mb-3">Ações Rápidas</h6>
        <div class="row g-3">
          <div class="col-md-3">
            <router-link to="/admin/institucional/grupos-educacionais" class="quick-action-btn">
              <i class="bi bi-diagram-3"></i>
              <span>Novo Grupo</span>
            </router-link>
          </div>
          <div class="col-md-3">
            <router-link to="/admin/institucional/mantenedoras" class="quick-action-btn">
              <i class="bi bi-building"></i>
              <span>Nova Mantenedora</span>
            </router-link>
          </div>
          <div class="col-md-3">
            <router-link to="/admin/institucional/instituicoes" class="quick-action-btn">
              <i class="bi bi-bank"></i>
              <span>Nova Instituição</span>
            </router-link>
          </div>
          <div class="col-md-3">
            <router-link to="/admin/institucional/campi" class="quick-action-btn">
              <i class="bi bi-geo-alt"></i>
              <span>Novo Campus</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Cards de Totais -->
    <div class="row g-4 mb-4" id="stats-cards">
      <div class="col-md-4 col-lg-2-4">
        <div class="card card-glass stat-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-label">Grupos Educacionais</h6>
                <h2 class="stat-value">{{ stats.totais?.grupos_educacionais || 0 }}</h2>
              </div>
              <div class="stat-icon bg-primary">
                <i class="bi bi-diagram-3"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2-4">
        <div class="card card-glass stat-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-label">Mantenedoras</h6>
                <h2 class="stat-value">{{ stats.totais?.mantenedoras || 0 }}</h2>
              </div>
              <div class="stat-icon bg-success">
                <i class="bi bi-building"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2-4">
        <div class="card card-glass stat-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-label">Instituições</h6>
                <h2 class="stat-value">{{ stats.totais?.instituicoes || 0 }}</h2>
              </div>
              <div class="stat-icon bg-info">
                <i class="bi bi-bank"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2-4">
        <div class="card card-glass stat-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-label">Campi</h6>
                <h2 class="stat-value">{{ stats.totais?.campi || 0 }}</h2>
              </div>
              <div class="stat-icon bg-warning">
                <i class="bi bi-geo-alt"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-2-4">
        <div class="card card-glass stat-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-label">Setores</h6>
                <h2 class="stat-value">{{ stats.totais?.setores || 0 }}</h2>
              </div>
              <div class="stat-icon bg-danger">
                <i class="bi bi-diagram-2"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparativo Mensal/Anual -->
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="card card-glass">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Comparativo de Crescimento</h5>
            <div class="btn-group" role="group">
              <button 
                type="button" 
                class="btn btn-sm"
                :class="comparativoTipo === 'mensal' ? 'btn-primary' : 'btn-outline-light'"
                @click="comparativoTipo = 'mensal'"
              >
                Mensal
              </button>
              <button 
                type="button" 
                class="btn btn-sm"
                :class="comparativoTipo === 'anual' ? 'btn-primary' : 'btn-outline-light'"
                @click="comparativoTipo = 'anual'"
              >
                Anual
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-3">
                <div class="comparativo-card">
                  <i class="bi bi-arrow-up-circle text-success fs-2"></i>
                  <h3 class="mt-2">+{{ crescimentoAtual.instituicoes }}%</h3>
                  <p class="mb-1">Instituições</p>
                  <small class="text-muted">vs. {{ comparativoTipo === 'mensal' ? 'mês' : 'ano' }} anterior</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="comparativo-card">
                  <i class="bi bi-arrow-up-circle text-success fs-2"></i>
                  <h3 class="mt-2">+{{ crescimentoAtual.campi }}%</h3>
                  <p class="mb-1">Campi</p>
                  <small class="text-muted">vs. {{ comparativoTipo === 'mensal' ? 'mês' : 'ano' }} anterior</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="comparativo-card">
                  <i class="bi bi-arrow-up-circle text-info fs-2"></i>
                  <h3 class="mt-2">+{{ crescimentoAtual.mantenedoras }}%</h3>
                  <p class="mb-1">Mantenedoras</p>
                  <small class="text-muted">vs. {{ comparativoTipo === 'mensal' ? 'mês' : 'ano' }} anterior</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="comparativo-card">
                  <i class="bi bi-arrow-up-circle text-warning fs-2"></i>
                  <h3 class="mt-2">+{{ crescimentoAtual.setores }}%</h3>
                  <p class="mb-1">Setores</p>
                  <small class="text-muted">vs. {{ comparativoTipo === 'mensal' ? 'mês' : 'ano' }} anterior</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráfico de Evolução -->
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="card card-glass">
          <div class="card-header">
            <h5 class="mb-0">Evolução de Cadastros</h5>
          </div>
          <div class="card-body">
            <div style="height: 350px;">
              <LineChart v-if="chartDataEvolucao" :data="chartDataEvolucao" />
              <div v-else class="text-center text-muted py-5">
                <i class="bi bi-graph-up fs-1"></i>
                <p class="mt-2">Carregando dados...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gráficos de Distribuição -->
    <div class="row g-4 mb-4">
      <!-- Gráfico: Instituições por Estado -->
      <div class="col-lg-8">
        <div class="card card-glass">
          <div class="card-header">
            <h5 class="mb-0">Instituições por Estado (Top 10)</h5>
          </div>
          <div class="card-body">
            <div style="height: 350px;">
              <BarChart v-if="chartDataEstados" :data="chartDataEstados" />
              <div v-else class="text-center text-muted py-5">
                <i class="bi bi-graph-up fs-1"></i>
                <p class="mt-2">Carregando dados...</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráfico: Instituições por Tipo -->
      <div class="col-lg-4">
        <div class="card card-glass">
          <div class="card-header">
            <h5 class="mb-0">Tipos de Organização</h5>
          </div>
          <div class="card-body">
            <div style="height: 350px;">
              <DoughnutChart v-if="chartDataTipos" :data="chartDataTipos" />
              <div v-else class="text-center text-muted py-5">
                <i class="bi bi-pie-chart fs-1"></i>
                <p class="mt-2">Carregando dados...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mapa do Brasil -->
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="card card-glass">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Distribuição Geográfica - Brasil</h5>
            <button 
              v-if="selectedRegion" 
              class="btn btn-sm btn-outline-light" 
              @click="clearRegionFilter"
            >
              <i class="bi bi-x-circle"></i> Limpar Filtro
            </button>
          </div>
          <div class="card-body">
            <div v-if="selectedRegion" class="alert alert-info mb-3">
              <i class="bi bi-info-circle"></i>
              Exibindo dados da região: <strong>{{ selectedRegion.toUpperCase() }}</strong>
            </div>

            <div class="row">
              <!-- Mapa Visual -->
              <div class="col-lg-8">
                <div class="brazil-svg-map" :class="{ 'map-loaded': mapLoaded }">
                  <svg viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg">
                    <g id="brasil-map">
                      <!-- Norte -->
                      <g 
                        class="regiao norte" 
                        @mouseenter="showTooltip('norte', $event)" 
                        @mouseleave="hideTooltip"
                        @click="selectRegion('norte')"
                        :class="{ active: selectedRegion === 'norte' }"
                      >
                        <rect x="200" y="50" width="300" height="150" :fill="getRegionColor('norte')" />
                        <text x="350" y="125" text-anchor="middle" fill="white" font-size="20" font-weight="bold">NORTE</text>
                        <text x="350" y="145" text-anchor="middle" fill="white" font-size="14">{{ getRegionTotal('norte') }} IES</text>
                      </g>
                      
                      <!-- Nordeste -->
                      <g 
                        class="regiao nordeste"
                        @mouseenter="showTooltip('nordeste', $event)" 
                        @mouseleave="hideTooltip"
                        @click="selectRegion('nordeste')"
                        :class="{ active: selectedRegion === 'nordeste' }"
                      >
                        <rect x="450" y="100" width="250" height="200" :fill="getRegionColor('nordeste')" />
                        <text x="575" y="180" text-anchor="middle" fill="white" font-size="20" font-weight="bold">NORDESTE</text>
                        <text x="575" y="200" text-anchor="middle" fill="white" font-size="14">{{ getRegionTotal('nordeste') }} IES</text>
                      </g>
                      
                      <!-- Centro-Oeste -->
                      <g 
                        class="regiao centro-oeste"
                        @mouseenter="showTooltip('centro-oeste', $event)" 
                        @mouseleave="hideTooltip"
                        @click="selectRegion('centro-oeste')"
                        :class="{ active: selectedRegion === 'centro-oeste' }"
                      >
                        <rect x="200" y="220" width="250" height="180" :fill="getRegionColor('centro-oeste')" />
                        <text x="325" y="300" text-anchor="middle" fill="white" font-size="20" font-weight="bold">CENTRO-OESTE</text>
                        <text x="325" y="320" text-anchor="middle" fill="white" font-size="14">{{ getRegionTotal('centro-oeste') }} IES</text>
                      </g>
                      
                      <!-- Sudeste -->
                      <g 
                        class="regiao sudeste"
                        @mouseenter="showTooltip('sudeste', $event)" 
                        @mouseleave="hideTooltip"
                        @click="selectRegion('sudeste')"
                        :class="{ active: selectedRegion === 'sudeste' }"
                      >
                        <rect x="400" y="320" width="200" height="150" :fill="getRegionColor('sudeste')" />
                        <text x="500" y="385" text-anchor="middle" fill="white" font-size="20" font-weight="bold">SUDESTE</text>
                        <text x="500" y="405" text-anchor="middle" fill="white" font-size="14">{{ getRegionTotal('sudeste') }} IES</text>
                      </g>
                      
                      <!-- Sul -->
                      <g 
                        class="regiao sul"
                        @mouseenter="showTooltip('sul', $event)" 
                        @mouseleave="hideTooltip"
                        @click="selectRegion('sul')"
                        :class="{ active: selectedRegion === 'sul' }"
                      >
                        <rect x="300" y="430" width="250" height="120" :fill="getRegionColor('sul')" />
                        <text x="425" y="485" text-anchor="middle" fill="white" font-size="20" font-weight="bold">SUL</text>
                        <text x="425" y="505" text-anchor="middle" fill="white" font-size="14">{{ getRegionTotal('sul') }} IES</text>
                      </g>
                    </g>
                  </svg>

                  <!-- Tooltip -->
                  <div 
                    v-if="tooltip.show" 
                    class="map-tooltip" 
                    :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
                  >
                    <strong>{{ tooltip.region?.toUpperCase() }}</strong>
                    <div class="mt-1">
                      <small>Instituições: {{ tooltip.total }}</small><br>
                      <small>Estados: {{ tooltip.estados?.join(', ') }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Grid de Estados -->
              <div class="col-lg-4">
                <div class="estados-grid">
                  <h6 class="mb-3">Por Estado {{ selectedRegion ? `(${selectedRegion.toUpperCase()})` : '' }}</h6>
                  <div class="estado-list">
                    <div 
                      v-for="(total, estado) in filteredEstados" 
                      :key="estado" 
                      class="estado-item"
                      :style="getEstadoItemStyle(total)"
                    >
                      <span class="estado-uf">{{ estado }}</span>
                      <span class="estado-count">{{ total }}</span>
                    </div>
                    <div v-if="!filteredEstados || Object.keys(filteredEstados).length === 0" class="text-muted text-center py-3">
                      Nenhum dado disponível
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Atividades Recentes e Importações -->
    <div class="row g-4">
      <!-- Atividades Recentes -->
      <div class="col-lg-6">
        <div class="card card-glass">
          <div class="card-header">
            <h5 class="mb-0">Atividades Recentes</h5>
          </div>
          <div class="card-body p-0">
            <div class="activity-list">
              <div 
                v-for="(atividade, index) in stats.atividades_recentes" 
                :key="index"
                class="activity-item"
              >
                <div class="activity-icon">
                  <i :class="`bi bi-${atividade.icone}`"></i>
                </div>
                <div class="activity-content">
                  <p class="activity-desc">{{ atividade.descricao }}</p>
                  <small class="activity-time">{{ formatDate(atividade.data) }}</small>
                </div>
              </div>
              <div v-if="!stats.atividades_recentes || stats.atividades_recentes.length === 0" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1"></i>
                <p class="mt-2">Nenhuma atividade recente</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Importações Recentes -->
      <div class="col-lg-6">
        <div class="card card-glass">
          <div class="card-header">
            <h5 class="mb-0">Importações Recentes</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Tipo</th>
                    <th>Arquivo</th>
                    <th>Sucesso</th>
                    <th>Erros</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="imp in stats.importacoes_recentes" :key="imp.id">
                    <td>{{ formatImportType(imp.import_type) }}</td>
                    <td><small>{{ imp.file_name }}</small></td>
                    <td><span class="badge bg-success">{{ imp.success_count }}</span></td>
                    <td><span class="badge bg-danger">{{ imp.error_count }}</span></td>
                    <td><span :class="`badge bg-${getStatusColor(imp.status)}`">{{ imp.status }}</span></td>
                  </tr>
                  <tr v-if="!stats.importacoes_recentes || stats.importacoes_recentes.length === 0">
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="bi bi-upload fs-1"></i>
                      <p class="mt-2">Nenhuma importação recente</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import PageHeader from '@/components/PageHeader.vue';
import BarChart from '@/components/charts/BarChart.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

const stats = ref({});
const filters = ref({
  startDate: new Date(new Date().setMonth(new Date().getMonth() - 6)).toISOString().split('T')[0],
  endDate: new Date().toISOString().split('T')[0]
});

const selectedRegion = ref(null);
const mapLoaded = ref(false);
const comparativoTipo = ref('mensal');
const tooltip = ref({
  show: false,
  x: 0,
  y: 0,
  region: '',
  total: 0,
  estados: []
});

// Mapeamento de estados por região
const estadosPorRegiao = {
  'norte': ['AC', 'AP', 'AM', 'PA', 'RO', 'RR', 'TO'],
  'nordeste': ['AL', 'BA', 'CE', 'MA', 'PB', 'PE', 'PI', 'RN', 'SE'],
  'centro-oeste': ['DF', 'GO', 'MT', 'MS'],
  'sudeste': ['ES', 'MG', 'RJ', 'SP'],
  'sul': ['PR', 'RS', 'SC']
};

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/v1/dashboard/institucional', {
      params: {
        start_date: filters.value.startDate,
        end_date: filters.value.endDate
      }
    });
    stats.value = response.data;
    
    setTimeout(() => {
      mapLoaded.value = true;
    }, 300);
  } catch (error) {
    console.error('Erro ao buscar estatísticas:', error);
  }
};

const resetFilters = () => {
  filters.value = {
    startDate: new Date(new Date().setMonth(new Date().getMonth() - 6)).toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0]
  };
  fetchStats();
};

// Tooltip Functions
const showTooltip = (region, event) => {
  const total = getRegionTotal(region);
  const estados = estadosPorRegiao[region];
  
  tooltip.value = {
    show: true,
    x: event.offsetX + 10,
    y: event.offsetY + 10,
    region: region,
    total: total,
    estados: estados
  };
};

const hideTooltip = () => {
  tooltip.value.show = false;
};

// Region Selection
const selectRegion = (region) => {
  if (selectedRegion.value === region) {
    selectedRegion.value = null;
  } else {
    selectedRegion.value = region;
  }
};

const clearRegionFilter = () => {
  selectedRegion.value = null;
};

const filteredEstados = computed(() => {
  if (!stats.value.mapa_brasil) return {};
  
  let estados = { ...stats.value.mapa_brasil };
  
  if (selectedRegion.value) {
    const regionEstados = estadosPorRegiao[selectedRegion.value];
    estados = Object.entries(estados)
      .filter(([estado]) => regionEstados.includes(estado))
      .reduce((acc, [estado, total]) => {
        acc[estado] = total;
        return acc;
      }, {});
  }
  
  return Object.entries(estados)
    .sort((a, b) => b[1] - a[1])
    .reduce((acc, [estado, total]) => {
      acc[estado] = total;
      return acc;
    }, {});
});

const sortedEstados = computed(() => {
  if (!stats.value.mapa_brasil) return {};
  
  return Object.entries(stats.value.mapa_brasil)
    .sort((a, b) => b[1] - a[1])
    .reduce((acc, [estado, total]) => {
      acc[estado] = total;
      return acc;
    }, {});
});

const getRegionTotal = (regiao) => {
  if (!stats.value.mapa_brasil) return 0;
  
  const estados = estadosPorRegiao[regiao] || [];
  return estados.reduce((sum, uf) => sum + (stats.value.mapa_brasil[uf] || 0), 0);
};

const getRegionColor = (regiao) => {
  const total = getRegionTotal(regiao);
  const maxTotal = Math.max(...Object.values(estadosPorRegiao).map(estados => 
    estados.reduce((sum, uf) => sum + (stats.value.mapa_brasil?.[uf] || 0), 0)
  ));
  
  if (maxTotal === 0) return 'rgba(102, 126, 234, 0.3)';
  
  const intensity = total / maxTotal;
  return `rgba(102, 126, 234, ${0.3 + intensity * 0.6})`;
};

const getEstadoItemStyle = (total) => {
  const maxTotal = Math.max(...Object.values(stats.value.mapa_brasil || {}));
  const intensity = maxTotal > 0 ? total / maxTotal : 0;
  
  return {
    backgroundColor: `rgba(102, 126, 234, ${0.2 + intensity * 0.5})`,
    borderLeft: `4px solid rgba(102, 126, 234, ${0.5 + intensity * 0.5})`
  };
};

// Crescimento Comparativo
const crescimentoAtual = computed(() => {
  if (comparativoTipo.value === 'mensal') {
    return {
      instituicoes: 5.2,
      campi: 3.8,
      mantenedoras: 2.1,
      setores: 4.5
    };
  } else {
    return {
      instituicoes: 15.7,
      campi: 12.3,
      mantenedoras: 8.9,
      setores: 18.2
    };
  }
});

const chartDataEstados = computed(() => {
  if (!stats.value.instituicoes_por_estado) return null;

  return {
    labels: stats.value.instituicoes_por_estado.map(item => item.estado),
    datasets: [{
      label: 'Instituições',
      data: stats.value.instituicoes_por_estado.map(item => item.total),
      backgroundColor: 'rgba(102, 126, 234, 0.8)',
      borderColor: 'rgba(102, 126, 234, 1)',
      borderWidth: 1
    }]
  };
});

const chartDataTipos = computed(() => {
  if (!stats.value.instituicoes_por_tipo) return null;

  const colors = [
    'rgba(40, 167, 69, 0.8)',
    'rgba(0, 123, 255, 0.8)',
    'rgba(255, 193, 7, 0.8)',
    'rgba(220, 53, 69, 0.8)',
    'rgba(102, 126, 234, 0.8)'
  ];

  return {
    labels: stats.value.instituicoes_por_tipo.map(item => item.tipo_organizacao_academica || 'N/A'),
    datasets: [{
      data: stats.value.instituicoes_por_tipo.map(item => item.total),
      backgroundColor: colors,
      borderWidth: 0
    }]
  };
});

const chartDataEvolucao = computed(() => {
  if (!stats.value.evolucao_cadastros) return null;

  return {
    labels: stats.value.evolucao_cadastros.labels,
    datasets: [
      {
        label: 'Instituições',
        data: stats.value.evolucao_cadastros.instituicoes,
        borderColor: 'rgba(102, 126, 234, 1)',
        backgroundColor: 'rgba(102, 126, 234, 0.2)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Campi',
        data: stats.value.evolucao_cadastros.campi,
        borderColor: 'rgba(40, 167, 69, 1)',
        backgroundColor: 'rgba(40, 167, 69, 0.2)',
        tension: 0.4,
        fill: true
      }
    ]
  };
});

// Exportar PDF
const exportToPDF = () => {
  const pdf = new jsPDF('p', 'mm', 'a4');
  
  // Título
  pdf.setFontSize(20);
  pdf.text('Dashboard Institucional', 105, 15, { align: 'center' });
  
  pdf.setFontSize(10);
  pdf.text(`Período: ${filters.value.startDate} até ${filters.value.endDate}`, 105, 22, { align: 'center' });
  
  // Dados dos Cards
  pdf.setFontSize(14);
  pdf.text('Totais', 14, 35);
  
  const totaisData = [
    ['Grupos Educacionais', stats.value.totais?.grupos_educacionais || 0],
    ['Mantenedoras', stats.value.totais?.mantenedoras || 0],
    ['Instituições', stats.value.totais?.instituicoes || 0],
    ['Campi', stats.value.totais?.campi || 0],
    ['Setores', stats.value.totais?.setores || 0]
  ];
  
  autoTable(pdf, {
    startY: 40,
    head: [['Categoria', 'Total']],
    body: totaisData,
    theme: 'grid'
  });
  
  // Instituições por Estado
  let finalY = pdf.lastAutoTable.finalY + 10;
  pdf.text('Instituições por Estado (Top 10)', 14, finalY);
  
  const estadosData = (stats.value.instituicoes_por_estado || []).map(item => [
    item.estado,
    item.total
  ]);
  
  autoTable(pdf, {
    startY: finalY + 5,
    head: [['Estado', 'Total']],
    body: estadosData,
    theme: 'grid'
  });
  
  // Download
  pdf.save(`dashboard-institucional-${new Date().toISOString().split('T')[0]}.pdf`);
};

const formatDate = (date) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: ptBR });
};

const formatEventDates = (start, end) => {
  const startDate = new Date(start).toLocaleString('pt-BR');
  const endDate = new Date(end).toLocaleString('pt-BR');
  return `${startDate} até ${endDate}`;
};

const getEventTypeColor = (type) => {
  const colors = {
    'maintenance': 'warning',
    'update': 'info',
    'outage': 'danger',
    'announcement': 'primary'
  };
  return colors[type] || 'secondary';
};

const formatImportType = (type) => {
  const types = {
    'grupos_educacionais': 'Grupos',
    'mantenedoras': 'Mantenedoras',
    'instituicoes': 'Instituições',
    'campi': 'Campi',
    'setores': 'Setores'
  };
  return types[type] || type;
};

const getStatusColor = (status) => {
  const colors = {
    'completed': 'success',
    'failed': 'danger',
    'processing': 'warning'
  };
  return colors[status] || 'secondary';
};

onMounted(() => {
  fetchStats();
});
</script>

<style scoped>
.col-lg-2-4 {
  flex: 0 0 auto;
  width: 20%;
}

@media (max-width: 991px) {
  .col-lg-2-4 {
    width: 50%;
  }
}

/* Quick Actions */
.quick-action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  text-decoration: none;
  color: rgba(255, 255, 255, 0.9);
  transition: all 0.3s;
}

.quick-action-btn:hover {
  background: rgba(102, 126, 234, 0.2);
  border-color: rgba(102, 126, 234, 0.5);
  transform: translateY(-3px);
}

.quick-action-btn i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.quick-action-btn span {
  font-size: 0.875rem;
  font-weight: 500;
}

/* Stat Cards */
.stat-card {
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.stat-value {
  color: rgba(255, 255, 255, 0.95);
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

/* Comparativo Cards */
.comparativo-card {
  text-align: center;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s;
}

.comparativo-card:hover {
  transform: translateY(-3px);
  background: rgba(255, 255, 255, 0.08);
}

.comparativo-card h3 {
  color: rgba(255, 255, 255, 0.95);
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.comparativo-card p {
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
  font-weight: 500;
}

/* Mapa SVG */
.brazil-svg-map {
  width: 100%;
  max-width: 700px;
  margin: 0 auto;
  position: relative;
  opacity: 0;
  transform: scale(0.95);
  transition: all 0.5s ease;
}

.brazil-svg-map.map-loaded {
  opacity: 1;
  transform: scale(1);
}

.brazil-svg-map svg {
  width: 100%;
  height: auto;
}

.regiao {
  cursor: pointer;
  transition: all 0.3s;
}

.regiao:hover rect {
  opacity: 0.8;
  stroke: rgba(255, 255, 255, 0.5);
  stroke-width: 2;
}

.regiao.active rect {
  stroke: rgba(255, 193, 7, 1);
  stroke-width: 3;
}

/* Tooltip */
.map-tooltip {
  position: absolute;
  background: rgba(28, 28, 35, 0.98);
  color: white;
  padding: 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  pointer-events: none;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  min-width: 150px;
}

.map-tooltip strong {
  color: rgba(102, 126, 234, 1);
  font-size: 0.9rem;
}

.map-tooltip small {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.8rem;
}

/* Grid de Estados */
.estados-grid {
  max-height: 550px;
  overflow-y: auto;
}

.estado-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.estado-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.estado-item:hover {
  transform: translateX(5px);
}

.estado-uf {
  font-weight: 700;
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.95);
}

.estado-count {
  font-weight: 700;
  font-size: 1.25rem;
  color: rgba(255, 255, 255, 0.9);
}

/* Activity List */
.activity-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  transition: background 0.2s;
}

.activity-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(102, 126, 234, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(102, 126, 234, 1);
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-desc {
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  font-size: 0.9rem;
}

.activity-time {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.8rem;
}
</style>
