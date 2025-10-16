<template>
  <div>
    <!-- Card de Busca -->
    <div class="card card-glass mb-4">
      <div class="card-header">
        <h4><i class="fas fa-search me-2"></i>Buscar Colaborador</h4>
      </div>
      <div class="card-body">
        <div class="input-group input-group-lg">
          <span class="input-group-text">
            <i class="fas fa-search"></i>
          </span>
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            @input="debouncedSearch"
            placeholder="Buscar por nome, matrícula ou e-mail funcional..."
          />
          <button class="btn btn-primary" @click="searchUsers" :disabled="searchLoading">
            <span v-if="searchLoading" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="fas fa-search"></i>
          </button>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="searchLoading" class="mt-4">
          <div v-for="n in 3" :key="n" class="skeleton-result mb-3">
            <div class="skeleton-content">
              <div class="skeleton skeleton-text mb-2" style="width: 200px;"></div>
              <div class="skeleton skeleton-text" style="width: 300px; height: 12px;"></div>
            </div>
          </div>
        </div>

        <!-- Resultados da busca -->
        <div v-else-if="searchResults.length > 0" class="mt-4">
          <button
            v-for="user in searchResults"
            :key="user.id"
            @click="selectUser(user)"
            class="result-item w-100 text-start mb-2 p-3"
          >
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1 text-white">
                  <i class="fas fa-user me-2"></i>{{ user.name }}
                </h6>
                <small class="text-white-50">
                  <i class="fas fa-id-card me-2"></i>{{ user.colaborador?.matricula_funcional || 'N/A' }}
                  <span class="mx-2">|</span>
                  <i class="fas fa-envelope me-2"></i>{{ user.colaborador?.email_funcional || user.email }}
                </small>
              </div>
              <i class="fas fa-chevron-right text-white-50"></i>
            </div>
          </button>
        </div>

        <div v-else-if="searchQuery && !searchLoading && searchResults.length === 0" class="alert alert-info mt-4">
          <i class="fas fa-info-circle me-2"></i>Nenhum colaborador encontrado.
        </div>
      </div>
    </div>

    <!-- Skeleton Loading para Permissões -->
    <div v-if="permissionsLoading">
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="skeleton skeleton-avatar me-3"></div>
            <div style="flex: 1;">
              <div class="skeleton skeleton-text mb-2" style="width: 200px;"></div>
              <div class="skeleton skeleton-text" style="width: 300px; height: 14px;"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="card card-glass mb-4">
        <div class="card-header">
          <div class="skeleton skeleton-text" style="width: 150px;"></div>
        </div>
        <div class="card-body">
          <div class="skeleton skeleton-text mb-2" style="width: 100%;"></div>
          <div class="skeleton skeleton-text mb-2" style="width: 80%;"></div>
          <div class="skeleton skeleton-text" style="width: 90%;"></div>
        </div>
      </div>
      <div class="card card-glass mb-4">
        <div class="card-header">
          <div class="skeleton skeleton-text" style="width: 180px;"></div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 mb-2" v-for="n in 8" :key="n">
              <div class="skeleton skeleton-badge"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Informações do Usuário -->
    <div v-else-if="selectedUser">
      <!-- Card de Avatar e Info -->
      <div class="card card-glass mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center">
              <div class="user-avatar me-3">
                {{ selectedUser.name.substring(0, 2).toUpperCase() }}
              </div>
              <div>
                <h5 class="text-white mb-1">{{ selectedUser.name }}</h5>
                <p class="text-white-50 mb-0 small">
                  {{ selectedUser.colaborador?.email_funcional || selectedUser.email }}
                </p>
              </div>
            </div>
            <button class="btn btn-sm btn-outline-light" @click="clearSelection">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Perfis Atribuídos -->
      <div class="card card-glass mb-4">
        <div class="card-header card-header-primary">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Perfis Atribuídos</h5>
            <button class="btn btn-primary" @click="openRolesModal">
              <i class="fas fa-user-tag me-2"></i>Gerenciar Perfis
            </button>
          </div>
        </div>
        <div class="card-body">
          <button
            v-for="role in userPermissions.roles"
            :key="role.id"
            class="btn btn-primary me-2 mb-2"
          >
            {{ role.name }}
          </button>
          <p v-if="!userPermissions.roles?.length" class="text-white-50 mb-0">
            <i class="fas fa-info-circle me-2"></i>Nenhum perfil atribuído
          </p>
        </div>
      </div>

      <!-- Permissões do Perfil -->
      <div class="card card-glass mb-4">
        <div class="card-header card-header-info">
          <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Permissões do Perfil</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div
              v-for="permission in userPermissions.permissions_from_roles"
              :key="permission"
              class="col-md-4 col-lg-3 mb-2"
            >
              <span class="badge badge-info w-100">{{ permission }}</span>
            </div>
          </div>
          <p v-if="!userPermissions.permissions_from_roles?.length" class="text-white-50 mb-0">
            <i class="fas fa-info-circle me-2"></i>Nenhuma permissão herdada
          </p>
        </div>
      </div>

      <!-- Permissões Avulsas -->
      <div class="card card-glass">
        <div class="card-header card-header-warning">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Permissões Avulsas</h5>
            <button class="btn btn-warning" @click="openPermissionsModal">
              <i class="fas fa-pencil-alt me-1"></i>Gerenciar
            </button>
          </div>
        </div>
        <div class="card-body">
          <span
            v-for="permission in userPermissions.direct_permissions"
            :key="permission"
            class="badge badge-warning me-2 mb-2"
          >
            {{ permission }}
          </span>
          <p v-if="!userPermissions.direct_permissions?.length" class="text-warning mb-0">
            <i class="fas fa-info-circle me-2"></i>Nenhuma permissão avulsa
          </p>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <RolesModal
      v-if="showRolesModal"
      :user="selectedUser"
      :current-roles="userPermissions.roles"
      @close="showRolesModal = false"
      @saved="refreshUserPermissions"
    />

    <PermissoesAvulsasModal
      v-if="showPermissionsModal"
      :user="selectedUser"
      :current-permissions="userPermissions.direct_permissions"
      @close="showPermissionsModal = false"
      @saved="refreshUserPermissions"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import RolesModal from '../components/RolesModal.vue';
import PermissoesAvulsasModal from '../components/PermissoesAvulsasModal.vue';

const searchQuery = ref('');
const searchResults = ref([]);
const selectedUser = ref(null);
const searchLoading = ref(false);
const permissionsLoading = ref(false);
const userPermissions = ref({
  roles: [],
  permissions_from_roles: [],
  direct_permissions: [],
  all_permissions: []
});
const showRolesModal = ref(false);
const showPermissionsModal = ref(false);

let searchTimeout = null;

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    searchUsers();
  }, 500);
};

const searchUsers = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }

  try {
    searchLoading.value = true;
    const response = await axios.get('/api/v1/users', {
      params: { search: searchQuery.value }
    });
    searchResults.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
  } finally {
    searchLoading.value = false;
  }
};

const selectUser = async (user) => {
  selectedUser.value = user;
  searchResults.value = [];
  searchQuery.value = '';
  await loadUserPermissions();
};

const loadUserPermissions = async () => {
  try {
    permissionsLoading.value = true;
    const response = await axios.get(`/api/v1/gestao-acessos/users/${selectedUser.value.id}/permissions`);
    userPermissions.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar permissões:', error);
  } finally {
    permissionsLoading.value = false;
  }
};

const clearSelection = () => {
  selectedUser.value = null;
  userPermissions.value = {
    roles: [],
    permissions_from_roles: [],
    direct_permissions: [],
    all_permissions: []
  };
};

const openRolesModal = () => {
  showRolesModal.value = true;
};

const openPermissionsModal = () => {
  showPermissionsModal.value = true;
};

const refreshUserPermissions = async () => {
  await loadUserPermissions();
  showRolesModal.value = false;
  showPermissionsModal.value = false;
};
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1) !important;
  backdrop-filter: blur(28px) !important;
  -webkit-backdrop-filter: blur(28px) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
  color: white;
}

.card-header {
  background: rgba(255, 255, 255, 0.05) !important;
  backdrop-filter: blur(15px) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.18) !important;
  padding: 1rem 1.25rem;
}

.card-header h4, .card-header h5 {
  color: white;
  margin: 0;
}

.card-header-primary {
  background: rgba(0, 123, 255, 0.25) !important;
  border-bottom: 1px solid rgba(0, 123, 255, 0.4) !important;
}

.card-header-info {
  background: rgba(13, 202, 240, 0.25) !important;
  border-bottom: 1px solid rgba(13, 202, 240, 0.4) !important;
}

.card-header-warning {
  background: rgba(255, 193, 7, 0.25) !important;
  border-bottom: 1px solid rgba(255, 193, 7, 0.4) !important;
}

.form-control, .input-group-text {
  background: rgba(255, 255, 255, 0.05) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
  color: white !important;
}

.form-control:focus {
  background: rgba(255, 255, 255, 0.1) !important;
  border-color: #0d6efd !important;
  color: white !important;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5) !important;
}

.result-item {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  transition: all 0.3s;
  cursor: pointer;
}

.result-item:hover {
  background: rgba(255, 255, 255, 0.12);
  transform: translateX(5px);
  border-color: rgba(255, 255, 255, 0.3);
}

.user-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.badge-info {
  background: rgba(13, 202, 240, 0.2) !important;
  color: #0dcaf0 !important;
  border: 1px solid rgba(13, 202, 240, 0.4) !important;
  padding: 8px 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-warning {
  background: rgba(255, 193, 7, 0.2) !important;
  color: #ffc107 !important;
  border: 1px solid rgba(255, 193, 7, 0.4) !important;
  padding: 8px 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.btn-warning {
  background: #000 !important;
  color: #ffc107 !important;
  border: 1px solid #ffc107 !important;
}

.btn-warning:hover {
  background: #ffc107 !important;
  color: #000 !important;
}

.alert-info {
  background: rgba(13, 202, 240, 0.15) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(13, 202, 240, 0.3) !important;
  color: #0dcaf0 !important;
}

/* Skeleton Loading */
.skeleton {
  position: relative;
  overflow: hidden;
  background-color: rgba(255, 255, 255, 0.08);
  border-radius: 6px;
}

.skeleton::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  transform: translateX(-100%);
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.12), transparent);
  animation: shimmer 1.8s infinite;
}

.skeleton-result {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

.skeleton-content {
  display: flex;
  flex-direction: column;
}

.skeleton-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-text {
  height: 16px;
  width: 100%;
  margin-bottom: 4px;
}

.skeleton-badge {
  height: 32px;
  width: 100%;
  border-radius: 4px;
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
</style>