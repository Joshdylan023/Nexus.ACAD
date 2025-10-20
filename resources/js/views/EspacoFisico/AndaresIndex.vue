<template>
  <div class="andares-page">
    <EspacoFisicoSkeleton v-if="loading" />

    <div v-else>
      <!-- Header -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h3 class="text-white mb-2">
                <i class="bi bi-stack me-2"></i>
                Gestão de Andares
              </h3>
              <p class="text-white-50 mb-0">Gerencie os andares dos blocos</p>
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
                v-if="$can('criar-andares')"
              >
                <i class="bi bi-plus-circle me-2"></i>
                Novo Andar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row g-3">
            <!-- ⭐ NOVO: Filtro de Instituição -->
            <div class="col-md-2">
              <label class="form-label text-white">Instituição</label>
              <select v-model="filtros.instituicao_id" @change="carregarCampiFiltro" class="form-select bg-transparent text-white border-secondary">
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
                @change="carregarPrediosFiltro" 
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
                @change="carregarBlocosFiltro" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.campus_id && prediosFiltro.length === 0"
              >
                <option value="">Todos</option>
                <option v-for="predio in prediosFiltro" :key="predio.id" :value="predio.id">
                  {{ predio.nome }}
                </option>
              </select>
            </div>

            <!-- Bloco -->
            <div class="col-md-2">
              <label class="form-label text-white">Bloco</label>
              <select 
                v-model="filtros.bloco_id" 
                @change="filtrar" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.predio_id && blocosFiltro.length === 0"
              >
                <option value="">Todos</option>
                <option v-for="bloco in blocosFiltro" :key="bloco.id" :value="bloco.id">
                  {{ bloco.nome }}
                </option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label text-white">Status</label>
              <select v-model="filtros.status" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todos</option>
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label text-white">Buscar</label>
              <input 
                v-model="filtros.search" 
                @input="filtrar"
                type="text" 
                class="form-control bg-transparent text-white border-secondary"
                placeholder="Nome..."
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Lista -->
      <div class="row g-3">
        <div class="col-md-3" v-for="andar in andares" :key="andar.id">
          <div class="card card-glass andar-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="text-white mb-1">{{ andar.nome }}</h5>
                  <span class="badge bg-info">{{ andar.numero }}</span>
                </div>
                <span :class="andar.status === 'Ativo' ? 'badge bg-success' : 'badge bg-secondary'">
                  {{ andar.status }}
                </span>
              </div>

              <div class="andar-info mb-3">
                <p class="text-white-50 small mb-1">
                  <i class="bi bi-layers me-1"></i>
                  {{ andar.bloco?.nome }}
                </p>
                <p class="text-white-50 small mb-0">
                  <i class="bi bi-building me-1"></i>
                  {{ andar.bloco?.predio?.nome }}
                </p>
              </div>

              <div class="d-flex gap-2">
                <button 
                  @click="abrirFormulario(andar)" 
                  class="btn btn-sm btn-outline-warning flex-fill"
                  v-if="$can('editar-andares')"
                >
                  <i class="bi bi-pencil me-1"></i> Editar
                </button>
                <button 
                  @click="excluir(andar.id)" 
                  class="btn btn-sm btn-outline-danger"
                  v-if="$can('excluir-andares')"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="andares.length === 0" class="text-center py-5">
        <i class="bi bi-inbox display-1 text-white-50"></i>
        <p class="text-white-50 mt-3">Nenhum andar cadastrado</p>
      </div>
    </div>

    <!-- Modal -->
    <AndarForm 
      v-if="mostrarFormulario"
      :andar="andarSelecionado"
      @close="fecharFormulario"
      @success="andarSalvo"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import EspacoFisicoSkeleton from '@/components/EspacoFisico/EspacoFisicoSkeleton.vue';
import AndarForm from './AndarForm.vue';

const loading = ref(true);
const loadingPdf = ref(false); // ✅ NOVO
const andares = ref([]);
const instituicoes = ref([]);
const campiFiltro = ref([]);
const prediosFiltro = ref([]);
const blocosFiltro = ref([]);
const mostrarFormulario = ref(false);
const andarSelecionado = ref(null);

const filtros = ref({
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  bloco_id: '',
  status: '',
  search: ''
});

onMounted(async () => {
  await Promise.all([
    carregarAndares(), 
    carregarInstituicoes(),
    carregarTodosCampi(),
    carregarTodosPredios(),
    carregarTodosBlocos()
  ]);
  loading.value = false;
});

// ✅ NOVA FUNÇÃO: EXPORTAR PDF
const exportarPdf = async () => {
  loadingPdf.value = true;
  
  try {
    const params = new URLSearchParams();
    
    // Adicionar filtros que estão preenchidos
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null) {
        params.append(key, filtros.value[key]);
      }
    });
    
    const queryString = params.toString();
    const url = queryString ? `/api/v1/andares/exportar-pdf?${queryString}` : '/api/v1/andares/exportar-pdf';
    
    // Fazer requisição
    const response = await axios.get(url, {
      responseType: 'blob',
    });
    
    // Criar link de download
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = `andares-${new Date().toISOString().split('T')[0]}.pdf`;
    link.click();
    
    // Limpar
    window.URL.revokeObjectURL(link.href);
    
    Swal.fire({
      icon: 'success',
      title: 'PDF gerado!',
      text: 'O relatório foi baixado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    console.error('Erro ao exportar PDF:', error);
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: error.response?.data?.message || 'Erro ao gerar PDF'
    });
  } finally {
    loadingPdf.value = false;
  }
};

const carregarAndares = async () => {
  try {
    const params = new URLSearchParams();
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null) {
        params.append(key, filtros.value[key]);
      }
    });
    
    const { data } = await axios.get(`/api/v1/andares?${params.toString()}`);
    andares.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar andares:', error);
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

const carregarCampiFiltro = async () => {
  if (!filtros.value.instituicao_id) {
    await carregarTodosCampi();
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    prediosFiltro.value = [];
    blocosFiltro.value = [];
    await carregarTodosPredios();
    await carregarTodosBlocos();
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${filtros.value.instituicao_id}&all=true`);
    campiFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    prediosFiltro.value = [];
    blocosFiltro.value = [];
    filtrar();
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

const carregarPrediosFiltro = async () => {
  if (!filtros.value.campus_id) {
    await carregarTodosPredios();
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    blocosFiltro.value = [];
    await carregarTodosBlocos();
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/predios?campus_id=${filtros.value.campus_id}&all=true`);
    prediosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.predio_id = '';
    filtros.value.bloco_id = '';
    blocosFiltro.value = [];
    filtrar();
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

const carregarBlocosFiltro = async () => {
  if (!filtros.value.predio_id) {
    await carregarTodosBlocos();
    filtros.value.bloco_id = '';
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/blocos?predio_id=${filtros.value.predio_id}&all=true`);
    blocosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.bloco_id = '';
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
  }
};

const filtrar = () => {
  carregarAndares();
};

const abrirFormulario = (andar) => {
  andarSelecionado.value = andar;
  mostrarFormulario.value = true;
};

const fecharFormulario = () => {
  mostrarFormulario.value = false;
  andarSelecionado.value = null;
};

const andarSalvo = () => {
  fecharFormulario();
  carregarAndares();
  Swal.fire('Sucesso!', 'Andar salvo com sucesso!', 'success');
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
      await axios.delete(`/api/v1/andares/${id}`);
      Swal.fire('Excluído!', 'Andar excluído com sucesso', 'success');
      carregarAndares();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao excluir andar', 'error');
    }
  }
};
</script>

<style scoped>
.andar-card {
  transition: all 0.3s;
  height: 100%;
  border-left: 3px solid #60a5fa;
}

.andar-card:hover {
  transform: translateY(-5px);
  border-left-color: #3b82f6;
}

.andar-info {
  padding: 10px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}
</style>
