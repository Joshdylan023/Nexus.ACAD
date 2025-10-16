<template>
  <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" @click.self="$emit('close')">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass">
        <div class="modal-header border-0">
          <div>
            <h5 class="modal-title text-white mb-1">
              <i class="fas fa-cog me-2"></i>Configurar Acessos - {{ instituicao.nome }}
            </h5>
            <p class="text-white-50 small mb-0">Defina perfis e permissões específicos para esta instituição</p>
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
            <!-- SEÇÃO: PERFIS -->
            <div class="access-section mb-4">
              <div class="section-title">
                <h6 class="text-white mb-3">
                  <i class="fas fa-user-tag me-2"></i>Perfis (Roles)
                </h6>
              </div>
              <div class="roles-grid">
                <div
                  v-for="role in availableRoles"
                  :key="role.id"
                  class="role-item"
                  :class="{ 'selected': isRoleSelected(role.name) }"
                  @click="toggleRole(role.name)"
                >
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="isRoleSelected(role.name)"
                      :id="`role-${role.id}`"
                      @click.stop
                    >
                    <label class="form-check-label" :for="`role-${role.id}`">
                      <strong>{{ role.name }}</strong>
                      <small class="d-block text-white-50">{{ role.description || 'Sem descrição' }}</small>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <hr class="divider">

            <!-- SEÇÃO: PERMISSÕES AVULSAS -->
            <div class="access-section">
              <div class="section-title">
                <h6 class="text-white mb-3">
                  <i class="fas fa-key me-2"></i>Permissões Avulsas
                </h6>
                <input
                  type="text"
                  class="form-control form-control-sm search-input"
                  v-model="permissionSearch"
                  placeholder="Buscar permissão..."
                >
              </div>
              <div class="permissions-grid">
                <div
                  v-for="permission in filteredPermissions"
                  :key="permission.id"
                  class="permission-item"
                  :class="{ 'selected': isPermissionSelected(permission.name) }"
                  @click="togglePermission(permission.name)"
                >
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="isPermissionSelected(permission.name)"
                      :id="`perm-${permission.id}`"
                      @click.stop
                    >
                    <label class="form-check-label" :for="`perm-${permission.id}`">
                      {{ formatPermissionName(permission.name) }}
                    </label>
                  </div>
                </div>
              </div>

              <div v-if="filteredPermissions.length === 0" class="text-center py-4">
                <i class="fas fa-search fa-2x text-white-50 mb-2"></i>
                <p class="text-white-50">Nenhuma permissão encontrada</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <div class="d-flex justify-content-between w-100 align-items-center">
            <div class="text-white-50 small">
              <i class="fas fa-check-double me-2"></i>
              {{ selectedRoles.length }} perfis • {{ selectedPermissions.length }} permissões
            </div>
            <div>
              <button type="button" class="btn btn-secondary me-2" @click="$emit('close')">
                <i class="fas fa-times me-2"></i>Cancelar
              </button>
              <button type="button" class="btn btn-success" @click="saveAcessos" :disabled="saving">
                <span v-if="saving">
                  <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                  Salvando...
                </span>
                <span v-else>
                  <i class="fas fa-save me-2"></i>Salvar Configurações
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, defineProps, defineEmits } from 'vue';
import axios from 'axios';

const props = defineProps({
  colaboradorId: {
    type: Number,
    required: true
  },
  instituicao: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(true);
const saving = ref(false);
const availableRoles = ref([]);
const availablePermissions = ref([]);
const selectedRoles = ref([]);
const selectedPermissions = ref([]);
const permissionSearch = ref('');

const filteredPermissions = computed(() => {
  if (!permissionSearch.value) return availablePermissions.value;
  
  const search = permissionSearch.value.toLowerCase();
  return availablePermissions.value.filter(p => 
    p.name.toLowerCase().includes(search)
  );
});

onMounted(async () => {
  await Promise.all([
    loadRoles(),
    loadPermissions(),
    loadCurrentAcessos()
  ]);
  loading.value = false;
});

const loadRoles = async () => {
  try {
    const response = await axios.get('/api/v1/perfis');
    availableRoles.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar perfis:', error);
  }
};

const loadPermissions = async () => {
  try {
    const response = await axios.get('/api/v1/permissoes');
    availablePermissions.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar permissões:', error);
  }
};

const loadCurrentAcessos = async () => {
  try {
    const response = await axios.get(
      `/api/v1/gestao-acessos/colaboradores/${props.colaboradorId}/instituicoes/${props.instituicao.id}/acessos`
    );
    selectedRoles.value = response.data.roles || [];
    selectedPermissions.value = response.data.permissions || [];
  } catch (error) {
    console.error('Erro ao carregar acessos atuais:', error);
  }
};

const isRoleSelected = (roleName) => {
  return selectedRoles.value.includes(roleName);
};

const isPermissionSelected = (permissionName) => {
  return selectedPermissions.value.includes(permissionName);
};

const toggleRole = (roleName) => {
  const index = selectedRoles.value.indexOf(roleName);
  if (index > -1) {
    selectedRoles.value.splice(index, 1);
  } else {
    selectedRoles.value.push(roleName);
  }
};

const togglePermission = (permissionName) => {
  const index = selectedPermissions.value.indexOf(permissionName);
  if (index > -1) {
    selectedPermissions.value.splice(index, 1);
  } else {
    selectedPermissions.value.push(permissionName);
  }
};

const formatPermissionName = (permission) => {
  return permission
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const saveAcessos = async () => {
  try {
    saving.value = true;

    // Salvar roles
    await axios.post(
      `/api/v1/gestao-acessos/colaboradores/${props.colaboradorId}/instituicoes/${props.instituicao.id}/roles`,
      { roles: selectedRoles.value }
    );

    // Salvar permissões
    await axios.post(
      `/api/v1/gestao-acessos/colaboradores/${props.colaboradorId}/instituicoes/${props.instituicao.id}/permissions`,
      { permissions: selectedPermissions.value }
    );

    emit('saved');
  } catch (error) {
    console.error('Erro ao salvar acessos:', error);
    alert('Erro ao salvar configurações. Tente novamente.');
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.modal-body {
  max-height: 600px;
  overflow-y: auto;
}

.divider {
  border: 0;
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 1.5rem 0;
}

.access-section {
  padding: 1rem 0;
}

.section-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.search-input {
  max-width: 300px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.search-input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  color: white;
  box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
}

.roles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.role-item {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.role-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
}

.role-item.selected {
  background: rgba(13, 110, 253, 0.25);
  border-color: rgba(13, 110, 253, 0.5);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
}

.permission-item {
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.permission-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(3px);
}

.permission-item.selected {
  background: rgba(255, 193, 7, 0.25);
  border-color: rgba(255, 193, 7, 0.5);
  box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
}

.form-check {
  margin: 0;
  padding: 0;
}

.form-check-input {
  cursor: pointer;
  width: 1.25rem;
  height: 1.25rem;
  margin-right: 0.75rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  background-color: rgba(255, 255, 255, 0.1);
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-check-label {
  cursor: pointer;
  color: white;
  margin: 0;
  flex: 1;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}

/* Scrollbar customizada */
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
