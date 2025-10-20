<template>
  <div class="espacos-page">
    <!-- Loading -->
    <EspacoFisicoSkeleton v-if="loading" />

    <!-- Content -->
    <div v-else>
      <!-- Header -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h3 class="text-white mb-2">
                <i class="bi bi-door-open me-2"></i>
                Gestão de Espaços Físicos
              </h3>
              <p class="text-white-50 mb-0">
                Salas, laboratórios, auditórios e outros espaços
              </p>
            </div>
            <div class="col-md-4 text-end">
              <!-- ✅ BOTÃO DE EXPORTAR PDF -->
              <button 
                @click="exportarPDF" 
                class="btn btn-outline-success me-2"
                :disabled="exportando"
              >
                <i class="bi" :class="exportando ? 'bi-hourglass-split' : 'bi-file-earmark-pdf'"></i>
                {{ exportando ? 'Gerando...' : 'Exportar PDF' }}
              </button>

              <button 
                @click="abrirFormulario(null)" 
                class="btn btn-primary"
                v-if="$can('criar-espacos')"
              >
                <i class="bi bi-plus-circle me-2"></i>
                Novo Espaço
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros Avançados -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <!-- Filtros de Hierarquia -->
          <div class="row g-3 mb-3">
            <!-- ⭐ NOVO: Instituição -->
            <div class="col-md-2">
              <label class="form-label text-white">Instituição</label>
              <select v-model="filtros.instituicao_id" @change="carregarCampi" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todas</option>
                <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                  {{ inst.nome_fantasia || inst.razao_social }}
                </option>
              </select>
            </div>

            <!-- Campus -->
            <div class="col-md-2">
              <label class="form-label text-white">Campus</label>
              <select 
                v-model="filtros.campus_id" 
                @change="carregarPredios" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.instituicao_id && campiFiltro.length === 0"
              >
                <option value="">Todos</option>
                <option v-for="campus in campiFiltro" :key="campus.id" :value="campus.id">
                  {{ campus.nome }}
                </option>
              </select>
            </div>

            <!-- Prédio -->
            <div class="col-md-2">
              <label class="form-label text-white">Prédio</label>
              <select 
                v-model="filtros.predio_id" 
                @change="carregarBlocos" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.campus_id && prediosFiltro.length === 0"
              >
                <option value="">Todos</option>
                <option v-for="predio in prediosFiltro" :key="predio.id" :value="predio.id">
                  {{ predio.nome }}
                </option>
              </select>
            </div>

            <!-- ⭐ NOVO: Bloco -->
            <div class="col-md-2">
              <label class="form-label text-white">Bloco</label>
              <select 
                v-model="filtros.bloco_id" 
                @change="carregarAndares" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.predio_id && blocosFiltro.length === 0"
              >
                <option value="">Todos</option>
                <option v-for="bloco in blocosFiltro" :key="bloco.id" :value="bloco.id">
                  {{ bloco.nome }}
                </option>
              </select>
            </div>

            <!-- ⭐ NOVO: Andar -->
            <div class="col-md-2">
              <label class="form-label text-white">Andar</label>
              <select 
                v-model="filtros.andar_id" 
                @change="filtrar" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.bloco_id && andaresFiltro.length === 0"
              >
                <option value="">Todos</option>
                <option v-for="andar in andaresFiltro" :key="andar.id" :value="andar.id">
                  {{ andar.nome }}
                </option>
              </select>
            </div>

            <!-- Tipo -->
            <div class="col-md-2">
              <label class="form-label text-white">Tipo</label>
              <select v-model="filtros.tipo" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todos</option>
                <option value="Sala de Aula">Sala de Aula</option>
                <option value="Laboratório">Laboratório</option>
                <option value="Auditório">Auditório</option>
                <option value="Biblioteca">Biblioteca</option>
                <option value="Sala de Reunião">Sala de Reunião</option>
                <option value="Área de Convivência">Área de Convivência</option>
              </select>
            </div>
          </div>

          <!-- Segunda Linha de Filtros -->
          <div class="row g-3 mb-3">
            <div class="col-md-2">
              <label class="form-label text-white">Status</label>
              <select v-model="filtros.status" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todos</option>
                <option value="Disponível">Disponível</option>
                <option value="Ocupado">Ocupado</option>
                <option value="Manutenção">Manutenção</option>
                <option value="Reforma">Reforma</option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label text-white">Capacidade Mín.</label>
              <input 
                v-model.number="filtros.capacidade_minima" 
                @input="filtrar"
                type="number" 
                class="form-control bg-transparent text-white border-secondary"
                placeholder="0"
              />
            </div>

            <div class="col-md-8">
              <label class="form-label text-white">Buscar</label>
              <input 
                v-model="filtros.search" 
                @input="filtrar"
                type="text" 
                class="form-control bg-transparent text-white border-secondary"
                placeholder="Nome, código..."
              />
            </div>
          </div>

          <!-- Filtros de Infraestrutura -->
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label text-white mb-2">Infraestrutura:</label>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check form-check-inline">
                  <input v-model="filtros.acessibilidade" @change="filtrar" class="form-check-input" type="checkbox" id="filter-acessibilidade">
                  <label class="form-check-label text-white" for="filter-acessibilidade">
                    <i class="bi bi-universal-access"></i> Acessibilidade
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input v-model="filtros.ar_condicionado" @change="filtrar" class="form-check-input" type="checkbox" id="filter-ar">
                  <label class="form-check-label text-white" for="filter-ar">
                    <i class="bi bi-wind"></i> Ar Condicionado
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input v-model="filtros.projetor" @change="filtrar" class="form-check-input" type="checkbox" id="filter-projetor">
                  <label class="form-check-label text-white" for="filter-projetor">
                    <i class="bi bi-projector"></i> Projetor
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input v-model="filtros.computadores" @change="filtrar" class="form-check-input" type="checkbox" id="filter-computadores">
                  <label class="form-check-label text-white" for="filter-computadores">
                    <i class="bi bi-pc-display"></i> Computadores
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Estatísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-white mb-1">{{ estatisticas.total }}</h5>
              <small class="text-white-50">Total</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-success mb-1">{{ estatisticas.disponiveis }}</h5>
              <small class="text-white-50">Disponíveis</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-danger mb-1">{{ estatisticas.ocupados }}</h5>
              <small class="text-white-50">Ocupados</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-warning mb-1">{{ estatisticas.manutencao }}</h5>
              <small class="text-white-50">Manutenção</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-info mb-1">{{ formatNumber(estatisticas.capacidade_total) }}</h5>
              <small class="text-white-50">Capacidade</small>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card card-glass stat-card-mini">
            <div class="card-body text-center">
              <h5 class="text-primary mb-1">{{ estatisticas.com_projetor }}</h5>
              <small class="text-white-50">Com Projetor</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Espaços -->
      <div class="row g-3">
        <div class="col-md-4" v-for="espaco in espacos" :key="espaco.id">
          <div class="card card-glass espaco-card">
            <div class="card-body">
              <!-- Header do Card -->
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="text-white mb-1">{{ espaco.codigo }}</h5>
                  <p class="text-white-50 small mb-0">{{ espaco.nome }}</p>
                </div>
                <span :class="getStatusBadge(espaco.status)">
                  {{ espaco.status }}
                </span>
              </div>

              <!-- Tipo e Localização -->
              <div class="espaco-meta mb-3">
                <span class="badge bg-primary-soft mb-2">
                  <i :class="getTipoIcon(espaco.tipo)"></i>
                  {{ espaco.tipo }}
                </span>
                <p class="text-white-50 small mb-0">
                  <i class="bi bi-building me-1"></i>
                  {{ espaco.localizacao_completa }}
                </p>
              </div>

              <!-- Capacidade e Área -->
              <div class="espaco-info mb-3">
                <div class="info-badge">
                  <i class="bi bi-people text-primary"></i>
                  <span>{{ espaco.capacidade || '-' }} pessoas</span>
                </div>
                <div class="info-badge" v-if="espaco.area">
                  <i class="bi bi-rulers text-warning"></i>
                  <span>{{ espaco.area }} m²</span>
                </div>
              </div>

              <!-- Recursos -->
              <div class="recursos-icons mb-3">
                <span v-if="espaco.ar_condicionado" class="recurso-icon" title="Ar Condicionado">
                  <i class="bi bi-wind"></i>
                </span>
                <span v-if="espaco.projetor" class="recurso-icon" title="Projetor">
                  <i class="bi bi-projector"></i>
                </span>
                <span v-if="espaco.computadores" class="recurso-icon" title="Computadores">
                  <i class="bi bi-pc-display"></i>
                </span>
                <span v-if="espaco.lousa_digital" class="recurso-icon" title="Lousa Digital">
                  <i class="bi bi-tv"></i>
                </span>
                <span v-if="espaco.wifi" class="recurso-icon" title="Wi-Fi">
                  <i class="bi bi-wifi"></i>
                </span>
                <span v-if="espaco.acessibilidade" class="recurso-icon" title="Acessibilidade">
                  <i class="bi bi-universal-access"></i>
                </span>
              </div>

              <!-- Ações -->
              <div class="d-flex gap-2">
                <button 
                  @click="verDetalhes(espaco.id)" 
                  class="btn btn-sm btn-outline-primary flex-fill"
                >
                  <i class="bi bi-eye me-1"></i>
                  Ver
                </button>
                <button 
                  @click="abrirFormulario(espaco)" 
                  class="btn btn-sm btn-outline-warning flex-fill"
                  v-if="$can('editar-espacos')"
                >
                  <i class="bi bi-pencil me-1"></i>
                  Editar
                </button>
                <button 
                  @click="excluir(espaco.id)" 
                  class="btn btn-sm btn-outline-danger"
                  v-if="$can('excluir-espacos')"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div class="card card-glass mt-4" v-if="paginacao.last_page > 1">
        <div class="card-body">
          <nav>
            <ul class="pagination pagination-glass mb-0">
              <li class="page-item" :class="{ disabled: paginacao.current_page === 1 }">
                <a class="page-link" @click="mudarPagina(paginacao.current_page - 1)">Anterior</a>
              </li>
              <li 
                class="page-item" 
                v-for="page in paginacao.last_page" 
                :key="page"
                :class="{ active: page === paginacao.current_page }"
              >
                <a class="page-link" @click="mudarPagina(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: paginacao.current_page === paginacao.last_page }">
                <a class="page-link" @click="mudarPagina(paginacao.current_page + 1)">Próximo</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Modal de Formulário -->
    <EspacoForm 
      v-if="mostrarFormulario"
      :espaco="espacoSelecionado"
      @close="fecharFormulario"
      @success="espacoSalvo"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
import EspacoFisicoSkeleton from '@/components/EspacoFisico/EspacoFisicoSkeleton.vue';
import EspacoForm from './EspacoForm.vue';

const router = useRouter();
const toast = useToast();
const loading = ref(true);
const espacos = ref([]);
const instituicoes = ref([]);
const campiFiltro = ref([]);
const prediosFiltro = ref([]);
const blocosFiltro = ref([]);
const andaresFiltro = ref([]);
const estatisticas = ref({});
const paginacao = ref({});
const mostrarFormulario = ref(false);
const espacoSelecionado = ref(null);
const exportando = ref(false); // ✅ NOVO

const filtros = ref({
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  bloco_id: '',
  andar_id: '',
  tipo: '',
  status: '',
  capacidade_minima: null,
  acessibilidade: false,
  ar_condicionado: false,
  projetor: false,
  computadores: false,
  search: '',
  page: 1
});

onMounted(async () => {
  await Promise.all([
    carregarEspacos(),
    carregarInstituicoes(),
    carregarTodosCampi(),
    carregarTodosPredios(),
    carregarTodosBlocos(),
    carregarTodosAndares(),
    carregarEstatisticas()
  ]);
  loading.value = false;
});

// ✅ FUNÇÃO PARA EXPORTAR PDF
const exportarPDF = async () => {
  exportando.value = true;
  
  try {
    const params = new URLSearchParams();
    
    // Adicionar todos os filtros ativos
    if (filtros.value.instituicao_id) params.append('instituicao_id', filtros.value.instituicao_id);
    if (filtros.value.campus_id) params.append('campus_id', filtros.value.campus_id);
    if (filtros.value.predio_id) params.append('predio_id', filtros.value.predio_id);
    if (filtros.value.bloco_id) params.append('bloco_id', filtros.value.bloco_id);
    if (filtros.value.andar_id) params.append('andar_id', filtros.value.andar_id);
    if (filtros.value.tipo) params.append('tipo', filtros.value.tipo);
    if (filtros.value.status) params.append('status', filtros.value.status);
    if (filtros.value.search) params.append('search', filtros.value.search);
    
    // Fazer download do PDF
    const response = await axios.get(`/api/v1/espacos-fisicos/exportar-pdf?${params.toString()}`, {
      responseType: 'blob'
    });
    
    // Criar link de download
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `relatorio-espacos-${Date.now()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    toast.success('Relatório gerado com sucesso!');
  } catch (error) {
    console.error('Erro ao exportar PDF:', error);
    toast.error('Erro ao gerar relatório');
  } finally {
    exportando.value = false;
  }
};

const carregarEspacos = async () => {
  try {
    const params = new URLSearchParams();
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null && filtros.value[key] !== false) {
        params.append(key, filtros.value[key]);
      }
    });

    const { data } = await axios.get(`/api/v1/espacos-fisicos?${params.toString()}`);
    espacos.value = data.data;
    paginacao.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total
    };
  } catch (error) {
    console.error('Erro ao carregar espaços:', error);
  }
};

const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
};

const carregarTodosCampi = async () => {
  try {
    const { data } = await axios.get('/api/v1/campi?all=true');
    campiFiltro.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarTodosPredios = async () => {
  try {
    const { data } = await axios.get('/api/v1/predios?all=true');
    prediosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
  }
};

const carregarTodosBlocos = async () => {
  try {
    const { data } = await axios.get('/api/v1/blocos?all=true');
    blocosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
  }
};

const carregarTodosAndares = async () => {
  try {
    const { data } = await axios.get('/api/v1/andares?all=true');
    andaresFiltro.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
  }
};

const carregarCampi = async () => {
  if (!filtros.value.instituicao_id) {
    await carregarTodosCampi();
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    filtros.value.andar_id = '';
    prediosFiltro.value = [];
    blocosFiltro.value = [];
    andaresFiltro.value = [];
    await carregarTodosPredios();
    await carregarTodosBlocos();
    await carregarTodosAndares();
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${filtros.value.instituicao_id}&all=true`);
    campiFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    filtros.value.andar_id = '';
    prediosFiltro.value = [];
    blocosFiltro.value = [];
    andaresFiltro.value = [];
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarPredios = async () => {
  if (!filtros.value.campus_id) {
    await carregarTodosPredios();
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    filtros.value.andar_id = '';
    blocosFiltro.value = [];
    andaresFiltro.value = [];
    await carregarTodosBlocos();
    await carregarTodosAndares();
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/predios?campus_id=${filtros.value.campus_id}&all=true`);
    prediosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    filtros.value.andar_id = '';
    blocosFiltro.value = [];
    andaresFiltro.value = [];
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
  }
};

const carregarBlocos = async () => {
  if (!filtros.value.predio_id) {
    await carregarTodosBlocos();
    filtros.value.bloco_id = '';
    filtros.value.andar_id = '';
    andaresFiltro.value = [];
    await carregarTodosAndares();
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/blocos?predio_id=${filtros.value.predio_id}&all=true`);
    blocosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.bloco_id = '';
    filtros.value.andar_id = '';
    andaresFiltro.value = [];
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
  }
};

const carregarAndares = async () => {
  if (!filtros.value.bloco_id) {
    await carregarTodosAndares();
    filtros.value.andar_id = '';
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/andares?bloco_id=${filtros.value.bloco_id}&all=true`);
    andaresFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.andar_id = '';
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
  }
};

const carregarEstatisticas = async () => {
  try {
    const { data } = await axios.get('/api/v1/espacos-fisicos/estatisticas');
    estatisticas.value = data;
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error);
  }
};

const filtrar = () => {
  filtros.value.page = 1;
  carregarEspacos();
};

const mudarPagina = (page) => {
  if (page >= 1 && page <= paginacao.value.last_page) {
    filtros.value.page = page;
    carregarEspacos();
  }
};

const abrirFormulario = (espaco) => {
  espacoSelecionado.value = espaco;
  mostrarFormulario.value = true;
};

const fecharFormulario = () => {
  mostrarFormulario.value = false;
  espacoSelecionado.value = null;
};

const espacoSalvo = () => {
  fecharFormulario();
  carregarEspacos();
  carregarEstatisticas();
  Swal.fire('Sucesso!', 'Espaço salvo com sucesso!', 'success');
};

const verDetalhes = (id) => {
  router.push({ name: 'espaco-detalhes', params: { id } });
};

const excluir = async (id) => {
  const result = await Swal.fire({
    title: 'Tem certeza?',
    text: 'Esta ação não poderá ser revertida!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/v1/espacos-fisicos/${id}`);
      Swal.fire('Excluído!', 'Espaço excluído com sucesso', 'success');
      carregarEspacos();
      carregarEstatisticas();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao excluir espaço', 'error');
    }
  }
};

const getStatusBadge = (status) => {
  const badges = {
    'Disponível': 'badge bg-success',
    'Ocupado': 'badge bg-danger',
    'Manutenção': 'badge bg-warning',
    'Reforma': 'badge bg-secondary',
    'Indisponível': 'badge bg-dark'
  };
  return badges[status] || 'badge bg-secondary';
};

const getTipoIcon = (tipo) => {
  const icons = {
    'Sala de Aula': 'bi bi-door-open',
    'Laboratório': 'bi bi-cpu',
    'Auditório': 'bi bi-megaphone',
    'Biblioteca': 'bi bi-book',
    'Sala de Reunião': 'bi bi-people',
    'Área de Convivência': 'bi bi-cup'
  };
  return icons[tipo] || 'bi bi-building';
};

const formatNumber = (num) => {
  if (!num) return '0';
  return Number(num).toLocaleString('pt-BR');
};
</script>

<style scoped>
.espaco-card {
  transition: all 0.3s ease;
  height: 100%;
}

.espaco-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}

.recursos-icons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.recurso-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #60a5fa;
  font-size: 1rem;
  transition: all 0.2s;
}

.recurso-icon:hover {
  background: rgba(96, 165, 250, 0.2);
  transform: scale(1.1);
}

.info-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  margin-right: 8px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.85rem;
}

.stat-card-mini {
  transition: transform 0.3s;
}

.stat-card-mini:hover {
  transform: translateY(-3px);
}
</style>
