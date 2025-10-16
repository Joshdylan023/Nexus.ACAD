<template>
    <div 
        class="modal fade show d-block" 
        tabindex="-1" 
        style="background-color: rgba(0,0,0,0.5);"
        @click.self="$emit('close')"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Gerenciar Perfis: {{ user.name }}
                    </h5>
                    <button 
                        type="button" 
                        class="btn-close btn-close-white" 
                        @click="$emit('close')"
                    ></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Selecione os perfis que este usuário deve ter. 
                        <strong>Um usuário pode ter múltiplos perfis.</strong>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>

                    <!-- Lista de Perfis -->
                    <div v-else>
                        <div 
                            v-for="role in availableRoles" 
                            :key="role.id"
                            class="form-check mb-3 p-3 border rounded hover:bg-light"
                            :class="{ 'bg-light': selectedRoles.includes(role.name) }"
                        >
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                :id="`role-${role.id}`"
                                :value="role.name"
                                v-model="selectedRoles"
                            >
                            <label 
                                class="form-check-label w-100 cursor-pointer" 
                                :for="`role-${role.id}`"
                            >
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ role.name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ role.permissions_count }} permissão(ões)
                                        </small>
                                    </div>
                                    <span 
                                        v-if="selectedRoles.includes(role.name)"
                                        class="badge bg-success"
                                    >
                                        <i class="bi bi-check-lg"></i>
                                    </span>
                                </div>
                            </label>
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
                        class="btn btn-primary" 
                        @click="saveRoles"
                        :disabled="saving"
                    >
                        <span v-if="saving">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Salvando...
                        </span>
                        <span v-else>
                            <i class="bi bi-check-lg me-2"></i>
                            Salvar
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    currentRoles: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'saved']);

const availableRoles = ref([]);
const selectedRoles = ref([]);
const loading = ref(true);
const saving = ref(false);

onMounted(async () => {
    await loadRoles();
    // Pré-selecionar perfis atuais
    selectedRoles.value = props.currentRoles.map(r => r.name);
});

const loadRoles = async () => {
    try {
        loading.value = true;
        const { data } = await axios.get('/api/v1/perfis');
        availableRoles.value = data;
    } catch (error) {
        console.error('Erro ao carregar perfis:', error);
        Swal.fire('Erro!', 'Não foi possível carregar os perfis.', 'error');
    } finally {
        loading.value = false;
    }
};

const saveRoles = async () => {
    try {
        saving.value = true;
        
        await axios.post(`/api/v1/gestao-acessos/users/${props.user.id}/roles`, {
            roles: selectedRoles.value
        });
        
        Swal.fire('Sucesso!', 'Perfis atualizados com sucesso!', 'success');
        emit('saved');
        emit('close');
    } catch (error) {
        console.error('Erro ao salvar perfis:', error);
        Swal.fire('Erro!', 'Não foi possível salvar os perfis.', 'error');
    } finally {
        saving.value = false;
    }
};
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.hover\:bg-light:hover {
    background-color: #f8f9fa !important;
}
</style>
