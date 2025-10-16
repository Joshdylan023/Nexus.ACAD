<template>
  <div class="modal fade show d-block" tabindex="-1" @click.self="$emit('close')">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content modal-glass">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-info-circle me-2"></i>Detalhes do Log
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <!-- Informações Gerais -->
          <div class="card card-info mb-3">
            <div class="card-body">
              <h6 class="mb-3">
                <i class="fas fa-info-circle me-2"></i>Informações Gerais
              </h6>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <strong>Ação:</strong>
                  <span class="badge ms-2" :class="getActionBadgeClass(log.action)">
                    {{ getActionName(log.action) }}
                  </span>
                </div>
                <div class="col-md-6 mb-2">
                  <strong>Módulo:</strong>
                  <span class="badge badge-module ms-2">
                    {{ getModuleName(log.module) }}
                  </span>
                </div>
                <div class="col-md-6 mb-2">
                  <strong>Usuário:</strong>
                  {{ log.user?.name || 'Sistema' }}
                </div>
                <div class="col-md-6 mb-2">
                  <strong>Data/Hora:</strong>
                  {{ formatDate(log.created_at) }}
                </div>
                <div class="col-md-6 mb-2">
                  <strong>IP:</strong>
                  {{ log.ip_address || 'N/A' }}
                </div>
                <div class="col-md-6 mb-2">
                  <strong>Entidade:</strong>
                  {{ log.auditable_type }}
                </div>
              </div>
            </div>
          </div>

          <!-- Descrição -->
          <div class="card card-info mb-3">
            <div class="card-body">
              <h6 class="mb-3">
                <i class="fas fa-align-left me-2"></i>Descrição
              </h6>
              <p class="mb-0">{{ log.description }}</p>
            </div>
          </div>

          <!-- Alterações (somente para updates) -->
          <div v-if="log.action === 'updated' && Object.keys(changes).length > 0" class="card card-warning">
            <div class="card-body">
              <h6 class="mb-3">
                <i class="fas fa-exchange-alt me-2"></i>Alterações Realizadas
              </h6>
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead>
                    <tr>
                      <th style="width: 30%;">Campo</th>
                      <th style="width: 35%;">Valor Anterior</th>
                      <th style="width: 35%;">Novo Valor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(change, field) in changes" :key="field">
                      <td><strong>{{ formatFieldName(field) }}</strong></td>
                      <td>
                        <span class="badge bg-danger">{{ formatValue(change.old) }}</span>
                      </td>
                      <td>
                        <span class="badge bg-success">{{ formatValue(change.new) }}</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Valores (para created) -->
          <div v-if="log.action === 'created' && log.new_values" class="card card-success">
            <div class="card-body">
              <h6 class="mb-3">
                <i class="fas fa-plus-circle me-2"></i>Dados Criados
              </h6>
              <pre class="json-viewer">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
            </div>
          </div>

          <!-- Valores (para deleted) -->
          <div v-if="log.action === 'deleted' && log.old_values" class="card card-danger">
            <div class="card-body">
              <h6 class="mb-3">
                <i class="fas fa-trash me-2"></i>Dados Removidos
              </h6>
              <pre class="json-viewer">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
            </div>
          </div>

          <!-- Metadados Técnicos -->
          <div class="card card-secondary">
            <div class="card-body">
              <h6 class="mb-3">
                <i class="fas fa-cogs me-2"></i>Metadados Técnicos
              </h6>
              <div class="row">
                <div class="col-12 mb-2">
                  <strong>User Agent:</strong>
                  <code class="ms-2">{{ log.user_agent || 'N/A' }}</code>
                </div>
                <div class="col-md-6 mb-2">
                  <strong>ID do Log:</strong>
                  {{ log.id }}
                </div>
                <div class="col-md-6 mb-2">
                  <strong>ID da Entidade:</strong>
                  {{ log.auditable_id }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show"></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  log: {
    type: Object,
    required: true
  }
});

defineEmits(['close']);

const changes = computed(() => {
  // ✅ Verificar se é uma atualização E tem valores old/new
  if (props.log.action !== 'updated') {
    console.log('Não é uma atualização');
    return {};
  }

  if (!props.log.old_values || !props.log.new_values) {
    console.log('Sem old_values ou new_values:', {
      old: props.log.old_values,
      new: props.log.new_values
    });
    return {};
  }

  const changesObj = {};
  
  // ✅ Campos que devem ser IGNORADOS
  const ignoredFields = [
    'updated_at',
    'created_at',
    'updated_by',
    'created_by',
    'password', // Nunca mostrar senha
    'remember_token',
  ];

  // ✅ Iterar pelos campos NOVOS
  for (const key in props.log.new_values) {
    // Pular campos ignorados
    if (ignoredFields.includes(key)) {
      continue;
    }

    const oldValue = props.log.old_values[key];
    const newValue = props.log.new_values[key];
    
    // ✅ IMPORTANTE: Converter para string para comparar
    // (resolve problema de "2" vs 2, "false" vs false, etc.)
    const oldStr = String(oldValue ?? '');
    const newStr = String(newValue ?? '');
    
    if (oldStr !== newStr) {
      changesObj[key] = {
        old: oldValue,
        new: newValue
      };
    }
  }

  console.log('Mudanças detectadas:', changesObj);
  return changesObj;
});

const getActionName = (action) => {
  const actions = {
    created: 'Criação',
    updated: 'Atualização',
    deleted: 'Exclusão',
    restored: 'Restauração'
  };
  return actions[action] || action;
};

const getModuleName = (module) => {
  const modules = {
    institucional: 'Institucional',
    pessoas_acessos: 'Pessoas & Acessos',
    academico: 'Acadêmico',
    financeiro: 'Financeiro',
    estagios: 'Estágios',
    sistema: 'Sistema'
  };
  return modules[module] || module;
};

const getActionBadgeClass = (action) => {
  const classes = {
    created: 'bg-success',
    updated: 'bg-info',
    deleted: 'bg-danger',
    restored: 'bg-warning'
  };
  return classes[action] || 'bg-secondary';
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
};

const formatFieldName = (field) => {
  // Mapear nomes de campos para nomes amigáveis
  const fieldNames = {
    'unidade_organizacional_type': 'Unidade Organizacional (Tipo)',
    'unidade_organizacional_id': 'Unidade Organizacional (ID)',
    'unidade_lotacao_type': 'Unidade de Lotação (Tipo)',
    'unidade_lotacao_id': 'Unidade de Lotação (ID)',
    'setor_vinculo_id': 'Setor de Vínculo',
    'gestor_imediato_id': 'Gestor Imediato',
    'is_gestor': 'É Gestor?',
    'matricula_funcional': 'Matrícula Funcional',
    'email_funcional': 'Email Funcional',
    'cargo': 'Cargo',
    'data_admissao': 'Data de Admissão',
    'status': 'Status',
    'foto_registro_rh': 'Foto RH',
    'user_id': 'ID do Usuário',
    'nome': 'Nome',
    'sigla': 'Sigla',
    'cnpj': 'CNPJ',
    'razao_social': 'Razão Social',
    'nome_fantasia': 'Nome Fantasia',
  };

  return fieldNames[field] || field
    .replace(/_/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase());
};

const formatValue = (value) => {
  if (value === null || value === undefined) return '(vazio)';
  if (value === '') return '(vazio)';
  if (value === true || value === 'true' || value === '1' || value === 1) return 'Sim';
  if (value === false || value === 'false' || value === '0' || value === 0) return 'Não';
  if (typeof value === 'object') return JSON.stringify(value);
  return value;
};
</script>

<style scoped>
.modal-glass {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: white;
}

.modal-header {
  background: rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.18);
}

.modal-footer {
  background: rgba(255, 255, 255, 0.05);
  border-top: 1px solid rgba(255, 255, 255, 0.18);
}

.modal-title {
  color: white;
}

.card-info {
  background: rgba(13, 110, 253, 0.1);
  border: 1px solid rgba(13, 110, 253, 0.3);
}

.card-warning {
  background: rgba(255, 193, 7, 0.1);
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.card-success {
  background: rgba(25, 135, 84, 0.1);
  border: 1px solid rgba(25, 135, 84, 0.3);
}

.card-danger {
  background: rgba(220, 53, 69, 0.1);
  border: 1px solid rgba(220, 53, 69, 0.3);
}

.card-secondary {
  background: rgba(108, 117, 125, 0.1);
  border: 1px solid rgba(108, 117, 125, 0.3);
}

.card-body h6 {
  color: white;
  font-weight: 600;
}

.badge-module {
  background: rgba(108, 117, 125, 0.3);
  color: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(108, 117, 125, 0.5);
}

.table {
  color: white;
  margin-bottom: 0;
}

.table th {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
  font-weight: 600;
}

.table td {
  border-color: rgba(255, 255, 255, 0.1);
}

.table-bordered {
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.json-viewer {
  background: rgba(0, 0, 0, 0.3);
  color: #0dcaf0;
  padding: 1rem;
  border-radius: 6px;
  max-height: 300px;
  overflow-y: auto;
  font-size: 0.875rem;
  margin-bottom: 0;
}

code {
  background: rgba(0, 0, 0, 0.3);
  color: #0dcaf0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.7);
}
</style>
