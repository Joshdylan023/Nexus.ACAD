<template>
  <div class="calendario-reservas-page">
    <CalendarioReservas />
  </div>
</template>

<script setup>
// Importar o componente que criamos anteriormente
import CalendarioReservas from '@/pages/Reservas/CalendarioReservas.vue';
</script>

<style scoped>
.calendario-reservas-page {
  width: 100%;
  min-height: calc(100vh - 200px);
}
</style>
