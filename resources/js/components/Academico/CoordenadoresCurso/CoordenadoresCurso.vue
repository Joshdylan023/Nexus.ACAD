<template>
  <div class="coordenadores-page">
    <!-- Header -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h3 class="text-white mb-2">
              <i class="bi bi-person-badge me-2"></i>
              Coordenadores de Curso
            </h3>
            <p class="text-white-50 mb-0">Gerencie os coordenadores titulares e adjuntos</p>
          </div>
          <div class="col-md-6 text-end">
            <!-- ✅ BOTÃO SEMPRE VISÍVEL -->
            <button 
              @click="abrirFormulario()" 
              class="btn btn-primary btn-lg"
            >
              <i class="bi bi-plus-circle me-2"></i>
              Novo Coordenador
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros Hierárquicos -->
    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="row g-3">
          <!-- Instituição -->
          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-building me-1"></i>Instituição
            </label>
            <select 
              v-model="filtros.instituicao_id" 
              @change="onInstituicaoChange"
              class="form-select bg-transparent text-white border-secondary"
            >
              <option value="">Todas</option>
              <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                {{ inst.nome_fantasia || inst.razao_social }}
              </option>
            </select>
          </div>

          <!-- Campus -->
          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-geo-alt me-1"></i>Campus
            </label>
            <select 
              v-model="filtros.campus_id" 
              @change="onCampusChange"
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.instituicao_id"
            >
              <option value="">Todos</option>
              <option v-for="campus in campi" :key="campus.id" :value="campus.id">
                {{ campus.nome }}
              </option>
            </select>
          </div>

          <!-- Curso -->
          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-mortarboard me-1"></i>Curso
            </label>
            <select 
              v-model="filtros.curso_id" 
              @change="filtrar"
              class="form-select bg-transparent text-white border-secondary"
              :disabled="!filtros.instituicao_id"
            >
              <option value="">Todos</option>
              <option v-for="curso in cursos" :key="curso.id" :value="curso.id">
                {{ curso.nome }}
              </option>
            </select>
          </div>

          <!-- Buscar -->
          <div class="col-md-3">
            <label class="form-label text-white">
              <i class="bi bi-search me-1"></i>Buscar
            </label>
            <input 
              v-model="filtros.search" 
              @input="filtrar"
              type="text" 
              class="form-control bg-transparent text-white border-secondary"
              placeholder="Nome do coordenador..."
            />
          </div>
        </div>

        <div class="row g-3 mt-2">
          <!-- Tipo -->
          <div class="col-md-2">
            <label class="form-label text-white">Tipo</label>
            <select v-model="filtros.tipo" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
              <option value="">Todos</option>
              <option value="Titular">Titular</option>
              <option value="Adjunto">Adjunto</option>
            </select>
          </div>

          <!-- Status -->
          <div class="col-md-2">
            <label class="form-label text-white">Status</label>
            <select v-model="filtros.status" @change="filtrar" class="form-select bg-transparent text-white border-secondary">
              <option value="">Todos</option>
              <option value="Ativo">Ativo</option>
              <option value="Inativo">Inativo</option>
            </select>
          </div>

          <!-- Apenas Ativos -->
          <div class="col-md-2">
            <label class="form-label text-white">&nbsp;</label>
            <div class="form-check form-switch">
              <input 
                v-model="filtros.apenas_ativos" 
                @change="filtrar"
                class="form-check-input" 
                type="checkbox" 
                id="apenasAtivos"
              />
              <label class="form-check-label text-white" for="apenasAtivos">
                Apenas Ativos
              </label>
            </div>
          </div>

          <!-- Botão Limpar Filtros -->
          <div class="col-md-2">
            <label class="form-label text-white">&nbsp;</label>
            <button @click="limparFiltros" class="btn btn-outline-secondary w-100">
              <i class="bi bi-x-circle me-2"></i>Limpar
            </button>
          </div>

          <!-- ✅ BOTÃO SINCRONIZAR TITULARES -->
          <div class="col-md-4">
            <label class="form-label text-white">&nbsp;</label>
            <button @click="sincronizarTitulares" class="btn btn-outline-info w-100" :disabled="sincronizando">
              <span v-if="sincronizando">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Sincronizando...
              </span>
              <span v-else>
                <i class="bi bi-arrow-repeat me-2"></i>Sincronizar Titulares
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela -->
    <div class="card card-glass">
      <div class="card-body">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
          </div>
        </div>

        <!-- Tabela -->
        <div v-else class="table-responsive">
          <table class="table table-dark table-hover">
            <thead>
              <tr>
                <th>Hierarquia</th>
                <th>Coordenador</th>
                <th>Tipo</th>
                <th>Período</th>
                <th>Portaria</th>
                <th>Status</th>
                <th class="text-center">Sincronizado</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="coordenador in coordenadores" :key="coordenador.id">
                <td>
                  <div class="hierarchy-cell">
                    <div class="text-white fw-bold">{{ coordenador.curso?.nome }}</div>
                    <div class="text-white-50 small">
                      <i class="bi bi-building me-1"></i>
                      {{ coordenador.curso?.instituicao?.nome_fantasia || 'N/A' }}
                    </div>
                    <div class="text-white-50 small" v-if="coordenador.curso?.campus">
                      <i class="bi bi-geo-alt me-1"></i>
                      {{ coordenador.curso.campus.nome }}
                    </div>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                      <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                      <strong class="text-white">{{ coordenador.colaborador?.usuario?.name }}</strong><br>
                      <small class="text-white-50">{{ coordenador.colaborador?.email_funcional }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span :class="getTipoBadge(coordenador.tipo)">
                    {{ coordenador.tipo }}
                  </span>
                </td>
                <td>
                  <div class="text-white">{{ formatDate(coordenador.data_inicio) }}</div>
                  <small v-if="coordenador.data_fim" class="text-white-50">
                    até {{ formatDate(coordenador.data_fim) }}
                  </small>
                  <small v-else class="text-success">
                    <i class="bi bi-check-circle me-1"></i>Em exercício
                  </small>
                </td>
                <td class="text-white">{{ coordenador.portaria || '-' }}</td>
                <td>
                  <span :class="getStatusBadge(coordenador.status)">
                    {{ coordenador.status }}
                  </span>
                </td>
                <!-- ✅ COLUNA DE SINCRONIZAÇÃO -->
                <td class="text-center">
                  <span v-if="coordenador.tipo === 'Titular' && isSincronizado(coordenador)" class="text-success">
                    <i class="bi bi-check-circle-fill"></i>
                  </span>
                  <span v-else-if="coordenador.tipo === 'Titular'" class="text-warning" title="Não sincronizado com o curso">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                  </span>
                  <span v-else class="text-white-50">-</span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <button 
                      @click="abrirFormulario(coordenador)" 
                      class="btn btn-outline-warning"
                      title="Editar"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button 
                      @click="excluir(coordenador.id)" 
                      class="btn btn-outline-danger"
                      title="Excluir"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && coordenadores.length === 0" class="text-center py-5">
          <i class="bi bi-inbox display-1 text-white-50"></i>
          <p class="text-white-50 mt-3">Nenhum coordenador encontrado com os filtros aplicados</p>
          <button @click="limparFiltros" class="btn btn-outline-primary mt-2">
            <i class="bi bi-arrow-clockwise me-2"></i>Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <CoordenadorCursoForm 
      v-if="mostrarFormulario"
      :coordenador="coordenadorSelecionado"
      @close="fecharFormulario"
      @success="coordenadorSalvo"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import CoordenadorCursoForm from './CoordenadorCursoForm.vue';

const coordenadores = ref([]);
const instituicoes = ref([]);
const campi = ref([]);
const cursos = ref([]);
const mostrarFormulario = ref(false);
const coordenadorSelecionado = ref(null);
const loading = ref(false);
const sincronizando = ref(false);

const filtros = ref({
  instituicao_id: '',
  campus_id: '',
  curso_id: '',
  tipo: '',
  status: '',
  apenas_ativos: false,
  search: ''
});

onMounted(() => {
  carregarInstituicoes();
  carregarCoordenadores();
});

// ✅ CARREGAMENTO HIERÁRQUICO
const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
};

const onInstituicaoChange = async () => {
  filtros.value.campus_id = '';
  filtros.value.curso_id = '';
  campi.value = [];
  cursos.value = [];

  if (filtros.value.instituicao_id) {
    await carregarCampi();
    await carregarCursos();
  }
  
  filtrar();
};

const carregarCampi = async () => {
  if (!filtros.value.instituicao_id) return;
  
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${filtros.value.instituicao_id}&all=true`);
    campi.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const onCampusChange = async () => {
  filtros.value.curso_id = '';
  await carregarCursos();
  filtrar();
};

const carregarCursos = async () => {
  if (!filtros.value.instituicao_id) return;

  try {
    const params = new URLSearchParams();
    params.append('instituicao_id', filtros.value.instituicao_id);
    if (filtros.value.campus_id) {
      params.append('campus_id', filtros.value.campus_id);
    }
    params.append('all', 'true');

    const { data } = await axios.get(`/api/v1/cursos?${params.toString()}`);
    cursos.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar cursos:', error);
  }
};

// ✅ COORDENADORES
const carregarCoordenadores = async () => {
  try {
    loading.value = true;
    const params = new URLSearchParams();
    
    Object.keys(filtros.value).forEach(key => {
      if (filtros.value[key] !== '' && filtros.value[key] !== null && filtros.value[key] !== false) {
        params.append(key, filtros.value[key]);
      }
    });
    params.append('all', 'true'); // ✅ Adicionado para buscar todos e evitar problemas de paginação

    const { data } = await axios.get(`/api/v1/coordenadores-curso?${params.toString()}`);
    coordenadores.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar coordenadores:', error);
    Swal.fire('Erro!', 'Erro ao carregar coordenadores', 'error');
  } finally {
    loading.value = false;
  }
};

const filtrar = () => {
  carregarCoordenadores();
};

const limparFiltros = () => {
  filtros.value = {
    instituicao_id: '',
    campus_id: '',
    curso_id: '',
    tipo: '',
    status: '',
    apenas_ativos: false,
    search: ''
  };
  campi.value = [];
  cursos.value = [];
  filtrar();
};

// ✅ SINCRONIZAÇÃO
const isSincronizado = (coordenador) => {
  return coordenador.curso?.coordenador_id === coordenador.colaborador_id;
};

const sincronizarTitulares = async () => {
  const result = await Swal.fire({
    title: 'Sincronizar Coordenadores Titulares?',
    text: 'Isso atualizará todos os cursos com seus coordenadores titulares ativos.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, sincronizar!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      sincronizando.value = true;
      const { data } = await axios.post('/api/v1/coordenadores-curso/sincronizar-titulares');
      
      const { cursos_atualizados, cursos_sem_coordenador } = data;
      let message = 'Sincronização concluída.<br>';
      if (cursos_atualizados > 0) {
        message += `<br>${cursos_atualizados} curso(s) tiveram seu coordenador atualizado.`
      }
      if (cursos_sem_coordenador > 0) {
        message += `<br>${cursos_sem_coordenador} curso(s) agora estão sem coordenador titular.`
      }
      if (cursos_atualizados === 0 && cursos_sem_coordenador === 0) {
        message += '<br>Nenhuma alteração foi necessária.'
      }

      Swal.fire({
        icon: 'success',
        title: 'Sincronização Concluída!',
        html: message,
      });
      
      carregarCoordenadores();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao sincronizar', 'error');
    } finally {
      sincronizando.value = false;
    }
  }
};

// ✅ FORMULÁRIO
const abrirFormulario = (coordenador = null) => {
  coordenadorSelecionado.value = coordenador;
  mostrarFormulario.value = true;
};

const fecharFormulario = () => {
  mostrarFormulario.value = false;
  coordenadorSelecionado.value = null;
};

const coordenadorSalvo = () => {
  fecharFormulario();
  carregarCoordenadores();
  Swal.fire('Sucesso!', 'Coordenador salvo com sucesso!', 'success');
};

// ✅ EXCLUSÃO
const excluir = async (id) => {
  const result = await Swal.fire({
    title: 'Tem certeza?',
    text: 'Esta ação não poderá ser revertida!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/v1/coordenadores-curso/${id}`);
      Swal.fire('Excluído!', 'Coordenador excluído com sucesso', 'success');
      carregarCoordenadores();
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao excluir coordenador', 'error');
    }
  }
};

// ✅ HELPERS
const getTipoBadge = (tipo) => {
  return tipo === 'Titular' ? 'badge bg-primary' : 'badge bg-info';
};

const getStatusBadge = (status) => {
  return status === 'Ativo' ? 'badge bg-success' : 'badge bg-secondary';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('pt-BR');
};
</script>

<style scoped>
.table-dark {
  --bs-table-bg: transparent;
}

.table-dark th {
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  padding: 12px;
  font-weight: 600;
}

.table-dark td {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 12px;
  vertical-align: middle;
}

.table-dark tbody tr:hover {
  background: rgba(255, 255, 255, 0.05);
}

.hierarchy-cell {
  line-height: 1.5;
}

.avatar-sm {
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  flex-shrink: 0;
}
</style>
