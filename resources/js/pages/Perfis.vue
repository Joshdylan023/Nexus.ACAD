<template>
    <div class="container-fluid px-4 py-6">
        <!-- Cabeçalho -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Perfis de Acesso
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Gerencie perfis e suas permissões
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="btn btn-primary"
                    v-if="$can('gerenciar-perfis')"
                >
                    <i class="bi bi-plus-lg me-2"></i>
                    Novo Perfil
                </button>
            </div>
        </div>

        <!-- Cards de Perfis -->
        <div class="row g-4" v-if="!loading">
            <div 
                class="col-md-6 col-lg-4" 
                v-for="perfil in perfis" 
                :key="perfil.id"
            >
                <div class="card shadow-sm hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <!-- Header do Card -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ perfil.name }}</h5>
                                <span class="badge bg-primary">
                                    {{ perfil.users_count }} usuário(s)
                                </span>
                            </div>
                            <div class="dropdown" v-if="$can('gerenciar-perfis')">
                                <button 
                                    class="btn btn-sm btn-light" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                >
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a 
                                            class="dropdown-item" 
                                            href="#" 
                                            @click.prevent="openEditModal(perfil)"
                                        >
                                            <i class="bi bi-pencil me-2"></i>
                                            Editar Nome
                                        </a>
                                    </li>
                                    <li>
                                        <a 
                                            class="dropdown-item" 
                                            href="#" 
                                            @click.prevent="openPermissoesModal(perfil)"
                                        >
                                            <i class="bi bi-shield-lock me-2"></i>
                                            Gerenciar Permissões
                                        </a>
                                    </li>
                                    <li>
                                        <a 
                                            class="dropdown-item" 
                                            href="#" 
                                            @click.prevent="duplicatePerfil(perfil)"
                                        >
                                            <i class="bi bi-files me-2"></i>
                                            Duplicar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a 
                                            class="dropdown-item text-danger" 
                                            href="#" 
                                            @click.prevent="deletePerfil(perfil)"
                                        >
                                            <i class="bi bi-trash me-2"></i>
                                            Excluir
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Informações -->
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                {{ perfil.permissions_count }} permissão(ões)
                            </small>
                        </div>

                        <!-- Preview de Permissões -->
                        <div class="d-flex flex-wrap gap-1">
                            <span 
                                class="badge bg-light text-dark" 
                                v-for="(perm, index) in perfil.permissions.slice(0, 3)" 
                                :key="index"
                            >
                                {{ formatPermission(perm) }}
                            </span>
                            <span 
                                class="badge bg-secondary" 
                                v-if="perfil.permissions.length > 3"
                            >
                                +{{ perfil.permissions.length - 3 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-else class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Modal Criar/Editar -->
        <div 
            class="modal fade" 
            id="perfilModal" 
            tabindex="-1"
            ref="perfilModal"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ editingPerfil ? 'Editar Perfil' : 'Novo Perfil' }}
                        </h5>
                        <button 
                            type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="savePerfil">
                            <div class="mb-3">
                                <label class="form-label">Nome do Perfil *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    v-model="form.name"
                                    placeholder="Ex: Coordenador Acadêmico"
                                    required
                                >
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button 
                            type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="savePerfil"
                            :disabled="saving"
                        >
                            <span v-if="saving">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Salvando...
                            </span>
                            <span v-else>Salvar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Permissões -->
        <PermissoesModal 
            v-if="selectedPerfil"
            :perfil="selectedPerfil"
            @close="closePermissoesModal"
            @saved="loadPerfis"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Modal } from 'bootstrap';
import axios from 'axios';
import Swal from 'sweetalert2';
import PermissoesModal from '../components/PermissoesModal.vue';

const perfis = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingPerfil = ref(null);
const selectedPerfil = ref(null);
const perfilModal = ref(null);
let modalInstance = null;

const form = ref({
    name: ''
});

onMounted(() => {
    loadPerfis();
});

const loadPerfis = async () => {
    try {
        loading.value = true;
        const { data } = await axios.get('/api/v1/perfis');
        perfis.value = data;
    } catch (error) {
        console.error('Erro ao carregar perfis:', error);
        Swal.fire('Erro!', 'Não foi possível carregar os perfis.', 'error');
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    editingPerfil.value = null;
    form.value = { name: '' };
    getModalInstance().show();
};

const openEditModal = (perfil) => {
    editingPerfil.value = perfil;
    form.value = { name: perfil.name };
    getModalInstance().show();
};

const openPermissoesModal = (perfil) => {
    selectedPerfil.value = perfil;
};

const closePermissoesModal = () => {
    selectedPerfil.value = null;
};

const savePerfil = async () => {
    try {
        saving.value = true;
        
        if (editingPerfil.value) {
            await axios.put(`/api/v1/perfis/${editingPerfil.value.id}`, form.value);
            Swal.fire('Sucesso!', 'Perfil atualizado com sucesso!', 'success');
        } else {
            await axios.post('/api/v1/perfis', form.value);
            Swal.fire('Sucesso!', 'Perfil criado com sucesso!', 'success');
        }
        
        getModalInstance().hide();
        loadPerfis();
    } catch (error) {
        console.error('Erro ao salvar perfil:', error);
        Swal.fire('Erro!', error.response?.data?.message || 'Não foi possível salvar o perfil.', 'error');
    } finally {
        saving.value = false;
    }
};

const duplicatePerfil = async (perfil) => {
    const { value: newName } = await Swal.fire({
        title: 'Duplicar Perfil',
        input: 'text',
        inputLabel: 'Nome do novo perfil',
        inputValue: `${perfil.name} (Cópia)`,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Você precisa informar um nome!';
            }
        }
    });

    if (newName) {
        try {
            await axios.post(`/api/v1/perfis/${perfil.id}/duplicate`, { name: newName });
            Swal.fire('Sucesso!', 'Perfil duplicado com sucesso!', 'success');
            loadPerfis();
        } catch (error) {
            Swal.fire('Erro!', error.response?.data?.message || 'Não foi possível duplicar o perfil.', 'error');
        }
    }
};

const deletePerfil = async (perfil) => {
    const result = await Swal.fire({
        title: 'Tem certeza?',
        text: `Deseja excluir o perfil "${perfil.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/v1/perfis/${perfil.id}`);
            Swal.fire('Excluído!', 'Perfil excluído com sucesso.', 'success');
            loadPerfis();
        } catch (error) {
            Swal.fire('Erro!', error.response?.data?.message || 'Não foi possível excluir o perfil.', 'error');
        }
    }
};

const formatPermission = (permission) => {
    return permission.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getModalInstance = () => {
    if (!modalInstance) {
        modalInstance = new Modal(perfilModal.value);
    }
    return modalInstance;
};
</script>
