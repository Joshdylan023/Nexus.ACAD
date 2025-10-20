<template>
  <div class="dashboard-360-page">
    <!-- ✅ SKELETON LOADING PROFISSIONAL -->
    <div v-if="loading">
      <!-- Header Skeleton -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center mb-3">
            <div class="col-md-8">
              <div class="skeleton skeleton-title mb-2"></div>
              <div class="skeleton skeleton-text" style="width: 60%;"></div>
            </div>
            <div class="col-md-4 text-end">
              <div class="skeleton skeleton-button ms-auto"></div>
            </div>
          </div>

          <!-- Filtros Skeleton -->
          <div class="row g-3">
            <div class="col-md-2" v-for="i in 6" :key="i">
              <div class="skeleton skeleton-text mb-2" style="width: 80px;"></div>
              <div class="skeleton skeleton-input"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-3" v-for="i in 4" :key="`kpi-${i}`">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="skeleton skeleton-text mb-2" style="width: 120px;"></div>
                  <div class="skeleton skeleton-heading mb-1"></div>
                  <div class="skeleton skeleton-text" style="width: 60px;"></div>
                </div>
                <div class="skeleton skeleton-icon"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-8">
          <div class="card card-glass">
            <div class="card-header">
              <div class="skeleton skeleton-text" style="width: 250px;"></div>
            </div>
            <div class="card-body">
              <div v-for="i in 5" :key="`bar-${i}`" class="mb-3">
                <div class="skeleton skeleton-bar"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card card-glass">
            <div class="card-header">
              <div class="skeleton skeleton-text" style="width: 150px;"></div>
            </div>
            <div class="card-body">
              <div v-for="i in 5" :key="`top-${i}`" class="mb-3">
                <div class="d-flex gap-2">
                  <div class="skeleton skeleton-badge"></div>
                  <div class="flex-grow-1">
                    <div class="skeleton skeleton-text mb-1"></div>
                    <div class="skeleton skeleton-text" style="width: 60%;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Semanal e Sugestões Skeleton -->
      <div class="row g-3 mb-4">
        <div class="col-md-6" v-for="i in 2" :key="`week-${i}`">
          <div class="card card-glass">
            <div class="card-header">
              <div class="skeleton skeleton-text" style="width: 200px;"></div>
            </div>
            <div class="card-body">
              <div v-for="j in 4" :key="`item-${j}`" class="mb-3">
                <div class="skeleton skeleton-bar"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações Rápidas Skeleton -->
      <div class="card card-glass">
        <div class="card-header">
          <div class="skeleton skeleton-text" style="width: 150px;"></div>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3" v-for="i in 4" :key="`action-${i}`">
              <div class="skeleton skeleton-action-card"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ CONTEÚDO REAL -->
    <div v-else>
      <!-- Header com Filtros Hierárquicos -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center mb-3">
            <div class="col-md-8">
              <h3 class="text-white mb-2">
                <i class="bi bi-pie-chart me-2"></i>
                Dashboard 360° - Espaços Físicos
              </h3>
              <p class="text-white-50 mb-0">
                Visão completa da infraestrutura e otimização de espaços
              </p>
            </div>
            <div class="col-md-4 text-end">
              <button 
                @click="atualizarDados" 
                class="btn btn-outline-light" 
                :disabled="atualizando"
              >
                <i class="bi bi-arrow-clockwise me-2" :class="{ 'spin': atualizando }"></i>
                {{ atualizando ? 'Atualizando...' : 'Atualizar' }}
              </button>
            </div>
          </div>

          <!-- ✅ FILTROS HIERÁRQUICOS CORRIGIDOS -->
          <div class="row g-3">
            <!-- INSTITUIÇÃO - AGORA COM NOME COMPLETO -->
            <div class="col-md-2">
              <label class="form-label text-white small">
                <i class="bi bi-building me-1"></i>
                Instituição
              </label>
              <select 
                v-model="filtros.instituicao_id" 
                @change="onInstituicaoChange"
                class="form-select bg-transparent text-white border-secondary form-select-sm"
              >
                <option value="">Todas</option>
                <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                  {{ inst.nome }}
                </option>
              </select>
            </div>

            <!-- CAMPUS -->
            <div class="col-md-2">
              <label class="form-label text-white small">
                <i class="bi bi-geo-alt me-1"></i>
                Campus
              </label>
              <select 
                v-model="filtros.campus_id" 
                @change="onCampusChange"
                class="form-select bg-transparent text-white border-secondary form-select-sm"
                :disabled="!filtros.instituicao_id"
              >
                <option value="">Todos</option>
                <option v-for="campus in campusFiltrados" :key="campus.id" :value="campus.id">
                  {{ campus.nome }}
                </option>
              </select>
            </div>

            <!-- PRÉDIO -->
            <div class="col-md-2">
              <label class="form-label text-white small">
                <i class="bi bi-buildings me-1"></i>
                Prédio
              </label>
              <select 
                v-model="filtros.predio_id" 
                @change="carregarDados"
                class="form-select bg-transparent text-white border-secondary form-select-sm"
                :disabled="!filtros.campus_id"
              >
                <option value="">Todos</option>
                <option v-for="predio in prediosFiltrados" :key="predio.id" :value="predio.id">
                  {{ predio.nome }}
                </option>
              </select>
            </div>

            <!-- TIPO DE ESPAÇO -->
            <div class="col-md-2">
              <label class="form-label text-white small">
                <i class="bi bi-door-open me-1"></i>
                Tipo de Espaço
              </label>
              <select 
                v-model="filtros.tipo_espaco" 
                @change="carregarDados"
                class="form-select bg-transparent text-white border-secondary form-select-sm"
              >
                <option value="">Todos os Tipos</option>
                <option value="Sala de Aula">Sala de Aula</option>
                <option value="Laboratório">Laboratório</option>
                <option value="Auditório">Auditório</option>
                <option value="Biblioteca">Biblioteca</option>
                <option value="Sala de Reunião">Sala de Reunião</option>
                <option value="Área de Convivência">Área de Convivência</option>
              </select>
            </div>

            <!-- PERÍODO INÍCIO -->
            <div class="col-md-2">
              <label class="form-label text-white small">
                <i class="bi bi-calendar-event me-1"></i>
                Período Início
              </label>
              <input 
                v-model="filtros.data_inicio" 
                @change="carregarDados"
                type="date" 
                class="form-control bg-transparent text-white border-secondary form-control-sm"
              />
            </div>

            <!-- PERÍODO FIM -->
            <div class="col-md-2">
              <label class="form-label text-white small">
                <i class="bi bi-calendar-check me-1"></i>
                Período Fim
              </label>
              <input 
                v-model="filtros.data_fim" 
                @change="carregarDados"
                type="date" 
                class="form-control bg-transparent text-white border-secondary form-control-sm"
              />
            </div>
          </div>

          <!-- BADGE DE FILTROS ATIVOS -->
          <div class="row mt-3" v-if="getFiltrosAtivos().length > 0">
            <div class="col-12">
              <div class="d-flex gap-2 align-items-center flex-wrap">
                <small class="text-white-50">Filtros ativos:</small>
                <span 
                  v-for="filtro in getFiltrosAtivos()" 
                  :key="filtro.key"
                  class="badge bg-primary"
                >
                  {{ filtro.label }}: {{ filtro.value }}
                  <i 
                    class="bi bi-x ms-1 cursor-pointer" 
                    @click="removerFiltro(filtro.key)"
                  ></i>
                </span>
                <button 
                  @click="limparFiltros" 
                  class="btn btn-sm btn-outline-danger"
                >
                  <i class="bi bi-x-circle me-1"></i>
                  Limpar Filtros
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Principais -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card card-glass stat-card stat-primary">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-white-50 mb-1 small">Total de Espaços</p>
                  <h3 class="text-white mb-0">{{ dashboard.resumo?.total_espacos || 0 }}</h3>
                </div>
                <div class="stat-icon bg-primary">
                  <i class="bi bi-door-open-fill"></i>
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
                  <p class="text-white-50 mb-1 small">Disponíveis</p>
                  <h3 class="text-white mb-0">{{ dashboard.resumo?.espacos_disponiveis || 0 }}</h3>
                  <small class="text-success">{{ percentualDisponivel }}%</small>
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
                  <p class="text-white-50 mb-1 small">Reservas Ativas</p>
                  <h3 class="text-white mb-0">{{ dashboard.resumo?.reservas_ativas || 0 }}</h3>
                  <small class="text-white-50">em andamento</small>
                </div>
                <div class="stat-icon bg-warning">
                  <i class="bi bi-calendar-check-fill"></i>
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
                  <p class="text-white-50 mb-1 small">Taxa de Ocupação</p>
                  <h3 class="text-white mb-0">{{ dashboard.resumo?.taxa_ocupacao || 0 }}%</h3>
                  <small :class="getTaxaOcupacaoClass">
                    {{ getTaxaOcupacaoLabel }}
                  </small>
                </div>
                <div class="stat-icon bg-info">
                  <i class="bi bi-speedometer2"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos e Dados -->
      <div class="row g-3 mb-4">
        <!-- Ocupação por Tipo -->
        <div class="col-md-8">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-bar-chart me-2"></i>
                Ocupação por Tipo de Espaço
              </h5>
            </div>
            <div class="card-body">
              <div v-if="dashboard.ocupacao_por_tipo?.length > 0" class="tipo-lista">
                <div 
                  v-for="tipo in dashboard.ocupacao_por_tipo" 
                  :key="tipo.tipo"
                  class="tipo-item"
                >
                  <div class="tipo-info">
                    <i :class="getTipoIcon(tipo.tipo)" class="tipo-icon"></i>
                    <div>
                      <p class="text-white mb-0">{{ tipo.tipo }}</p>
                      <small class="text-white-50">
                        {{ tipo.total_espacos }} espaços | {{ tipo.total_reservas }} reservas
                      </small>
                    </div>
                  </div>
                  <div class="tipo-progress">
                    <div 
                      class="progress-bar-custom"
                      :style="{ width: calcularPercentual(tipo.total_reservas, getTotalReservas()) + '%' }"
                    >
                      <span>{{ tipo.total_reservas }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-5">
                <i class="bi bi-inbox text-white-50 fs-1"></i>
                <p class="text-white-50 mt-2">Nenhum dado disponível</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Espaços Utilizados -->
        <div class="col-md-4">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-trophy me-2"></i>
                Top Espaços
              </h5>
            </div>
            <div class="card-body">
              <div v-if="dashboard.top_espacos?.length > 0" class="top-espacos-lista">
                <div 
                  v-for="(espaco, index) in dashboard.top_espacos" 
                  :key="espaco.id"
                  class="top-espaco-item"
                >
                  <div class="top-ranking">
                    <span class="ranking-badge" :class="`ranking-${index + 1}`">
                      {{ index + 1 }}
                    </span>
                  </div>
                  <div class="top-info">
                    <p class="text-white mb-0">{{ espaco.nome }}</p>
                    <small class="text-white-50">{{ espaco.tipo }}</small>
                  </div>
                  <div class="top-count">
                    <span class="badge bg-primary">{{ espaco.total_reservas }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-5">
                <i class="bi bi-inbox text-white-50 fs-1"></i>
                <p class="text-white-50 mt-2">Nenhum dado disponível</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Distribuição Semanal e Sugestões -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-calendar-week me-2"></i>
                Distribuição Semanal
              </h5>
            </div>
            <div class="card-body">
              <div v-if="dashboard.distribuicao_semanal?.length > 0" class="semana-lista">
                <div 
                  v-for="dia in dashboard.distribuicao_semanal" 
                  :key="dia.dia"
                  class="semana-item"
                >
                  <div class="semana-dia">{{ dia.dia }}</div>
                  <div class="semana-progress">
                    <div 
                      class="semana-bar"
                      :style="{ width: calcularPercentual(dia.total, getMaxSemanal()) + '%' }"
                    ></div>
                  </div>
                  <div class="semana-count">{{ dia.total }}</div>
                </div>
              </div>
              <div v-else class="text-center py-5">
                <i class="bi bi-inbox text-white-50 fs-1"></i>
                <p class="text-white-50 mt-2">Nenhum dado disponível</p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-lightbulb me-2"></i>
                Sugestões de Otimização
              </h5>
            </div>
            <div class="card-body">
              <div v-if="dashboard.sugestoes_otimizacao?.length > 0" class="sugestoes-lista">
                <div 
                  v-for="(sugestao, index) in dashboard.sugestoes_otimizacao" 
                  :key="index"
                  class="sugestao-item"
                  :class="`sugestao-${sugestao.tipo}`"
                >
                  <i :class="getSugestaoIcon(sugestao.tipo)"></i>
                  <div>
                    <p class="text-white mb-1">{{ sugestao.titulo }}</p>
                    <small class="text-white-50">{{ sugestao.mensagem }}</small>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <i class="bi bi-check-circle text-success fs-1"></i>
                <p class="text-white-50 mt-2">Tudo funcionando perfeitamente!</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações Rápidas -->
      <div class="row g-3">
        <div class="col-md-12">
          <div class="card card-glass">
            <div class="card-header">
              <h5 class="text-white mb-0">
                <i class="bi bi-lightning me-2"></i>
                Ações Rápidas
              </h5>
            </div>
            <div class="card-body">
              <div class="acoes-grid">
                <router-link :to="{ name: 'espacos-fisicos' }" class="acao-card">
                  <i class="bi bi-door-open"></i>
                  <span>Gerenciar Espaços</span>
                </router-link>
                <router-link :to="{ name: 'reservas-espacos' }" class="acao-card">
                  <i class="bi bi-calendar-check"></i>
                  <span>Ver Reservas</span>
                </router-link>
                <router-link :to="{ name: 'calendario-reservas' }" class="acao-card">
                  <i class="bi bi-calendar3"></i>
                  <span>Calendário</span>
                </router-link>
                <button @click="exportarRelatorio" class="acao-card">
                  <i class="bi bi-file-earmark-excel"></i>
                  <span>Exportar Relatório</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const loading = ref(true);
const atualizando = ref(false);

// ✅ DADOS DE HIERARQUIA
const instituicoes = ref([]);
const campi = ref([]);
const predios = ref([]);

// ✅ FILTROS COM HIERARQUIA COMPLETA
const filtros = ref({
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  tipo_espaco: '',
  data_inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  data_fim: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0]
});

const dashboard = ref({
  resumo: {},
  ocupacao_tempo_real: {},
  reservas_por_periodo: [],
  top_espacos: [],
  distribuicao_semanal: [],
  sugestoes_otimizacao: [],
  ocupacao_por_tipo: []
});

// ✅ COMPUTED - FILTROS EM CASCATA
const campusFiltrados = computed(() => {
  if (!filtros.value.instituicao_id) return [];
  return campi.value.filter(c => c.instituicao_id == filtros.value.instituicao_id);
});

const prediosFiltrados = computed(() => {
  if (!filtros.value.campus_id) return [];
  return predios.value.filter(p => p.campus_id == filtros.value.campus_id);
});

const percentualDisponivel = computed(() => {
  const total = dashboard.value.resumo?.total_espacos || 0;
  const disponiveis = dashboard.value.resumo?.espacos_disponiveis || 0;
  if (total === 0) return 0;
  return Math.round((disponiveis / total) * 100);
});

const getTaxaOcupacaoClass = computed(() => {
  const taxa = dashboard.value.resumo?.taxa_ocupacao || 0;
  if (taxa > 80) return 'text-danger';
  if (taxa > 60) return 'text-warning';
  return 'text-success';
});

const getTaxaOcupacaoLabel = computed(() => {
  const taxa = dashboard.value.resumo?.taxa_ocupacao || 0;
  if (taxa > 80) return 'Alto';
  if (taxa > 60) return 'Moderado';
  return 'Normal';
});

// ✅ WATCHERS PARA DEBUG (OPCIONAL - PODE REMOVER)
watch(() => filtros.value, (newVal) => {
  console.log('✅ Filtros atualizados:', newVal);
}, { deep: true });

// ✅ LIFECYCLE
onMounted(async () => {
  await Promise.all([
    carregarHierarquia(),
    carregarDados()
  ]);
  loading.value = false;
});

// ✅ CARREGAR HIERARQUIA (INSTITUIÇÃO → CAMPUS → PRÉDIO)
const carregarHierarquia = async () => {
  try {
    console.log('🔄 Carregando hierarquia...');
    
    const [instituicoesRes, campiRes, prediosRes] = await Promise.all([
      axios.get('/api/v1/instituicoes?all=true'),
      axios.get('/api/v1/campi?all=true'),
      axios.get('/api/v1/predios?all=true')
    ]);
    
    instituicoes.value = Array.isArray(instituicoesRes.data) ? instituicoesRes.data : (instituicoesRes.data?.data || []);
    campi.value = Array.isArray(campiRes.data) ? campiRes.data : (campiRes.data?.data || []);
    predios.value = Array.isArray(prediosRes.data) ? prediosRes.data : (prediosRes.data?.data || []);
    
    console.log('✅ Hierarquia carregada:', {
      instituicoes: instituicoes.value.length,
      campi: campi.value.length,
      predios: predios.value.length
    });
  } catch (error) {
    console.error('❌ Erro ao carregar hierarquia:', error);
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Erro ao carregar filtros de hierarquia',
      confirmButtonText: 'OK'
    });
  }
};

// ✅ HANDLERS DE MUDANÇA DE FILTRO (CORRIGIDOS)
const onInstituicaoChange = () => {
  console.log('🔄 Instituição alterada para:', filtros.value.instituicao_id);
  filtros.value.campus_id = '';
  filtros.value.predio_id = '';
  carregarDados();
};

const onCampusChange = () => {
  console.log('🔄 Campus alterado para:', filtros.value.campus_id);
  filtros.value.predio_id = '';
  carregarDados();
};

// ✅ CARREGAR DADOS DO DASHBOARD (CORRIGIDO)
const carregarDados = async () => {
  try {
    console.log('🔄 Carregando dados do dashboard com filtros:', filtros.value);
    
    const params = new URLSearchParams();
    
    // ✅ ADICIONA PARÂMETROS SOMENTE SE EXISTIREM
    if (filtros.value.instituicao_id) {
      params.append('instituicao_id', filtros.value.instituicao_id);
    }
    if (filtros.value.campus_id) {
      params.append('campus_id', filtros.value.campus_id);
    }
    if (filtros.value.predio_id) {
      params.append('predio_id', filtros.value.predio_id);
    }
    if (filtros.value.tipo_espaco) {
      params.append('tipo_espaco', filtros.value.tipo_espaco);
    }
    if (filtros.value.data_inicio) {
      params.append('data_inicio', filtros.value.data_inicio);
    }
    if (filtros.value.data_fim) {
      params.append('data_fim', filtros.value.data_fim);
    }

    const url = `/api/v1/dashboard-espacos?${params.toString()}`;
    console.log('📡 Request URL:', url);
    
    const { data } = await axios.get(url);
    dashboard.value = data;
    
    console.log('✅ Dados carregados:', data);
  } catch (error) {
    console.error('❌ Erro ao carregar dados:', error);
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Não foi possível carregar os dados do dashboard',
      confirmButtonText: 'OK'
    });
  }
};

// ✅ ATUALIZAR DADOS
const atualizarDados = async () => {
  atualizando.value = true;
  await carregarDados();
  atualizando.value = false;
  Swal.fire({
    icon: 'success',
    title: 'Atualizado!',
    text: 'Dados atualizados com sucesso',
    timer: 1500,
    showConfirmButton: false
  });
};

// ✅ GERENCIAMENTO DE FILTROS (CORRIGIDO)
const getFiltrosAtivos = () => {
  const ativos = [];
  
  if (filtros.value.instituicao_id) {
    const inst = instituicoes.value.find(i => i.id == filtros.value.instituicao_id);
    if (inst) ativos.push({ 
      key: 'instituicao_id', 
      label: 'Instituição', 
      value: inst.nome // ✅ AGORA USA O NOME COMPLETO
    });
  }
  
  if (filtros.value.campus_id) {
    const campus = campi.value.find(c => c.id == filtros.value.campus_id);
    if (campus) ativos.push({ key: 'campus_id', label: 'Campus', value: campus.nome });
  }
  
  if (filtros.value.predio_id) {
    const predio = predios.value.find(p => p.id == filtros.value.predio_id);
    if (predio) ativos.push({ key: 'predio_id', label: 'Prédio', value: predio.nome });
  }
  
  if (filtros.value.tipo_espaco) {
    ativos.push({ key: 'tipo_espaco', label: 'Tipo', value: filtros.value.tipo_espaco });
  }
  
  return ativos;
};

const removerFiltro = (key) => {
  console.log('🗑️ Removendo filtro:', key);
  
  if (key === 'instituicao_id') {
    filtros.value.instituicao_id = '';
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
  } else if (key === 'campus_id') {
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
  } else {
    filtros.value[key] = '';
  }
  carregarDados();
};

const limparFiltros = () => {
  console.log('🗑️ Limpando todos os filtros');
  
  filtros.value.instituicao_id = '';
  filtros.value.campus_id = '';
  filtros.value.predio_id = '';
  filtros.value.tipo_espaco = '';
  carregarDados();
};

// ✅ FUNÇÕES AUXILIARES
const getTotalReservas = () => {
  return dashboard.value.ocupacao_por_tipo?.reduce((acc, tipo) => acc + tipo.total_reservas, 0) || 1;
};

const getMaxSemanal = () => {
  return Math.max(...(dashboard.value.distribuicao_semanal?.map(d => d.total) || [1]));
};

const calcularPercentual = (valor, total) => {
  if (total === 0) return 0;
  return Math.min(Math.round((valor / total) * 100), 100);
};

const getTipoIcon = (tipo) => {
  const icons = {
    'Sala de Aula': 'bi bi-door-open',
    'Laboratório': 'bi bi-cpu',
    'Auditório': 'bi bi-megaphone',
    'Biblioteca': 'bi bi-book',
    'Sala de Reunião': 'bi bi-people',
    'Área de Convivência': 'bi bi-cup-hot'
  };
  return icons[tipo] || 'bi bi-building';
};

const getSugestaoIcon = (tipo) => {
  const icons = {
    'warning': 'bi bi-exclamation-triangle text-warning',
    'info': 'bi bi-info-circle text-info',
    'success': 'bi bi-check-circle text-success',
    'error': 'bi bi-x-circle text-danger'
  };
  return icons[tipo] || 'bi bi-info-circle text-info';
};

const exportarRelatorio = async () => {
  try {
    const params = new URLSearchParams();
    if (filtros.value.instituicao_id) params.append('instituicao_id', filtros.value.instituicao_id);
    if (filtros.value.campus_id) params.append('campus_id', filtros.value.campus_id);
    if (filtros.value.predio_id) params.append('predio_id', filtros.value.predio_id);
    if (filtros.value.tipo_espaco) params.append('tipo_espaco', filtros.value.tipo_espaco);
    if (filtros.value.data_inicio) params.append('data_inicio', filtros.value.data_inicio);
    if (filtros.value.data_fim) params.append('data_fim', filtros.value.data_fim);

    window.open(`/api/v1/relatorios/reservas/excel?${params.toString()}`, '_blank');
  } catch (error) {
    Swal.fire('Erro!', 'Erro ao exportar relatório', 'error');
  }
};
</script>

<style scoped>
/* ============================= */
/* SKELETON LOADING STYLES */
/* ============================= */
.skeleton {
  background: linear-gradient(90deg, 
    rgba(255, 255, 255, 0.05) 25%, 
    rgba(255, 255, 255, 0.1) 50%, 
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.skeleton-title {
  height: 32px;
  width: 350px;
}

.skeleton-text {
  height: 16px;
  width: 100%;
}

.skeleton-button {
  height: 38px;
  width: 120px;
  border-radius: 6px;
}

.skeleton-input {
  height: 38px;
  width: 100%;
  border-radius: 6px;
}

.skeleton-heading {
  height: 40px;
  width: 80px;
}

.skeleton-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
}

.skeleton-bar {
  height: 30px;
  width: 100%;
  border-radius: 8px;
}

.skeleton-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-action-card {
  height: 120px;
  width: 100%;
  border-radius: 12px;
}

/* ============================= */
/* STATS CARDS */
/* ============================= */
.stat-card {
  transition: transform 0.3s;
  height: 100%;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: white;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ============================= */
/* FILTROS ATIVOS */
/* ============================= */
.cursor-pointer {
  cursor: pointer;
}

.badge .bi-x {
  transition: transform 0.2s;
}

.badge .bi-x:hover {
  transform: scale(1.2);
}

/* ============================= */
/* OCUPAÇÃO POR TIPO */
/* ============================= */
.tipo-lista {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.tipo-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.tipo-info {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 220px;
}

.tipo-icon {
  font-size: 1.5rem;
  color: #60a5fa;
}

.tipo-progress {
  flex: 1;
}

.progress-bar-custom {
  height: 30px;
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 10px;
  transition: width 0.5s ease;
  min-width: 40px;
}

.progress-bar-custom span {
  color: white;
  font-weight: 600;
  font-size: 0.85rem;
}

/* ============================= */
/* TOP ESPAÇOS */
/* ============================= */
.top-espacos-lista {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.top-espaco-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  transition: all 0.3s;
}

.top-espaco-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.ranking-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.ranking-1 {
  background: linear-gradient(135deg, #FFD700, #FFA500);
  color: #000;
}

.ranking-2 {
  background: linear-gradient(135deg, #C0C0C0, #808080);
  color: #fff;
}

.ranking-3 {
  background: linear-gradient(135deg, #CD7F32, #8B4513);
  color: #fff;
}

.ranking-4, .ranking-5 {
  background: rgba(59, 130, 246, 0.3);
  color: #fff;
}

.top-info {
  flex: 1;
}

.top-info p {
  font-size: 0.9rem;
}

/* ============================= */
/* DISTRIBUIÇÃO SEMANAL */
/* ============================= */
.semana-lista {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.semana-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.semana-dia {
  min-width: 80px;
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

.semana-progress {
  flex: 1;
  height: 24px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  overflow: hidden;
}

.semana-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
  transition: width 0.5s ease;
  min-width: 2px;
}

.semana-count {
  min-width: 40px;
  text-align: right;
  color: white;
  font-weight: 600;
}

/* ============================= */
/* SUGESTÕES */
/* ============================= */
.sugestoes-lista {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.sugestao-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border-left: 3px solid;
}

.sugestao-warning {
  border-left-color: #fbbf24;
}

.sugestao-info {
  border-left-color: #3b82f6;
}

.sugestao-success {
  border-left-color: #22c55e;
}

.sugestao-error {
  border-left-color: #ef4444;
}

.sugestao-item i {
  font-size: 1.5rem;
  margin-top: 4px;
}

/* ============================= */
/* AÇÕES RÁPIDAS */
/* ============================= */
.acoes-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
}

.acao-card {
  padding: 25px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: white;
  transition: all 0.3s;
  cursor: pointer;
}

.acao-card:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-3px);
}

.acao-card i {
  font-size: 2rem;
  color: #60a5fa;
}

.acao-card span {
  text-align: center;
  font-size: 0.9rem;
}

/* ============================= */
/* RESPONSIVE */
/* ============================= */
@media (max-width: 768px) {
  .acoes-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .skeleton-title {
    width: 100%;
  }
}
</style>
