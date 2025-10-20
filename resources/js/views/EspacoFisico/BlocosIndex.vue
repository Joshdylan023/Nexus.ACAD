<template>
  <div class="blocos-page">
    <EspacoFisicoSkeleton v-if="loading" />

    <div v-else>
      <!-- Header -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h3 class="text-white mb-2">
                <i class="bi bi-layers me-2"></i>
                Gestão de Blocos
              </h3>
              <p class="text-white-50 mb-0">Gerencie os blocos dos prédios</p>
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
                v-if="$can('criar-blocos')"
              >
                <i class="bi bi-plus-circle me-2"></i>
                Novo Bloco
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
            <div class="col-md-3">
              <label class="form-label text-white">Instituição</label>
              <select v-model="filtros.instituicao_id" @change="carregarCampiFiltro" class="form-select bg-transparent text-white border-secondary">
                <option value="">Todas as Instituições</option>
                <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                  {{ inst.nome_fantasia || inst.razao_social }}
                </option>
              </select>
            </div>

            <!-- Filtro de Campus (dependente da Instituição) -->
            <div class="col-md-3">
              <label class="form-label text-white">Campus</label>
              <select 
                v-model="filtros.campus_id" 
                @change="carregarPrediosFiltro" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.instituicao_id && campiFiltro.length === 0"
              >
                <option value="">Todos os Campi</option>
                <option v-for="campus in campiFiltro" :key="campus.id" :value="campus.id">
                  {{ campus.nome }}
                </option>
              </select>
            </div>

            <!-- Filtro de Prédio (dependente do Campus) -->
            <div class="col-md-2">
              <label class="form-label text-white">Prédio</label>
              <select 
                v-model="filtros.predio_id" 
                @change="filtrar" 
                class="form-select bg-transparent text-white border-secondary"
                :disabled="!filtros.campus_id && prediosFiltro.length === 0"
              >
                <option value="">Todos os Prédios</option>
                <option v-for="predio in prediosFiltro" :key="predio.id" :value="predio.id">
                  {{ predio.nome }}
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
                placeholder="Nome, código..."
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Lista -->
      <div class="row g-3">
        <div class="col-md-4" v-for="bloco in blocos" :key="bloco.id">
          <div class="card card-glass bloco-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="text-white mb-1">{{ bloco.nome }}</h5>
                  <span class="badge bg-secondary">{{ bloco.codigo }}</span>
                </div>
                <span :class="bloco.status === 'Ativo' ? 'badge bg-success' : 'badge bg-secondary'">
                  {{ bloco.status }}
                </span>
              </div>

              <p class="text-white-50 small">
                <i class="bi bi-building me-1"></i>
                {{ bloco.predio?.nome }}
              </p>

              <div class="d-flex gap-2">
                <button 
                  @click="abrirFormulario(bloco)" 
                  class="btn btn-sm btn-outline-warning flex-fill"
                  v-if="$can('editar-blocos')"
                >
                  <i class="bi bi-pencil me-1"></i> Editar
                </button>
                <button 
                  @click="excluir(bloco.id)" 
                  class="btn btn-sm btn-outline-danger"
                  v-if="$can('excluir-blocos')"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="blocos.length === 0" class="text-center py-5">
        <i class="bi bi-inbox display-1 text-white-50"></i>
        <p class="text-white-50 mt-3">Nenhum bloco cadastrado</p>
      </div>
    </div>

    <!-- Modal -->
    <BlocoForm 
      v-if="mostrarFormulario"
      :bloco="blocoSelecionado"
      @close="fecharFormulario"
      @success="blocoSalvo"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import EspacoFisicoSkeleton from '@/components/EspacoFisico/EspacoFisicoSkeleton.vue';
import BlocoForm from './BlocoForm.vue';

const loading = ref(true);
const loadingPdf = ref(false); // ✅ NOVO
const blocos = ref([]);
const instituicoes = ref([]);
const campiFiltro = ref([]);
const prediosFiltro = ref([]);
const mostrarFormulario = ref(false);
const blocoSelecionado = ref(null);

const filtros = ref({
  instituicao_id: '',
  campus_id: '',
  predio_id: '',
  status: '',
  search: ''
});

onMounted(async () => {
  await Promise.all([
    carregarBlocos(), 
    carregarInstituicoes(),
    carregarTodosCampi(),
    carregarTodosPredios()
  ]);
  loading.value = false;
});

// ✅ NOVA FUNÇÃO: EXPORTAR PDF
const exportarPdf = async () => {
  loadingPdf.value = true;
  
  try {
    const params = new URLSearchParams();
    
    // Adicionar filtros
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null) {
        params.append(key, filtros.value[key]);
      }
    });
    
    const queryString = params.toString();
    const url = queryString 
      ? `/api/v1/blocos/exportar-pdf?${queryString}` 
      : '/api/v1/blocos/exportar-pdf';
    
    console.log('🔍 URL da requisição:', url);
    
    const response = await axios.get(url, {
      responseType: 'blob',
    });
    
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = `blocos-${new Date().toISOString().split('T')[0]}.pdf`;
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

const carregarBlocos = async () => {
  try {
    const params = new URLSearchParams();
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null) {
        params.append(key, filtros.value[key]);
      }
    });
    
    const { data } = await axios.get(`/api/v1/blocos?${params.toString()}`);
    blocos.value = Array.isArray(data) ? data : (data?.data || []);
  } catch (error) {
    console.error('Erro ao carregar blocos:', error);
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
    prediosFiltro.value = [];
    await carregarTodosPredios();
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${filtros.value.instituicao_id}&all=true`);
    campiFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.campus_id = '';
    filtros.value.predio_id = '';
    prediosFiltro.value = [];
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
    filtrar();
    return;
  }
  
  try {
    const { data } = await axios.get(`/api/v1/predios?campus_id=${filtros.value.campus_id}&all=true`);
    prediosFiltro.value = Array.isArray(data) ? data : (data?.data || []);
    filtros.value.predio_id = '';
    filtrar();
  } catch (error) {
    console.error('Erro ao carregar prédios:', error);
  }
};

const filtrar = () => {
  carregarBlocos();
};

const abrirFormulario = (bloco) => {
  blocoSelecionado.value = bloco;
  mostrarFormulario.value = true;
};

const fecharFormulario = () => {
  mostrarFormulario.value = false;
  blocoSelecionado.value = null;
};

const blocoSalvo = () => {
  fecharFormulario();
  carregarBlocos();
  Swal.fire('Sucesso!', 'Bloco salvo com sucesso!', 'success');
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
      await axios.delete(`/api/v1/blocos/${id}`);
      Swal.fire('Excluído!', 'Bloco excluído com sucesso', 'success');
      carregarBlocos();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao excluir bloco', 'error');
    }
  }
};
</script>

<style scoped>
.bloco-card {
  transition: all 0.3s;
  height: 100%;
}

.bloco-card:hover {
  transform: translateY(-5px);
}
</style>
