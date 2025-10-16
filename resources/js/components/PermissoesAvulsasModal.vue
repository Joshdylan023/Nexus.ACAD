<template>
    <div 
        class="modal fade show d-block" 
        tabindex="-1" 
        style="background-color: rgba(0,0,0,0.5);"
        @click.self="$emit('close')"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="bi bi-star-fill me-2"></i>
                            Permissões Avulsas: {{ user.name }}
                        </h5>
                        <small>
                            Adicione permissões específicas além das do perfil
                        </small>
                    </div>
                    <button 
                        type="button" 
                        class="btn-close" 
                        @click="$emit('close')"
                    ></button>
                </div>

                <div class="modal-body">
                    <!-- Alerta -->
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Atenção:</strong> Permissões avulsas são adicionadas diretamente ao usuário,
                        independente do perfil. Use apenas quando necessário!
                    </div>

                    <!-- Busca -->
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="searchTerm"
                                placeholder="Buscar permissão..."
                            >
                        </div>
                    </div>

                    <!-- Contador -->
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            <strong>{{ selectedPermissions.length }}</strong> permissão(ões) avulsa(s) selecionada(s)
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>

                    <!-- Lista de Permissões -->
                    <div v-else>
                        <div 
                            v-for="(group, moduleName) in groupedPermissions" 
                            :key="moduleName"
                            class="mb-3"
                        >
                            <div class="card">
                                <div 
                                    class="card-header bg-light cursor-pointer"
                                    @click="toggleModule(moduleName)"
                                >
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i :class="getModuleIcon(moduleName)" class="me-2"></i>
                                            <strong>{{ moduleName }}</strong>
                                        </div>
                                        <i 
                                            class="bi" 
                                            :class="expandedModules.includes(moduleName) ? 'bi-chevron-up' : 'bi-chevron-down'"
                                        ></i>
                                    </div>
                                </div>

                                <div 
                                    class="card-body"
                                    v-show="expandedModules.includes(moduleName)"
                                >
                                    <div class="row g-3">
                                        <div 
                                            class="col-md-6" 
                                            v-for="permission in group" 
                                            :key="permission.name"
                                        >
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    :id="permission.name"
                                                    :value="permission.name"
                                                    v-model="selectedPermissions"
                                                >
                                                <label 
                                                    class="form-check-label" 
                                                    :for="permission.name"
                                                >
                                                    {{ formatPermission(permission.name) }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        @click="$emit('close')"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-warning" 
                        @click="savePermissions"
                        :disabled="saving"
                    >
                        <span v-if="saving">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Salvando...
                        </span>
                        <span v-else>
                            <i class="bi bi-check-lg me-2"></i>
                            Salvar Permissões Avulsas
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    currentPermissions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'saved']);

const permissions = ref([]);
const selectedPermissions = ref([]);
const loading = ref(true);
const saving = ref(false);
const searchTerm = ref('');
const expandedModules = ref([]);

onMounted(async () => {
    await loadPermissions();
    selectedPermissions.value = [...props.currentPermissions];
    expandedModules.value = Object.keys(groupedPermissions.value);
});

const loadPermissions = async () => {
    try {
        loading.value = true;
        const { data } = await axios.get('/api/v1/permissoes');
        permissions.value = data;
    } catch (error) {
        console.error('Erro ao carregar permissões:', error);
        Swal.fire('Erro!', 'Não foi possível carregar as permissões.', 'error');
    } finally {
        loading.value = false;
    }
};

const filteredPermissions = computed(() => {
    if (!searchTerm.value) return permissions.value;
    
    const term = searchTerm.value.toLowerCase();
    return permissions.value.filter(p => 
        p.name.toLowerCase().includes(term)
    );
});

const groupedPermissions = computed(() => {
    const groups = {};
    
    filteredPermissions.value.forEach(permission => {
        const moduleName = getModuleName(permission.name);
        if (!groups[moduleName]) {
            groups[moduleName] = [];
        }
        groups[moduleName].push(permission);
    });
    
    return groups;
});

const getModuleName = (permissionName) => {
    if (permissionName.includes('institucional') || permissionName.includes('grupos') || 
        permissionName.includes('mantenedoras') || permissionName.includes('instituicoes') || 
        permissionName.includes('campi')) {
        return 'Institucional';
    }
    if (permissionName.includes('academico') || permissionName.includes('cursos') || 
        permissionName.includes('disciplinas') || permissionName.includes('curriculos')) {
        return 'Acadêmico';
    }
    if (permissionName.includes('colaboradores') || permissionName.includes('acessos') || 
        permissionName.includes('perfis')) {
        return 'Pessoas e Acessos';
    }
    if (permissionName.includes('professores')) {
        return 'Professores';
    }
    if (permissionName.includes('financeiro')) {
        return 'Financeiro';
    }
    if (permissionName.includes('relatorios')) {
        return 'Relatórios';
    }
    if (permissionName.includes('importacoes')) {
        return 'Importação';
    }
    return 'Outros';
};

const getModuleIcon = (moduleName) => {
    const iconMap = {
        'Institucional': 'bi bi-building',
        'Acadêmico': 'bi bi-book',
        'Pessoas e Acessos': 'bi bi-people',
        'Professores': 'bi bi-person-badge',
        'Financeiro': 'bi bi-cash-coin',
        'Relatórios': 'bi bi-graph-up',
        'Importação': 'bi bi-upload',
        'Outros': 'bi bi-three-dots',
    };
    return iconMap[moduleName] || 'bi bi-circle';
};

const formatPermission = (permission) => {
    return permission.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const toggleModule = (moduleName) => {
    const index = expandedModules.value.indexOf(moduleName);
    if (index > -1) {
        expandedModules.value.splice(index, 1);
    } else {
        expandedModules.value.push(moduleName);
    }
};

const savePermissions = async () => {
    try {
        saving.value = true;
        
        await axios.post(`/api/v1/gestao-acessos/users/${props.user.id}/sync-direct-permissions`, {
            permissions: selectedPermissions.value
        });
        
        Swal.fire('Sucesso!', 'Permissões avulsas atualizadas!', 'success');
        emit('saved');
        emit('close');
    } catch (error) {
        console.error('Erro ao salvar permissões:', error);
        Swal.fire('Erro!', 'Não foi possível salvar as permissões.', 'error');
    } finally {
        saving.value = false;
    }
};
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
