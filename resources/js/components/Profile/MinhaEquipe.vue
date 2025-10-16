<template>
  <div class="minha-equipe-page">
    <!-- Header -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="text-white mb-2">
              <i class="bi bi-people-fill me-2"></i>
              Minha Equipe de Trabalho
            </h3>
            <p class="text-white-50 mb-0">
              {{ isGestor ? 'Gerencie seus subordinados e veja sua estrutura hierárquica' : 'Conheça seu gestor e colegas de equipe' }}
            </p>
          </div>
          <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
              <button @click="exportarExcel" class="btn btn-outline-success btn-sm" :disabled="exportando">
                <i class="bi bi-file-earmark-excel me-1"></i>
                {{ exportando ? 'Exportando...' : 'Excel' }}
              </button>
              <button @click="exportarPDF" class="btn btn-outline-danger btn-sm" :disabled="exportando">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                {{ exportando ? 'Exportando...' : 'PDF' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
<!-- Loading com Skeleton -->
<div v-if="loading">
  <!-- Skeleton para Stats -->
  <div class="row g-3 mb-4">
    <div v-for="n in 4" :key="`stat-${n}`" class="col-md-3">
      <div class="card card-glass">
        <div class="card-body">
          <div class="placeholder-glow">
            <span class="placeholder col-8 mb-2"></span>
            <h3 class="placeholder col-4"></h3>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Skeleton para Barra de Busca -->
  <div class="card card-glass mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="placeholder-glow">
            <span class="placeholder col-12 form-control"></span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="placeholder-glow">
            <span class="placeholder col-12 form-control"></span>
          </div>
        </div>
        <div class="col-md-2">
          <div class="placeholder-glow">
            <span class="placeholder col-12 form-control"></span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="placeholder-glow">
            <span class="placeholder col-12 form-control"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Skeleton para Card do Gestor -->
  <div class="card card-glass mb-4">
    <div class="card-header">
      <div class="placeholder-glow">
        <span class="placeholder col-4"></span>
      </div>
    </div>
    <div class="card-body">
      <div class="card card-glass">
        <div class="card-body">
          <div class="d-flex align-items-start">
            <div class="rounded-circle foto-placeholder me-3"></div>
            <div class="flex-grow-1">
              <div class="placeholder-glow">
                <h6 class="placeholder col-6 mb-2"></h6>
                <p class="placeholder col-8 mb-3"></p>
                <div class="placeholder col-12 mb-2" style="height: 60px;"></div>
                <div class="d-flex gap-2 mt-3">
                  <span class="placeholder col-3 btn"></span>
                  <span class="placeholder col-3 btn"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Skeleton para Cards de Colaboradores -->
  <div class="card card-glass">
    <div class="card-header">
      <div class="placeholder-glow">
        <span class="placeholder col-5"></span>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div v-for="n in 6" :key="`skeleton-${n}`" class="col-md-4">
          <div class="card card-glass">
            <div class="card-body">
              <div class="d-flex align-items-start mb-3">
                <div class="rounded-circle foto-placeholder-small me-3"></div>
                <div class="flex-grow-1">
                  <div class="placeholder-glow">
                    <h6 class="placeholder col-8 mb-2"></h6>
                    <p class="placeholder col-10 mb-2"></p>
                  </div>
                </div>
              </div>
              
              <div class="placeholder-glow">
                <div class="placeholder col-12 mb-2" style="height: 80px;"></div>
                <div class="d-flex gap-2 mt-3">
                  <span class="placeholder col-8 btn"></span>
                  <span class="placeholder col-3 btn"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


    <div v-else>
      <!-- ⭐ ESTATÍSTICAS (só aparece para GESTORES) -->
      <div v-if="isGestor" class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Subordinados Diretos</p>
                  <h3 class="text-white mb-0">{{ stats.total_diretos || 0 }}</h3>
                </div>
                <div class="stat-icon">👥</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Gestores na Equipe</p>
                  <h3 class="text-white mb-0">{{ stats.total_gestores || 0 }}</h3>
                </div>
                <div class="stat-icon">👔</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Subordinados Indiretos</p>
                  <h3 class="text-white mb-0">{{ stats.total_indiretos || 0 }}</h3>
                </div>
                <div class="stat-icon">🔗</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass stat-card stat-destaque">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Total Geral</p>
                  <h3 class="text-white mb-0">{{ stats.total_geral || 0 }}</h3>
                </div>
                <div class="stat-icon">🎯</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ⭐ BARRA DE BUSCA E FILTROS -->
      <div v-if="subordinados.length > 0 || colegas.length > 0" class="card card-glass mb-4">
        <div class="card-body">
          <div class="row g-3">
            <!-- Busca -->
            <div class="col-md-4">
              <div class="input-group">
                <span class="input-group-text bg-transparent text-white border-secondary">
                  <i class="bi bi-search"></i>
                </span>
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  class="form-control bg-transparent text-white border-secondary"
                  placeholder="Buscar por nome, cargo ou matrícula..."
                  @input="aplicarFiltros"
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

            <!-- Filtro por Setor -->
            <div class="col-md-3">
              <select 
                v-model="filtroSetor" 
                @change="aplicarFiltros" 
                class="form-select bg-transparent text-white border-secondary"
              >
                <option value="">Todos os Setores</option>
                <option v-for="setor in setoresUnicos" :key="setor" :value="setor">
                  {{ setor }}
                </option>
              </select>
            </div>

            <!-- Filtro por Tipo (só para gestores) -->
            <div class="col-md-2" v-if="isGestor">
              <select 
                v-model="filtroTipo" 
                @change="aplicarFiltros" 
                class="form-select bg-transparent text-white border-secondary"
              >
                <option value="">Todos os Tipos</option>
                <option value="gestor">Gestores</option>
                <option value="colaborador">Colaboradores</option>
              </select>
            </div>

            <!-- Ordenação -->
            <div class="col-md-3">
              <select 
                v-model="ordenacao" 
                @change="aplicarOrdenacao" 
                class="form-select bg-transparent text-white border-secondary"
              >
                <option value="nome">Ordenar por Nome</option>
                <option value="cargo">Ordenar por Cargo</option>
                <option value="admissao">Ordenar por Admissão</option>
                <option value="setor">Ordenar por Setor</option>
              </select>
            </div>
          </div>

          <!-- Resultado da Busca -->
          <div v-if="searchQuery || filtroSetor || filtroTipo" class="mt-3">
            <p class="text-white-50 small mb-0">
              <i class="bi bi-funnel me-1"></i>
              Mostrando {{ listaDados.length }} de {{ listaDadosOriginal.length }} pessoa(s)
              <button @click="limparFiltros" class="btn btn-link btn-sm text-warning">
                Limpar filtros
              </button>
            </p>
          </div>
        </div>
      </div>

      <!-- ⭐ SEÇÃO: MEU GESTOR (aparece para TODOS que têm gestor) -->
      <div v-if="meuGestor" class="mb-4">
        <div class="card card-glass">
          <div class="card-header">
            <h5 class="text-white mb-0">
              <i class="bi bi-person-badge me-2"></i>
              {{ isGestor ? 'Meu Gestor Direto' : 'Meu Gestor' }}
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="card card-glass colaborador-card gestor-destaque">
                  <div class="card-body">
                    <div class="d-flex align-items-start">
                      <img 
                        v-if="meuGestor.foto" 
                        :src="meuGestor.foto" 
                        alt="Foto"
                        class="rounded-circle foto-colaborador me-3"
                      >
                      <div v-else class="rounded-circle foto-placeholder me-3">
                        <i class="bi bi-person-fill"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="text-white mb-1">
                          {{ meuGestor.nome }}
                          <span class="badge bg-warning text-dark ms-2">
                            <i class="bi bi-star-fill me-1"></i>Gestor
                          </span>
                        </h6>
                        <p class="text-white-50 small mb-2">{{ meuGestor.cargo }}</p>
                        
                        <div class="colaborador-info">
                          <div class="info-row">
                            <i class="bi bi-envelope text-white-50"></i>
                            <span class="text-white small">{{ meuGestor.email }}</span>
                          </div>
                          <div class="info-row" v-if="meuGestor.telefone">
                            <i class="bi bi-telephone text-white-50"></i>
                            <span class="text-white small">{{ meuGestor.telefone }}</span>
                          </div>
                          <div class="info-row" v-if="meuGestor.setor">
                            <i class="bi bi-building text-white-50"></i>
                            <span class="text-white small">{{ meuGestor.setor }}</span>
                          </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                          <router-link 
                            :to="`/admin/pessoas-acessos/colaboradores/${meuGestor.id}`"
                            class="btn btn-sm btn-outline-light"
                          >
                            <i class="bi bi-eye me-1"></i>Ver Perfil
                          </router-link>
                          <a 
                            :href="`mailto:${meuGestor.email}`"
                            class="btn btn-sm btn-outline-light"
                          >
                            <i class="bi bi-envelope me-1"></i>Enviar Email
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ⭐ SEÇÃO: SUBORDINADOS (só aparece para GESTORES) -->
      <div v-if="isGestor" class="mb-4">
        <div class="card card-glass">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0">
              <i class="bi bi-people me-2"></i>
              Meus Subordinados Diretos
            </h5>
            <span class="badge bg-primary">{{ listaDados.length }} pessoa(s)</span>
          </div>
          <div class="card-body">
            <div v-if="listaDados.length === 0" class="text-center py-5">
              <i class="bi bi-people fa-4x text-white-50 mb-3"></i>
              <p class="text-white-50">
                {{ searchQuery || filtroSetor || filtroTipo ? 'Nenhum subordinado encontrado com os filtros aplicados' : 'Você ainda não possui subordinados diretos' }}
              </p>
            </div>

            <div v-else class="row g-3">
              <div 
                v-for="colaborador in listaDados" 
                :key="colaborador.id"
                class="col-md-4"
              >
                <div class="card card-glass colaborador-card">
                  <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                      <img 
                        v-if="colaborador.foto" 
                        :src="colaborador.foto" 
                        alt="Foto"
                        class="rounded-circle foto-colaborador-small me-3"
                      >
                      <div v-else class="rounded-circle foto-placeholder-small me-3">
                        <i class="bi bi-person-fill"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="text-white mb-1">{{ colaborador.nome }}</h6>
                        <p class="text-white-50 small mb-0">{{ colaborador.cargo }}</p>
                        <span v-if="colaborador.is_gestor" class="badge bg-primary mt-1">
                          <i class="bi bi-star-fill me-1"></i>Gestor
                        </span>
                      </div>
                    </div>

                    <div class="colaborador-info">
                      <div class="info-row">
                        <i class="bi bi-hash text-white-50"></i>
                        <span class="text-white small">{{ colaborador.matricula }}</span>
                      </div>
                      <div class="info-row">
                        <i class="bi bi-envelope text-white-50"></i>
                        <span class="text-white small">{{ colaborador.email }}</span>
                      </div>
                      <div class="info-row" v-if="colaborador.setor">
                        <i class="bi bi-building text-white-50"></i>
                        <span class="text-white small">{{ colaborador.setor }}</span>
                      </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                      <router-link 
                        :to="`/admin/pessoas-acessos/colaboradores/${colaborador.id}`"
                        class="btn btn-sm btn-outline-light flex-grow-1"
                      >
                        <i class="bi bi-eye me-1"></i>Ver Perfil
                      </router-link>
                      <a 
                        :href="`mailto:${colaborador.email}`"
                        class="btn btn-sm btn-outline-light"
                        title="Enviar Email"
                      >
                        <i class="bi bi-envelope"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ⭐ SEÇÃO: COLEGAS DE EQUIPE (aparece para COLABORADORES não-gestores) -->
      <div v-if="!isGestor" class="mb-4">
        <div class="card card-glass">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0">
              <i class="bi bi-people me-2"></i>
              Meus Colegas de Equipe
            </h5>
            <span class="badge bg-primary">{{ listaDados.length }} pessoa(s)</span>
          </div>
          <div class="card-body">
            <div v-if="listaDados.length === 0" class="text-center py-5">
              <i class="bi bi-people fa-4x text-white-50 mb-3"></i>
              <p class="text-white-50">
                {{ searchQuery || filtroSetor ? 'Nenhum colega encontrado com os filtros aplicados' : 'Você ainda não tem colegas na mesma equipe' }}
              </p>
            </div>

            <div v-else class="row g-3">
              <div 
                v-for="colega in listaDados" 
                :key="colega.id"
                class="col-md-4"
              >
                <div class="card card-glass colaborador-card">
                  <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                      <img 
                        v-if="colega.foto" 
                        :src="colega.foto" 
                        alt="Foto"
                        class="rounded-circle foto-colaborador-small me-3"
                      >
                      <div v-else class="rounded-circle foto-placeholder-small me-3">
                        <i class="bi bi-person-fill"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="text-white mb-1">{{ colega.nome }}</h6>
                        <p class="text-white-50 small mb-0">{{ colega.cargo }}</p>
                      </div>
                    </div>

                    <div class="colaborador-info">
                      <div class="info-row">
                        <i class="bi bi-hash text-white-50"></i>
                        <span class="text-white small">{{ colega.matricula }}</span>
                      </div>
                      <div class="info-row">
                        <i class="bi bi-envelope text-white-50"></i>
                        <span class="text-white small">{{ colega.email }}</span>
                      </div>
                      <div class="info-row" v-if="colega.setor">
                        <i class="bi bi-building text-white-50"></i>
                        <span class="text-white small">{{ colega.setor }}</span>
                      </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                      <router-link 
                        :to="`/admin/pessoas-acessos/colaboradores/${colega.id}`"
                        class="btn btn-sm btn-outline-light flex-grow-1"
                      >
                        <i class="bi bi-eye me-1"></i>Ver Perfil
                      </router-link>
                      <a 
                        :href="`mailto:${colega.email}`"
                        class="btn btn-sm btn-outline-light"
                        title="Enviar Email"
                      >
                        <i class="bi bi-envelope"></i>
                      </a>
                    </div>
                  </div>
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const exportando = ref(false);
const isGestor = ref(false);
const meuGestor = ref(null);
const subordinados = ref([]);
const colegas = ref([]);
const stats = ref({});

// Filtros e busca
const searchQuery = ref('');
const filtroSetor = ref('');
const filtroTipo = ref('');
const ordenacao = ref('nome');

// Lista de dados filtrada
const listaDadosOriginal = computed(() => {
  return isGestor.value ? subordinados.value : colegas.value;
});

const listaDados = ref([]);

// Setores únicos para o filtro
const setoresUnicos = computed(() => {
  const dados = isGestor.value ? subordinados.value : colegas.value;
  const setores = [...new Set(dados.map(c => c.setor).filter(Boolean))];
  return setores.sort();
});

const carregarEquipe = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/minha-equipe');
    
    isGestor.value = response.data.is_gestor;
    meuGestor.value = response.data.meu_gestor;
    subordinados.value = response.data.subordinados || [];
    colegas.value = response.data.colegas || [];
    
    // Inicializa a lista filtrada
    listaDados.value = [...listaDadosOriginal.value];
    aplicarOrdenacao();
    
    console.log('✅ Equipe carregada:', response.data);
  } catch (error) {
    console.error('❌ Erro ao carregar equipe:', error);
  } finally {
    loading.value = false;
  }
};

const carregarStats = async () => {
  try {
    const response = await axios.get('/api/v1/minha-equipe/stats');
    stats.value = response.data;
    console.log('📊 Stats carregadas:', stats.value);
  } catch (error) {
    console.error('❌ Erro ao carregar estatísticas:', error);
  }
};

const aplicarFiltros = () => {
  let resultado = [...listaDadosOriginal.value];

  // Filtro de busca
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    resultado = resultado.filter(c => 
      c.nome.toLowerCase().includes(query) ||
      c.cargo.toLowerCase().includes(query) ||
      c.matricula.toLowerCase().includes(query) ||
      c.email.toLowerCase().includes(query)
    );
  }

  // Filtro de setor
  if (filtroSetor.value) {
    resultado = resultado.filter(c => c.setor === filtroSetor.value);
  }

  // Filtro de tipo (só para gestores)
  if (filtroTipo.value && isGestor.value) {
    if (filtroTipo.value === 'gestor') {
      resultado = resultado.filter(c => c.is_gestor);
    } else if (filtroTipo.value === 'colaborador') {
      resultado = resultado.filter(c => !c.is_gestor);
    }
  }

  listaDados.value = resultado;
  aplicarOrdenacao();
};

const aplicarOrdenacao = () => {
  listaDados.value.sort((a, b) => {
    switch (ordenacao.value) {
      case 'nome':
        return a.nome.localeCompare(b.nome);
      case 'cargo':
        return a.cargo.localeCompare(b.cargo);
      case 'admissao':
        if (!a.data_admissao) return 1;
        if (!b.data_admissao) return -1;
        return new Date(b.data_admissao) - new Date(a.data_admissao);
      case 'setor':
        return (a.setor || '').localeCompare(b.setor || '');
      default:
        return 0;
    }
  });
};

const limparBusca = () => {
  searchQuery.value = '';
  aplicarFiltros();
};

const limparFiltros = () => {
  searchQuery.value = '';
  filtroSetor.value = '';
  filtroTipo.value = '';
  aplicarFiltros();
};

const exportarExcel = async () => {
  exportando.value = true;
  try {
    const response = await axios.get('/api/v1/minha-equipe/export/excel', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `minha-equipe-${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    console.log('✅ Excel exportado com sucesso');
  } catch (error) {
    console.error('❌ Erro ao exportar Excel:', error);
    alert('Erro ao exportar Excel. Tente novamente.');
  } finally {
    exportando.value = false;
  }
};

const exportarPDF = async () => {
  exportando.value = true;
  try {
    const response = await axios.get('/api/v1/minha-equipe/export/pdf', {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `minha-equipe-${new Date().toISOString().split('T')[0]}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    console.log('✅ PDF exportado com sucesso');
  } catch (error) {
    console.error('❌ Erro ao exportar PDF:', error);
    alert('Erro ao exportar PDF. Tente novamente.');
  } finally {
    exportando.value = false;
  }
};

onMounted(() => {
  carregarEquipe();
  carregarStats();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

.colaborador-card {
  transition: transform 0.3s, box-shadow 0.3s;
  height: 100%;
}

.colaborador-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.gestor-destaque {
  border: 2px solid rgba(255, 193, 7, 0.5);
  background: rgba(255, 193, 7, 0.05);
}

.foto-colaborador {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.foto-colaborador-small {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.foto-placeholder, .foto-placeholder-small {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1.5rem;
}

.foto-placeholder {
  width: 80px;
  height: 80px;
}

.foto-placeholder-small {
  width: 60px;
  height: 60px;
  font-size: 1.2rem;
}

.colaborador-info {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.info-row:last-child {
  border-bottom: none;
}

.stat-card {
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  font-size: 2rem;
}

.stat-destaque {
  border: 2px solid rgba(102, 126, 234, 0.5);
}

/* Estilos para inputs com fundo transparente */
.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-control:focus {
  background-color: rgba(255, 255, 255, 0.1);
  border-color: rgba(102, 126, 234, 0.8);
  color: white;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.form-select:focus {
  background-color: rgba(255, 255, 255, 0.1);
  border-color: rgba(102, 126, 234, 0.8);
  color: white;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.form-select option {
  background: #1a1a2e;
  color: white;
}

/* Skeleton Loading */
.placeholder {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 0.375rem;
}

.placeholder-glow .placeholder {
  animation: placeholder-glow 2s ease-in-out infinite;
}

@keyframes placeholder-glow {
  50% {
    opacity: 0.5;
  }
}

</style>
