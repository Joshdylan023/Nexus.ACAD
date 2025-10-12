<template>
  <tbody>
    <tr v-for="i in rows" :key="i">
      <td v-for="j in columns" :key="j" class="ps-4">
        <div class="skeleton-line" :style="{ width: getRandomWidth() }"></div>
      </td>
    </tr>
  </tbody>
</template>

<script setup>
defineProps({
  rows: {
    type: Number,
    default: 5
  },
  columns: {
    type: Number,
    default: 4
  }
});

const getRandomWidth = () => {
  const widths = ['60%', '70%', '80%', '90%', '100%'];
  return widths[Math.floor(Math.random() * widths.length)];
};
</script>

<style scoped>
.skeleton-line {
  height: 18px;
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05) 25%,
    rgba(255, 255, 255, 0.1) 50%,
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 4px;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
