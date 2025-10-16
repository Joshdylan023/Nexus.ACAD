<template>
  <div class="perfis-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="text-white mb-1">
          <i class="fas fa-user-tag me-2"></i>Gestão de Perfis
        </h2>
        <p class="text-white-50 mb-0">Gerencie perfis de acesso e suas permissões</p>
      </div>
      <button class="btn btn-success" @click="openCreateModal">
        <i class="fas fa-plus me-2"></i>Novo Perfil
      </button>
    </div>

    <!-- Filtros e Busca -->
    <div class="card card-glass mb-4">
      <div class="card-body p-3">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input
                type="text"
                class="form-control"
                v-model="searchQuery"
                placeholder="Buscar perfis..."
              >
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="sortBy">
              <option value="name">Nome</option>
              <option value="permissions_count">Permissões</option>
              <option value="users_count">Usuários</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" v-model="sortOrder">
              <option value="asc">Crescente</option>
              <option value="desc">Decrescente</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>

<!-- Grid de Perfis -->
<div v-else class="perfis-grid">
  <div
    v-for="perfil in filteredPerfis"
    :key="perfil.id"
    class="perfil-card"
  >
    <div class="perfil-header">
      <div class="perfil-icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <h5 class="perfil-name">{{ perfil.name }}</h5>
    </div>

    <div class="perfil-stats">
      <div class="stat-item">
        <i class="fas fa-key text-warning"></i>
        <div>
          <span class="stat-value">{{ perfil.permissions_count }}</span>
          <span class="stat-label">Permissões</span>
        </div>
      </div>
      <div class="stat-item">
        <i class="fas fa-users text-info"></i>
        <div>
          <span class="stat-value">{{ perfil.users_count }}</span>
          <span class="stat-label">Usuários</span>
        </div>
      </div>
    </div>

    <div class="perfil-actions">
      <button
        class="btn btn-sm btn-outline-light"
        @click="viewDetails(perfil)"
        title="Ver Detalhes"
      >
        <i class="fas fa-eye"></i>
      </button>
      <button
        class="btn btn-sm btn-outline-primary"
        @click="editPerfil(perfil)"
        title="Editar"
      >
        <i class="fas fa-edit"></i>
      </button>
      <button
        class="btn btn-sm btn-outline-info"
        @click="duplicatePerfil(perfil)"
        title="Duplicar"
      >
        <i class="fas fa-copy"></i>
      </button>
      <button
        class="btn btn-sm btn-outline-danger"
        @click="confirmDelete(perfil)"
        title="Excluir"
        :disabled="perfil.users_count > 0"
      >
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </div>
</div>


    <!-- Empty State -->
    <div v-if="!loading && filteredPerfis.length === 0" class="empty-state">
      <i class="fas fa-user-tag fa-3x text-white-50 mb-3"></i>
      <h5 class="text-white-50">Nenhum perfil encontrado</h5>
      <p class="text-white-50">Crie um novo perfil para começar</p>
    </div>

    <!-- Modals -->
    <PerfilFormModal
      v-if="showFormModal"
      :perfil="selectedPerfil"
      @close="closeFormModal"
      @saved="handleSaved"
    />

    <PerfilDetailsModal
      v-if="showDetailsModal"
      :perfil="selectedPerfil"
      @close="showDetailsModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import PerfilFormModal from '@/components/PerfilFormModal.vue';
import PerfilDetailsModal from '@/components/PerfilDetailsModal.vue';

const perfis = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const sortBy = ref('name');
const sortOrder = ref('asc');
const showFormModal = ref(false);
const showDetailsModal = ref(false);
const selectedPerfil = ref(null);

const filteredPerfis = computed(() => {
  let result = [...perfis.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(p => p.name.toLowerCase().includes(query));
  }

  result.sort((a, b) => {
    const aVal = a[sortBy.value];
    const bVal = b[sortBy.value];
    
    if (sortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1;
    } else {
      return aVal < bVal ? 1 : -1;
    }
  });

  return result;
});

const loadPerfis = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/v1/perfis');
    perfis.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar perfis:', error);
    alert('Erro ao carregar perfis');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  selectedPerfil.value = null;
  showFormModal.value = true;
};

const editPerfil = (perfil) => {
  selectedPerfil.value = perfil;
  showFormModal.value = true;
};

const viewDetails = (perfil) => {
  selectedPerfil.value = perfil;
  showDetailsModal.value = true;
};

const duplicatePerfil = async (perfil) => {
  if (!confirm(`Deseja duplicar o perfil "${perfil.name}"?`)) return;

  try {
    await axios.post(`/api/v1/perfis/${perfil.id}/duplicate`);
    alert('Perfil duplicado com sucesso!');
    await loadPerfis();
  } catch (error) {
    console.error('Erro ao duplicar perfil:', error);
    alert('Erro ao duplicar perfil');
  }
};

const confirmDelete = async (perfil) => {
  if (!confirm(`Deseja excluir o perfil "${perfil.name}"?\n\nEsta ação não pode ser desfeita.`)) return;

  try {
    await axios.delete(`/api/v1/perfis/${perfil.id}`);
    alert('Perfil excluído com sucesso!');
    await loadPerfis();
  } catch (error) {
    console.error('Erro ao excluir perfil:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    } else {
      alert('Erro ao excluir perfil');
    }
  }
};

const closeFormModal = () => {
  showFormModal.value = false;
  selectedPerfil.value = null;
};

const handleSaved = async () => {
  closeFormModal();
  await loadPerfis();
};

onMounted(() => {
  loadPerfis();
});
</script>

<style scoped>
.perfis-container {
  padding: 2rem;
  min-height: 100vh;
}

.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.search-box {
  position: relative;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.5);
}

.search-box input {
  padding-left: 2.5rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.search-box input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-box input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  color: white;
  box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
}

.form-select {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.form-select:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  color: white;
  box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
}

.form-select option {
  background: #1a1a2e;
  color: white;
}

.perfis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.perfil-card {
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.perfil-card:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.perfil-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.perfil-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.perfil-name {
  color: white;
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.perfil-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-item {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.stat-item i {
  font-size: 1.5rem;
}

.stat-value {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
}

/* ✅ BOTÕES SEMPRE VISÍVEIS */
.perfil-actions {
  display: flex;
  gap: 0.5rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  opacity: 1;
  visibility: visible;
}
.perfil-actions .btn {
  flex: 1;
  padding: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.perfil-actions .btn i {
  font-size: 0.9rem;
}

.perfil-actions .btn:hover:not(:disabled) {
  transform: translateY(-2px);
}

.perfil-actions .btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.btn {
  transition: all 0.3s ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>
