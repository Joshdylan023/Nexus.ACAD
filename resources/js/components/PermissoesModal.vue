<template>
    <div 
        class="modal fade show d-block" 
        tabindex="-1" 
        style="background-color: rgba(0,0,0,0.5);"
        @click.self="$emit('close')"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title mb-1">
                            <i class="bi bi-shield-lock me-2"></i>
                            Permissões: {{ perfil.name }}
                        </h5>
                        <small class="opacity-75">
                            Selecione as permissões para este perfil
                        </small>
                    </div>
                    <button 
                        type="button" 
                        class="btn-close btn-close-white" 
                        @click="$emit('close')"
                    ></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <!-- Barra de Busca e Filtros -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
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
                        <div class="col-md-6 text-end">
                            <button 
                                class="btn btn-outline-primary btn-sm me-2"
                                @click="selectAll"
                            >
                                <i class="bi bi-check-all me-1"></i>
                                Selecionar Todas
                            </button>
                            <button 
                                class="btn btn-outline-secondary btn-sm"
                                @click="deselectAll"
                            >
                                <i class="bi bi-x me-1"></i>
                                Desmarcar Todas
                            </button>
                        </div>
                    </div>

                    <!-- Contador de Selecionadas -->
                    <div class="alert alert-info d-flex align-items-center mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            <strong>{{ selectedPermissions.length }}</strong> de 
                            <strong>{{ filteredPermissions.length }}</strong> 
                            permissões selecionadas
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>

                    <!-- Permissões Agrupadas por Módulo -->
                    <div v-else>
                        <div 
                            v-for="(group, moduleName) in groupedPermissions" 
                            :key="moduleName"
                            class="mb-4"
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
                                            <span class="badge bg-primary ms-2">
                                                {{ group.length }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge bg-success me-2">
                                                {{ countSelected(group) }} selecionada(s)
                                            </span>
                                            <i 
                                                class="bi" 
                                                :class="expandedModules.includes(moduleName) ? 'bi-chevron-up' : 'bi-chevron-down'"
                                            ></i>
                                        </div>
                                    </div>
                                </div>

                                <div 
                                    class="card-body"
                                    v-show="expandedModules.includes(moduleName)"
                                >
                                    <div class="row g-3">
                                        <div 
                                            class="col-md-6 col-lg-4" 
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

                <!-- Footer -->
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
                        class="btn btn-primary" 
                        @click="savePermissions"
                        :disabled="saving"
                    >
                        <span v-if="saving">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Salvando...
                        </span>
                        <span v-else>
                            <i class="bi bi-check-lg me-2"></i>
                            Salvar Permissões
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
    perfil: {
        type: Object,
        required: true
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
    // Expandir todos os módulos por padrão
    expandedModules.value = Object.keys(groupedPermissions.value);
});

const loadPermissions = async () => {
    try {
        loading.value = true;
        
        // Carregar todas as permissões disponíveis
        const { data: allPermissions } = await axios.get('/api/v1/permissoes');
        permissions.value = allPermissions;
        
        // Carregar permissões atuais do perfil
        selectedPermissions.value = [...props.perfil.permissions];
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
    const moduleMap = {
        'institucional': 'Institucional',
        'grupos': 'Institucional',
        'mantenedoras': 'Institucional',
        'instituicoes': 'Institucional',
        'campi': 'Institucional',
        'identidade-visual': 'Institucional',
        'atos-regulatorios': 'Institucional',
        
        'academico': 'Acadêmico',
        'cursos': 'Acadêmico',
        'disciplinas': 'Acadêmico',
        'curriculos': 'Acadêmico',
        'ementas': 'Acadêmico',
        'areas-conhecimento': 'Acadêmico',
        'historico-academico': 'Acadêmico',
        'requerimento': 'Acadêmico',
        
        'colaboradores': 'Pessoas e Acessos',
        'acessos': 'Pessoas e Acessos',
        'perfis': 'Pessoas e Acessos',
        'permissoes': 'Pessoas e Acessos',
        'logs-auditoria': 'Pessoas e Acessos',
        'documentacao-colaborador': 'Pessoas e Acessos',
        
        'professores': 'Professores',
        'vinculos-professores': 'Professores',
        'formacao-professores': 'Professores',
        
        'financeiro': 'Financeiro',
        'encargos-financeiros': 'Financeiro',
        'boletos': 'Financeiro',
        
        'relatorios': 'Relatórios',
        
        'importacoes': 'Importação',
        'templates-importacao': 'Importação',
        
        'eventos-sistema': 'Sistema',
        'notificacoes': 'Sistema',
        'configuracoes': 'Sistema',
    };
    
    for (const [key, value] of Object.entries(moduleMap)) {
        if (permissionName.includes(key)) {
            return value;
        }
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
        'Sistema': 'bi bi-gear',
        'Outros': 'bi bi-three-dots',
    };
    
    return iconMap[moduleName] || 'bi bi-circle';
};

const formatPermission = (permission) => {
    return permission
        .replace(/-/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase());
};

const countSelected = (group) => {
    return group.filter(p => selectedPermissions.value.includes(p.name)).length;
};

const toggleModule = (moduleName) => {
    const index = expandedModules.value.indexOf(moduleName);
    if (index > -1) {
        expandedModules.value.splice(index, 1);
    } else {
        expandedModules.value.push(moduleName);
    }
};

const selectAll = () => {
    selectedPermissions.value = filteredPermissions.value.map(p => p.name);
};

const deselectAll = () => {
    selectedPermissions.value = [];
};

const savePermissions = async () => {
    try {
        saving.value = true;
        
        await axios.post(`/api/v1/perfis/${props.perfil.id}/sync-permissoes`, {
            permissions: selectedPermissions.value
        });
        
        Swal.fire('Sucesso!', 'Permissões atualizadas com sucesso!', 'success');
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

.modal.show {
    display: block !important;
}
</style>
