<template>
  <div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="text-white mb-1">
          <i class="fas fa-user-shield me-2"></i>Gerir Acessos
        </h2>
        <p class="text-white-50 mb-0">{{ colaborador?.usuario?.name }}</p>
      </div>
      <router-link :to="`/admin/pessoas-acessos/colaboradores/${id}`" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Voltar aos Detalhes
      </router-link>
    </div>

    <!-- Card Principal -->
    <div class="card card-glass">
      <div class="card-body p-4">
        <!-- Avatar e Informações -->
        <div class="info-header mb-4">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-3">
              {{ colaborador?.usuario?.name?.substring(0, 2).toUpperCase() || 'CA' }}
            </div>
            <div>
              <h4 class="text-white mb-1">{{ colaborador?.usuario?.name }}</h4>
              <p class="text-white-50 mb-0">
                <i class="fas fa-id-card me-2"></i>{{ colaborador?.matricula_funcional }}
                <span class="mx-2">•</span>
                <i class="fas fa-envelope me-2"></i>{{ colaborador?.email_funcional }}
              </p>
            </div>
          </div>
        </div>

        <hr class="divider">

        <!-- ⭐ SEÇÃO: Instituições com Acesso -->
        <div class="section-container mb-4">
          <div class="section-header">
            <div>
              <h5 class="text-white mb-1">
                <i class="fas fa-building me-2"></i>Instituições com Acesso
              </h5>
              <p class="text-white-50 small mb-0">Defina em quais instituições o colaborador pode acessar e seus perfis</p>
            </div>
            <button class="btn btn-success" @click="openInstituicoesModal">
              <i class="fas fa-edit me-2"></i>Gerenciar Instituições
            </button>
          </div>
          <div class="section-content">
            <div v-if="instituicoesAcesso.length">
              <!-- Lista de Instituições com Accordion -->
              <div
                v-for="instituicao in instituicoesAcesso"
                :key="instituicao.id"
                class="instituicao-card mb-3"
              >
                <div class="instituicao-header" @click="toggleInstituicao(instituicao.id)">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="text-white mb-1">
                        <i class="fas fa-university me-2"></i>{{ instituicao.nome }}
                      </h6>
                      <small class="text-white-50">
                        <i class="fas fa-shield-alt me-1"></i>{{ instituicao.roles?.length || 0 }} perfis
                        <span class="mx-2">•</span>
                        <i class="fas fa-key me-1"></i>{{ instituicao.permissions?.length || 0 }} permissões
                      </small>
                    </div>
                    <div>
                      <button class="btn btn-sm btn-outline-light me-2" @click.stop="openInstituicaoAcessosModal(instituicao)">
                        <i class="fas fa-cog me-1"></i>Configurar
                      </button>
                      <i 
                        class="fas" 
                        :class="expandedInstituicao === instituicao.id ? 'fa-chevron-up' : 'fa-chevron-down'"
                      ></i>
                    </div>
                  </div>
                </div>
                
                <!-- Detalhes Expandidos -->
                <div v-if="expandedInstituicao === instituicao.id" class="instituicao-body">
                  <div class="row">
                    <div class="col-md-6">
                      <h6 class="text-white mb-2">
                        <i class="fas fa-shield-alt me-2"></i>Perfis:
                      </h6>
                      <div v-if="instituicao.roles?.length">
                        <span
                          v-for="role in instituicao.roles"
                          :key="role"
                          class="badge badge-custom badge-primary me-2 mb-2"
                        >
                          {{ role }}
                        </span>
                      </div>
                      <p v-else class="text-white-50 small">Nenhum perfil atribuído</p>
                    </div>
                    <div class="col-md-6">
                      <h6 class="text-white mb-2">
                        <i class="fas fa-key me-2"></i>Permissões:
                      </h6>
                      <div v-if="instituicao.permissions?.length">
                        <span
                          v-for="permission in instituicao.permissions"
                          :key="permission"
                          class="badge badge-custom badge-warning me-2 mb-2"
                        >
                          {{ formatPermissionName(permission) }}
                        </span>
                      </div>
                      <p v-else class="text-white-50 small">Nenhuma permissão avulsa</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="empty-state">
              <i class="fas fa-info-circle me-2"></i>Nenhuma instituição atribuída
            </div>
          </div>
        </div>

        <hr class="divider">

        <!-- Perfis Atribuídos (GLOBAL) -->
        <div class="section-container mb-4">
          <div class="section-header">
            <div>
              <h5 class="text-white mb-1">
                <i class="fas fa-user-tag me-2"></i>Perfis Globais
              </h5>
              <p class="text-white-50 small mb-0">Perfis que se aplicam a todas as instituições</p>
            </div>
            <button class="btn btn-primary" @click="openRolesModal">
              <i class="fas fa-edit me-2"></i>Editar Perfis
            </button>
          </div>
          <div class="section-content">
            <div v-if="userPermissions.roles?.length" class="badges-container">
              <span
                v-for="role in userPermissions.roles"
                :key="role.id"
                class="badge-custom badge-primary"
              >
                <i class="fas fa-shield-alt me-2"></i>{{ role.name }}
              </span>
            </div>
            <div v-else class="empty-state">
              <i class="fas fa-info-circle me-2"></i>Nenhum perfil global atribuído
            </div>
          </div>
        </div>

        <hr class="divider">

        <!-- Permissões via Perfis (GLOBAL) -->
        <div class="section-container mb-4">
          <div class="section-header">
            <div>
              <h5 class="text-white mb-1">
                <i class="fas fa-list-check me-2"></i>Permissões Globais via Perfis
              </h5>
              <p class="text-white-50 small mb-0">Permissões herdadas dos perfis globais</p>
            </div>
          </div>
          <div class="section-content">
            <div v-if="userPermissions.permissions_from_roles?.length" class="permissions-grid">
              <div
                v-for="permission in userPermissions.permissions_from_roles"
                :key="permission"
                class="permission-item"
              >
                <i class="fas fa-check-circle text-info me-2"></i>
                <span>{{ formatPermissionName(permission) }}</span>
              </div>
            </div>
            <div v-else class="empty-state">
              <i class="fas fa-info-circle me-2"></i>Nenhuma permissão herdada de perfis
            </div>
          </div>
        </div>

        <hr class="divider">

        <!-- Permissões Avulsas (GLOBAL) -->
        <div class="section-container">
          <div class="section-header">
            <div>
              <h5 class="text-white mb-1">
                <i class="fas fa-key me-2"></i>Permissões Avulsas Globais
              </h5>
              <p class="text-white-50 small mb-0">Permissões específicas fora dos perfis</p>
            </div>
            <button class="btn btn-warning text-dark" @click="openPermissionsModal">
              <i class="fas fa-cog me-2"></i>Gerenciar Permissões
            </button>
          </div>
          <div class="section-content">
            <div v-if="userPermissions.direct_permissions?.length" class="badges-container">
              <span
                v-for="permission in userPermissions.direct_permissions"
                :key="permission"
                class="badge-custom badge-warning"
              >
                <i class="fas fa-lock me-2"></i>{{ formatPermissionName(permission) }}
              </span>
            </div>
            <div v-else class="empty-state">
              <i class="fas fa-info-circle me-2"></i>Nenhuma permissão avulsa concedida
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <InstituicoesModal
      v-if="showInstituicoesModal"
      :colaborador-id="parseInt(id)"
      :current-instituicoes="instituicoesAcesso"
      @close="showInstituicoesModal = false"
      @saved="refreshInstituicoes"
    />

    <InstituicaoAcessosModal
      v-if="showInstituicaoAcessosModal"
      :colaborador-id="parseInt(id)"
      :instituicao="selectedInstituicao"
      @close="closeInstituicaoAcessosModal"
      @saved="refreshInstituicoes"
    />

    <RolesModal
      v-if="showRolesModal"
      :user="colaborador?.usuario"
      :current-roles="userPermissions.roles"
      @close="showRolesModal = false"
      @saved="refreshUserPermissions"
    />

    <PermissoesAvulsasModal
      v-if="showPermissionsModal"
      :user="colaborador?.usuario"
      :current-permissions="userPermissions.direct_permissions"
      @close="showPermissionsModal = false"
      @saved="refreshUserPermissions"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue';
import axios from 'axios';
import InstituicoesModal from '@/components/InstituicoesModal.vue';
import InstituicaoAcessosModal from '@/components/InstituicaoAcessosModal.vue';
import RolesModal from '@/components/RolesModal.vue';
import PermissoesAvulsasModal from '@/components/PermissoesAvulsasModal.vue';

const props = defineProps({ id: String });
const colaborador = ref(null);
const instituicoesAcesso = ref([]);
const userPermissions = ref({
  roles: [],
  permissions_from_roles: [],
  direct_permissions: [],
  all_permissions: []
});
const showInstituicoesModal = ref(false);
const showInstituicaoAcessosModal = ref(false);
const showRolesModal = ref(false);
const showPermissionsModal = ref(false);
const selectedInstituicao = ref(null);
const expandedInstituicao = ref(null);

const fetchColaborador = async () => {
  try {
    const response = await axios.get(`/api/v1/colaboradores/${props.id}`);
    colaborador.value = response.data;
    
    if (colaborador.value?.usuario?.id) {
      await loadUserPermissions();
    }
    
    await loadInstituicoes();
  } catch (error) {
    console.error("Erro ao buscar dados do colaborador:", error);
  }
};

const loadUserPermissions = async () => {
  try {
    const response = await axios.get(`/api/v1/gestao-acessos/users/${colaborador.value.usuario.id}/permissions`);
    userPermissions.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar permissões:', error);
  }
};

const loadInstituicoes = async () => {
  try {
    const response = await axios.get(`/api/v1/gestao-acessos/colaboradores/${props.id}/instituicoes`);
    instituicoesAcesso.value = response.data.instituicoes;
  } catch (error) {
    console.error('Erro ao carregar instituições:', error);
  }
};

const formatPermissionName = (permission) => {
  return permission
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const toggleInstituicao = (id) => {
  expandedInstituicao.value = expandedInstituicao.value === id ? null : id;
};

const openInstituicoesModal = () => {
  showInstituicoesModal.value = true;
};

const openInstituicaoAcessosModal = (instituicao) => {
  selectedInstituicao.value = instituicao;
  showInstituicaoAcessosModal.value = true;
};

const closeInstituicaoAcessosModal = () => {
  showInstituicaoAcessosModal.value = false;
  selectedInstituicao.value = null;
};

const openRolesModal = () => {
  showRolesModal.value = true;
};

const openPermissionsModal = () => {
  showPermissionsModal.value = true;
};

const refreshInstituicoes = async () => {
  await loadInstituicoes();
  showInstituicoesModal.value = false;
  closeInstituicaoAcessosModal();
};

const refreshUserPermissions = async () => {
  await loadUserPermissions();
  showRolesModal.value = false;
  showPermissionsModal.value = false;
};

onMounted(() => {
  fetchColaborador();
});
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.info-header {
  padding-bottom: 1.5rem;
}

.avatar-circle {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  font-weight: bold;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.divider {
  border: 0;
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 1.5rem 0;
}

.section-container {
  padding: 1rem 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-content {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

/* Instituição Card */
.instituicao-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.instituicao-card:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.2);
}

.instituicao-header {
  padding: 1rem 1.25rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.instituicao-header:hover {
  background: rgba(255, 255, 255, 0.05);
}

.instituicao-body {
  padding: 1rem 1.25rem;
  background: rgba(0, 0, 0, 0.1);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.badges-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.badge-custom {
  display: inline-flex;
  align-items: center;
  padding: 0.625rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.badge-success {
  background: rgba(25, 135, 84, 0.4);
  color: #ffffff;
  border: 1px solid rgba(25, 135, 84, 0.6);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.badge-success:hover {
  background: rgba(25, 135, 84, 0.6);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4);
}

.badge-primary {
  background: rgba(13, 110, 253, 0.4);
  color: #ffffff;
  border: 1px solid rgba(13, 110, 253, 0.6);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.badge-primary:hover {
  background: rgba(13, 110, 253, 0.6);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.badge-warning {
  background: rgba(255, 193, 7, 0.4);
  color: #ffffff;
  border: 1px solid rgba(255, 193, 7, 0.6);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.badge-warning:hover {
  background: rgba(255, 193, 7, 0.6);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 0.75rem;
}

.permission-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background: rgba(13, 202, 240, 0.25);
  border: 1px solid rgba(13, 202, 240, 0.4);
  border-radius: 8px;
  color: #ffffff;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.3s ease;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.permission-item:hover {
  background: rgba(13, 202, 240, 0.35);
  transform: translateX(3px);
  box-shadow: 0 2px 8px rgba(13, 202, 240, 0.3);
}

.permission-item .text-info {
  color: #0dcaf0 !important;
  filter: brightness(1.3);
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: rgba(255, 255, 255, 0.5);
  font-style: italic;
}

.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
</style>
