<template>
  <div>
    <PageHeader 
      title="Hierarquia Organizacional"
      :breadcrumbs="[
        { label: 'Estrutura Organizacional' },
        { label: 'Hierarquia' }
      ]"
    >
      <template #actions>
        <button @click="fetchData" class="btn btn-primary">
          <i class="bi bi-arrow-clockwise"></i> Atualizar
        </button>
      </template>
    </PageHeader>

    <div class="card card-glass mb-4">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <i class="bi bi-info-circle text-info me-2" style="font-size: 1.25rem;"></i>
          <span class="text-white-50">Visualize a estrutura completa da organização. Clique nos cards para expandir/recolher.</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="card card-glass">
      <div class="card-body">
        <TableSkeleton :columns="1" :rows="3" />
      </div>
    </div>

    <div v-else class="hierarchy-wrapper">
      <div v-for="grupo in grupos" :key="grupo.id" class="hierarchy-item">
        <!-- Card do Grupo -->
        <div 
          class="hierarchy-node grupo-node"
          @click="toggleNode('grupo', grupo.id)"
        >
          <div class="node-icon">
            <i class="bi bi-building"></i>
          </div>
          <div class="node-content">
            <div class="node-header">
              <span class="node-type">Grupo Educacional</span>
              <i 
                class="bi" 
                :class="isExpanded('grupo', grupo.id) ? 'bi-chevron-up' : 'bi-chevron-down'"
              ></i>
            </div>
            <h6 class="node-title">{{ grupo.nome }}</h6>
            <p class="node-subtitle">{{ grupo.cnpj }}</p>
            <span class="node-badge bg-primary">
              {{ getMantenedorasByGrupo(grupo.id).length }} mantenedoras
            </span>
          </div>
        </div>

        <!-- Mantenedoras deste Grupo -->
        <transition name="expand">
          <div v-show="isExpanded('grupo', grupo.id)" class="hierarchy-children">
            <div v-for="mant in getMantenedorasByGrupo(grupo.id)" :key="mant.id" class="hierarchy-item">
              <div 
                class="hierarchy-node mantenedora-node"
                @click.stop="toggleNode('mant', mant.id)"
              >
                <div class="node-icon">
                  <i class="bi bi-diagram-3"></i>
                </div>
                <div class="node-content">
                  <div class="node-header">
                    <span class="node-type">Mantenedora</span>
                    <i 
                      class="bi" 
                      :class="isExpanded('mant', mant.id) ? 'bi-chevron-up' : 'bi-chevron-down'"
                    ></i>
                  </div>
                  <h6 class="node-title">{{ mant.razao_social }}</h6>
                  <p class="node-subtitle">{{ mant.nome_fantasia }}</p>
                  <span class="node-badge bg-info">
                    {{ getInstituicoesByMantenedora(mant.id).length }} instituições
                  </span>
                </div>
              </div>

              <!-- Instituições desta Mantenedora -->
              <transition name="expand">
                <div v-show="isExpanded('mant', mant.id)" class="hierarchy-children">
                  <div v-for="inst in getInstituicoesByMantenedora(mant.id)" :key="inst.id" class="hierarchy-item">
                    <div 
                      class="hierarchy-node instituicao-node"
                      @click.stop="toggleNode('inst', inst.id)"
                    >
                      <div class="node-icon">
                        <i class="bi bi-mortarboard"></i>
                      </div>
                      <div class="node-content">
                        <div class="node-header">
                          <span class="node-type">Instituição</span>
                          <i 
                            class="bi" 
                            :class="isExpanded('inst', inst.id) ? 'bi-chevron-up' : 'bi-chevron-down'"
                          ></i>
                        </div>
                        <h6 class="node-title">{{ inst.razao_social }}</h6>
                        <p class="node-subtitle">{{ inst.nome_fantasia }}</p>
                        <span class="node-badge bg-success">
                          {{ getCampiByInstituicao(inst.id).length }} campi
                        </span>
                      </div>
                    </div>

                    <!-- Campi desta Instituição -->
                    <transition name="expand">
                      <div v-show="isExpanded('inst', inst.id)" class="hierarchy-children">
                        <div v-for="campus in getCampiByInstituicao(inst.id)" :key="campus.id" class="hierarchy-item">
                          <div class="hierarchy-node campus-node">
                            <div class="node-icon">
                              <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="node-content">
                              <div class="node-header">
                                <span class="node-type">Campus</span>
                              </div>
                              <h6 class="node-title">{{ campus.nome }}</h6>
                              <p class="node-subtitle">{{ campus.endereco_completo }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </transition>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';

const loading = ref(true);
const grupos = ref([]);
const mantenedoras = ref([]);
const instituicoes = ref([]);
const campi = ref([]);
const expandedNodes = ref({});

const toggleNode = (type, id) => {
  const key = `${type}-${id}`;
  expandedNodes.value[key] = !expandedNodes.value[key];
};

const isExpanded = (type, id) => {
  return expandedNodes.value[`${type}-${id}`] || false;
};

const getMantenedorasByGrupo = (grupoId) => {
  return mantenedoras.value.filter(m => m.grupo_educacional_id === grupoId);
};

const getInstituicoesByMantenedora = (mantId) => {
  return instituicoes.value.filter(i => i.mantenedora_id === mantId);
};

const getCampiByInstituicao = (instId) => {
  return campi.value.filter(c => c.instituicao_id === instId);
};

const fetchData = async () => {
  try {
    loading.value = true;
    const [gruposRes, mantRes, instRes, campiRes] = await Promise.all([
      axios.get('/api/v1/grupos-educacionais'),
      axios.get('/api/v1/mantenedoras'),
      axios.get('/api/v1/instituicoes'),
      axios.get('/api/v1/campi')
    ]);

    grupos.value = gruposRes.data;
    mantenedoras.value = mantRes.data;
    instituicoes.value = instRes.data;
    campi.value = campiRes.data;
  } catch (error) {
    console.error('Erro ao buscar dados:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.hierarchy-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.hierarchy-item {
  position: relative;
}

.hierarchy-node {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 
    0 4px 15px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.hierarchy-node:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 8px 25px rgba(0, 0, 0, 0.2),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
  border-color: rgba(102, 126, 234, 0.3);
}

.node-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  font-size: 1.5rem;
}

.grupo-node .node-icon {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
  color: #667eea;
}

.mantenedora-node .node-icon {
  background: rgba(23, 162, 184, 0.2);
  color: #17a2b8;
}

.instituicao-node .node-icon {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.campus-node .node-icon {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.node-content {
  flex: 1;
  min-width: 0;
}

.node-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.node-type {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: rgba(255, 255, 255, 0.4);
}

.node-header i {
  color: rgba(255, 255, 255, 0.3);
  font-size: 1rem;
  transition: transform 0.3s ease;
}

.node-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 0.25rem;
  line-height: 1.3;
}

.node-subtitle {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.45);
  margin-bottom: 0.5rem;
}

.node-badge {
  display: inline-block;
  font-size: 0.7rem;
  padding: 0.25rem 0.65rem;
  border-radius: 0.375rem;
  font-weight: 600;
}

.hierarchy-children {
  margin-top: 1rem;
  margin-left: 3rem;
  padding-left: 1.5rem;
  border-left: 2px solid rgba(102, 126, 234, 0.15);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Animações de expansão */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  margin-top: 0;
}

.expand-enter-to,
.expand-leave-from {
  opacity: 1;
  max-height: 2000px;
  margin-top: 1rem;
}

/* Responsividade */
@media (max-width: 768px) {
  .hierarchy-children {
    margin-left: 1rem;
    padding-left: 1rem;
  }
  
  .node-icon {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }
}
</style>
