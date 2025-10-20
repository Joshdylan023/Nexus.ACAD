<template>
  <div class="field-mapping-editor">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">Mapeamento de Campos</h4>
        <p class="text-muted mb-0">
          Configure como os campos do sistema externo serão mapeados para o sistema interno
        </p>
      </div>
      <button 
        class="btn btn-sm btn-outline-primary"
        @click="addMapping"
      >
        <i class="bi bi-plus-circle me-2"></i>
        Adicionar Campo
      </button>
    </div>

    <!-- Preview dos dados do sistema externo -->
    <div v-if="sampleData" class="card glass-effect mb-4">
      <div class="card-body">
        <h6 class="mb-3">
          <i class="bi bi-eye me-2"></i>
          Dados de Exemplo do Sistema Externo
        </h6>
        <pre class="bg-dark text-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">{{ sampleData }}</pre>
        <button 
          class="btn btn-sm btn-outline-light mt-2"
          @click="detectFields"
        >
          <i class="bi bi-magic me-2"></i>
          Detectar Campos Automaticamente
        </button>
      </div>
    </div>

    <!-- Lista de Mapeamentos -->
    <div class="mappings-list">
      <TransitionGroup name="list">
        <div 
          v-for="(mapping, index) in mappings" 
          :key="mapping.id"
          class="card glass-effect mb-3"
        >
          <div class="card-body">
            <div class="row align-items-center">
              <!-- Campo de Origem (Sistema Externo) -->
              <div class="col-md-4">
                <label class="form-label small text-muted">
                  <i class="bi bi-arrow-down-circle me-1"></i>
                  Campo de Origem
                </label>
                <select 
                  v-model="mapping.source_field" 
                  class="form-select form-select-sm"
                  @change="onSourceFieldChange(mapping)"
                >
                  <option value="">Selecione...</option>
                  <option 
                    v-for="field in detectedFields" 
                    :key="field"
                    :value="field"
                  >
                    {{ field }}
                  </option>
                </select>
                <input 
                  v-if="!detectedFields.length"
                  v-model="mapping.source_field"
                  type="text"
                  class="form-control form-control-sm mt-2"
                  placeholder="Digite o nome do campo"
                />
              </div>

              <!-- Seta de Mapeamento -->
              <div class="col-md-1 text-center">
                <i class="bi bi-arrow-right text-primary fs-4"></i>
              </div>

              <!-- Campo de Destino (Sistema Interno) -->
              <div class="col-md-4">
                <label class="form-label small text-muted">
                  <i class="bi bi-arrow-up-circle me-1"></i>
                  Campo de Destino
                </label>
                <select 
                  v-model="mapping.target_field" 
                  class="form-select form-select-sm"
                  required
                >
                  <option value="">Selecione...</option>
                  <optgroup label="Campos Obrigatórios">
                    <option value="matricula_funcional">Matrícula Funcional *</option>
                    <option value="cpf">CPF</option>
                    <option value="nome_completo">Nome Completo *</option>
                    <option value="email">Email</option>
                  </optgroup>
                  <optgroup label="Dados Profissionais">
                    <option value="cargo">Cargo</option>
                    <option value="departamento">Departamento</option>
                    <option value="data_admissao">Data de Admissão</option>
                    <option value="status">Status</option>
                  </optgroup>
                  <optgroup label="Dados Pessoais">
                    <option value="telefone">Telefone</option>
                    <option value="data_nascimento">Data de Nascimento</option>
                    <option value="endereco">Endereço</option>
                  </optgroup>
                </select>
              </div>

              <!-- Configurações Adicionais -->
              <div class="col-md-2">
                <div class="form-check form-switch">
                  <input 
                    v-model="mapping.is_required" 
                    class="form-check-input" 
                    type="checkbox"
                    :id="`required-${mapping.id}`"
                  />
                  <label class="form-check-label small" :for="`required-${mapping.id}`">
                    Obrigatório
                  </label>
                </div>
                <select 
                  v-model="mapping.transformation" 
                  class="form-select form-select-sm mt-2"
                >
                  <option value="">Sem transformação</option>
                  <option value="uppercase">MAIÚSCULAS</option>
                  <option value="lowercase">minúsculas</option>
                  <option value="trim">Remover espaços</option>
                  <option value="date">Formato de data</option>
                </select>
              </div>

              <!-- Ações -->
              <div class="col-md-1 text-end">
                <button 
                  class="btn btn-sm btn-outline-danger"
                  @click="removeMapping(index)"
                  title="Remover mapeamento"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>

            <!-- Preview de valor -->
            <div v-if="mapping.source_field && sampleData" class="mt-3 p-2 bg-light rounded">
              <small class="text-muted">Preview:</small>
              <code class="ms-2">{{ getPreviewValue(mapping) }}</code>
            </div>
          </div>
        </div>
      </TransitionGroup>

      <!-- Empty State -->
      <div v-if="!mappings.length" class="text-center py-5">
        <i class="bi bi-link-45deg display-1 text-muted"></i>
        <p class="text-muted">Nenhum mapeamento configurado</p>
        <button class="btn btn-primary" @click="addMapping">
          <i class="bi bi-plus-circle me-2"></i>
          Adicionar Primeiro Mapeamento
        </button>
      </div>
    </div>

    <!-- Footer com Ações -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div>
        <span class="badge bg-primary me-2">{{ mappings.length }} mapeamentos</span>
        <span v-if="requiredMappingsCount < 2" class="badge bg-warning">
          ⚠️ Configure ao menos Matrícula e Nome
        </span>
      </div>
      <div>
        <button class="btn btn-outline-secondary me-2" @click="testMapping">
          <i class="bi bi-play-circle me-2"></i>
          Testar Mapeamento
        </button>
        <button 
          class="btn btn-primary" 
          @click="saveMappings"
          :disabled="!canSave"
        >
          <i class="bi bi-check-circle me-2"></i>
          Salvar Configuração
        </button>
      </div>
    </div>

    <!-- Modal de Teste -->
    <div v-if="showTestModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content glass-effect">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-clipboard-check me-2"></i>
              Resultado do Teste de Mapeamento
            </h5>
            <button type="button" class="btn-close" @click="showTestModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <h6 class="text-muted">Dados de Entrada</h6>
                <pre class="bg-dark text-light p-3 rounded small">{{ sampleData }}</pre>
              </div>
              <div class="col-md-6">
                <h6 class="text-muted">Dados Mapeados</h6>
                <pre class="bg-success text-light p-3 rounded small">{{ testResult }}</pre>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showTestModal = false">Fechar</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showTestModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  integrationId: {
    type: Number,
    required: true
  },
  initialMappings: {
    type: Array,
    default: () => []
  },
  sampleData: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['save', 'test']);

// Estado
const mappings = ref([]);
const detectedFields = ref([]);
const showTestModal = ref(false);
const testResult = ref(null);
let nextId = 1;

// Inicializar mapeamentos
if (props.initialMappings && props.initialMappings.length) {
  mappings.value = props.initialMappings.map(m => ({
    ...m,
    id: nextId++
  }));
}

// Computed
const requiredMappingsCount = computed(() => {
  return mappings.value.filter(m => 
    ['matricula_funcional', 'nome_completo'].includes(m.target_field)
  ).length;
});

const canSave = computed(() => {
  return mappings.value.length > 0 && 
         requiredMappingsCount.value >= 2 &&
         mappings.value.every(m => m.source_field && m.target_field);
});

// Métodos
const addMapping = () => {
  mappings.value.push({
    id: nextId++,
    entity_type: 'colaborador',
    source_field: '',
    target_field: '',
    is_required: false,
    transformation: '',
    default_value: null
  });
};

const removeMapping = (index) => {
  mappings.value.splice(index, 1);
};

const detectFields = () => {
  if (!props.sampleData || !props.sampleData.data) return;
  
  // ✅ CORRIGIDO: Usar o array 'fields' ou as chaves do primeiro objeto de dados
  const fields = props.sampleData.fields || Object.keys(props.sampleData.data[0] || {});
  detectedFields.value = fields;
  
  // Auto-mapear campos óbvios
  const autoMappings = {
    'matricula': 'matricula_funcional',
    'nome': 'nome_completo',
    'cpf': 'cpf',
    'email': 'email',
    'cargo': 'cargo',
    'departamento': 'departamento',
  };
  
  fields.forEach(field => {
    const normalized = field.toLowerCase();
    const targetField = autoMappings[normalized];
    
    if (targetField && !mappings.value.find(m => m.target_field === targetField)) {
      mappings.value.push({
        id: nextId++,
        entity_type: 'colaborador',
        source_field: field,
        target_field: targetField,
        is_required: ['matricula_funcional', 'nome_completo'].includes(targetField),
        transformation: '',
        default_value: null
      });
    }
  });
};

const getPreviewValue = (mapping) => {
  if (!props.sampleData || !props.sampleData.data || !props.sampleData.data.length || !mapping.source_field) return 'N/A';
  
  // ✅ CORRIGIDO: Acessar o primeiro objeto de dados
  let value = (props.sampleData.data[0] || {})[mapping.source_field];
  
  // Aplicar transformação
  if (mapping.transformation && value) {
    switch (mapping.transformation) {
      case 'uppercase':
        value = value.toUpperCase();
        break;
      case 'lowercase':
        value = value.toLowerCase();
        break;
      case 'trim':
        value = value.trim();
        break;
    }
  }
  
  return value || 'null';
};

const onSourceFieldChange = (mapping) => {
  // Sugerir campo de destino baseado no nome
  const suggestions = {
    'matricula': 'matricula_funcional',
    'registration': 'matricula_funcional',
    'nome': 'nome_completo',
    'name': 'nome_completo',
    'cpf': 'cpf',
    'email': 'email',
    'cargo': 'cargo',
    'position': 'cargo',
    'job': 'cargo',
  };
  
  const normalized = mapping.source_field.toLowerCase();
  if (suggestions[normalized] && !mapping.target_field) {
    mapping.target_field = suggestions[normalized];
  }
};

const testMapping = () => {
  if (!props.sampleData || !props.sampleData.data || !props.sampleData.data.length) {
    alert('Nenhum dado de exemplo disponível');
    return;
  }
  
  const mapped = {};
  const sampleRecord = props.sampleData.data[0] || {};
  
  mappings.value.forEach(mapping => {
    if (mapping.source_field && mapping.target_field) {
      // ✅ CORRIGIDO: Acessar o primeiro objeto de dados
      let value = sampleRecord[mapping.source_field];
      
      if (mapping.transformation && value) {
        switch (mapping.transformation) {
          case 'uppercase':
            value = String(value).toUpperCase();
            break;
          case 'lowercase':
            value = String(value).toLowerCase();
            break;
          case 'trim':
            value = String(value).trim();
            break;
        }
      }
      
      mapped[mapping.target_field] = value;
    }
  });
  
  testResult.value = mapped;
  showTestModal.value = true;
};

const saveMappings = async () => {
  if (!canSave.value) {
    alert('Configure ao menos Matrícula e Nome Completo');
    return;
  }
  
  try {
    const payload = mappings.value.map(m => ({
      entity_type: m.entity_type,
      source_field: m.source_field,
      target_field: m.target_field,
      is_required: m.is_required,
      transformation: m.transformation || null,
      default_value: m.default_value
    }));
    
    await axios.put(`/api/v1/hr/integrations/${props.integrationId}`, {
      field_mapping: payload
    });
    
    emit('save', payload);
    
    alert('✅ Mapeamento salvo com sucesso!');
  } catch (error) {
    console.error('Erro ao salvar mapeamento:', error);
    alert('❌ Erro ao salvar mapeamento');
  }
};

// Detectar campos automaticamente quando sampleData mudar
watch(() => props.sampleData, (newVal) => {
  if (newVal && !detectedFields.value.length) {
    detectFields();
  }
}, { immediate: true });
</script>

<style scoped>
.field-mapping-editor {
  padding: 1.5rem;
}

.glass-effect {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

pre {
  font-size: 0.85rem;
  margin: 0;
}

.modal.show {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
