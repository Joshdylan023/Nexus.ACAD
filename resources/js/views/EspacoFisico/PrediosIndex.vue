<template>
  <div class="predios-page">
    <!-- Loading Skeleton -->
    <EspacoFisicoSkeleton v-if="loading" />

    <!-- Content -->
    <div v-else>
      <!-- Header -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h3 class="text-white mb-2">
                <i class="bi bi-building me-2"></i>
                Gestão de Prédios
              </h3>
              <p class="text-white-50 mb-0">
                Gerencie os prédios da instituição
              </p>
            </div>
            <div class="col-md-6 text-end">
              <!-- ✅ BOTÃO EXPORTAR PDF -->
              <button 
                @click="exportarPdf" 
                class="btn btn-outline-danger me-2"
                :disabled="loadingPdf"
              >
                <i class="bi me-2" :class="loadingPdf ? 'bi-hourglass-split' : 'bi-file-pdf'"></i>
                {{ loadingPdf ? 'Gerando PDF...' : 'Exportar PDF' }}
              </button>
              
              <button 
                @click="abrirFormulario(null)" 
                class="btn btn-primary"
                v-if="$can('criar-predios')"
              >
                <i class="bi bi-plus-circle me-2"></i>
                Novo Prédio
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row g-3">
            <!-- Filtro de Instituição -->
            <div class="col-md-3">
              <label class="form-label text-white">Instituição</label>
              <select v-model="filtros.instituicao_id" @change="carregarCampiFiltro" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todas as Instituições</option>
                <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                  {{ inst.nome_fantasia || inst.razao_social }}
                </option>
              </select>
            </div>

            <!-- Campus (dependente da Instituição) -->
            <div class="col-md-3">
              <label class="form-label text-white">Campus</label>
              <select 
                v-model="filtros.campus_id" 
                @change="filtrar" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.instituicao_id && campiFiltro.length === 0"
              >
                <option value="">Todos os Campi</option>
                <option v-for="campus in campiFiltro" :key="campus.id" :value="campus.id">
                  {{ campus.nome }}
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label text-white">Status</label>
              <select v-model="filtros.status" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todos</option>
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
                <option value="Manutenção">Manutenção</option>
                <option value="Reforma">Reforma</option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label text-white">Acessibilidade</label>
              <select v-model="filtros.acessibilidade" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todos</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
              </select>
            </div>

            <div class="col-md-2">
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
        </div>
      </div>

      <!-- Estatísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Total de Prédios</p>
                  <h4 class="text-white mb-0">{{ estatisticas.total_predios }}</h4>
                </div>
                <div class="stat-icon bg-primary">
                  <i class="bi bi-building"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Prédios Ativos</p>
                  <h4 class="text-white mb-0">{{ estatisticas.predios_ativos }}</h4>
                </div>
                <div class="stat-icon bg-success">
                  <i class="bi bi-check-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Com Acessibilidade</p>
                  <h4 class="text-white mb-0">{{ estatisticas.com_acessibilidade }}</h4>
                </div>
                <div class="stat-icon bg-info">
                  <i class="bi bi-universal-access"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Área Total</p>
                  <h4 class="text-white mb-0">{{ formatarArea(estatisticas.area_total) }}</h4>
                </div>
                <div class="stat-icon bg-warning">
                  <i class="bi bi-rulers"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Prédios -->
      <div class="row g-3">
        <div class="col-md-4" v-for="predio in predios" :key="predio.id">
          <div class="card card-glass predio-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="text-white mb-1">{{ predio.nome }}</h5>
                  <span class="badge bg-secondary">{{ predio.codigo }}</span>
                </div>
                <span :class="getStatusClass(predio.status)">
                  {{ predio.status }}
                </span>
              </div>

              <p class="text-white-50 small mb-3">
                <i class="bi bi-geo-alt me-1"></i>
                {{ predio.campus?.nome }}
              </p>

              <div class="predio-info">
                <div class="info-item">
                  <i class="bi bi-layers text-primary"></i>
                  <span>{{ predio.total_blocos }} blocos</span>
                </div>
                <div class="info-item">
                  <i class="bi bi-building text-info"></i>
                  <span>{{ predio.total_andares }} andares</span>
                </div>
                <div class="info-item" v-if="predio.area_construida">
                  <i class="bi bi-rulers text-warning"></i>
                  <span>{{ formatarArea(predio.area_construida) }}</span>
                </div>
              </div>

              <div class="d-flex gap-2 mt-3">
                <span v-if="predio.acessibilidade" class="badge bg-success-soft">
                  <i class="bi bi-universal-access"></i> Acessível
                </span>
                <span v-if="predio.elevador" class="badge bg-info-soft">
                  <i class="bi bi-arrow-up-square"></i> Elevador
                </span>
                <span v-if="predio.ar_condicionado" class="badge bg-primary-soft">
                  <i class="bi bi-wind"></i> Ar Cond.
                </span>
              </div>

              <div class="d-flex gap-2 mt-3">
                <button 
                  @click="verDetalhes(predio.id)" 
                  class="btn btn-sm btn-outline-primary flex-fill"
                >
                  <i class="bi bi-eye me-1"></i>
                  Ver
                </button>
                <button 
                  @click="abrirFormulario(predio)" 
                  class="btn btn-sm btn-outline-warning flex-fill"
                  v-if="$can('editar-predios')"
                >
                  <i class="bi bi-pencil me-1"></i>
                  Editar
                </button>
                <button 
                  @click="excluir(predio.id)" 
                  class="btn btn-sm btn-outline-danger"
                  v-if="$can('excluir-predios')"
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
    <PredioForm 
      v-if="mostrarFormulario"
      :predio="predioSelecionado"
      @close="fecharFormulario"
      @success="predioSalvo"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import EspacoFisicoSkeleton from '@/components/EspacoFisico/EspacoFisicoSkeleton.vue';
import PredioForm from './PredioForm.vue';

const router = useRouter();
const loading = ref(true);
const loadingPdf = ref(false); // ✅ NOVO
const predios = ref([]);
const instituicoes = ref([]);
const campiFiltro = ref([]);
const estatisticas = ref({});
const paginacao = ref({});
const mostrarFormulario = ref(false);
const predioSelecionado = ref(null);

const filtros = ref({
  instituicao_id: '',
  campus_id: '',
  status: '',
  acessibilidade: '',
  search: '',
  page: 1
});

onMounted(async () => {
  await Promise.all([
    carregarPredios(),
    carregarInstituicoes(),
    carregarTodosCampi(),
    carregarEstatisticas()
  ]);
  loading.value = false;
});

// ✅ NOVA FUNÇÃO: EXPORTAR PDF
const exportarPdf = async () => {
  loadingPdf.value = true;
  
  try {
    const params = new URLSearchParams();
    
    // Adicionar filtros (exceto page)
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null && key !== 'page') {
        params.append(key, filtros.value[key]);
      }
    });
    
    const queryString = params.toString();
    const url = queryString 
      ? `/api/v1/predios/exportar-pdf?${queryString}` 
      : '/api/v1/predios/exportar-pdf';
    
    console.log('🔍 URL da requisição:', url);
    
    const response = await axios.get(url, {
      responseType: 'blob',
    });
    
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = `predios-${new Date().toISOString().split('T')[0]}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(link.href);
    
    Swal.fire({
      icon: 'success',
      title: 'PDF gerado!',
      text: 'O relatório foi baixado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    console.error('❌ Erro ao exportar PDF:', error);
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: error.response?.data?.message || 'Erro ao gerar PDF'
    });
  } finally {
    loadingPdf.value = false;
  }
};

const carregarPredios = async () => {
  try {
    const params = new URLSearchParams();
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null) {
        params.append(key, filtros.value[key]);
      }
    });

    const { data } = await axios.get(`/api/v1/predios?${params.toString()}`);
    predios.value = data.data;
    paginacao.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total
    };
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
    Swal.fire('Erro!', 'Não foi possível carregar os prédios', 'error');
  }
};

const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
    instituicoes.value = [];
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

const carregarCampiFiltro = async () => {
  if (!filtros.value.instituicao_id) {
    await carregarTodosCampi();
    filtros.value.campus_id = '';
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${filtros.value.instituicao_id}&all=true`);
    campiFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.campus_id = '';
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const carregarEstatisticas = async () => {
  try {
    const { data } = await axios.get('/api/v1/predios/estatisticas');
    estatisticas.value = data;
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error);
  }
};

const filtrar = () => {
  filtros.value.page = 1;
  carregarPredios();
};

const mudarPagina = (page) => {
  if (page >= 1 && page <= paginacao.value.last_page) {
    filtros.value.page = page;
    carregarPredios();
  }
};

const abrirFormulario = (predio) => {
  predioSelecionado.value = predio;
  mostrarFormulario.value = true;
};

const fecharFormulario = () => {
  mostrarFormulario.value = false;
  predioSelecionado.value = null;
};

const predioSalvo = () => {
  fecharFormulario();
  carregarPredios();
  carregarEstatisticas();
  Swal.fire('Sucesso!', 'Prédio salvo com sucesso!', 'success');
};

const verDetalhes = (id) => {
  router.push({ name: 'predio-detalhes', params: { id } });
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
      await axios.delete(`/api/v1/predios/${id}`);
      Swal.fire('Excluído!', 'Prédio excluído com sucesso', 'success');
      carregarPredios();
      carregarEstatisticas();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao excluir prédio', 'error');
    }
  }
};

const getStatusClass = (status) => {
  const classes = {
    'Ativo': 'badge bg-success',
    'Inativo': 'badge bg-secondary',
    'Manutenção': 'badge bg-warning',
    'Reforma': 'badge bg-danger'
  };
  return classes[status] || 'badge bg-secondary';
};

const formatarArea = (area) => {
  if (!area) return '-';
  return `${Number(area).toLocaleString('pt-BR')} m²`;
};
</script>

<style scoped>
.predio-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
}

.predio-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}

.predio-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.info-item i {
  font-size: 1.1rem;
}

.stat-card {
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.badge-success-soft {
  background: rgba(25, 135, 84, 0.2);
  color: #4ade80;
}

.badge-info-soft {
  background: rgba(13, 202, 240, 0.2);
  color: #60a5fa;
}

.badge-primary-soft {
  background: rgba(13, 110, 253, 0.2);
  color: #818cf8;
}
</style>
