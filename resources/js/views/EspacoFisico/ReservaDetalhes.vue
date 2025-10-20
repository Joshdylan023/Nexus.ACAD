<template>
  <teleport to="body">
    <div class="modal-overlay" @click="$emit('close')">
      <div class="modal-container card-glass" @click.stop>
        <!-- Header -->
        <div class="modal-header">
          <div>
            <h4 class="text-white mb-1">
              <i class="bi bi-calendar-check me-2"></i>
              Detalhes da Reserva #{{ reserva.id }}
            </h4>
            <span :class="getStatusBadge(reserva.status)">
              {{ reserva.status }}
            </span>
          </div>
          <button @click="$emit('close')" class="btn-close btn-close-white"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <!-- Informações do Espaço -->
          <div class="info-section mb-4">
            <h5 class="text-white mb-3">
              <i class="bi bi-door-open me-2"></i>
              Espaço Físico
            </h5>
            <div class="card card-dark mb-2">
              <div class="card-body">
                <h6 class="text-white mb-3">{{ reserva.espaco_fisico?.nome }}</h6>
                
                <!-- Hierarquia Completa -->
                <div class="row g-2 mb-3">
                  <!-- Instituição -->
                  <div class="col-md-6" v-if="reserva.espaco_fisico?.andar?.bloco?.predio?.campus?.instituicao">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-building me-1"></i>
                      <strong>Instituição:</strong> 
                      {{ reserva.espaco_fisico.andar.bloco.predio.campus.instituicao.nome_fantasia || 
                         reserva.espaco_fisico.andar.bloco.predio.campus.instituicao.razao_social }}
                    </p>
                  </div>
                  
                  <!-- Campus -->
                  <div class="col-md-6" v-if="reserva.espaco_fisico?.andar?.bloco?.predio?.campus">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-geo-alt me-1"></i>
                      <strong>Campus:</strong> 
                      {{ reserva.espaco_fisico.andar.bloco.predio.campus.nome }}
                    </p>
                  </div>
                  
                  <!-- Prédio -->
                  <div class="col-md-6" v-if="reserva.espaco_fisico?.andar?.bloco?.predio">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-buildings me-1"></i>
                      <strong>Prédio:</strong> 
                      {{ reserva.espaco_fisico.andar.bloco.predio.nome }}
                    </p>
                  </div>
                  
                  <!-- Bloco -->
                  <div class="col-md-6" v-if="reserva.espaco_fisico?.andar?.bloco">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-bricks me-1"></i>
                      <strong>Bloco:</strong> 
                      {{ reserva.espaco_fisico.andar.bloco.nome }}
                    </p>
                  </div>
                  
                  <!-- Andar -->
                  <div class="col-md-6" v-if="reserva.espaco_fisico?.andar">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-layers me-1"></i>
                      <strong>Andar:</strong> 
                      {{ reserva.espaco_fisico.andar.nome_completo || reserva.espaco_fisico.andar.nome }}
                    </p>
                  </div>
                  
                  <!-- Código do Espaço -->
                  <div class="col-md-6">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-hash me-1"></i>
                      <strong>Código:</strong> 
                      {{ reserva.espaco_fisico?.codigo }}
                    </p>
                  </div>
                </div>
                
                <!-- Detalhes do Espaço -->
                <div class="row g-2">
                  <div class="col-md-6">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-tag me-1"></i>
                      <strong>Tipo:</strong> 
                      {{ reserva.espaco_fisico?.tipo }}
                    </p>
                  </div>
                  
                  <div class="col-md-6">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-people me-1"></i>
                      <strong>Capacidade:</strong> 
                      {{ reserva.espaco_fisico?.capacidade || '-' }} pessoas
                    </p>
                  </div>
                  
                  <div class="col-md-6" v-if="reserva.espaco_fisico?.area">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-rulers me-1"></i>
                      <strong>Área:</strong> 
                      {{ reserva.espaco_fisico.area }} m²
                    </p>
                  </div>
                  
                  <div class="col-md-6">
                    <p class="text-white-50 small mb-0">
                      <i class="bi bi-check-circle me-1"></i>
                      <strong>Status:</strong> 
                      <span :class="getEspacoStatusClass(reserva.espaco_fisico?.status)">
                        {{ reserva.espaco_fisico?.status }}
                      </span>
                    </p>
                  </div>
                </div>
                
                <!-- Recursos do Espaço -->
                <div class="mt-3" v-if="hasRecursos">
                  <p class="text-white-50 small mb-2">
                    <i class="bi bi-cpu me-1"></i>
                    <strong>Recursos disponíveis:</strong>
                  </p>
                  <div class="d-flex flex-wrap gap-1">
                    <span v-if="reserva.espaco_fisico?.ar_condicionado" class="badge bg-info">
                      <i class="bi bi-wind"></i> Ar Condicionado
                    </span>
                    <span v-if="reserva.espaco_fisico?.projetor" class="badge bg-info">
                      <i class="bi bi-projector"></i> Projetor
                    </span>
                    <span v-if="reserva.espaco_fisico?.lousa_digital" class="badge bg-info">
                      <i class="bi bi-tv"></i> Lousa Digital
                    </span>
                    <span v-if="reserva.espaco_fisico?.wifi" class="badge bg-info">
                      <i class="bi bi-wifi"></i> Wi-Fi
                    </span>
                    <span v-if="reserva.espaco_fisico?.computadores" class="badge bg-info">
                      <i class="bi bi-pc-display"></i> {{ reserva.espaco_fisico.quantidade_computadores || '' }} Computadores
                    </span>
                    <span v-if="reserva.espaco_fisico?.sistema_som" class="badge bg-info">
                      <i class="bi bi-volume-up"></i> Sistema de Som
                    </span>
                    <span v-if="reserva.espaco_fisico?.acessibilidade" class="badge bg-info">
                      <i class="bi bi-universal-access"></i> Acessibilidade
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Informações da Reserva -->
          <div class="info-section mb-4">
            <h5 class="text-white mb-3">
              <i class="bi bi-info-circle me-2"></i>
              Informações da Reserva
            </h5>
            
            <div class="row g-3">
              <div class="col-md-12">
                <div class="info-item">
                  <label class="text-white-50 small">Motivo/Título:</label>
                  <p class="text-white mb-0">{{ reserva.motivo }}</p>
                </div>
              </div>

              <div class="col-md-12" v-if="reserva.descricao">
                <div class="info-item">
                  <label class="text-white-50 small">Descrição:</label>
                  <p class="text-white mb-0">{{ reserva.descricao }}</p>
                </div>
              </div>

              <div class="col-md-6">
                <div class="info-item">
                  <label class="text-white-50 small">Período:</label>
                  <p class="text-white mb-0">
                    <i class="bi bi-calendar-event me-1"></i>
                    {{ formatDate(reserva.data_inicio) }} a {{ formatDate(reserva.data_fim) }}
                  </p>
                </div>
              </div>

              <div class="col-md-6">
                <div class="info-item">
                  <label class="text-white-50 small">Horário:</label>
                  <p class="text-white mb-0">
                    <i class="bi bi-clock me-1"></i>
                    {{ reserva.hora_inicio?.substring(0, 5) }} às {{ reserva.hora_fim?.substring(0, 5) }}
                  </p>
                </div>
              </div>

              <div class="col-md-6" v-if="reserva.finalidade">
                <div class="info-item">
                  <label class="text-white-50 small">Finalidade:</label>
                  <p class="text-white mb-0">{{ reserva.finalidade }}</p>
                </div>
              </div>

              <div class="col-md-6" v-if="reserva.quantidade_pessoas">
                <div class="info-item">
                  <label class="text-white-50 small">Participantes:</label>
                  <p class="text-white mb-0">{{ reserva.quantidade_pessoas }} pessoas</p>
                </div>
              </div>

              <!-- Recorrência -->
              <div class="col-md-12" v-if="reserva.recorrente">
                <div class="info-item">
                  <label class="text-white-50 small">Recorrência:</label>
                  <p class="text-white mb-0">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    {{ reserva.frequencia_recorrencia }}
                    <span v-if="reserva.recorrencia_ate">
                      até {{ formatDate(reserva.recorrencia_ate) }}
                    </span>
                  </p>
                  <div v-if="reserva.dias_semana?.length" class="mt-2">
                    <span 
                      v-for="dia in reserva.dias_semana" 
                      :key="dia" 
                      class="badge bg-primary me-1"
                    >
                      {{ dia }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Recursos Adicionais -->
              <div class="col-md-12" v-if="reserva.recursos_adicionais?.length">
                <div class="info-item">
                  <label class="text-white-50 small">Recursos Necessários:</label>
                  <div class="mt-1">
                    <span 
                      v-for="recurso in reserva.recursos_adicionais" 
                      :key="recurso" 
                      class="badge bg-info me-1"
                    >
                      {{ recurso }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Observações -->
              <div class="col-md-12" v-if="reserva.observacoes">
                <div class="info-item">
                  <label class="text-white-50 small">Observações:</label>
                  <p class="text-white mb-0">{{ reserva.observacoes }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Solicitante e Aprovação -->
          <div class="info-section mb-4">
            <h5 class="text-white mb-3">
              <i class="bi bi-person-check me-2"></i>
              Solicitação e Aprovação
            </h5>
            
            <div class="row g-3">
              <div class="col-md-6">
                <div class="info-item">
                  <label class="text-white-50 small">Solicitante:</label>
                  <p class="text-white mb-0">{{ reserva.solicitante?.name }}</p>
                </div>
              </div>

              <div class="col-md-6">
                <div class="info-item">
                  <label class="text-white-50 small">Data da Solicitação:</label>
                  <p class="text-white mb-0">{{ formatDateTime(reserva.created_at) }}</p>
                </div>
              </div>

              <div class="col-md-6" v-if="reserva.aprovador">
                <div class="info-item">
                  <label class="text-white-50 small">Aprovado por:</label>
                  <p class="text-white mb-0">{{ reserva.aprovador?.name }}</p>
                </div>
              </div>

              <div class="col-md-6" v-if="reserva.data_aprovacao">
                <div class="info-item">
                  <label class="text-white-50 small">Data da Aprovação:</label>
                  <p class="text-white mb-0">{{ formatDateTime(reserva.data_aprovacao) }}</p>
                </div>
              </div>

              <!-- Motivo da Rejeição -->
              <div class="col-md-12" v-if="reserva.motivo_rejeicao">
                <div class="alert alert-danger">
                  <strong>Motivo da Rejeição:</strong>
                  <p class="mb-0 mt-1">{{ reserva.motivo_rejeicao }}</p>
                </div>
              </div>

              <!-- Motivo do Cancelamento -->
              <div class="col-md-12" v-if="reserva.motivo_cancelamento">
                <div class="alert alert-warning">
                  <strong>Motivo do Cancelamento:</strong>
                  <p class="mb-0 mt-1">{{ reserva.motivo_cancelamento }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div class="info-section" v-if="podeAprovar || podeCancelar">
            <h5 class="text-white mb-3">
              <i class="bi bi-gear me-2"></i>
              Ações
            </h5>
            
            <div class="d-flex gap-2">
              <!-- Ações de Aprovação -->
              <button 
                v-if="podeAprovar"
                @click="aprovar" 
                class="btn btn-success"
              >
                <i class="bi bi-check-circle me-1"></i>
                Aprovar
              </button>
              <button 
                v-if="podeAprovar"
                @click="rejeitar" 
                class="btn btn-danger"
              >
                <i class="bi bi-x-circle me-1"></i>
                Rejeitar
              </button>

              <!-- Ação de Cancelamento -->
              <button 
                v-if="podeCancelar"
                @click="cancelar" 
                class="btn btn-warning"
              >
                <i class="bi bi-slash-circle me-1"></i>
                Cancelar Reserva
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button @click="$emit('close')" type="button" class="btn btn-secondary">
            <i class="bi bi-x-circle me-1"></i>
            Fechar
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  reserva: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'refresh']);

const user = computed(() => {
  const userData = localStorage.getItem('user');
  return userData ? JSON.parse(userData) : {};
});

const permissions = computed(() => {
  return user.value.permissions || [];
});

const podeAprovar = computed(() => {
  return props.reserva.status === 'Pendente' && permissions.value?.includes('aprovar-reservas');
});

const podeCancelar = computed(() => {
  return ['Pendente', 'Aprovada'].includes(props.reserva.status) && 
         props.reserva.solicitante_id === user.value.id;
});

const hasRecursos = computed(() => {
  const espaco = props.reserva.espaco_fisico;
  if (!espaco) return false;
  
  return espaco.ar_condicionado || 
         espaco.projetor || 
         espaco.lousa_digital || 
         espaco.wifi || 
         espaco.computadores || 
         espaco.sistema_som || 
         espaco.acessibilidade;
});

const aprovar = async () => {
  const result = await Swal.fire({
    title: 'Aprovar Reserva?',
    text: 'Esta ação aprovará a reserva.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, aprovar!',
    cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/v1/reservas-espacos/${props.reserva.id}/aprovar`);
      Swal.fire('Aprovado!', 'Reserva aprovada com sucesso', 'success');
      emit('refresh');
      emit('close');
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao aprovar reserva', 'error');
    }
  }
};

const rejeitar = async () => {
  const { value: motivo } = await Swal.fire({
    title: 'Rejeitar Reserva',
    input: 'textarea',
    inputLabel: 'Motivo da rejeição',
    inputPlaceholder: 'Digite o motivo...',
    inputValidator: (value) => {
      if (!value) {
        return 'Você precisa informar um motivo!';
      }
    },
    showCancelButton: true,
    confirmButtonText: 'Rejeitar',
    cancelButtonText: 'Cancelar'
  });

  if (motivo) {
    try {
      await axios.post(`/api/v1/reservas-espacos/${props.reserva.id}/rejeitar`, { 
        motivo_rejeicao: motivo 
      });
      Swal.fire('Rejeitado!', 'Reserva rejeitada', 'success');
      emit('refresh');
      emit('close');
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao rejeitar reserva', 'error');
    }
  }
};

const cancelar = async () => {
  const result = await Swal.fire({
    title: 'Cancelar Reserva?',
    text: 'Esta ação não poderá ser revertida!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, cancelar!',
    cancelButtonText: 'Não'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/v1/reservas-espacos/${props.reserva.id}/cancelar`);
      Swal.fire('Cancelado!', 'Reserva cancelada com sucesso', 'success');
      emit('refresh');
      emit('close');
    } catch (error) {
      Swal.fire('Erro!', error.response?.data?.message || 'Erro ao cancelar reserva', 'error');
    }
  }
};

const getStatusBadge = (status) => {
  const badges = {
    'Pendente': 'badge bg-warning',
    'Aprovada': 'badge bg-success',
    'Rejeitada': 'badge bg-danger',
    'Cancelada': 'badge bg-secondary',
    'Concluída': 'badge bg-info'
  };
  return badges[status] || 'badge bg-secondary';
};

const getEspacoStatusClass = (status) => {
  const classes = {
    'Disponível': 'badge bg-success',
    'Ocupado': 'badge bg-warning',
    'Manutenção': 'badge bg-danger',
    'Reforma': 'badge bg-secondary',
    'Indisponível': 'badge bg-dark'
  };
  return classes[status] || 'badge bg-secondary';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('pt-BR');
};

const formatDateTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleString('pt-BR');
};
</script>

<style scoped>
.modal-container {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 15px;
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 20px;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: start;
}

.modal-body {
  padding: 1.5rem;
  max-height: calc(90vh - 200px);
  overflow-y: auto;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.info-section h5 {
  font-size: 1rem;
  font-weight: 600;
}

.info-item {
  margin-bottom: 0.5rem;
}

.info-item label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.card-dark {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.alert {
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid;
}

.alert-danger {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
  color: #fca5a5;
}

.alert-warning {
  background: rgba(245, 158, 11, 0.2);
  border-color: rgba(245, 158, 11, 0.4);
  color: #fcd34d;
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}
</style>
