<template>
  <div class="organograma-page">
    <!-- Header -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="text-white mb-2">
              <i class="bi bi-diagram-3 me-2"></i>
              Organograma Hierárquico
            </h3>
            <p class="text-white-50 mb-0">
              Visualize a estrutura organizacional completa da empresa
            </p>
          </div>
          <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
              <button @click="resetZoom" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrows-angle-contract me-1"></i>
                Resetar
              </button>
              <button @click="expandirTodos" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrows-expand me-1"></i>
                {{ todosExpandidos ? 'Retrair' : 'Expandir' }}
              </button>
              <button @click="exportarImagem" class="btn btn-outline-success btn-sm" :disabled="exportando">
                <i class="bi bi-image me-1"></i>
                {{ exportando ? 'Exportando...' : 'PNG' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Busca e Filtros -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-transparent text-white border-secondary">
                <i class="bi bi-search"></i>
              </span>
              <input 
                v-model="searchQuery" 
                type="text" 
                class="form-control bg-transparent text-white border-secondary"
                placeholder="Buscar colaborador por nome, cargo ou matrícula..."
                @input="buscarColaborador"
              >
              <button 
                v-if="searchQuery" 
                @click="limparBusca" 
                class="btn btn-outline-secondary"
                type="button"
              >
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>
          <div class="col-md-3">
            <select 
              v-model="filtroNivel" 
              @change="aplicarFiltro"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todos os Níveis</option>
              <option v-for="n in maxNivel" :key="n" :value="n">
                Nível {{ n }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <select 
              v-model="filtroSetor" 
              @change="aplicarFiltro"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todos os Setores</option>
              <option v-for="setor in setoresUnicos" :key="setor" :value="setor">
                {{ setor }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div v-if="!loading && todosColaboradores.length > 0" class="card card-glass mb-3">
      <div class="card-body py-2">
        <div class="row text-center">
          <div class="col-md-3">
            <small class="text-white-50">Total de Colaboradores</small>
            <h5 class="text-white mb-0">{{ todosColaboradores.length }}</h5>
          </div>
          <div class="col-md-3">
            <small class="text-white-50">Níveis Hierárquicos</small>
            <h5 class="text-white mb-0">{{ maxNivel }}</h5>
          </div>
          <div class="col-md-3">
            <small class="text-white-50">Gestores</small>
            <h5 class="text-white mb-0">{{ totalGestores }}</h5>
          </div>
          <div class="col-md-3">
            <small class="text-white-50">Colaboradores</small>
            <h5 class="text-white mb-0">{{ todosColaboradores.length - totalGestores }}</h5>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading com Skeleton -->
    <div v-if="loading" class="card card-glass organograma-container">
      <div class="card-body p-4">
        <div class="skeleton-tree">
          <!-- Nó raiz skeleton -->
          <div class="skeleton-node">
            <div class="skeleton-foto"></div>
            <div class="skeleton-info">
              <div class="skeleton-line skeleton-line-lg"></div>
              <div class="skeleton-line skeleton-line-sm"></div>
            </div>
          </div>

          <!-- Linha conectora -->
          <div class="skeleton-connector"></div>

          <!-- Subordinados skeleton -->
          <div class="skeleton-subordinados">
            <div v-for="n in 3" :key="n" class="skeleton-node">
              <div class="skeleton-foto"></div>
              <div class="skeleton-info">
                <div class="skeleton-line skeleton-line-lg"></div>
                <div class="skeleton-line skeleton-line-sm"></div>
              </div>
            </div>
          </div>
        </div>
        <p class="text-white-50 text-center mt-4">Carregando organograma...</p>
      </div>
    </div>

    <!-- Organograma -->
    <div v-else class="card card-glass organograma-container" ref="orgContainer">
      <div class="card-body p-4">
        <div 
          class="organograma-tree" 
          :style="{ 
            transform: `scale(${zoomLevel})`,
            transformOrigin: 'top center'
          }"
          ref="orgTree"
        >
          <NoColaborador 
            v-if="orgVisivel || organogramaRaiz"
            :colaborador="orgVisivel || organogramaRaiz"
            :nivel="0"
            :expandido="expandidos[(orgVisivel || organogramaRaiz)?.id]"
            :destacado="destacados.includes((orgVisivel || organogramaRaiz)?.id)"
            @toggle="toggleExpansao"
            @selecionar="selecionarColaborador"
          />
        </div>

        <!-- Zoom Controls -->
        <div class="zoom-controls">
          <button @click="zoomIn" class="btn btn-sm btn-outline-light mb-2">
            <i class="bi bi-zoom-in"></i>
          </button>
          <button @click="zoomOut" class="btn btn-sm btn-outline-light">
            <i class="bi bi-zoom-out"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <teleport to="body">
      <div v-if="colaboradorSelecionado" class="modal-overlay" @click="fecharModal">
        <div class="modal-content card-glass" @click.stop>
          <div class="modal-header">
            <h5 class="text-white mb-0">
              <i class="bi bi-person-circle me-2"></i>
              Detalhes do Colaborador
            </h5>
            <button @click="fecharModal" class="btn-close btn-close-white"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-4">
              <img 
                v-if="colaboradorSelecionado.foto" 
                :src="colaboradorSelecionado.foto" 
                alt="Foto"
                class="rounded-circle foto-modal"
              >
              <div v-else class="rounded-circle foto-placeholder-modal mx-auto">
                <i class="bi bi-person-fill"></i>
              </div>
              <h4 class="text-white mt-3 mb-1">{{ colaboradorSelecionado.nome }}</h4>
              <p class="text-white-50">{{ colaboradorSelecionado.cargo }}</p>
              <span v-if="colaboradorSelecionado.is_gestor" class="badge bg-warning text-dark">
                <i class="bi bi-star-fill me-1"></i>Gestor
              </span>
            </div>

            <div class="info-grid">
              <div class="info-item">
                <i class="bi bi-hash text-primary"></i>
                <div>
                  <small class="text-white-50">Matrícula</small>
                  <p class="text-white mb-0">{{ colaboradorSelecionado.matricula }}</p>
                </div>
              </div>
              <div class="info-item">
                <i class="bi bi-envelope text-primary"></i>
                <div>
                  <small class="text-white-50">Email</small>
                  <p class="text-white mb-0">{{ colaboradorSelecionado.email }}</p>
                </div>
              </div>
              <div class="info-item" v-if="colaboradorSelecionado.telefone">
                <i class="bi bi-telephone text-primary"></i>
                <div>
                  <small class="text-white-50">Telefone</small>
                  <p class="text-white mb-0">{{ colaboradorSelecionado.telefone }}</p>
                </div>
              </div>
              <div class="info-item" v-if="colaboradorSelecionado.setor">
                <i class="bi bi-building text-primary"></i>
                <div>
                  <small class="text-white-50">Setor</small>
                  <p class="text-white mb-0">{{ colaboradorSelecionado.setor }}</p>
                </div>
              </div>
              <div class="info-item" v-if="colaboradorSelecionado.unidade">
                <i class="bi bi-geo-alt text-primary"></i>
                <div>
                  <small class="text-white-50">Unidade</small>
                  <p class="text-white mb-0">{{ colaboradorSelecionado.unidade }}</p>
                </div>
              </div>
              <div class="info-item" v-if="colaboradorSelecionado.data_admissao">
                <i class="bi bi-calendar text-primary"></i>
                <div>
                  <small class="text-white-50">Data de Admissão</small>
                  <p class="text-white mb-0">{{ colaboradorSelecionado.data_admissao }}</p>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4">
              <router-link 
                :to="`/admin/pessoas-acessos/colaboradores/${colaboradorSelecionado.id}`"
                class="btn btn-primary flex-grow-1"
              >
                <i class="bi bi-eye me-1"></i>Ver Perfil Completo
              </router-link>
              <a 
                :href="`mailto:${colaboradorSelecionado.email}`"
                class="btn btn-outline-light"
              >
                <i class="bi bi-envelope"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import html2canvas from 'html2canvas';
import NoColaborador from './NoColaborador.vue';

const loading = ref(true);
const exportando = ref(false);
const organogramaRaiz = ref(null);
const orgVisivel = ref(null);
const todosColaboradores = ref([]);
const expandidos = ref({});
const destacados = ref([]);
const todosExpandidos = ref(true);

// Filtros
const searchQuery = ref('');
const filtroNivel = ref('');
const filtroSetor = ref('');

// Zoom
const zoomLevel = ref(1);
const orgContainer = ref(null);
const orgTree = ref(null);

// Modal
const colaboradorSelecionado = ref(null);

// Computeds
const maxNivel = computed(() => {
  return calcularNivelMaximo(organogramaRaiz.value, 0);
});

const setoresUnicos = computed(() => {
  const setores = [...new Set(todosColaboradores.value.map(c => c.setor).filter(Boolean))];
  return setores.sort();
});

const totalGestores = computed(() => {
  return todosColaboradores.value.filter(c => c.is_gestor).length;
});

// Métodos
const carregarOrganograma = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/organograma');
    
    todosColaboradores.value = response.data.colaboradores || [];
    organogramaRaiz.value = construirArvore(response.data.raiz, response.data.colaboradores);
    orgVisivel.value = organogramaRaiz.value;
    
    // Expandir todos por padrão
    expandirTodosNos(organogramaRaiz.value);
    
    console.log('✅ Organograma carregado:', organogramaRaiz.value);
  } catch (error) {
    console.error('❌ Erro ao carregar organograma:', error);
  } finally {
    loading.value = false;
  }
};

const construirArvore = (raiz, colaboradores) => {
  if (!raiz) return null;
  
  const colaborador = { ...raiz };
  colaborador.subordinados = colaboradores
    .filter(c => c.gestor_imediato_id === colaborador.id)
    .map(c => construirArvore(c, colaboradores));
  
  return colaborador;
};

const expandirTodosNos = (no) => {
  if (!no) return;
  expandidos.value[no.id] = true;
  if (no.subordinados) {
    no.subordinados.forEach(sub => expandirTodosNos(sub));
  }
};

const toggleExpansao = (id) => {
  expandidos.value[id] = !expandidos.value[id];
};

const expandirTodos = () => {
  todosExpandidos.value = !todosExpandidos.value;
  if (todosExpandidos.value) {
    expandirTodosNos(organogramaRaiz.value);
  } else {
    expandidos.value = { [organogramaRaiz.value.id]: true };
  }
};

const buscarColaborador = () => {
  if (!searchQuery.value) {
    destacados.value = [];
    orgVisivel.value = organogramaRaiz.value;
    return;
  }
  
  const query = searchQuery.value.toLowerCase().trim();
  const encontrados = todosColaboradores.value
    .filter(c => {
      const nome = (c.nome || '').toLowerCase();
      const cargo = (c.cargo || '').toLowerCase();
      const matricula = (c.matricula || '').toLowerCase();
      const email = (c.email || '').toLowerCase();
      
      return nome.includes(query) ||
             cargo.includes(query) ||
             matricula.includes(query) ||
             email.includes(query);
    })
    .map(c => c.id);
  
  destacados.value = encontrados;

  // Expandir todos os níveis até os encontrados
  if (encontrados.length > 0) {
    expandirAteFolha(organogramaRaiz.value, encontrados);
    
    // Auto-scroll até o primeiro encontrado após um delay
    setTimeout(() => {
      const elemento = document.querySelector('.colaborador-node.destacado');
      if (elemento) {
        elemento.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center' 
        });
      }
    }, 500);
    
    console.log(`✅ Busca por "${query}" encontrou ${encontrados.length} colaborador(es)`);
  } else {
    console.log(`⚠️ Busca por "${query}" não encontrou resultados`);
  }
};


const expandirAteFolha = (no, idsDestacados) => {
  if (!no) return false;
  
  if (idsDestacados.includes(no.id)) {
    expandidos.value[no.id] = true;
    return true;
  }
  
  if (no.subordinados) {
    for (const sub of no.subordinados) {
      if (expandirAteFolha(sub, idsDestacados)) {
        expandidos.value[no.id] = true;
        return true;
      }
    }
  }
  
  return false;
};

const limparBusca = () => {
  searchQuery.value = '';
  destacados.value = [];
  orgVisivel.value = organogramaRaiz.value;
};

const aplicarFiltro = () => {
  if (!filtroNivel.value && !filtroSetor.value) {
    orgVisivel.value = organogramaRaiz.value;
    return;
  }

  // Clonar árvore original
  orgVisivel.value = JSON.parse(JSON.stringify(organogramaRaiz.value));
  
  // Aplicar filtros recursivamente
  filtrarArvore(orgVisivel.value, 0);
};

const filtrarArvore = (no, nivelAtual) => {
  if (!no) return false;
  
  let manterNo = true;
  
  // Filtro por nível
  if (filtroNivel.value && parseInt(filtroNivel.value) !== nivelAtual) {
    manterNo = false;
  }
  
  // Filtro por setor
  if (filtroSetor.value && no.setor !== filtroSetor.value) {
    manterNo = false;
  }
  
  // Filtrar subordinados
  if (no.subordinados) {
    no.subordinados = no.subordinados.filter(sub => 
      filtrarArvore(sub, nivelAtual + 1)
    );
  }
  
  return manterNo || (no.subordinados && no.subordinados.length > 0);
};

const calcularNivelMaximo = (no, nivelAtual) => {
  if (!no || !no.subordinados || no.subordinados.length === 0) {
    return nivelAtual;
  }
  
  return Math.max(
    ...no.subordinados.map(sub => calcularNivelMaximo(sub, nivelAtual + 1))
  );
};

const selecionarColaborador = (colaborador) => {
  colaboradorSelecionado.value = colaborador;
};

const fecharModal = () => {
  colaboradorSelecionado.value = null;
};

// Zoom
const zoomIn = () => {
  if (zoomLevel.value < 2) {
    zoomLevel.value += 0.1;
  }
};

const zoomOut = () => {
  if (zoomLevel.value > 0.5) {
    zoomLevel.value -= 0.1;
  }
};

const resetZoom = () => {
  zoomLevel.value = 1;
};

const exportarImagem = async () => {
  exportando.value = true;
  try {
    const orgTreeElement = orgTree.value;
    
    if (!orgTreeElement) {
      alert('Erro: elemento do organograma não encontrado');
      return;
    }

    // Resetar zoom para captura
    const zoomOriginal = zoomLevel.value;
    zoomLevel.value = 1;

    // Aguardar renderização
    await new Promise(resolve => setTimeout(resolve, 300));

    // Capturar imagem
    const canvas = await html2canvas(orgTreeElement, {
      backgroundColor: '#1a1a2e',
      scale: 2, // Maior qualidade
      logging: false,
      useCORS: true,
      allowTaint: true
    });

    // Restaurar zoom
    zoomLevel.value = zoomOriginal;

    // Converter para blob e baixar
    canvas.toBlob((blob) => {
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `organograma-${new Date().toISOString().split('T')[0]}.png`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      
      console.log('✅ Imagem exportada com sucesso');
    });
  } catch (error) {
    console.error('❌ Erro ao exportar imagem:', error);
    alert('Erro ao exportar imagem. Tente novamente.');
  } finally {
    exportando.value = false;
  }
};

onMounted(() => {
  carregarOrganograma();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

.organograma-container {
  min-height: 600px;
  max-height: 80vh;
  overflow: auto;
  position: relative;
}

.organograma-tree {
  display: flex;
  justify-content: center;
  padding: 40px 20px;
  transition: transform 0.3s ease;
  min-width: min-content;
}

.zoom-controls {
  position: absolute;
  bottom: 20px;
  right: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 10;
}

/* Skeleton Loading */
.skeleton-tree {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 20px;
}

.skeleton-node {
  background: rgba(255, 255, 255, 0.08);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 1rem;
  min-width: 200px;
  text-align: center;
  margin: 10px;
}

.skeleton-foto {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  margin: 0 auto 0.75rem;
  animation: skeleton-pulse 1.5s ease-in-out infinite;
}

.skeleton-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-line {
  height: 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  animation: skeleton-pulse 1.5s ease-in-out infinite;
}

.skeleton-line-lg {
  width: 70%;
  margin: 0 auto;
}

.skeleton-line-sm {
  width: 50%;
  margin: 0 auto;
}

.skeleton-connector {
  width: 2px;
  height: 30px;
  background: rgba(255, 255, 255, 0.2);
  margin: 0;
  animation: skeleton-pulse 1.5s ease-in-out infinite;
}

.skeleton-subordinados {
  display: flex;
  gap: 20px;
  justify-content: center;
}

@keyframes skeleton-pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(5px);
}

.modal-content {
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1.5rem;
}

.foto-modal {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.foto-placeholder-modal {
  width: 120px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 3px solid rgba(255, 255, 255, 0.3);
  font-size: 3rem;
  color: rgba(255, 255, 255, 0.5);
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.info-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.info-item i {
  font-size: 1.5rem;
  flex-shrink: 0;
}

/* Inputs transparentes */
.form-control, .form-select {
  color: white;
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-control:focus, .form-select:focus {
  background-color: rgba(255, 255, 255, 0.1);
  border-color: rgba(102, 126, 234, 0.8);
  color: white;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.form-select option {
  background: #1a1a2e;
  color: white;
}
</style>
