<template>
    <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="text-center">
            <div class="mb-4">
                <i class="bi bi-shield-lock text-danger" style="font-size: 6rem;"></i>
            </div>
            <h1 class="display-4 text-white mb-3">Acesso Negado</h1>
            <p class="lead text-white-50 mb-4">{{ message }}</p>
            
            <div class="d-flex gap-3 justify-content-center">
                <button @click="goBack" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-2"></i>
                    Voltar
                </button>
                <router-link to="./admin/dashboard" class="btn btn-primary">
                    <i class="bi bi-house me-2"></i>
                    Ir para o Início
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const message = computed(() => {
    return route.query.message || 'Você não tem permissão para acessar esta página, solicite acesso ao administrador.';
});

const goBack = () => {
    router.go(-1);
};
</script>

<style scoped>
.text-white-50 {
    color: rgba(255, 255, 255, 0.5);
}
</style>
