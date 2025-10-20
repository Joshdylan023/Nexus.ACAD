<template>
  <div class="container-fluid">
    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loading" class="row">
      <div class="col-12">
        <div class="card card-glass">
          <div class="card-body">
            <div class="skeleton-header mb-4"></div>
            <div class="skeleton-line mb-3" style="width: 60%"></div>
            <div class="skeleton-line mb-3" style="width: 80%"></div>
            <div class="skeleton-line mb-3" style="width: 40%"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ⭐ CONTEÚDO PRINCIPAL -->
    <div v-else>
      <!-- BREADCRUMB + AÇÕES -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="mb-1 text-white">{{ catalogo.nome }}</h2>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item">
                <router-link to="/admin/dashboard">Dashboard</router-link>
              </li>
              <li class="breadcrumb-item">
                <router-link to="/admin/academico/catalogo-cursos">Catálogo de Cursos</router-link>
              </li>
              <li class="breadcrumb-item active">{{ catalogo.codigo }}</li>
            </ol>
          </nav>
        </div>
        <div class="d-flex gap-2">
          <button 
            v-if="totalInstituicoesVinculadas > 0"
            @click="sincronizarTodos" 
            class="btn btn-outline-warning"
            :disabled="sincronizando"
          >
            <i class="bi bi-arrow-repeat me-2"></i>
            {{ sincronizando ? 'Sincronizando...' : 'Sincronizar Todos os Cursos' }}
          </button>
          <button 
            @click="duplicarCurso" 
            class="btn btn-outline-info"
          >
            <i class="bi bi-files me-2"></i>Duplicar para Outro Grupo
          </button>
          <button @click="editMode = !editMode" class="btn btn-outline-warning">
            <i class="bi" :class="editMode ? 'bi-x-circle' : 'bi-pencil'"></i>
            {{ editMode ? 'Cancelar' : 'Editar' }}
          </button>
          <router-link to="/admin/academico/catalogo-cursos" class="btn btn-outline-light">
            <i class="bi bi-arrow-left me-2"></i>Voltar
          </router-link>
        </div>
      </div>

      <!-- CARDS DE INFORMAÇÕES RÁPIDAS -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card card-glass text-center">
            <div class="card-body">
              <i class="bi bi-bookmark-star display-4 text-primary mb-2"></i>
              <h6 class="text-white-50 mb-1">Código</h6>
              <h4 class="text-white mb-0">{{ catalogo.codigo }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass text-center">
            <div class="card-body">
              <i class="bi bi-mortarboard display-4 text-success mb-2"></i>
              <h6 class="text-white-50 mb-1">Nível</h6>
              <h4 class="text-white mb-0">{{ catalogo.nivel }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass text-center">
            <div class="card-body">
              <i class="bi bi-calendar3 display-4 text-warning mb-2"></i>
              <h6 class="text-white-50 mb-1">Duração</h6>
              <h4 class="text-white mb-0">{{ catalogo.duracao_padrao_semestres }} sem.</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-glass text-center">
            <div class="card-body">
              <i class="bi bi-building display-4 text-info mb-2"></i>
              <h6 class="text-white-50 mb-1">Instituições</h6>
              <h4 class="text-white mb-0">{{ totalInstituicoesVinculadas }}</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- ABAS DE NAVEGAÇÃO -->
      <ul class="nav nav-tabs nav-tabs-glass mb-4" role="tablist">
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'geral' }" 
            @click="activeTab = 'geral'"
            role="tab"
          >
            <i class="bi bi-info-circle me-2"></i>Informações Gerais
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'instituicoes' }" 
            @click="activeTab = 'instituicoes'"
            role="tab"
          >
            <i class="bi bi-building me-2"></i>Instituições Vinculadas
            <span class="badge bg-primary ms-2">{{ totalInstituicoesVinculadas }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link" 
            :class="{ active: activeTab === 'descricoes' }" 
            @click="activeTab = 'descricoes'"
            role="tab"
          >
            <i class="bi bi-file-text me-2"></i>Descrições Detalhadas
          </a>
        </li>
      </ul>

      <!-- CONTEÚDO DAS ABAS -->
      <div class="tab-content">
        <!-- ABA: INFORMAÇÕES GERAIS -->
        <div v-show="activeTab === 'geral'" class="tab-pane active">
          <div class="row">
            <!-- Coluna Esquerda -->
            <div class="col-md-6">
              <div class="card card-glass mb-4">
                <div class="card-header bg-glass border-glass">
                  <h5 class="mb-0 text-white">
                    <i class="bi bi-info-circle me-2"></i>Dados Básicos
                  </h5>
                </div>
                <div class="card-body">
                  <!-- Código -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Código</label>
                    <input 
                      v-if="editMode" 
                      v-model="form.codigo" 
                      type="text" 
                      class="form-control form-control-glass text-uppercase"
                    >
                    <p v-else class="text-white mb-0">
                      <code class="badge bg-info">{{ catalogo.codigo }}</code>
                    </p>
                  </div>

                  <!-- Nome -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Nome do Curso</label>
                    <input 
                      v-if="editMode" 
                      v-model="form.nome" 
                      type="text" 
                      class="form-control form-control-glass"
                    >
                    <p v-else class="text-white mb-0">{{ catalogo.nome }}</p>
                  </div>

                  <!-- Sigla -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Sigla</label>
                    <input 
                      v-if="editMode" 
                      v-model="form.sigla" 
                      type="text" 
                      class="form-control form-control-glass text-uppercase"
                    >
                    <p v-else class="text-white mb-0">{{ catalogo.sigla || '—' }}</p>
                  </div>

                  <!-- Nível -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Nível de Ensino</label>
                    <select 
                      v-if="editMode" 
                      v-model="form.nivel" 
                      class="form-select form-select-glass"
                    >
                      <option value="Graduação">Graduação</option>
                      <option value="Pós-Graduação">Pós-Graduação</option>
                      <option value="Técnico">Técnico</option>
                      <option value="Mestrado">Mestrado</option>
                      <option value="Doutorado">Doutorado</option>
                    </select>
                    <p v-else class="text-white mb-0">
                      <span class="badge bg-primary">{{ catalogo.nivel }}</span>
                    </p>
                  </div>

                  <!-- Grau -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Grau Acadêmico</label>
                    <select 
                      v-if="editMode" 
                      v-model="form.grau" 
                      class="form-select form-select-glass"
                    >
                      <option value="">-- Não definido --</option>
                      <option value="Bacharelado">Bacharelado</option>
                      <option value="Licenciatura">Licenciatura</option>
                      <option value="Tecnólogo">Tecnólogo</option>
                    </select>
                    <p v-else class="text-white mb-0">{{ catalogo.grau || '—' }}</p>
                  </div>

                  <!-- Modalidade -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Modalidade</label>
                    <select 
                      v-if="editMode" 
                      v-model="form.modalidade" 
                      class="form-select form-select-glass"
                    >
                      <option value="presencial">Presencial</option>
                      <option value="ead">EAD</option>
                      <option value="semipresencial">Semipresencial</option>
                    </select>
                    <p v-else class="text-white mb-0">
                      <span 
                        class="badge" 
                        :class="{
                          'bg-primary': catalogo.modalidade === 'presencial',
                          'bg-info': catalogo.modalidade === 'ead',
                          'bg-warning': catalogo.modalidade === 'semipresencial'
                        }"
                      >
                        {{ catalogo.modalidade }}
                      </span>
                    </p>
                  </div>

                  <!-- Status -->
                  <div class="mb-3">
                    <label class="form-label text-white-50">Status</label>
                    <select 
                      v-if="editMode" 
                      v-model="form.status" 
                      class="form-select form-select-glass"
                    >
                      <option value="ativo">Ativo</option>
                      <option value="inativo">Inativo</option>
                    </select>
                    <p v-else class="text-white mb-0">
                      <span 
                        class="badge" 
                        :class="{
                          'bg-success': catalogo.status === 'ativo',
                          'bg-secondary': catalogo.status === 'inativo'
                        }"
                      >
                        {{ catalogo.status }}
                      </span>
                    </p>
                  </div>

                  <!-- Botão Salvar -->
                  <button 
                    v-if="editMode" 
                    @click="salvarAlteracoes" 
                    class="btn btn-success w-100"
                    :disabled="salvando"
                  >
                    <i class="bi" :class="salvando ? 'bi-hourglass-split' : 'bi-check-circle'"></i>
                    {{ salvando ? 'Salvando...' : 'Salvar Alterações' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Coluna Direita -->
            <div class="col-md-6">
              <!-- Duração e Carga Horária -->
              <div class="card card-glass mb-4">
                <div class="card-header bg-glass border-glass">
                  <h5 class="mb-0 text-white">
                    <i class="bi bi-clock-history me-2"></i>Duração e Carga Horária
                  </h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label text-white-50">Duração Padrão (semestres)</label>
                    <input 
                      v-if="editMode" 
                      v-model="form.duracao_padrao_semestres" 
                      type="number" 
                      class="form-control form-control-glass"
                    >
                    <p v-else class="text-white mb-0">{{ catalogo.duracao_padrao_semestres || '—' }} semestres</p>
                  </div>

                  <div class="mb-3">
                    <label class="form-label text-white-50">Prazo Máximo (semestres)</label>
                    <input 
                      v-if="editMode" 
                      v-model="form.prazo_maximo_semestres" 
                      type="number" 
                      class="form-control form-control-glass"
                    >
                    <p v-else class="text-white mb-0">{{ catalogo.prazo_maximo_semestres || '—' }} semestres</p>
                  </div>

                  <div class="mb-0">
                    <label class="form-label text-white-50">Carga Horária Total</label>
                    <input 
                      v-if="editMode" 
                      v-model="form.carga_horaria_total" 
                      type="number" 
                      class="form-control form-control-glass"
                    >
                    <p v-else class="text-white mb-0">{{ catalogo.carga_horaria_total || '—' }} horas</p>
                  </div>
                </div>
              </div>

              <!-- Vínculos -->
              <div class="card card-glass">
                <div class="card-header bg-glass border-glass">
                  <h5 class="mb-0 text-white">
                    <i class="bi bi-link-45deg me-2"></i>Vínculos
                  </h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label text-white-50">Grupo Educacional</label>
                    <p class="text-white mb-0">
                      <i class="bi bi-building-fill text-primary me-2"></i>
                      {{ catalogo.grupo_educacional?.nome || '—' }}
                    </p>
                  </div>

                  <div class="mb-0">
                    <label class="form-label text-white-50">Área de Conhecimento</label>
                    <p class="text-white mb-0">
                      <i class="bi bi-journal-bookmark-fill text-warning me-2"></i>
                      {{ catalogo.area_conhecimento?.nome || '—' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ABA: INSTITUIÇÕES VINCULADAS -->
        <div v-show="activeTab === 'instituicoes'" class="tab-pane">
          <div class="card card-glass">
            <div class="card-header bg-glass border-glass">
              <h5 class="mb-0 text-white">
                <i class="bi bi-building me-2"></i>Instituições que Utilizam Este Curso
              </h5>
            </div>
            <div class="card-body">
              <div v-if="loadingInstituicoes" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Carregando...</span>
                </div>
              </div>
              <div v-else-if="instituicoesVinculadas.length === 0" class="text-center py-5">
                <i class="bi bi-inbox display-4 text-white-50 d-block mb-3"></i>
                <p class="text-white-50">Nenhuma instituição está utilizando este curso do catálogo ainda.</p>
              </div>
              <div v-else class="row g-4">
                <div v-for="instituicao in instituicoesVinculadas" :key="instituicao.instituicao_id" class="col-md-6">
                  <div class="card card-glass">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                          <h6 class="text-white mb-1">{{ instituicao.nome_fantasia }}</h6>
                          <small class="text-white-50">{{ instituicao.sigla }}</small>
                        </div>
                        <span class="badge bg-info">{{ instituicao.total_cursos }} curso(s)</span>
                      </div>
                      <div v-for="curso in instituicao.cursos" :key="curso.id" class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                          <div>
                            <small class="text-white-50">{{ curso.codigo_ies }}</small>
                            <br>
                            <span class="text-white">{{ curso.campus || 'Todos os campi' }}</span>
                          </div>
                          <span 
                            class="badge" 
                            :class="{
                              'bg-success': curso.status === 'Ativo',
                              'bg-warning text-dark': curso.status === 'Em Extinção',
                              'bg-secondary': curso.status === 'Em Planejamento'
                            }"
                          >
                            {{ curso.status }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ABA: DESCRIÇÕES DETALHADAS -->
        <div v-show="activeTab === 'descricoes'" class="tab-pane">
          <div class="card card-glass">
            <div class="card-body">
              <!-- Descrição -->
              <div class="mb-4">
                <label class="form-label text-white-50">Descrição do Curso</label>
                <textarea 
                  v-if="editMode" 
                  v-model="form.descricao" 
                  class="form-control form-control-glass"
                  rows="5"
                ></textarea>
                <p v-else class="text-white" v-html="catalogo.descricao || '<em class=\'text-white-50\'>Não informado</em>'"></p>
              </div>

              <!-- Objetivo -->
              <div class="mb-4">
                <label class="form-label text-white-50">Objetivo</label>
                <textarea 
                  v-if="editMode" 
                  v-model="form.objetivo" 
                  class="form-control form-control-glass"
                  rows="5"
                ></textarea>
                <p v-else class="text-white" v-html="catalogo.objetivo || '<em class=\'text-white-50\'>Não informado</em>'"></p>
              </div>

              <!-- Perfil do Egresso -->
              <div class="mb-0">
                <label class="form-label text-white-50">Perfil do Egresso</label>
                <textarea 
                  v-if="editMode" 
                  v-model="form.perfil_egresso" 
                  class="form-control form-control-glass"
                  rows="5"
                ></textarea>
                <p v-else class="text-white" v-html="catalogo.perfil_egresso || '<em class=\'text-white-50\'>Não informado</em>'"></p>
              </div>

              <!-- Botão Salvar -->
              <button 
                v-if="editMode" 
                @click="salvarAlteracoes" 
                class="btn btn-success w-100 mt-4"
                :disabled="salvando"
              >
                <i class="bi" :class="salvando ? 'bi-hourglass-split' : 'bi-check-circle'"></i>
                {{ salvando ? 'Salvando...' : 'Salvar Descrições' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const route = useRoute();
const catalogoId = computed(() => route.params.id);

const loading = ref(true);
const loadingInstituicoes = ref(false);
const salvando = ref(false);
const sincronizando = ref(false);
const editMode = ref(false);
const activeTab = ref('geral');

const catalogo = ref({});
const form = ref({});
const instituicoesVinculadas = ref([]);

const totalInstituicoesVinculadas = computed(() => instituicoesVinculadas.value.length);

// Carregar dados do catálogo
const fetchCatalogo = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/v1/catalogo-cursos/${catalogoId.value}`);
    catalogo.value = response.data;
    form.value = { ...response.data };
  } catch (error) {
    console.error('Erro ao carregar catálogo:', error);
    Swal.fire('Erro!', 'Não foi possível carregar os dados do catálogo.', 'error');
  } finally {
    loading.value = false;
  }
};

// Carregar instituições vinculadas
const fetchInstituicoesVinculadas = async () => {
  loadingInstituicoes.value = true;
  try {
    const response = await axios.get(`/api/v1/catalogo-cursos/${catalogoId.value}/instituicoes-vinculadas`);
    instituicoesVinculadas.value = response.data.instituicoes || [];
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  } finally {
    loadingInstituicoes.value = false;
  }
};

// Salvar alterações
const salvarAlteracoes = async () => {
  salvando.value = true;
  try {
    const response = await axios.put(`/api/v1/catalogo-cursos/${catalogoId.value}`, form.value);
    catalogo.value = response.data.data;
    editMode.value = false;
    
    Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Curso do catálogo atualizado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Erro ao salvar:', error);
    Swal.fire('Erro!', 'Não foi possível salvar as alterações.', 'error');
  } finally {
    salvando.value = false;
  }
};

// Sincronizar todos os cursos vinculados
const sincronizarTodos = async () => {
  const result = await Swal.fire({
    title: 'Sincronizar Todos os Cursos?',
    html: `Esta ação irá atualizar <strong>${totalInstituicoesVinculadas.value} instituições</strong> com as informações mais recentes do catálogo.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#667eea',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sim, sincronizar!',
    cancelButtonText: 'Cancelar'
  });

  if (!result.isConfirmed) return;

  sincronizando.value = true;
  try {
    const response = await axios.post(`/api/v1/catalogo-cursos/${catalogoId.value}/sincronizar`);
    
    Swal.fire({
      icon: 'success',
      title: 'Sincronizado!',
      text: response.data.message,
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchInstituicoesVinculadas();
  } catch (error) {
    console.error('Erro ao sincronizar:', error);
    Swal.fire('Erro!', 'Não foi possível sincronizar os cursos.', 'error');
  } finally {
    sincronizando.value = false;
  }
};

// Duplicar curso
const duplicarCurso = async () => {
  const { value: grupoId } = await Swal.fire({
    title: 'Duplicar Curso',
    text: 'Informe o ID do grupo educacional de destino:',
    input: 'number',
    inputPlaceholder: 'ID do grupo',
    showCancelButton: true,
    confirmButtonText: 'Duplicar',
    cancelButtonText: 'Cancelar'
  });

  if (!grupoId) return;

  const { value: novoCodigo } = await Swal.fire({
    title: 'Novo Código',
    text: 'Informe o código para o novo curso:',
    input: 'text',
    inputPlaceholder: 'Ex: ENG-002',
    showCancelButton: true
  });

  if (!novoCodigo) return;

  try {
    await axios.post(`/api/v1/catalogo-cursos/${catalogoId.value}/duplicar`, {
      grupo_educacional_id: grupoId,
      novo_codigo: novoCodigo
    });

    Swal.fire({
      icon: 'success',
      title: 'Duplicado!',
      text: 'Curso duplicado com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Erro ao duplicar:', error);
    Swal.fire('Erro!', 'Não foi possível duplicar o curso.', 'error');
  }
};

onMounted(() => {
  fetchCatalogo();
  fetchInstituicoesVinculadas();
});
</script>

<style scoped>
.nav-tabs-glass .nav-link {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  margin-right: 5px;
  border-radius: 8px 8px 0 0;
  transition: all 0.3s ease;
}

.nav-tabs-glass .nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.nav-tabs-glass .nav-link.active {
  background: rgba(102, 126, 234, 0.3);
  border-color: rgba(102, 126, 234, 0.5);
  color: #fff;
}
</style>
