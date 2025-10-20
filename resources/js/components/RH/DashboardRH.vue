<template>
  <div class="dashboard-rh-page">
    <!-- Header -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="text-white mb-2">
              <i class="bi bi-graph-up me-2"></i>
              Dashboard de Recursos Humanos
            </h3>
            <p class="text-white-50 mb-0">
              Indicadores e métricas gerenciais do departamento de RH
            </p>
          </div>
          <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
              <button @click="atualizarDados" class="btn btn-outline-light btn-sm" :disabled="loading">
                <i class="bi bi-arrow-clockwise me-1"></i>
                {{ loading ? 'Atualizando...' : 'Atualizar' }}
              </button>
              <button @click="async () => await exportarRelatorio()" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>
                Exportar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ CARD DE ALERTAS - COLABORADORES PENDENTES (NOVO) -->
    <div class="row mb-4">
      <div class="col-12">
        <ColaboradoresPendentesCard />
      </div>
    </div>

    <!-- Filtros Avançados -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <!-- Linha 1: Período -->
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label text-white">Período</label>
            <select v-model="filtros.periodo" @change="atualizarDados" class="form-select bg-transparent text-white border-secondary">
              <option value="mes">Este Mês</option>
              <option value="trimestre">Este Trimestre</option>
              <option value="semestre">Este Semestre</option>
              <option value="ano">Este Ano</option>
              <option value="customizado">Personalizado</option>
            </select>
          </div>
          <div v-if="filtros.periodo === 'customizado'" class="col-md-3">
            <label class="form-label text-white">Data Início</label>
            <input v-model="filtros.dataInicio" type="date" @change="atualizarDados" class="form-control bg-transparent text-white border-secondary">
          </div>
          <div v-if="filtros.periodo === 'customizado'" class="col-md-3">
            <label class="form-label text-white">Data Fim</label>
            <input v-model="filtros.dataFim" type="date" @change="atualizarDados" class="form-control bg-transparent text-white border-secondary">
          </div>
          <div class="col-md-3">
            <label class="form-label text-white">Status</label>
            <select v-model="filtros.status" @change="atualizarDados" class="form-select bg-transparent text-white border-secondary">
              <option value="">Todos</option>
              <option value="Ativo">Ativos</option>
              <option value="Afastado">Afastados</option>
              <option value="Desligado">Desligados</option>
            </select>
          </div>
        </div>

        <!-- Linha 2: Hierarquia Institucional -->
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-building me-1"></i>
              Grupo Educacional
            </label>
            <select 
              v-model="filtros.grupoEducacional" 
              @change="onGrupoChange"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todos os Grupos</option>
              <option v-for="grupo in gruposEducacionais" :key="grupo.id" :value="grupo.id">
                {{ grupo.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-building me-1"></i>
              Mantenedora
            </label>
            <select 
              v-model="filtros.mantenedora" 
              @change="onMantenedoraChange"
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.grupoEducacional"
            >
              <option value="">Todas as Mantenedoras</option>
              <option v-for="mant in mantenedoras" :key="mant.id" :value="mant.id">
                {{ mant.razao_social }}
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-mortarboard me-1"></i>
              Instituição
            </label>
            <select 
              v-model="filtros.instituicao" 
              @change="onInstituicaoChange"
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.mantenedora"
            >
              <option value="">Todas as Instituições</option>
              <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                {{ inst.sigla }} - {{ inst.razao_social }}
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-geo-alt me-1"></i>
              Campus
            </label>
            <select 
              v-model="filtros.campus" 
              @change="onCampusChange"
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.instituicao"
            >
              <option value="">Todos os Campi</option>
              <option v-for="camp in campi" :key="camp.id" :value="camp.id">
                {{ camp.nome }}
              </option>
            </select>
          </div>
        </div>

        <!-- Linha 3: Setor e Cargo -->
        <div class="row g-3 mt-2">
          <div class="col-md-4">
            <label class="form-label text-white">
              <i class="bi bi-folder me-1"></i>
              Setor
            </label>
            <select 
              v-model="filtros.setor" 
              @change="atualizarDados"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todos os Setores</option>
              <option v-for="setor in setores" :key="setor.id" :value="setor.id">
                {{ setor.nome }}
              </option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label text-white">
              <i class="bi bi-briefcase me-1"></i>
              Cargo
            </label>
            <select 
              v-model="filtros.cargo" 
              @change="atualizarDados"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todos os Cargos</option>
              <option v-for="cargo in cargosUnicos" :key="cargo" :value="cargo">
                {{ cargo }}
              </option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label text-white">
              <i class="bi bi-person-badge me-1"></i>
              Tipo
            </label>
            <select 
              v-model="filtros.tipo" 
              @change="atualizarDados"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todos</option>
              <option value="gestor">Gestores</option>
              <option value="colaborador">Colaboradores</option>
            </select>
          </div>
        </div>

        <!-- Botão Limpar Filtros -->
        <div class="row mt-3">
          <div class="col-12 text-end">
            <button @click="limparFiltros" class="btn btn-outline-warning btn-sm">
              <i class="bi bi-x-circle me-1"></i>
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading">
      <DashboardRHSkeleton />
    </div>

    <div v-else>
      <!-- KPIs Principais -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card card-glass stat-card stat-primary">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-white-50 mb-1 small">Total de Colaboradores</p>
                  <h3 class="text-white mb-0">{{ kpis.totalColaboradores }}</h3>
                  <span class="badge bg-success mt-2">
                    <i class="bi bi-arrow-up"></i> {{ kpis.variacaoTotal }}%
                  </span>
                </div>
                <div class="stat-icon bg-primary">
                  <i class="bi bi-people-fill"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card card-glass stat-card stat-success">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-white-50 mb-1 small">Ativos</p>
                  <h3 class="text-white mb-0">{{ kpis.ativos }}</h3>
                  <span class="text-white-50 small">{{ percentual(kpis.ativos, kpis.totalColaboradores) }}% do total</span>
                </div>
                <div class="stat-icon bg-success">
                  <i class="bi bi-check-circle-fill"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card card-glass stat-card stat-warning">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-white-50 mb-1 small">Taxa de Turnover</p>
                  <h3 class="text-white mb-0">{{ kpis.turnover }}%</h3>
                  <span :class="kpis.turnover > 10 ? 'badge bg-danger' : 'badge bg-success'">
                    {{ kpis.turnover > 10 ? 'Atenção' : 'Saudável' }}
                  </span>
                </div>
                <div class="stat-icon bg-warning">
                  <i class="bi bi-arrow-repeat"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card card-glass stat-card stat-info">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-white-50 mb-1 small">Tempo Médio de Casa</p>
                  <h3 class="text-white mb-0">{{ kpis.tempoMedio }}</h3>
                  <span class="text-white-50 small">anos</span>
                </div>
                <div class="stat-icon bg-info">
                  <i class="bi bi-clock-history"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Secundários -->
      <div class="row g-3 mb-4">
        <div class="col-md-2">
          <div class="card card-glass stat-card-small">
            <div class="card-body text-center">
              <i class="bi bi-person-plus-fill text-success fs-3 mb-2"></i>
              <h5 class="text-white mb-1">{{ kpis.admissoes }}</h5>
              <small class="text-white-50">Admissões</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-small">
            <div class="card-body text-center">
              <i class="bi bi-person-dash-fill text-danger fs-3 mb-2"></i>
              <h5 class="text-white mb-1">{{ kpis.desligamentos }}</h5>
              <small class="text-white-50">Desligamentos</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-small">
            <div class="card-body text-center">
              <i class="bi bi-star-fill text-warning fs-3 mb-2"></i>
              <h5 class="text-white mb-1">{{ kpis.gestores }}</h5>
              <small class="text-white-50">Gestores</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-small">
            <div class="card-body text-center">
              <i class="bi bi-clock-fill text-info fs-3 mb-2"></i>
              <h5 class="text-white mb-1">{{ kpis.afastados }}</h5>
              <small class="text-white-50">Afastados</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-small">
            <div class="card-body text-center">
              <i class="bi bi-building text-primary fs-3 mb-2"></i>
              <h5 class="text-white mb-1">{{ kpis.totalSetores }}</h5>
              <small class="text-white-50">Setores</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-small">
            <div class="card-body text-center">
              <i class="bi bi-cake2-fill text-danger fs-3 mb-2"></i>
              <h5 class="text-white mb-1">{{ kpis.aniversariantes }}</h5>
              <small class="text-white-50">Aniversariantes</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="row g-3 mb-4">
        <!-- Evolução de Contratações -->
        <div class="col-md-8">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-graph-up me-2"></i>
                Evolução do Quadro
              </h5>
            </div>
            <div class="card-body">
              <Line :data="graficoEvolucao" :options="opcoesGrafico" />
            </div>
          </div>
        </div>

        <!-- Distribuição por Status -->
        <div class="col-md-4">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-pie-chart me-2"></i>
                Por Status
              </h5>
            </div>
            <div class="card-body">
              <Doughnut :data="graficoStatus" :options="opcoesGraficoPizza" />
            </div>
          </div>
        </div>
      </div>

      <!-- Mais Gráficos -->
      <div class="row g-3 mb-4">
        <!-- Top Setores -->
        <div class="col-md-6">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-bar-chart me-2"></i>
                Top 10 Setores
              </h5>
            </div>
            <div class="card-body">
              <Bar :data="graficoSetores" :options="opcoesGraficoBarras" />
            </div>
          </div>
        </div>

        <!-- Top Cargos -->
        <div class="col-md-6">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-briefcase me-2"></i>
                Top 10 Cargos
              </h5>
            </div>
            <div class="card-body">
              <Bar :data="graficoCargos" :options="opcoesGraficoBarras" />
            </div>
          </div>
        </div>
      </div>

      <!-- Tabela de Movimentações Recentes -->
      <div class="card card-glass">
        <div class="card-header">
          <h5 class="text-white mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Movimentações Recentes
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-dark table-hover">
              <thead>
                <tr>
                  <th>Data</th>
                  <th>Colaborador</th>
                  <th>Tipo</th>
                  <th>Cargo</th>
                  <th>Setor</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="mov in movimentacoes" :key="mov.id">
                  <td>{{ mov.data }}</td>
                  <td>{{ mov.nome }}</td>
                  <td>
                    <span :class="badgeClass(mov.tipo)">
                      <i :class="iconClass(mov.tipo)"></i>
                      {{ mov.tipo }}
                    </span>
                  </td>
                  <td>{{ mov.cargo }}</td>
                  <td>{{ mov.setor }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import DashboardRHSkeleton from './DashboardRHSkeleton.vue';
import ColaboradoresPendentesCard from '@/components/RH/ColaboradoresPendentesCard.vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

// Registrar componentes do Chart.js
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
);

const loading = ref(true);
const kpis = ref({
  totalColaboradores: 0,
  ativos: 0,
  afastados: 0,
  desligados: 0,
  gestores: 0,
  admissoes: 0,
  desligamentos: 0,
  turnover: 0,
  tempoMedio: 0,
  aniversariantes: 0,
  totalSetores: 0,
  variacaoTotal: 0
});
const movimentacoes = ref([]);

// Dados hierárquicos
const gruposEducacionais = ref([]);
const mantenedoras = ref([]);
const instituicoes = ref([]);
const campi = ref([]);
const setores = ref([]);
const cargosUnicos = ref([]);

// Filtros
const filtros = ref({
  periodo: 'mes',
  dataInicio: '',
  dataFim: '',
  status: '',
  grupoEducacional: '',
  mantenedora: '',
  instituicao: '',
  campus: '',
  setor: '',
  cargo: '',
  tipo: ''
});

// Dados dos gráficos
const graficoEvolucao = ref({
  labels: [],
  datasets: []
});
const graficoStatus = ref({
  labels: [],
  datasets: []
});
const graficoSetores = ref({
  labels: [],
  datasets: []
});
const graficoCargos = ref({
  labels: [],
  datasets: []
});

// Métodos de mudança de filtro
const onGrupoChange = async () => {
  filtros.value.mantenedora = '';
  filtros.value.instituicao = '';
  filtros.value.campus = '';
  filtros.value.setor = '';
  mantenedoras.value = [];
  instituicoes.value = [];
  campi.value = [];
  
  if (filtros.value.grupoEducacional) {
    await carregarMantenedoras();
  }
  atualizarDados();
};

const onMantenedoraChange = async () => {
  filtros.value.instituicao = '';
  filtros.value.campus = '';
  filtros.value.setor = '';
  instituicoes.value = [];
  campi.value = [];
  
  if (filtros.value.mantenedora) {
    await carregarInstituicoes();
  }
  atualizarDados();
};

const onInstituicaoChange = async () => {
  filtros.value.campus = '';
  filtros.value.setor = '';
  campi.value = [];
  
  if (filtros.value.instituicao) {
    await carregarCampi();
  }
  atualizarDados();
};

const onCampusChange = async () => {
  filtros.value.setor = '';
  atualizarDados();
};

const limparFiltros = () => {
  filtros.value = {
    periodo: 'mes',
    dataInicio: '',
    dataFim: '',
    status: '',
    grupoEducacional: '',
    mantenedora: '',
    instituicao: '',
    campus: '',
    setor: '',
    cargo: '',
    tipo: ''
  };
  mantenedoras.value = [];
  instituicoes.value = [];
  campi.value = [];
  atualizarDados();
};

// Métodos de carregamento
const carregarDadosIniciais = async () => {
  try {
    console.log('🔄 Carregando dados iniciais...');
    
    // Carregar grupos educacionais
    const gruposResp = await axios.get('/api/v1/grupos-educacionais');
    gruposEducacionais.value = gruposResp.data;
    console.log('✅ Grupos carregados:', gruposEducacionais.value.length);

    // Carregar todos os setores inicialmente
    const setoresResp = await axios.get('/api/v1/setores');
    setores.value = setoresResp.data;
    console.log('✅ Setores carregados:', setores.value.length);

    // Carregar cargos únicos
    const cargosResp = await axios.get('/api/v1/colaboradores/cargos-unicos');
    cargosUnicos.value = cargosResp.data;
    console.log('✅ Cargos carregados:', cargosUnicos.value.length);

  } catch (error) {
    console.error('❌ Erro ao carregar dados iniciais:', error);
  }
};

const carregarMantenedoras = async () => {
  if (!filtros.value.grupoEducacional) return;
  try {
    console.log('🔄 Carregando mantenedoras do grupo:', filtros.value.grupoEducacional);
    const response = await axios.get(`/api/v1/grupos-educacionais/${filtros.value.grupoEducacional}/mantenedoras`);
    mantenedoras.value = response.data;
    console.log('✅ Mantenedoras carregadas:', mantenedoras.value.length, mantenedoras.value);
  } catch (error) {
    console.error('❌ Erro ao carregar mantenedoras:', error);
  }
};

const carregarInstituicoes = async () => {
  if (!filtros.value.mantenedora) return;
  try {
    console.log('🔄 Carregando instituições da mantenedora:', filtros.value.mantenedora);
    const response = await axios.get(`/api/v1/mantenedoras/${filtros.value.mantenedora}/instituicoes`);
    instituicoes.value = response.data;
    console.log('✅ Instituições carregadas:', instituicoes.value.length);
  } catch (error) {
    console.error('❌ Erro ao carregar instituições:', error);
  }
};

const carregarCampi = async () => {
  if (!filtros.value.instituicao) return;
  try {
    console.log('🔄 Carregando campi da instituição:', filtros.value.instituicao);
    const response = await axios.get(`/api/v1/instituicoes/${filtros.value.instituicao}/campi`);
    campi.value = response.data;
    console.log('✅ Campi carregados:', campi.value.length);
  } catch (error) {
    console.error('❌ Erro ao carregar campi:', error);
  }
};

// Atualizar dados
const atualizarDados = async () => {
  loading.value = true;
  try {
    console.log('🔄 Atualizando dashboard com filtros:', filtros.value);
    const response = await axios.get('/api/v1/relatorios/dashboard-rh', {
      params: filtros.value
    });
    
    kpis.value = response.data.kpis;
    movimentacoes.value = response.data.movimentacoes;
    
    // Configurar gráficos
    configurarGraficos(response.data);
    
    console.log('✅ Dashboard RH carregado:', response.data);
  } catch (error) {
    console.error('❌ Erro ao carregar dashboard:', error);
  } finally {
    loading.value = false;
  }
};

const configurarGraficos = (dados) => {
  // Gráfico de Evolução
  graficoEvolucao.value = {
    labels: dados.evolucao.labels,
    datasets: [
      {
        label: 'Admissões',
        data: dados.evolucao.admissoes,
        borderColor: 'rgb(75, 192, 192)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.4
      },
      {
        label: 'Desligamentos',
        data: dados.evolucao.desligamentos,
        borderColor: 'rgb(255, 99, 132)',
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        tension: 0.4
      }
    ]
  };

  // Gráfico de Status
  graficoStatus.value = {
    labels: ['Ativos', 'Afastados', 'Desligados'],
    datasets: [{
      data: [
        dados.statusDistribuicao.ativos, 
        dados.statusDistribuicao.afastados, 
        dados.statusDistribuicao.desligados
      ],
      backgroundColor: [
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(255, 99, 132, 0.8)'
      ],
      borderWidth: 0
    }]
  };

  // Gráfico de Setores
  graficoSetores.value = {
    labels: dados.topSetores.labels,
    datasets: [{
      label: 'Colaboradores',
      data: dados.topSetores.valores,
      backgroundColor: 'rgba(102, 126, 234, 0.8)',
      borderColor: 'rgba(102, 126, 234, 1)',
      borderWidth: 1
    }]
  };

  // Gráfico de Cargos
  graficoCargos.value = {
    labels: dados.topCargos.labels,
    datasets: [{
      label: 'Colaboradores',
      data: dados.topCargos.valores,
      backgroundColor: 'rgba(255, 159, 64, 0.8)',
      borderColor: 'rgba(255, 159, 64, 1)',
      borderWidth: 1
    }]
  };
};

const opcoesGrafico = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: 'white'
      }
    }
  },
  scales: {
    x: {
      ticks: { color: 'white' },
      grid: { color: 'rgba(255, 255, 255, 0.1)' }
    },
    y: {
      ticks: { color: 'white' },
      grid: { color: 'rgba(255, 255, 255, 0.1)' }
    }
  }
};

const opcoesGraficoPizza = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: 'white'
      }
    }
  }
};

const opcoesGraficoBarras = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    x: {
      ticks: { color: 'white' },
      grid: { color: 'rgba(255, 255, 255, 0.1)' }
    },
    y: {
      ticks: { color: 'white' },
      grid: { display: false }
    }
  }
};

const percentual = (valor, total) => {
  if (!total) return 0;
  return ((valor / total) * 100).toFixed(1);
};

const badgeClass = (tipo) => {
  const classes = {
    'Admissão': 'badge bg-success',
    'Desligamento': 'badge bg-danger',
    'Afastamento': 'badge bg-warning'
  };
  return classes[tipo] || 'badge bg-secondary';
};

const iconClass = (tipo) => {
  const icons = {
    'Admissão': 'bi bi-person-plus-fill',
    'Desligamento': 'bi bi-person-dash-fill',
    'Afastamento': 'bi bi-clock-fill'
  };
  return icons[tipo] || 'bi bi-circle-fill';
};

const exportarRelatorio = async () => {
  try {
    const response = await axios.get('/api/v1/relatorios/dashboard-rh/export', {
      params: filtros.value,
      responseType: 'blob',
    });

    const headerLine = response.headers['content-disposition'];
    let filename = 'relatorio-rh.xlsx';
    if (headerLine) {
      const filenameMatch = headerLine.match(/filename="(.+)"/);
      if (filenameMatch && filenameMatch.length > 1) {
        filename = filenameMatch[1];
      }
    }

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

  } catch (error) {
    console.error('Erro ao exportar relatório:', error);
  }
};

onMounted(async () => {
  await carregarDadosIniciais();
  atualizarDados();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

.stat-card {
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: white;
}

.stat-card-small {
  transition: transform 0.3s;
}

.stat-card-small:hover {
  transform: scale(1.05);
}

canvas {
  max-height: 300px;
}

.form-select option, .form-control {
  background: #1a1a2e;
  color: white;
}

.form-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
