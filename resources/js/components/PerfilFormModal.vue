<template>
  <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" @click.self="$emit('close')">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass">
        <div class="modal-header border-0">
          <div>
            <h5 class="modal-title text-white mb-1">
              <i class="fas fa-user-tag me-2"></i>{{ isEdit ? 'Editar Perfil' : 'Novo Perfil' }}
            </h5>
            <p class="text-white-50 small mb-0">{{ isEdit ? 'Atualize os dados do perfil' : 'Crie um novo perfil de acesso' }}</p>
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
            <!-- Nome do Perfil -->
            <div class="form-section mb-4">
              <label class="form-label text-white">
                <i class="fas fa-tag me-2"></i>Nome do Perfil
              </label>
              <input
                type="text"
                class="form-control form-control-lg"
                v-model="form.name"
                placeholder="Ex: Coordenador Acadêmico"
                required
              >
            </div>

            <hr class="divider">

            <!-- Busca de Permissões -->
            <div class="form-section mb-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label text-white mb-0">
                  <i class="fas fa-key me-2"></i>Permissões
                </label>
                <div class="text-white-50 small">
                  <i class="fas fa-check-double me-2"></i>
                  {{ selectedPermissions.length }} de {{ availablePermissions.length }} selecionadas
                </div>
              </div>
              
              <div class="search-box mb-3">
                <i class="fas fa-search"></i>
                <input
                  type="text"
                  class="form-control"
                  v-model="permissionSearch"
                  placeholder="Buscar permissões..."
                >
              </div>

              <!-- Ações Rápidas -->
              <div class="quick-actions mb-3">
                <button type="button" class="btn btn-sm btn-outline-success" @click="selectAll">
                  <i class="fas fa-check-double me-1"></i>Selecionar Todas
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" @click="deselectAll">
                  <i class="fas fa-times me-1"></i>Desmarcar Todas
                </button>
              </div>
            </div>

            <!-- Grid de Permissões por Módulo -->
            <div class="permissions-container">
              <div
                v-for="(perms, module) in groupedPermissions"
                :key="module"
                class="module-section"
              >
                <div class="module-header" @click="toggleModule(module)">
                  <div>
                    <i :class="moduleIcons[module] || 'fas fa-folder'" class="me-2"></i>
                    <strong>{{ formatModuleName(module) }}</strong>
                    <span class="badge bg-secondary ms-2">{{ perms.length }}</span>
                  </div>
                  <i class="fas" :class="expandedModules.includes(module) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </div>

                <div v-if="expandedModules.includes(module)" class="module-body">
                  <div class="permissions-grid">
                    <div
                      v-for="permission in perms"
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
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            <i class="fas fa-times me-2"></i>Cancelar
          </button>
          <button type="button" class="btn btn-success" @click="save" :disabled="saving">
            <span v-if="saving">
              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
              Salvando...
            </span>
            <span v-else>
              <i class="fas fa-save me-2"></i>Salvar Perfil
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, defineProps, defineEmits } from 'vue';
import axios from 'axios';

const props = defineProps({
  perfil: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(true);
const saving = ref(false);
const availablePermissions = ref([]);
const selectedPermissions = ref([]);
const permissionSearch = ref('');
const expandedModules = ref([]);

const form = ref({
  name: ''
});

const isEdit = computed(() => !!props.perfil);

const moduleIcons = {
  institucional: 'fas fa-building',
  academico: 'fas fa-graduation-cap',
  professores: 'fas fa-chalkboard-teacher',
  colaboradores: 'fas fa-users',
  financeiro: 'fas fa-dollar-sign',
  relatorios: 'fas fa-chart-bar',
  importacoes: 'fas fa-file-import',
  configuracoes: 'fas fa-cog',
  acessos: 'fas fa-key'
};

const filteredPermissions = computed(() => {
  if (!permissionSearch.value) return availablePermissions.value;
  
  const search = permissionSearch.value.toLowerCase();
  return availablePermissions.value.filter(p => 
    p.name.toLowerCase().includes(search)
  );
});

const groupedPermissions = computed(() => {
  const groups = {};
  
  filteredPermissions.value.forEach(perm => {
    const module = extractModule(perm.name);
    if (!groups[module]) {
      groups[module] = [];
    }
    groups[module].push(perm);
  });
  
  return groups;
});

const extractModule = (permissionName) => {
  const parts = permissionName.split('-');
  if (parts.length > 1) {
    return parts[parts.length - 1];
  }
  return 'outros';
};

const formatModuleName = (module) => {
  const names = {
    institucional: 'Institucional',
    academico: 'Acadêmico',
    professores: 'Professores',
    colaboradores: 'Colaboradores',
    financeiro: 'Financeiro',
    relatorios: 'Relatórios',
    importacoes: 'Importações',
    configuracoes: 'Configurações',
    acessos: 'Acessos',
    outros: 'Outros'
  };
  return names[module] || module.charAt(0).toUpperCase() + module.slice(1);
};

const formatPermissionName = (permission) => {
  return permission
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const toggleModule = (module) => {
  const index = expandedModules.value.indexOf(module);
  if (index > -1) {
    expandedModules.value.splice(index, 1);
  } else {
    expandedModules.value.push(module);
  }
};

const isPermissionSelected = (permissionName) => {
  return selectedPermissions.value.includes(permissionName);
};

const togglePermission = (permissionName) => {
  const index = selectedPermissions.value.indexOf(permissionName);
  if (index > -1) {
    selectedPermissions.value.splice(index, 1);
  } else {
    selectedPermissions.value.push(permissionName);
  }
};

const selectAll = () => {
  selectedPermissions.value = availablePermissions.value.map(p => p.name);
};

const deselectAll = () => {
  selectedPermissions.value = [];
};

const loadPermissions = async () => {
  try {
    const response = await axios.get('/api/v1/perfis/permissoes-disponiveis');
    availablePermissions.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar permissões:', error);
  }
};

const loadPerfil = async () => {
  if (!props.perfil) return;
  
  try {
    const response = await axios.get(`/api/v1/perfis/${props.perfil.id}`);
    form.value.name = response.data.name;
    selectedPermissions.value = response.data.permissions || [];
  } catch (error) {
    console.error('Erro ao carregar perfil:', error);
  }
};

const save = async () => {
  if (!form.value.name) {
    alert('Por favor, informe o nome do perfil');
    return;
  }

  try {
    saving.value = true;

    const data = {
      name: form.value.name,
      permissions: selectedPermissions.value
    };

    if (isEdit.value) {
      await axios.put(`/api/v1/perfis/${props.perfil.id}`, data);
    } else {
      await axios.post('/api/v1/perfis', data);
    }

    emit('saved');
  } catch (error) {
    console.error('Erro ao salvar perfil:', error);
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    } else {
      alert('Erro ao salvar perfil');
    }
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  loading.value = true;
  await loadPermissions();
  await loadPerfil();
  
  // Expandir todos os módulos por padrão
  expandedModules.value = Object.keys(groupedPermissions.value);
  
  loading.value = false;
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

.form-control, .form-control-lg {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-control:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  color: white;
  box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.1);
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
}

.quick-actions {
  display: flex;
  gap: 0.5rem;
}

.permissions-container {
  max-height: 400px;
  overflow-y: auto;
}

.module-section {
  margin-bottom: 1rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
}

.module-header:hover {
  background: rgba(255, 255, 255, 0.08);
}

.module-body {
  padding: 1rem;
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 0.75rem;
}

.permission-item {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.permission-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.permission-item.selected {
  background: rgba(25, 135, 84, 0.25);
  border-color: rgba(25, 135, 84, 0.5);
}

.form-check {
  margin: 0;
}

.form-check-input {
  cursor: pointer;
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  background-color: rgba(255, 255, 255, 0.1);
}

.form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
}

.form-check-label {
  cursor: pointer;
  color: white;
  margin-left: 0.5rem;
}

.btn-close-white {
  filter: brightness(0) invert(1);
}

.modal-body::-webkit-scrollbar,
.permissions-container::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track,
.permissions-container::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb,
.permissions-container::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover,
.permissions-container::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
