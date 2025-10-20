<template>
  <div class="container-fluid">
    <!-- ⭐ HEADER COM BREADCRUMB -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 text-white">Grandes Áreas de Conhecimento</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <router-link to="/admin/dashboard">Dashboard</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/admin/academico">Gestão Acadêmica</router-link>
            </li>
            <li class="breadcrumb-item active">Grandes Áreas</li>
          </ol>
        </nav>
      </div>
      <button 
        v-if="!showForm" 
        @click="showCreateForm" 
        class="btn btn-primary"
      >
        <i class="bi bi-plus-circle me-2"></i>Nova Grande Área
      </button>
    </div>

    <!-- ⭐ FORMULÁRIO (SLIDE DOWN) -->
    <transition name="slide-fade">
      <div v-if="showForm" class="card card-glass mb-4">
        <div class="card-header bg-glass border-glass">
          <h5 class="mb-0 text-white">
            <i class="bi bi-bookmark-fill me-2"></i>
            {{ isEditing ? 'Editar Grande Área' : 'Nova Grande Área' }}
          </h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="isEditing ? updateGrandeArea() : createGrandeArea()">
            <div class="row">
              <div class="col-md-8">
                <label for="nome" class="form-label text-white-50">
                  Nome da Grande Área <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  id="nome" 
                  class="form-control form-control-glass" 
                  :class="{ 'is-invalid': validationErrors.nome }"
                  v-model="form.nome" 
                  placeholder="Ex: Ciências Exatas e da Terra"
                  required
                  autofocus
                >
                <div v-if="validationErrors.nome" class="invalid-feedback d-block">
                  {{ validationErrors.nome[0] }}
                </div>
              </div>

              <div class="col-md-4 d-flex align-items-end">
                <button 
                  type="submit" 
                  class="btn btn-success me-2"
                  :disabled="saving"
                >
                  <i class="bi" :class="saving ? 'bi-hourglass-split' : 'bi-check-circle'"></i>
                  {{ saving ? 'Salvando...' : 'Salvar' }}
                </button>
                <button 
                  type="button" 
                  @click="hideForm" 
                  class="btn btn-outline-light"
                  :disabled="saving"
                >
                  <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- ⭐ SKELETON LOADING -->
    <div v-if="loading" class="card card-glass">
      <div class="card-body">
        <div v-for="n in 5" :key="n" class="skeleton-item mb-3">
          <div class="skeleton-line" style="width: 15%"></div>
          <div class="skeleton-line" style="width: 60%"></div>
          <div class="skeleton-line" style="width: 20%"></div>
        </div>
      </div>
    </div>

    <!-- ⭐ LISTAGEM COM LIQUID GLASS -->
    <div v-else class="card card-glass">
      <div class="card-header bg-glass border-glass">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-white">
            <i class="bi bi-list-ul me-2"></i>
            Grandes Áreas Cadastradas
          </h5>
          <span class="badge bg-primary">{{ grandesAreas.length }} registros</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4" style="width: 80px">ID</th>
                <th>Nome da Grande Área</th>
                <th class="text-center" style="width: 150px">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="grandesAreas.length === 0">
                <td colspan="3" class="text-center text-white-50 py-5">
                  <i class="bi bi-inbox display-4 d-block mb-3"></i>
                  Nenhuma grande área cadastrada ainda.
                </td>
              </tr>
              <tr 
                v-for="grandeArea in grandesAreas" 
                :key="grandeArea.id"
                class="table-row-hover"
              >
                <td class="ps-4">
                  <span class="badge bg-info">{{ grandeArea.id }}</span>
                </td>
                <td>
                  <strong class="text-white">{{ grandeArea.nome }}</strong>
                </td>
                <td class="text-center">
                  <button 
                    @click="showEditForm(grandeArea)" 
                    class="btn btn-sm btn-outline-light me-1" 
                    title="Editar"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button 
                    @click="confirmDelete(grandeArea)" 
                    class="btn btn-sm btn-outline-danger" 
                    title="Excluir"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// ⭐ ESTADOS
const grandesAreas = ref([]);
const loading = ref(true);
const saving = ref(false);
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// ⭐ FORMULÁRIO
const form = ref({
  nome: ''
});

// ⭐ VALIDAÇÃO
const validationErrors = ref({});

// ==========================================
// FUNÇÕES AUXILIARES
// ==========================================

const resetForm = () => {
  form.value = { nome: '' };
  validationErrors.value = {};
};

const handleApiError = (error) => {
  if (error.response?.status === 422) {
    validationErrors.value = error.response.data.errors || {};
  } else if (error.response?.status === 403) {
    Swal.fire({
      icon: 'error',
      title: 'Acesso Negado',
      text: 'Você não tem permissão para realizar esta ação.',
      confirmButtonColor: '#667eea'
    });
  } else {
    Swal.fire({
      icon: 'error',
      title: 'Erro Inesperado',
      text: 'Ocorreu um erro ao processar sua solicitação.',
      confirmButtonColor: '#667eea'
    });
  }
};

// ==========================================
// CARREGAR GRANDES ÁREAS
// ==========================================

const fetchGrandesAreas = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/grandes-areas');
    grandesAreas.value = response.data.data || response.data;
  } catch (error) {
    console.error("Erro ao buscar grandes áreas:", error);
    handleApiError(error);
  } finally {
    loading.value = false;
  }
};

// ==========================================
// MOSTRAR/OCULTAR FORMULÁRIO
// ==========================================

const showCreateForm = () => {
  isEditing.value = false;
  editingId.value = null;
  resetForm();
  showForm.value = true;
};

const showEditForm = (grandeArea) => {
  isEditing.value = true;
  editingId.value = grandeArea.id;
  form.value = { ...grandeArea };
  validationErrors.value = {};
  showForm.value = true;
  
  // Scroll suave para o formulário
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const hideForm = () => {
  showForm.value = false;
  setTimeout(() => {
    resetForm();
    isEditing.value = false;
    editingId.value = null;
  }, 300);
};

// ==========================================
// CRIAR GRANDE ÁREA
// ==========================================

const createGrandeArea = async () => {
  saving.value = true;
  validationErrors.value = {};
  
  try {
    await axios.post('/api/v1/grandes-areas', form.value);
    
    Swal.fire({
      icon: 'success',
      title: 'Sucesso!',
      text: 'Grande Área criada com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchGrandesAreas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  } finally {
    saving.value = false;
  }
};

// ==========================================
// ATUALIZAR GRANDE ÁREA
// ==========================================

const updateGrandeArea = async () => {
  saving.value = true;
  validationErrors.value = {};
  
  try {
    await axios.put(`/api/v1/grandes-areas/${editingId.value}`, form.value);
    
    Swal.fire({
      icon: 'success',
      title: 'Atualizado!',
      text: 'Grande Área atualizada com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchGrandesAreas();
    hideForm();
  } catch (error) {
    handleApiError(error);
  } finally {
    saving.value = false;
  }
};

// ==========================================
// EXCLUIR GRANDE ÁREA
// ==========================================

const confirmDelete = (grandeArea) => {
  Swal.fire({
    title: 'Tem certeza?',
    text: `Deseja excluir a grande área "${grandeArea.nome}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sim, excluir!',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then(async (result) => {
    if (result.isConfirmed) {
      await deleteGrandeArea(grandeArea.id);
    }
  });
};

const deleteGrandeArea = async (id) => {
  try {
    await axios.delete(`/api/v1/grandes-areas/${id}`);
    
    Swal.fire({
      icon: 'success',
      title: 'Excluído!',
      text: 'Grande Área excluída com sucesso.',
      timer: 2000,
      showConfirmButton: false
    });
    
    await fetchGrandesAreas();
  } catch (error) {
    console.error("Erro ao excluir grande área:", error);
    
    if (error.response?.status === 409) {
      Swal.fire({
        icon: 'error',
        title: 'Não é possível excluir',
        text: 'Esta grande área está sendo utilizada em outros registros.',
        confirmButtonColor: '#667eea'
      });
    } else {
      handleApiError(error);
    }
  }
};

// ==========================================
// INICIALIZAÇÃO
// ==========================================

onMounted(() => {
  fetchGrandesAreas();
});
</script>

<style scoped>
/* ================================
   LIQUID GLASS COMPONENTS
   ================================ */

.card-glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
}

.bg-glass {
  background: rgba(255, 255, 255, 0.05) !important;
}

.border-glass {
  border-color: rgba(255, 255, 255, 0.1) !important;
}

/* ================================
   FORM CONTROLS
   ================================ */

.form-control-glass {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.form-control-glass:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(102, 126, 234, 0.5);
  color: #fff;
  box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.form-control-glass::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-control-glass.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  color: #ff6b6b;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* ================================
   SKELETON LOADING
   ================================ */

.skeleton-item {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  animation: skeleton-fade 1.5s ease-in-out infinite;
}

.skeleton-line {
  height: 12px;
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.1) 25%,
    rgba(255, 255, 255, 0.2) 50%,
    rgba(255, 255, 255, 0.1) 75%
  );
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
  margin-bottom: 8px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

@keyframes skeleton-fade {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* ================================
   TABLE STYLES
   ================================ */

.table-dark {
  --bs-table-bg: transparent;
  --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
  --bs-table-border-color: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
}

.table-dark thead th {
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  color: rgba(255, 255, 255, 0.7);
}

.table-row-hover {
  transition: all 0.2s ease;
}

.table-row-hover:hover {
  background: rgba(255, 255, 255, 0.08) !important;
  transform: scale(1.01);
}

/* ================================
   TRANSITIONS
   ================================ */

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* ================================
   BREADCRUMB
   ================================ */

.breadcrumb {
  background: transparent;
  padding: 0;
  margin: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "›";
  color: rgba(255, 255, 255, 0.5);
}

.breadcrumb-item a {
  color: rgba(102, 126, 234, 0.8);
  text-decoration: none;
  transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
  color: rgba(102, 126, 234, 1);
}

.breadcrumb-item.active {
  color: rgba(255, 255, 255, 0.7);
}

/* ================================
   UTILITIES
   ================================ */

.text-white-50 {
  color: rgba(255, 255, 255, 0.5) !important;
}

.text-muted-glass {
  color: rgba(255, 255, 255, 0.5) !important;
}
</style>
