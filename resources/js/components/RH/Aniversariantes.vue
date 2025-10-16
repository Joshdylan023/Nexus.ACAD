<template>
  <div class="aniversariantes-page">
    <!-- Header -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="text-white mb-2">
              🎂 Aniversariantes
            </h3>
            <p class="text-white-50 mb-0">
              {{ total }} aniversariante(s) encontrado(s)
            </p>
          </div>
          <div class="col-md-4 text-end">
            <select v-model="periodoSelecionado" @change="carregarAniversariantes()" class="form-select">
              <option value="hoje">Hoje</option>
              <option value="semana">Esta Semana</option>
              <option value="mes">Este Mês</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading" class="aniversariantes-skeleton">
      <!-- Stats Skeleton -->
      <div class="row g-3 mb-4">
        <div v-for="n in 3" :key="n" class="col-md-4">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div class="placeholder-glow w-100">
                  <p class="placeholder col-6"></p>
                  <h3 class="placeholder col-4"></h3>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista Skeleton -->
      <div class="row g-3">
        <div v-for="n in 6" :key="n" class="col-md-4">
          <div class="card card-glass aniversariante-card">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3 placeholder-glow">
                <div class="rounded-circle foto-placeholder me-3 placeholder"></div>
                <div class="flex-grow-1">
                  <h6 class="placeholder col-8"></h6>
                  <p class="placeholder col-5"></p>
                </div>
              </div>
              <div class="aniversariante-info placeholder-glow">
                <div class="info-row">
                  <span class="placeholder col-4"></span>
                  <span class="placeholder col-3"></span>
                </div>
                <div class="info-row">
                  <span class="placeholder col-3"></span>
                  <span class="placeholder col-2"></span>
                </div>
                <div class="info-row">
                  <span class="placeholder col-4"></span>
                  <span class="placeholder col-4"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Conteúdo Real -->
    <div v-else>
      <!-- Estatísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card card-glass stat-card stat-hoje">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Hoje</p>
                  <h3 class="text-white mb-0">{{ stats.hoje }}</h3>
                </div>
                <div class="stat-icon">🎉</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Esta Semana</p>
                  <h3 class="text-white mb-0">{{ stats.semana }}</h3>
                </div>
                <div class="stat-icon">📅</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-glass stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-white-50 mb-1 small">Este Mês</p>
                  <h3 class="text-white mb-0">{{ stats.mes }}</h3>
                </div>
                <div class="stat-icon">🎂</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Aniversariantes -->
      <div>
        <div v-if="aniversariantes.length === 0" class="card card-glass">
          <div class="card-body text-center py-5">
            <i class="fas fa-birthday-cake fa-4x text-white-50 mb-3"></i>
            <p class="text-white-50">Nenhum aniversariante no período selecionado</p>
          </div>
        </div>

        <div v-else class="row g-3">
          <div 
            v-for="aniversariante in aniversariantes" 
            :key="aniversariante.id"
            class="col-md-4"
          >
            <div 
              class="card card-glass aniversariante-card" 
              :class="{ 'aniversariante-hoje': isAniversarioHoje(aniversariante.data_nascimento) }"
            >
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <img 
                    v-if="aniversariante.foto_perfil_url" 
                    :src="`/storage/${aniversariante.foto_perfil_url}`" 
                    alt="Foto"
                    class="rounded-circle foto-aniversariante me-3"
                  >
                  <div v-else class="rounded-circle foto-placeholder me-3">
                    <i class="fas fa-user"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="text-white mb-1">{{ aniversariante.nome_completo }}</h6>
                    <p class="text-white-50 small mb-0">Colaborador</p>
                  </div>
                  <div v-if="isAniversarioHoje(aniversariante.data_nascimento)" class="badge-hoje">
                    🎉 HOJE!
                  </div>
                </div>

                <div class="aniversariante-info">
                  <div class="info-row">
                    <span class="text-white-50 small">Data:</span>
                    <span class="text-white small">{{ formatDate(aniversariante.data_nascimento) }}</span>
                  </div>
                  <div class="info-row">
                    <span class="text-white-50 small">Idade:</span>
                    <span class="text-white small">{{ calcularIdade(aniversariante.data_nascimento) }} anos</span>
                  </div>
                  <div class="info-row">
                    <span class="text-white-50 small">Falta:</span>
                    <span class="text-white small">{{ calcularDiasFaltando(aniversariante.data_nascimento) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginação -->
        <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
          <nav>
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                <a class="page-link" @click.prevent="changePage(pagination.current_page - 1)">Anterior</a>
              </li>
              <li 
                v-for="page in pagination.last_page" 
                :key="page" 
                class="page-item" 
                :class="{ active: page === pagination.current_page }"
              >
                <a class="page-link" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                <a class="page-link" @click.prevent="changePage(pagination.current_page + 1)">Próxima</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const aniversariantes = ref([]);
const stats = ref({
  hoje: 0,
  semana: 0,
  mes: 0
});
const total = ref(0);
const periodoSelecionado = ref('mes'); // ✅ DEFAULT: Este Mês
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
});

const carregarDados = async (page = 1) => {
  loading.value = true;
  try {
    console.log('🔍 Buscando dados... Período:', periodoSelecionado.value, 'Página:', page);
    
    const [aniversariantesResponse, statsResponse] = await Promise.all([
      axios.get('/api/v1/aniversariantes', {
        params: { 
          periodo: periodoSelecionado.value,
          page: page
        }
      }),
      // Só carrega stats no primeiro load
      page === 1 ? axios.get('/api/v1/aniversariantes/stats') : Promise.resolve(null)
    ]);
    
    console.log('📦 Resposta Aniversariantes:', aniversariantesResponse.data);
    
    // Processa aniversariantes
    aniversariantes.value = aniversariantesResponse.data.data || [];
    total.value = aniversariantesResponse.data.total || 0;
    pagination.value = {
      current_page: aniversariantesResponse.data.current_page || 1,
      last_page: aniversariantesResponse.data.last_page || 1,
      per_page: aniversariantesResponse.data.per_page || 10,
      total: aniversariantesResponse.data.total || 0
    };

    // Processa stats se foram carregados
    if (statsResponse) {
      stats.value = statsResponse.data;
      console.log('📊 Stats:', stats.value);
    }
    
    console.log('✅ Total:', total.value, 'Página:', pagination.value.current_page);

  } catch (error) {
    console.error('❌ Erro ao carregar dados:', error);
    aniversariantes.value = [];
    total.value = 0;
  } finally {
    loading.value = false;
  }
};

const carregarAniversariantes = (page = 1) => {
  // Esta função agora só chama a principal
  carregarDados(page);
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    carregarAniversariantes(page);
  }
};

const formatDate = (date) => {
  if (!date) return 'Não informado';
  return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
};

const calcularIdade = (dataNascimento) => {
  if (!dataNascimento) return 0;
  const hoje = new Date();
  const nascimento = new Date(dataNascimento);
  let idade = hoje.getFullYear() - nascimento.getFullYear();
  const mesAtual = hoje.getMonth();
  const diaAtual = hoje.getDate();
  
  if (mesAtual < nascimento.getMonth() || 
      (mesAtual === nascimento.getMonth() && diaAtual < nascimento.getDate())) {
    idade--;
  }
  
  return idade;
};

const isAniversarioHoje = (dataNascimento) => {
  if (!dataNascimento) return false;
  const hoje = new Date();
  const nascimento = new Date(dataNascimento);
  return hoje.getDate() === nascimento.getDate() && 
         hoje.getMonth() === nascimento.getMonth();
};

const calcularDiasFaltando = (dataNascimento) => {
  if (!dataNascimento) return '';
  
  const hoje = new Date();
  const nascimento = new Date(dataNascimento);
  
  // Aniversário deste ano
  const aniversarioEsteAno = new Date(hoje.getFullYear(), nascimento.getMonth(), nascimento.getDate());
  
  // Se já passou, calcular para o próximo ano
  if (aniversarioEsteAno < hoje) {
    aniversarioEsteAno.setFullYear(hoje.getFullYear() + 1);
  }
  
  const diffTime = aniversarioEsteAno - hoje;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) return 'É hoje! 🎉';
  if (diffDays === 1) return 'Amanhã';
  if (diffDays <= 7) return `${diffDays} dias`;
  
  return `${diffDays} dias`;
};

onMounted(() => {
  carregarDados();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 15px;
}

/* Stats */
.stat-card {
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  font-size: 2rem;
}

.stat-hoje {
  border: 2px solid rgba(255, 215, 0, 0.5);
}

/* Aniversariante Card */
.aniversariante-card {
  transition: transform 0.3s;
  height: 100%;
}

.aniversariante-card:hover {
  transform: translateY(-5px);
}

.aniversariante-hoje {
  border: 2px solid rgba(255, 215, 0, 0.7);
  background: rgba(255, 215, 0, 0.05);
}

.foto-aniversariante {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.foto-placeholder {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  color: rgba(255, 255, 255, 0.5);
}

.badge-hoje {
  background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
  color: #000;
  padding: 0.25rem 0.5rem;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: bold;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.aniversariante-info {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.info-row:last-child {
  border-bottom: none;
}

/* Paginação */
.pagination {
  gap: 0.5rem;
}

.page-link {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #fff;
  cursor: pointer;
}

.page-link:hover {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.page-item.active .page-link {
  background: rgba(102, 126, 234, 0.8);
  border-color: rgba(102, 126, 234, 1);
}

.page-item.disabled .page-link {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>