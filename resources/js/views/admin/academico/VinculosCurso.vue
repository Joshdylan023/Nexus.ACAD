<template>
  <div>
    <h5 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>Atos Regulatórios</h5>
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>Número</th>
          <th>Tipo</th>
          <th>Data</th>
          <th>Validade</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ato in atos" :key="ato.id">
          <td>{{ ato.numero_processo }}</td>
          <td>{{ ato.tipo }}</td>
          <td>{{ ato.data_publicacao }}</td>
          <td>{{ ato.data_validade || '—' }}</td>
          <td>
            <span class="badge" :class="getStatusClass(ato.status)">{{ ato.status }}</span>
          </td>
        </tr>
        <tr v-if="atos.length === 0">
          <td colspan="5" class="text-center text-muted">Nenhum ato encontrado.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
const props = defineProps({ cursoId: { type: [String, Number], required: true } })
const atos = ref([])
const getStatusClass = status => status === 'Vigente' ? 'bg-success' : 'bg-secondary'
const fetchAtos = async () => {
  const { data } = await axios.get(`/api/v1/cursos/${props.cursoId}`)
  atos.value = data.atos_regulatorios || []
}
onMounted(fetchAtos)
watch(() => props.cursoId, fetchAtos)
</script>
