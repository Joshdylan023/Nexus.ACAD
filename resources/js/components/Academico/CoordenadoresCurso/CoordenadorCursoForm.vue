<template>
  <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7);">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content bg-dark border-secondary">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-white">
            <i class="bi bi-person-badge me-2"></i>
            {{ coordenador ? 'Editar' : 'Novo' }} Coordenador de Curso
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <form @submit.prevent="salvar">
          <div class="modal-body">
            <div class="row g-3">
              <!-- ✅ HIERARQUIA: Instituição -->
              <div class="col-md-4">
                <label class="form-label text-white">
                  <i class="bi bi-building me-1"></i>
                  Instituição <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="hierarquia.instituicao_id" 
                  @change="onInstituicaoChange"
                  class="form-select bg-dark text-white border-secondary"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="inst in instituicoes" :key="inst.id" :value="inst.id">
                    {{ inst.nome_fantasia || inst.razao_social }}
                  </option>
                </select>
              </div>

              <!-- ✅ HIERARQUIA: Campus -->
              <div class="col-md-4">
                <label class="form-label text-white">
                  <i class="bi bi-geo-alt me-1"></i>
                  Campus
                </label>
                <select 
                  v-model="hierarquia.campus_id" 
                  @change="onCampusChange"
                  class="form-select bg-dark text-white border-secondary"
                  :disabled="!hierarquia.instituicao_id"
                >
                  <option value="">Todos os campi</option>
                  <option v-for="campus in campi" :key="campus.id" :value="campus.id">
                    {{ campus.nome }}
                  </option>
                </select>
              </div>

              <!-- ✅ HIERARQUIA: Curso -->
              <div class="col-md-4">
                <label class="form-label text-white">
                  <i class="bi bi-mortarboard me-1"></i>
                  Curso <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.curso_id" 
                  class="form-select bg-dark text-white border-secondary"
                  :disabled="!hierarquia.instituicao_id || cursos.length === 0"
                  required
                >
                  <option value="">Selecione...</option>
                  <option v-for="curso in cursos" :key="curso.id" :value="curso.id">
                    {{ curso.nome }}
                  </option>
                </select>
              </div>

              <!-- Colaborador -->
              <div class="col-md-12">
                <label class="form-label text-white">
                  Colaborador <span class="text-danger">*</span>
                </label>
                <v-select
                    v-model="form.colaborador_id"
                    :options="colaboradores"
                    :filterable="false"
                    @search="onColaboradorSearch"
                    :loading="buscandoColaboradores"
                    :reduce="colaborador => colaborador.id"
                    placeholder="Digite para buscar por matrícula ou nome"
                    required
                    class="v-select-dark"
                >
                    <template #no-options>
                        <div class="text-center p-2">Nenhum colaborador encontrado.</div>
                    </template>
                    <template #option="option">
                        <div class="d-flex flex-column">
                            <strong>{{ option.usuario.name }}</strong>
                            <small class="text-muted">Matrícula: {{ option.matricula_funcional }}</small>
                        </div>
                    </template>
                    <template #selected-option="option">
                        <div v-if="option.usuario" class="text-white">
                            {{ option.usuario.name }}
                            <small class="text-white-50 ms-2">(Matrícula: {{ option.matricula_funcional }})</small>
                        </div>
                    </template>
                </v-select>
              </div>

              <!-- Tipo -->
              <div class="col-md-4">
                <label class="form-label text-white">
                  Tipo <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.tipo" 
                  class="form-select bg-dark text-white border-secondary"
                  required
                >
                  <option value="">Selecione...</option>
                  <option value="Titular">Titular</option>
                  <option value="Adjunto">Adjunto</option>
                </select>
              </div>

              <!-- Status -->
              <div class="col-md-4">
                <label class="form-label text-white">
                  Status <span class="text-danger">*</span>
                </label>
                <select 
                  v-model="form.status" 
                  class="form-select bg-dark text-white border-secondary"
                  required
                >
                  <option value="Ativo">Ativo</option>
                  <option value="Inativo">Inativo</option>
                </select>
              </div>

              <!-- Portaria -->
              <div class="col-md-4">
                <label class="form-label text-white">Portaria</label>
                <input 
                  v-model="form.portaria" 
                  type="text" 
                  class="form-control bg-dark text-white border-secondary"
                  placeholder="Nº da Portaria"
                />
              </div>

              <!-- Data Início -->
              <div class="col-md-6">
                <label class="form-label text-white">
                  Data Início <span class="text-danger">*</span>
                </label>
                <input 
                  v-model="form.data_inicio" 
                  type="date" 
                  class="form-control bg-dark text-white border-secondary"
                  required
                />
              </div>

              <!-- Data Fim -->
              <div class="col-md-6">
                <label class="form-label text-white">Data Fim</label>
                <input 
                  v-model="form.data_fim" 
                  type="date" 
                  class="form-control bg-dark text-white border-secondary"
                />
                <small class="text-white-50">Deixe vazio se ainda estiver em exercício</small>
              </div>

              <!-- Observações -->
              <div class="col-12">
                <label class="form-label text-white">Observações</label>
                <textarea 
                  v-model="form.observacoes" 
                  class="form-control bg-dark text-white border-secondary"
                  rows="3"
                  placeholder="Observações sobre a coordenação..."
                ></textarea>
              </div>
            </div>

            <!-- Alerta de validação -->
            <div v-if="erro" class="alert alert-danger mt-3">
              <i class="bi bi-exclamation-triangle me-2"></i>
              {{ erro }}
            </div>
          </div>

          <div class="modal-footer border-secondary">
            <button 
              type="button" 
              class="btn btn-secondary" 
              @click="$emit('close')"
              :disabled="salvando"
            >
              <i class="bi bi-x-circle me-2"></i>
              Cancelar
            </button>
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="salvando"
            >
              <span v-if="salvando">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Salvando...
              </span>
              <span v-else>
                <i class="bi bi-check-circle me-2"></i>
                Salvar
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const props = defineProps({
  coordenador: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const form = ref({
  curso_id: '',
  colaborador_id: '',
  tipo: 'Titular',
  status: 'Ativo',
  data_inicio: '',
  data_fim: '',
  portaria: '',
  observacoes: ''
});

const hierarquia = ref({
  instituicao_id: '',
  campus_id: ''
});

const instituicoes = ref([]);
const campi = ref([]);
const cursos = ref([]);
const colaboradores = ref([]);
const salvando = ref(false);
const erro = ref('');
const buscandoColaboradores = ref(false);
let debounceTimer = null;

onMounted(async () => {
  await carregarInstituicoes();
  if (props.coordenador) {
    await preencherFormulario();
  } else {
    // Para novos, buscar colaboradores iniciais se necessário
    await carregarColaboradores();
  }
});

// HIERARQUIA E DADOS DEPENDENTES
const carregarInstituicoes = async () => {
  try {
    const { data } = await axios.get('/api/v1/instituicoes?all=true');
    instituicoes.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
};

const onInstituicaoChange = async () => {
  hierarquia.value.campus_id = '';
  form.value.curso_id = '';
  campi.value = [];
  cursos.value = [];

  if (hierarquia.value.instituicao_id) {
    await carregarCampi();
    await carregarCursos();
  }
};

const carregarCampi = async () => {
  if (!hierarquia.value.instituicao_id) return;
  try {
    const { data } = await axios.get(`/api/v1/campi?instituicao_id=${hierarquia.value.instituicao_id}&all=true`);
    campi.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar campi:', error);
  }
};

const onCampusChange = async () => {
  form.value.curso_id = '';
  await carregarCursos();
};

const carregarCursos = async () => {
  if (!hierarquia.value.instituicao_id) return;
  try {
    const params = new URLSearchParams();
    params.append('instituicao_id', hierarquia.value.instituicao_id);
    if (hierarquia.value.campus_id) {
      params.append('campus_id', hierarquia.value.campus_id);
    }
    params.append('all', 'true');

    const { data } = await axios.get(`/api/v1/cursos?${params.toString()}`);
    cursos.value = data;
  } catch (error) {
    console.error('Erro ao carregar cursos:', error);
  }
};

// COLABORADORES (com busca)
const carregarColaboradores = async (search = '', initialId = null) => {
  try {
    buscandoColaboradores.value = true;
    const params = new URLSearchParams();
    params.append('status', 'Ativo');
    if (search) {
      params.append('search', search);
    }
    if (initialId) {
      params.append('id', initialId);
    }
    params.append('limit', 50);

    const { data } = await axios.get(`/api/v1/colaboradores?${params.toString()}`);
    colaboradores.value = data.data || data;
  } catch (error) {
    console.error('Erro ao carregar colaboradores:', error);
  } finally {
    buscandoColaboradores.value = false;
  }
};

const onColaboradorSearch = (search, loading) => {
  if (search.length > 2) {
    loading(true);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      carregarColaboradores(search).finally(() => loading(false));
    }, 350);
  }
};

// FORMULÁRIO
const preencherFormulario = async () => {
  form.value = {
    curso_id: props.coordenador.curso_id,
    colaborador_id: props.coordenador.colaborador_id,
    tipo: props.coordenador.tipo,
    status: props.coordenador.status,
    data_inicio: props.coordenador.data_inicio,
    data_fim: props.coordenador.data_fim || '',
    portaria: props.coordenador.portaria || '',
    observacoes: props.coordenador.observacoes || ''
  };

  if (props.coordenador.curso) {
    hierarquia.value.instituicao_id = props.coordenador.curso.instituicao_id;
    hierarquia.value.campus_id = props.coordenador.curso.campus_id || '';
    await carregarCampi();
    await carregarCursos();
  }

  if (props.coordenador.colaborador_id) {
    await carregarColaboradores('', props.coordenador.colaborador_id);
  }
};

const salvar = async () => {
  erro.value = '';
  salvando.value = true;

  try {
    if (props.coordenador) {
      await axios.put(`/api/v1/coordenadores-curso/${props.coordenador.id}`, form.value);
    } else {
      await axios.post('/api/v1/coordenadores-curso', form.value);
    }
    emit('success');
  } catch (error) {
    console.error('Erro ao salvar coordenador:', error);
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      erro.value = Object.values(errors).flat().join(', ');
    } else {
      erro.value = error.response?.data?.message || 'Erro ao salvar coordenador';
    }
  } finally {
    salvando.value = false;
  }
};
</script>

<style>
.modal.show {
  display: block;
}

.form-control:focus,
.form-select:focus {
  background-color: #212529;
  border-color: #60a5fa;
  color: white;
  box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.25);
}

.form-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.alert {
  border-radius: 8px;
}

/* Estilos para o v-select */
.v-select-dark .vs__dropdown-toggle,
.v-select-dark .vs__dropdown-menu {
  background: #2c3034; /* Um pouco mais claro que o modal */
  border: 1px solid #495057;
  color: white;
  border-radius: 0.375rem;
}

.v-select-dark .vs__search,
.v-select-dark .vs__search:focus,
.v-select-dark .vs__selected {
  color: white;
  font-size: 1rem;
  padding: 0.375rem 0.75rem;
}

.v-select-dark .vs__selected-options {
    padding: 0 5px;
}

.v-select-dark .vs__dropdown-option {
  color: #e9ecef;
  padding: 0.75rem 1rem;
}

.v-select-dark .vs__dropdown-option--highlight {
  background: #0d6efd;
  color: white;
}

.v-select-dark .vs__clear,
.v-select-dark .vs__open-indicator {
  fill: #adb5bd;
}

.v-select-dark .vs--loading .vs__open-indicator {
    opacity: 0;
}

.v-select-dark.vs--open .vs__dropdown-toggle {
    border-color: #60a5fa;
    box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.25);
}
</style>