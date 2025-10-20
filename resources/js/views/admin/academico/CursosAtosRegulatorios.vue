<template>
  <div class="container-fluid py-4">
    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loadingCurso" class="skeleton-container">
      <div class="skeleton skeleton-title mb-3"></div>
      <div class="skeleton skeleton-breadcrumb mb-4"></div>
      <div class="skeleton skeleton-card"></div>
    </div>

    <!-- ⭐ CONTEÚDO PRINCIPAL -->
    <div v-else>
      <!-- ✅ BREADCRUMB E HIERARQUIA -->
      <div class="mb-4">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-glass border-glass p-3">
            <li class="breadcrumb-item">
              <router-link to="/admin/dashboard" class="text-white">
                <i class="bi bi-house-door me-1"></i>Dashboard
              </router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/admin/academico" class="text-white">Gestão Acadêmica</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/admin/academico/cursos" class="text-white">Cursos</router-link>
            </li>
            <li class="breadcrumb-item active text-white-50">Atos Regulatórios</li>
          </ol>
        </nav>

        <!-- ✅ INFORMAÇÕES DO CURSO -->
        <div class="card bg-glass border-glass mb-4">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="flex-grow-1">
                <h2 class="text-white mb-2">
                  <i class="bi bi-file-earmark-text me-2"></i>
                  Atos Regulatórios
                </h2>
                <h5 class="text-white-50 mb-3">{{ cursoNome }}</h5>
                
                <!-- Hierarquia do Curso -->
                <div class="d-flex flex-wrap gap-2 align-items-center text-white-50 small">
                  <span v-if="curso.instituicao" class="badge bg-primary">
                    <i class="bi bi-building me-1"></i>
                    {{ curso.instituicao.nome_fantasia }}
                  </span>
                  <span v-if="curso.campus" class="badge bg-info">
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ curso.campus.nome }}
                  </span>
                  <span v-if="curso.nivel" class="badge bg-success">
                    <i class="bi bi-mortarboard me-1"></i>
                    {{ curso.nivel }}
                  </span>
                  <span v-if="curso.modalidade" class="badge bg-warning text-dark">
                    <i class="bi bi-laptop me-1"></i>
                    {{ curso.modalidade }}
                  </span>
                  <span v-if="curso.coordenador" class="badge bg-secondary">
                    <i class="bi bi-person me-1"></i>
                    Coord.: {{ curso.coordenador.usuario?.name || curso.coordenador.name }}
                  </span>
                </div>
              </div>

              <button @click="router.go(-1)" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left me-2"></i>Voltar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ BOTÃO ADICIONAR NOVO -->
      <div class="mb-4">
        <button v-if="!showForm" @click="showCreateForm" class="btn btn-success btn-lg">
          <i class="bi bi-plus-lg me-2"></i>Novo Ato Regulatório
        </button>
      </div>

      <!-- ✅ FORMULÁRIO -->
      <transition name="slide-fade">
        <div v-if="showForm" class="card bg-glass border-glass mb-4">
          <div class="card-header bg-glass-dark border-glass">
            <h5 class="mb-0 text-white">
              <i class="bi bi-file-earmark-plus me-2"></i>
              {{ isEditing ? 'Editar Ato Regulatório' : 'Novo Ato Regulatório' }}
            </h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="isEditing ? updateAto() : createAto()">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="tipo_ato" class="form-label text-white">
                    Tipo do Ato <span class="text-danger">*</span>
                  </label>
                  <select 
                    class="form-select form-select-glass" 
                    :class="{ 'is-invalid': validationErrors.tipo_ato }" 
                    id="tipo_ato" 
                    v-model="form.tipo_ato" 
                    required
                  >
                    <option :value="null">-- Selecione --</option>
                    <option value="Autorização">Autorização</option>
                    <option value="Reconhecimento">Reconhecimento</option>
                    <option value="Renovação de Reconhecimento">Renovação de Reconhecimento</option>
                  </select>
                  <div v-if="validationErrors.tipo_ato" class="invalid-feedback">
                    {{ validationErrors.tipo_ato[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="numero_portaria" class="form-label text-white">
                    Número da Portaria <span class="text-danger">*</span>
                  </label>
                  <input 
                    type="text" 
                    class="form-control form-control-glass" 
                    :class="{ 'is-invalid': validationErrors.numero_portaria }" 
                    id="numero_portaria" 
                    v-model="form.numero_portaria" 
                    placeholder="Ex: Portaria nº 123/2024"
                    required
                  >
                  <div v-if="validationErrors.numero_portaria" class="invalid-feedback">
                    {{ validationErrors.numero_portaria[0] }}
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="codigo_mec" class="form-label text-white">
                    Código MEC <span class="text-danger">*</span>
                  </label>
                  <input 
                    type="text" 
                    class="form-control form-control-glass" 
                    :class="{ 'is-invalid': validationErrors.codigo_mec }" 
                    id="codigo_mec" 
                    v-model="form.codigo_mec" 
                    placeholder="Ex: 12345"
                    required
                  >
                  <div v-if="validationErrors.codigo_mec" class="invalid-feedback">
                    {{ validationErrors.codigo_mec[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="codigo_emec" class="form-label text-white">Código e-MEC</label>
                  <input 
                    type="text" 
                    class="form-control form-control-glass" 
                    :class="{ 'is-invalid': validationErrors.codigo_emec }" 
                    id="codigo_emec" 
                    v-model="form.codigo_emec" 
                    placeholder="Ex: 67890"
                  >
                  <div v-if="validationErrors.codigo_emec" class="invalid-feedback">
                    {{ validationErrors.codigo_emec[0] }}
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="data_publicacao_dou" class="form-label text-white">
                    Data de Publicação no D.O.U. <span class="text-danger">*</span>
                  </label>
                  <input 
                    type="date" 
                    class="form-control form-control-glass" 
                    :class="{ 'is-invalid': validationErrors.data_publicacao_dou }" 
                    id="data_publicacao_dou" 
                    v-model="form.data_publicacao_dou" 
                    required
                  >
                  <div v-if="validationErrors.data_publicacao_dou" class="invalid-feedback">
                    {{ validationErrors.data_publicacao_dou[0] }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="data_validade_ato" class="form-label text-white">Data de Validade</label>
                  <input 
                    type="date" 
                    class="form-control form-control-glass" 
                    :class="{ 'is-invalid': validationErrors.data_validade_ato }" 
                    id="data_validade_ato" 
                    v-model="form.data_validade_ato"
                  >
                  <div v-if="validationErrors.data_validade_ato" class="invalid-feedback">
                    {{ validationErrors.data_validade_ato[0] }}
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <label for="link_publicacao" class="form-label text-white">Link para Publicação</label>
                <input 
                  type="url" 
                  class="form-control form-control-glass" 
                  :class="{ 'is-invalid': validationErrors.link_publicacao }" 
                  id="link_publicacao" 
                  v-model="form.link_publicacao" 
                  placeholder="https://..."
                >
                <div v-if="validationErrors.link_publicacao" class="invalid-feedback">
                  {{ validationErrors.link_publicacao[0] }}
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-check-lg me-2"></i>Salvar
                </button>
                <button type="button" @click="hideForm" class="btn btn-secondary">
                  <i class="bi bi-x-lg me-2"></i>Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <!-- ✅ LISTA DE ATOS -->
      <div class="card bg-glass border-glass">
        <div class="card-header bg-glass-dark border-glass">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <h5 class="mb-0 text-white">
                <i class="bi bi-list-ul me-2"></i>Histórico de Atos Regulatórios
              </h5>
              <span class="badge bg-primary">{{ atosRegulatorios.length }} ato(s)</span>
            </div>
            
            <!-- ✅ BOTÕES DE EXPORTAÇÃO -->
            <div class="btn-group" role="group">
              <button 
                @click="exportarExcel" 
                class="btn btn-sm btn-success"
                :disabled="exporting || atosRegulatorios.length === 0"
                title="Exportar para Excel"
              >
                <i class="bi bi-file-earmark-excel me-1"></i>
                Excel
              </button>
              <button 
                @click="exportarPDF" 
                class="btn btn-sm btn-danger"
                :disabled="exporting || atosRegulatorios.length === 0"
                title="Exportar para PDF"
              >
                <i class="bi bi-file-earmark-pdf me-1"></i>
                PDF
              </button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <!-- SKELETON LOADING -->
          <div v-if="loading" class="p-4">
            <div v-for="n in 3" :key="n" class="skeleton skeleton-row mb-3"></div>
          </div>

          <!-- TABELA -->
          <div v-else class="table-responsive">
            <table class="table table-dark table-hover mb-0">
              <thead>
                <tr>
                  <th class="text-center" style="width: 60px;">#</th>
                  <th>Tipo do Ato</th>
                  <th>Códigos MEC</th>
                  <th>Portaria</th>
                  <th>Publicação DOU</th>
                  <th>Validade</th>
                  <th>Link</th>
                  <th class="text-center" style="width: 200px;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="atosRegulatorios.length === 0">
                  <td colspan="8" class="text-center text-white-50 py-5">
                    <i class="bi bi-inbox display-1 d-block mb-3 opacity-25"></i>
                    <p class="mb-0">Nenhum ato regulatório cadastrado para este curso.</p>
                  </td>
                </tr>

                <tr v-for="ato in atosRegulatorios" :key="ato.id">
                  <td class="text-center text-white-50 fw-bold">{{ ato.id }}</td>
                  <td>
                    <span class="badge" :class="{
                      'bg-primary': ato.tipo_ato === 'Autorização',
                      'bg-success': ato.tipo_ato === 'Reconhecimento',
                      'bg-info': ato.tipo_ato === 'Renovação de Reconhecimento'
                    }">
                      {{ ato.tipo_ato }}
                    </span>
                  </td>
                  <td>
                    <div class="small text-white">
                      <div><strong>MEC:</strong> {{ ato.codigo_mec }}</div>
                      <div v-if="ato.codigo_emec" class="text-white-50">
                        <strong>e-MEC:</strong> {{ ato.codigo_emec }}
                      </div>
                    </div>
                  </td>
                  <td class="text-white">{{ ato.numero_portaria }}</td>
                  <td>
                    <span class="badge bg-secondary">
                      <i class="bi bi-calendar-event me-1"></i>
                      {{ formatDate(ato.data_publicacao_dou) }}
                    </span>
                  </td>
                  <td>
                    <span v-if="ato.data_validade_ato" class="badge" :class="{
                      'bg-danger': isExpired(ato.data_validade_ato),
                      'bg-warning text-dark': isExpiringSoon(ato.data_validade_ato),
                      'bg-success': !isExpired(ato.data_validade_ato) && !isExpiringSoon(ato.data_validade_ato)
                    }">
                      <i class="bi bi-calendar-check me-1"></i>
                      {{ formatDate(ato.data_validade_ato) }}
                    </span>
                    <span v-else class="text-white-50 small">Sem validade</span>
                  </td>
                  <td class="text-center">
                    <a 
                      v-if="ato.link_publicacao" 
                      :href="ato.link_publicacao" 
                      target="_blank" 
                      class="btn btn-sm btn-outline-primary"
                    >
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <span v-else class="text-white-50 small">-</span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <button 
                        @click="showEditForm(ato)" 
                        class="btn btn-sm btn-warning"
                        title="Editar"
                      >
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button 
                        @click="deleteAto(ato.id)" 
                        class="btn btn-sm btn-danger"
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const cursoId = ref(route.params.id);
const curso = ref({});
const cursoNome = ref('');
const atosRegulatorios = ref([]);
const loading = ref(true);
const loadingCurso = ref(true);
const exporting = ref(false);

const form = ref({
  curso_id: cursoId.value,
  tipo_ato: null,
  codigo_mec: '',
  codigo_emec: '',
  numero_portaria: '',
  data_publicacao_dou: '',
  link_publicacao: '',
  data_validade_ato: '',
});

const isEditing = ref(false);
const editingId = ref(null);
const validationErrors = ref({});
const showForm = ref(false);

// ✅ EXPORTAR PARA EXCEL
const exportarExcel = async () => {
  try {
    exporting.value = true;
    
    const response = await axios.get(`/api/v1/cursos/${cursoId.value}/atos-regulatorios/export/excel`, {
      responseType: 'blob'
    });
    
    // Criar link de download
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `atos_regulatorios_${cursoNome.value}_${new Date().getTime()}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    alert('Arquivo Excel exportado com sucesso!');
    
  } catch (error) {
    console.error('Erro ao exportar Excel:', error);
    alert('Erro ao exportar para Excel.');
  } finally {
    exporting.value = false;
  }
};

// ✅ EXPORTAR PARA PDF
const exportarPDF = async () => {
  try {
    exporting.value = true;
    
    const response = await axios.get(`/api/v1/cursos/${cursoId.value}/atos-regulatorios/export/pdf`, {
      responseType: 'blob'
    });
    
    // Criar link de download
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `atos_regulatorios_${cursoNome.value}_${new Date().getTime()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    alert('Arquivo PDF exportado com sucesso!');
    
  } catch (error) {
    console.error('Erro ao exportar PDF:', error);
    alert('Erro ao exportar para PDF.');
  } finally {
    exporting.value = false;
  }
};

// ✅ BUSCAR DADOS DO CURSO
const fetchCurso = async () => {
  try {
    loadingCurso.value = true;
    const response = await axios.get(`/api/v1/cursos/${cursoId.value}`);
    curso.value = response.data;
    cursoNome.value = response.data.nome;
  } catch (error) {
    console.error("Erro ao buscar curso:", error);
    alert('Erro ao carregar os dados do curso.');
  } finally {
    loadingCurso.value = false;
  }
};

// ✅ BUSCAR ATOS REGULATÓRIOS
const fetchAtos = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/cursos-atos-regulatorios?curso_id=${cursoId.value}`);
    atosRegulatorios.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar atos regulatórios:", error);
  } finally {
    loading.value = false;
  }
};

// ✅ FORMATAR DATA
const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('pt-BR');
};

// ✅ VERIFICAR SE ESTÁ EXPIRADO
const isExpired = (date) => {
  return new Date(date) < new Date();
};

// ✅ VERIFICAR SE ESTÁ EXPIRANDO EM BREVE (90 DIAS)
const isExpiringSoon = (date) => {
  const diffTime = new Date(date) - new Date();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays > 0 && diffDays <= 90;
};

// ✅ TRATAMENTO DE ERROS
const handleApiError = (error) => {
  validationErrors.value = {};
  if (error.response && error.response.status === 422) {
    validationErrors.value = error.response.data.errors;
  } else {
    console.error("Erro na API:", error);
    alert('Ocorreu um erro inesperado.');
  }
};

// ✅ MOSTRAR FORMULÁRIO DE CRIAÇÃO
const showCreateForm = () => {
  isEditing.value = false;
  form.value = {
    curso_id: cursoId.value,
    tipo_ato: null,
    codigo_mec: '',
    codigo_emec: '',
    numero_portaria: '',
    data_publicacao_dou: '',
    link_publicacao: '',
    data_validade_ato: '',
  };
  validationErrors.value = {};
  showForm.value = true;
};

// ✅ MOSTRAR FORMULÁRIO DE EDIÇÃO
const showEditForm = (ato) => {
  isEditing.value = true;
  editingId.value = ato.id;
  form.value = { ...ato };
  validationErrors.value = {};
  showForm.value = true;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// ✅ OCULTAR FORMULÁRIO
const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

// ✅ CRIAR ATO
const createAto = async () => {
  try {
    await axios.post('/api/v1/cursos-atos-regulatorios', form.value);
    await fetchAtos();
    hideForm();
    alert('Ato regulatório criado com sucesso!');
  } catch (error) {
    handleApiError(error);
  }
};

// ✅ ATUALIZAR ATO
const updateAto = async () => {
  try {
    await axios.put(`/api/v1/cursos-atos-regulatorios/${editingId.value}`, form.value);
    await fetchAtos();
    hideForm();
    alert('Ato regulatório atualizado com sucesso!');
  } catch (error) {
    handleApiError(error);
  }
};

// ✅ DELETAR ATO
const deleteAto = async (id) => {
  if (confirm("Tem certeza que deseja excluir este ato regulatório?")) {
    try {
      await axios.delete(`/api/v1/cursos-atos-regulatorios/${id}`);
      await fetchAtos();
      alert('Ato regulatório excluído com sucesso.');
    } catch (error) {
      handleApiError(error);
    }
  }
};

onMounted(() => {
  fetchCurso();
  fetchAtos();
});
</script>

<style scoped>
/* ✅ GLASS MORPHISM EFFECTS */
.bg-glass {
  background: rgba(255, 255, 255, 0.05) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.bg-glass-dark {
  background: rgba(0, 0, 0, 0.3) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.border-glass {
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: 12px;
}

/* ✅ FORM CONTROLS GLASS */
.form-control-glass,
.form-select-glass {
  background: rgba(255, 255, 255, 0.05) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #fff !important;
  border-radius: 8px;
}

.form-control-glass:focus,
.form-select-glass:focus {
  background: rgba(255, 255, 255, 0.08) !important;
  border-color: rgba(102, 126, 234, 0.5) !important;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
  color: #fff !important;
}

.form-control-glass::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-select-glass option {
  background: #1a1a2e;
  color: #fff;
}

/* ✅ BREADCRUMB */
.breadcrumb {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: 12px;
}

.breadcrumb-item a {
  color: #fff;
  text-decoration: none;
  transition: color 0.3s;
}

.breadcrumb-item a:hover {
  color: #667eea;
}

.breadcrumb-item + .breadcrumb-item::before {
  color: rgba(255, 255, 255, 0.5);
}

/* ✅ CARD */
.card {
  border-radius: 12px;
  overflow: hidden;
}

.card-header {
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* ✅ TABLE */
.table-dark {
  color: #fff;
}

.table-dark thead th {
  background: rgba(0, 0, 0, 0.3);
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.table-dark tbody tr {
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: background-color 0.3s;
}

.table-dark tbody tr:hover {
  background: rgba(255, 255, 255, 0.05);
}

.table-dark tbody td {
  vertical-align: middle;
  padding: 1rem;
}

/* ✅ SKELETON LOADING */
.skeleton-container {
  padding: 2rem;
}

.skeleton {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05) 25%,
    rgba(255, 255, 255, 0.1) 50%,
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: 8px;
}

.skeleton-title {
  height: 40px;
  width: 50%;
}

.skeleton-breadcrumb {
  height: 50px;
  width: 100%;
}

.skeleton-card {
  height: 300px;
  width: 100%;
}

.skeleton-row {
  height: 60px;
  width: 100%;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* ✅ ANIMAÇÃO DO FORMULÁRIO */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

/* ✅ BADGES */
.badge {
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: 6px;
}

/* ✅ BUTTONS */
.btn {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* ✅ RESPONSIVE */
@media (max-width: 768px) {
  .d-flex.justify-content-between {
    flex-direction: column;
    gap: 1rem;
  }
  
  .table-responsive {
    font-size: 0.85rem;
  }
}
</style>
