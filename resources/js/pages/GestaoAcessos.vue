<template>
    <div class="container-fluid px-4 py-6">
        <!-- Cabeçalho -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Gestão de Acessos
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Gerencie perfis e permissões avulsas dos colaboradores
            </p>
        </div>

        <!-- Busca de Usuário -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-search me-2"></i>
                            Buscar Colaborador
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            v-model="searchTerm"
                            @input="searchUsers"
                            placeholder="Digite o nome ou e-mail do colaborador..."
                        >
                        
                        <!-- Lista de Resultados -->
                        <div 
                            v-if="searchResults.length > 0 && !selectedUser"
                            class="list-group mt-2 shadow-sm"
                            style="max-height: 300px; overflow-y: auto;"
                        >
                            <a 
                                href="#" 
                                class="list-group-item list-group-item-action"
                                v-for="user in searchResults" 
                                :key="user.id"
                                @click.prevent="selectUser(user)"
                            >
                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ user.name }}</h6>
                                        <small class="text-muted">{{ user.email }}</small>
                                    </div>
                                    <span class="badge bg-primary align-self-center">
                                        {{ user.colaborador?.tipo_colaborador || 'Colaborador' }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Usuário Selecionado -->
        <div v-if="selectedUser" class="row g-4">
            <!-- Card de Info do Usuário -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div 
                                class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px; font-size: 2rem;"
                            >
                                {{ getInitials(selectedUser.name) }}
                            </div>
                        </div>
                        <h5 class="card-title mb-1">{{ selectedUser.name }}</h5>
                        <p class="text-muted mb-3">{{ selectedUser.email }}</p>
                        
                        <button 
                            class="btn btn-outline-secondary btn-sm w-100"
                            @click="clearSelection"
                        >
                            <i class="bi bi-x-circle me-2"></i>
                            Selecionar Outro Usuário
                        </button>
                    </div>
                </div>
            </div>

            <!-- Gestão de Permissões -->
            <div class="col-lg-8">
                <!-- Loading -->
                <div v-if="loadingPermissions" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <div v-else>
                    <!-- Perfis do Usuário -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                Perfis Atribuídos
                            </h6>
                        </div>
                        <div class="card-body">
                            <div v-if="userPermissions.roles && userPermissions.roles.length > 0">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span 
                                        v-for="role in userPermissions.roles" 
                                        :key="role.id"
                                        class="badge bg-primary fs-6 py-2 px-3"
                                    >
                                        {{ role.name }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-muted">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhum perfil atribuído
                            </div>
                            
                            <button 
                                class="btn btn-sm btn-outline-primary mt-3"
                                @click="showRolesModal = true"
                            >
                                <i class="bi bi-pencil me-2"></i>
                                Gerenciar Perfis
                            </button>
                        </div>
                    </div>

                    <!-- Permissões do Perfil -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-shield-check me-2"></i>
                                Permissões do Perfil
                            </h6>
                        </div>
                        <div class="card-body">
                            <div v-if="userPermissions.permissions_from_roles && userPermissions.permissions_from_roles.length > 0">
                                <div class="d-flex flex-wrap gap-2">
                                    <span 
                                        v-for="perm in userPermissions.permissions_from_roles" 
                                        :key="perm"
                                        class="badge bg-info text-dark"
                                    >
                                        {{ formatPermission(perm) }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-muted">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhuma permissão herdada do perfil
                            </div>
                        </div>
                    </div>

                    <!-- Permissões Avulsas -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-star-fill me-2"></i>
                                    Permissões Avulsas
                                </h6>
                                <button 
                                    class="btn btn-sm btn-dark"
                                    @click="showPermissoesAvulsasModal = true"
                                >
                                    <i class="bi bi-pencil me-2"></i>
                                    Gerenciar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Permissões avulsas</strong> são adicionadas diretamente ao usuário, 
                                independente do perfil. Use com cautela!
                            </div>
                            
                            <div v-if="userPermissions.direct_permissions && userPermissions.direct_permissions.length > 0">
                                <div class="d-flex flex-wrap gap-2">
                                    <span 
                                        v-for="perm in userPermissions.direct_permissions" 
                                        :key="perm"
                                        class="badge bg-warning text-dark position-relative"
                                    >
                                        {{ formatPermission(perm) }}
                                        <button 
                                            class="btn-close btn-close-sm ms-2"
                                            style="font-size: 0.6rem;"
                                            @click="revokePermission(perm)"
                                        ></button>
                                    </span>
                                </div>
                                
                                <button 
                                    class="btn btn-sm btn-outline-danger mt-3"
                                    @click="clearAllDirectPermissions"
                                >
                                    <i class="bi bi-trash me-2"></i>
                                    Remover Todas as Permissões Avulsas
                                </button>
                            </div>
                            <div v-else class="text-muted">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhuma permissão avulsa atribuída
                            </div>
                        </div>
                    </div>

                    <!-- Resumo Total -->
                    <div class="alert alert-success mt-4">
                        <h6 class="alert-heading">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Resumo de Permissões
                        </h6>
                        <p class="mb-0">
                            Este usuário possui <strong>{{ userPermissions.all_permissions?.length || 0 }}</strong> 
                            permissões no total (perfil + avulsas).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Gerenciar Perfis -->
        <RolesModal 
            v-if="showRolesModal"
            :user="selectedUser"
            :currentRoles="userPermissions.roles || []"
            @close="showRolesModal = false"
            @saved="refreshPermissions"
        />

        <!-- Modal: Gerenciar Permissões Avulsas -->
        <PermissoesAvulsasModal 
            v-if="showPermissoesAvulsasModal"
            :user="selectedUser"
            :currentPermissions="userPermissions.direct_permissions || []"
            @close="showPermissoesAvulsasModal = false"
            @saved="refreshPermissions"
        />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { debounce } from 'lodash';
import RolesModal from '../components/RolesModal.vue';
import PermissoesAvulsasModal from '../components/PermissoesAvulsasModal.vue';

const searchTerm = ref('');
const searchResults = ref([]);
const selectedUser = ref(null);
const userPermissions = ref({});
const loadingPermissions = ref(false);
const showRolesModal = ref(false);
const showPermissoesAvulsasModal = ref(false);

const searchUsers = debounce(async () => {
    if (searchTerm.value.length < 3) {
        searchResults.value = [];
        return;
    }

    try {
        const { data } = await axios.get('/api/v1/users/search', {
            params: { q: searchTerm.value }
        });
        searchResults.value = data;
    } catch (error) {
        console.error('Erro ao buscar usuários:', error);
    }
}, 300);

const selectUser = async (user) => {
    selectedUser.value = user;
    searchResults.value = [];
    await loadUserPermissions();
};

const clearSelection = () => {
    selectedUser.value = null;
    userPermissions.value = {};
    searchTerm.value = '';
};

const loadUserPermissions = async () => {
    try {
        loadingPermissions.value = true;
        const { data } = await axios.get(`/api/v1/gestao-acessos/users/${selectedUser.value.id}/permissions`);
        userPermissions.value = data;
    } catch (error) {
        console.error('Erro ao carregar permissões:', error);
        Swal.fire('Erro!', 'Não foi possível carregar as permissões do usuário.', 'error');
    } finally {
        loadingPermissions.value = false;
    }
};

const refreshPermissions = async () => {
    await loadUserPermissions();
};

const revokePermission = async (permission) => {
    const result = await Swal.fire({
        title: 'Revogar Permissão?',
        text: `Deseja remover a permissão "${formatPermission(permission)}" deste usuário?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, revogar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.post(`/api/v1/gestao-acessos/users/${selectedUser.value.id}/revoke-permission`, {
                permission
            });
            Swal.fire('Sucesso!', 'Permissão revogada.', 'success');
            await refreshPermissions();
        } catch (error) {
            Swal.fire('Erro!', 'Não foi possível revogar a permissão.', 'error');
        }
    }
};

const clearAllDirectPermissions = async () => {
    const result = await Swal.fire({
        title: 'Remover TODAS as permissões avulsas?',
        text: 'O usuário manterá apenas as permissões do perfil.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sim, remover todas',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/v1/gestao-acessos/users/${selectedUser.value.id}/clear-direct-permissions`);
            Swal.fire('Sucesso!', 'Permissões avulsas removidas.', 'success');
            await refreshPermissions();
        } catch (error) {
            Swal.fire('Erro!', 'Não foi possível remover as permissões.', 'error');
        }
    }
};

const formatPermission = (permission) => {
    return permission.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getInitials = (name) => {
    return name
        .split(' ')
        .slice(0, 2)
        .map(n => n[0])
        .join('')
        .toUpperCase();
};
</script>
