<template>
    <div class="card card-glass">
        <div class="card-body text-center">
            <h1 class="display-4">Bem-vindo(a)</h1>
            <p class="lead">ao módulo de</p>
            <h2 class="display-5">{{ modulo }}</h2>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.3);">
            <p>Selecione uma das opções no menu à esquerda para começar.</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
  modulo: String,
});
</script>