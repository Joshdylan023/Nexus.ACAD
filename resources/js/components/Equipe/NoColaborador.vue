<template>
  <div class="no-container">
    <!-- Card do Colaborador -->
    <div 
      class="colaborador-node" 
      :class="{
        'is-gestor': colaborador.is_gestor,
        'destacado': destacado,
        'nivel-0': nivel === 0,
        'nivel-1': nivel === 1,
        'nivel-2': nivel === 2,
        'nivel-3-plus': nivel >= 3
      }"
      @click="$emit('selecionar', colaborador)"
    >
      <!-- Foto -->
      <div class="node-foto">
        <img 
          v-if="colaborador.foto" 
          :src="colaborador.foto" 
          :alt="colaborador.nome"
          class="foto-circular"
        >
        <div v-else class="foto-placeholder-circular">
          <i class="bi bi-person-fill"></i>
        </div>
      </div>

      <!-- Info -->
      <div class="node-info">
        <h6 class="node-nome">{{ colaborador.nome }}</h6>
        <p class="node-cargo">{{ colaborador.cargo }}</p>
        <span v-if="colaborador.is_gestor" class="badge-gestor">
          <i class="bi bi-star-fill"></i>
        </span>
      </div>

      <!-- Contador de subordinados -->
      <div 
        v-if="colaborador.subordinados && colaborador.subordinados.length > 0"
        class="subordinados-count"
        @click.stop="$emit('toggle', colaborador.id)"
      >
        <span>{{ colaborador.subordinados.length }}</span>
        <i class="bi" :class="expandido ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
      </div>
    </div>

    <!-- Linha conectora -->
    <div 
      v-if="colaborador.subordinados && colaborador.subordinados.length > 0 && expandido"
      class="connector-line"
    ></div>

    <!-- Subordinados -->
    <div 
      v-if="colaborador.subordinados && colaborador.subordinados.length > 0 && expandido"
      class="subordinados-container"
    >
      <NoColaborador
        v-for="subordinado in colaborador.subordinados"
        :key="subordinado.id"
        :colaborador="subordinado"
        :nivel="nivel + 1"
        :expandido="expandido"
        :destacado="destacado"
        @toggle="$emit('toggle', $event)"
        @selecionar="$emit('selecionar', $event)"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  colaborador: {
    type: Object,
    required: true
  },
  nivel: {
    type: Number,
    default: 0
  },
  expandido: {
    type: Boolean,
    default: true
  },
  destacado: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle', 'selecionar']);
</script>

<style scoped>
.no-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.colaborador-node {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 1rem;
  min-width: 200px;
  max-width: 200px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  margin: 10px;
}

.colaborador-node:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  border-color: rgba(102, 126, 234, 0.8);
}

.colaborador-node.is-gestor {
  border-color: rgba(255, 193, 7, 0.6);
  background: rgba(255, 193, 7, 0.08);
}

.colaborador-node.destacado {
  border-color: rgba(0, 255, 0, 0.8);
  box-shadow: 0 0 20px rgba(0, 255, 0, 0.4);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

/* Níveis hierárquicos */
.nivel-0 {
  border-width: 3px;
  border-color: rgba(255, 193, 7, 0.8);
  background: rgba(255, 193, 7, 0.12);
  min-width: 220px;
  max-width: 220px;
}

.nivel-1 {
  border-color: rgba(102, 126, 234, 0.6);
}

.nivel-2 {
  border-color: rgba(76, 175, 80, 0.6);
}

.nivel-3-plus {
  border-color: rgba(156, 39, 176, 0.6);
}

/* Foto */
.node-foto {
  margin-bottom: 0.75rem;
}

.foto-circular {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.foto-placeholder-circular {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1.5rem;
}

/* Info */
.node-info {
  margin-bottom: 0.5rem;
}

.node-nome {
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
  line-height: 1.2;
}

.node-cargo {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.75rem;
  margin: 0;
  line-height: 1.2;
}

.badge-gestor {
  display: inline-block;
  background: rgba(255, 193, 7, 0.9);
  color: #000;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  margin-top: 0.5rem;
}

/* Contador de subordinados */
.subordinados-count {
  position: absolute;
  bottom: -15px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(102, 126, 234, 0.9);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  cursor: pointer;
  z-index: 10;
  transition: all 0.3s ease;
}

.subordinados-count:hover {
  background: rgba(102, 126, 234, 1);
  transform: translateX(-50%) scale(1.1);
}

/* Linhas conectoras */
.connector-line {
  width: 2px;
  height: 30px;
  background: rgba(255, 255, 255, 0.3);
  margin: 0;
}

/* Container de subordinados */
.subordinados-container {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: flex-start;
  gap: 20px;
  margin-top: 10px;
  position: relative;
}

.subordinados-container::before {
  content: '';
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 40px);
  height: 2px;
  background: rgba(255, 255, 255, 0.3);
}

.subordinados-container > .no-container::before {
  content: '';
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 10px;
  background: rgba(255, 255, 255, 0.3);
}
</style>
