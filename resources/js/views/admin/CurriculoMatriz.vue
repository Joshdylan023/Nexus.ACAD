<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Gerenciar Matriz Curricular para: {{ curriculo.nome_matriz }}</h2>
      <button @click="router.go(-1)" class="btn btn-secondary">Voltar</button>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            Disciplinas Disponíveis
          </div>
          <ul class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
            <li v-for="disciplina in disciplinasDisponiveis" :key="disciplina.id" class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ disciplina.codigo }}</strong> - {{ disciplina.nome }} ({{ disciplina.carga_horaria_total }}h)
              </div>
              <button @click="abrirModalAdicionar(disciplina)" class="btn btn-sm btn-primary">
                Adicionar
              </button>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            Estrutura da Matriz
          </div>
          <div class="card-body">
            <div v-for="periodo in periodos" :key="periodo" class="mb-4">
              <h4>{{ periodo }}º Período</h4>
              <ul class="list-group">
                <li v-for="item in disciplinasNaMatriz.filter(d => d.periodo_sugerido === periodo)" :key="item.id" class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ item.disciplina.codigo }}</strong> - {{ item.disciplina.nome }}
                  </div>
                  <button @click="removerDisciplina(item.id)" class="btn btn-sm btn-danger">
                    Remover
                  </button>
                </li>
              </ul>
              <hr>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="addDisciplinaModal" tabindex="-1" aria-labelledby="addDisciplinaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addDisciplinaModalLabel">Adicionar Disciplina: {{ disciplinaSelecionada?.nome }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="adicionarDisciplina">
              <div class="mb-3">
                <label for="periodo_sugerido" class="form-label">Período Sugerido</label>
                <select class="form-select" v-model="formDisciplina.periodo_sugerido" required>
                  <option v-for="periodo in periodosValidos" :key="periodo" :value="periodo">
                    {{ periodo }}º Período
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label for="tipo_disciplina" class="form-label">Tipo da Disciplina</label>
                <select class="form-select" v-model="formDisciplina.tipo_disciplina" required>
                  <option value="Obrigatória">Obrigatória</option>
                  <option value="Eletiva">Eletiva</option>
                </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar Adição</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { Modal } from 'bootstrap';

const route = useRoute();
const router = useRouter();

const curriculoId = ref(route.params.id);
const curriculo = ref({});
const disciplinas = ref([]);
const disciplinasNaMatriz = ref([]);
const loading = ref(true);

const addDisciplinaModal = ref(null);
const disciplinaSelecionada = ref(null);
const formDisciplina = ref({
    periodo_sugerido: 1,
    tipo_disciplina: 'Obrigatória',
});

const disciplinasDisponiveis = computed(() => {
    const idsNaMatriz = disciplinasNaMatriz.value.map(item => item.disciplina_id);
    return disciplinas.value.filter(d => !idsNaMatriz.includes(d.id));
});

const periodos = computed(() => {
    if (disciplinasNaMatriz.value.length === 0) return [];
    const p = disciplinasNaMatriz.value.map(d => d.periodo_sugerido);
    return [...new Set(p)].sort((a, b) => a - b);
});

const fetchCurriculo = async () => {
  try {
    const response = await axios.get(`/api/v1/curriculos/${curriculoId.value}`);
    curriculo.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar currículo:", error);
  }
};

const fetchDisciplinas = async () => {
  try {
    const response = await axios.get('/api/v1/disciplinas');
    disciplinas.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar disciplinas:", error);
  }
};

const fetchDisciplinasDaMatriz = async () => {
  try {
    loading.value = true;
    console.log("Buscando disciplinas da matriz para curriculo_id:", curriculoId.value);
    const response = await axios.get(`/api/v1/curriculos-disciplinas?curriculo_id=${curriculoId.value}`);
    console.log("Disciplinas da matriz recebidas:", response.data);
    disciplinasNaMatriz.value = response.data.data;
  } catch (error) {
    console.error("Erro ao buscar disciplinas da matriz:", error);
  } finally {
    loading.value = false;
  }
};

const periodosValidos = computed(() => {
    if (curriculo.value.curso) {
        return Array.from({ length: curriculo.value.curso.duracao_padrao_semestres }, (_, i) => i + 1);
    }
    return [];
});


const abrirModalAdicionar = (disciplina) => {
    disciplinaSelecionada.value = disciplina;
    formDisciplina.value = {
        periodo_sugerido: 1,
        tipo_disciplina: 'Obrigatória',
    };
    if (addDisciplinaModal.value) {
        addDisciplinaModal.value.show();
    }
};

const adicionarDisciplina = async () => {
    const payload = {
        curriculo_id: curriculoId.value,
        disciplina_id: disciplinaSelecionada.value.id,
        periodo_sugerido: formDisciplina.value.periodo_sugerido,
        tipo_disciplina: formDisciplina.value.tipo_disciplina,
    };
    console.log("Payload para adicionar disciplina:", payload);
    try {
        await axios.post('/api/v1/curriculos-disciplinas', payload);
        console.log("Disciplina adicionada com sucesso!");
        await fetchDisciplinasDaMatriz();
        addDisciplinaModal.value.hide();
    } catch (error) {
        console.error("Erro ao adicionar disciplina:", error);
        alert('Erro ao adicionar disciplina. Verifique se ela já não foi inserida.');
    }
};

const removerDisciplina = async (id) => {
    if (confirm("Tem certeza que deseja remover esta disciplina do currículo?")) {
        try {
            await axios.delete(`/api/v1/curriculos-disciplinas/${id}`);
            await fetchDisciplinasDaMatriz();
        } catch (error) {
            console.error("Erro ao remover disciplina:", error);
        }
    }
};

onMounted(() => {
    fetchCurriculo();
    fetchDisciplinas();
    fetchDisciplinasDaMatriz();
    
    const modalElement = document.getElementById('addDisciplinaModal');
    if (modalElement) {
        addDisciplinaModal.value = new Modal(modalElement);
    }
});
</script>