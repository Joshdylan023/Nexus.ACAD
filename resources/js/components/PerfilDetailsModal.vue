<template>
  <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" @click.self="$emit('close')">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass">
        <div class="modal-header border-0">
          <div>
            <h5 class="modal-title text-white mb-1">
              <i class="fas fa-shield-alt me-2"></i>{{ perfilData?.name }}
            </h5>
            <p class="text-white-50 small mb-0">Detalhes do perfil de acesso</p>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <div class="modal-body">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-light" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>

          <div v-else>
            <!-- Estatísticas -->
            <div class="stats-row mb-4">
              <div class="stat-card">
                <div class="stat-icon bg-warning">
                  <i class="fas fa-key"></i>
                </div>
                <div>
                  <div class="stat-value">{{ perfilData?.permissions?.length || 0 }}</div>
                  <div class="stat-label">Permissões</div>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon bg-info">
                  <i class="fas fa-users"></i>
                </div>
                <div>
                  <div class="stat-value">{{ perfilData?.users?.length || 0 }}</div>
                  <div class="stat-label">Usuários</div>
                </div>
              </div>
              <div class="stat-card">
                <div class="stat-icon bg-secondary">
                  <i class="fas fa-calendar"></i>
                </div>
                <div>
                  <div class="stat-value">{{ formatDate(perfilData?.created_at) }}</div>
                  <div class="stat-label">Criado em</div>
                </div>
              </div>
            </div>

            <hr class="divider">

            <!-- Permissões -->
            <div class="section mb-4">
              <h6 class="text-white mb-3">
                <i class="fas fa-key me-2"></i>Permissões ({{ perfilData?.permissions?.length || 0 }})
              </h6>
              <div v-if="perfilData?.permissions?.length" class="permissions-grid">
                <div
                  v-for="permission in perfilData.permissions"
                  :key="permission"
                  class="permission-badge"
                >
                  <i class="fas fa-check-circle me-2"></i>
                  {{ formatPermissionName(permission) }}
                </div>
              </div>
              <div v-else class="empty-state">
                <i class="fas fa-info-circle me-2"></i>Nenhuma permissão atribuída
              </div>
            </div>

            <hr class="divider">

            <!-- Usuários com este perfil -->
            <div class="section">
              <h6 class="text-white mb-3">
                <i class="fas fa-users me-2"></i>Usuários com este perfil ({{ perfilData?.users?.length || 0 }})
              </h6>
              <div v-if="perfilData?.users?.length" class="users-list">
                <div
                  v-for="user in perfilData.users"
                  :key="user.id"
                  class="user-item"
                >
                  <div class="user-avatar">
                    {{ user.name.substring(0, 2).toUpperCase() }}
                  </div>
                  <div class="user-info">
                    <div class="user-name">{{ user.name }}</div>
                    <div class="user-email">{{ user.email }}</div>
                  </div>
                </div>
              </div>
              <div v-else class="empty-state">
                <i class="fas fa-info-circle me-2"></i>Nenhum usuário com este perfil
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            <i class="fas fa-times me-2"></i>Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import axios from 'axios';

const props = defineProps({
  perfil: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close']);

const loading = ref(true);
const perfilData = ref(null);

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR');
};

const formatPermissionName = (permission) => {
  return permission
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const loadPerfilDetails = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/api/v1/perfis/${props.perfil.id}`);
    perfilData.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar detalhes do perfil:', error);
    alert('Erro ao carregar detalhes do perfil');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadPerfilDetails();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.modal-body {
  max-height: 70vh;
  overflow-y: auto;
}

.divider {
  border: 0;
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 1.5rem 0;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-icon.bg-warning {
  background: rgba(255, 193, 7, 0.3);
}

.stat-icon.bg-info {
  background: rgba(13, 202, 240, 0.3);
}

.stat-icon.bg-secondary {
  background: rgba(108, 117, 125, 0.3);
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 0.75rem;
}

.permission-badge {
  padding: 0.75rem 1rem;
  background: rgba(25, 135, 84, 0.25);
  border: 1px solid rgba(25, 135, 84, 0.4);
  border-radius: 8px;
  color: white;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
}

.permission-badge i {
  color: #198754;
}

.users-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.user-item:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(4px);
}

.user-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: white;
  font-size: 1rem;
}

.user-info {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: white;
  margin-bottom: 0.25rem;
}

.user-email {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: rgba(255, 255, 255, 0.5);
  font-style: italic;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
