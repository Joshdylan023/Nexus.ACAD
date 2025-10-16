<template>
  <div>
    <PageHeader 
      title="Eventos de Sistema"
      :breadcrumbs="[
        { label: 'Configurações' },
        { label: 'Eventos de Sistema' }
      ]"
    >
      <template #actions>
        <button @click="showCreateForm" class="btn btn-success">
          <i class="bi bi-plus-lg"></i> Novo Evento
        </button>
      </template>
    </PageHeader>

    <!-- Alerta de Evento Ativo -->
    <div v-if="activeEvent && activeEvent.id" class="alert alert-warning-glass mb-4">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
          <div>
            <strong>{{ activeEvent.title }}</strong>
            <p class="mb-0 mt-1" style="font-size: 0.875rem;">{{ activeEvent.description }}</p>
          </div>
        </div>
        <button @click="deactivateEvent(activeEvent.id)" class="btn btn-danger">
          <i class="bi bi-stop-circle"></i> Finalizar Evento
        </button>
      </div>
    </div>

    <!-- Formulário de Criação/Edição -->
    <div v-if="showForm" class="card card-glass mb-4">
      <div class="card-header">{{ isEditing ? 'Editar Evento' : 'Criar Novo Evento' }}</div>
      <div class="card-body">
        <form @submit.prevent="isEditing ? updateEvent() : createEvent()">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de Evento</label>
              <select class="form-select" v-model="form.type" required>
                <option value="maintenance">Manutenção</option>
                <option value="import">Importação em Massa</option>
                <option value="backup">Backup</option>
                <option value="migration">Migração</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Título do Evento</label>
              <input type="text" class="form-control" v-model="form.title" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea class="form-control" v-model="form.description" rows="3"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Data/Hora de Início</label>
              <input type="datetime-local" class="form-control" v-model="form.start_at" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Data/Hora de Fim (Opcional)</label>
              <input type="datetime-local" class="form-control" v-model="form.end_at">
            </div>
          </div>

          <div class="card card-glass mb-3">
            <div class="card-header">Restrições de Acesso</div>
            <div class="card-body">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" v-model="form.block_student_portal" id="blockStudent">
                <label class="form-check-label" for="blockStudent">
                  Bloquear Portal do Aluno
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" v-model="form.block_teacher_portal" id="blockTeacher">
                <label class="form-check-label" for="blockTeacher">
                  Bloquear Portal do Docente
                </label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" v-model="form.block_admin_portal" id="blockAdmin">
                <label class="form-check-label" for="blockAdmin">
                  Bloquear Portal Administrativo
                </label>
              </div>

              <hr class="my-3">

              <div class="mb-3">
                <label class="form-label">
                  <i class="bi bi-person-check me-2"></i>
                  Colaboradores Autorizados (Acesso Especial)
                </label>
                <v-select
                  multiple
                  v-model="form.restricted_access"
                  :options="users"
                  :reduce="user => user.id"
                  label="display_info"
                  placeholder="Busque por nome ou matrícula"
                />
                <small class="form-text text-muted d-block mt-2">
                  Estes usuários terão acesso ao sistema mesmo durante o evento.
                </small>
              </div>

              <!-- O v-select já exibe os usuários selecionados -->
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Salvar
          </button>
          <button type="button" @click="hideForm" class="btn btn-secondary ms-2">
            Cancelar
          </button>
        </form>
      </div>
    </div>

    <!-- Lista de Eventos -->
    <div class="card card-glass">
      <div class="card-header"><h5 class="mb-0">Eventos Cadastrados</h5></div>
      <div class="card-body p-0">
        <TableSkeleton v-if="loading" :columns="6" :rows="5" />
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="ps-4">Tipo</th>
                <th>Título</th>
                <th>Status</th>
                <th>Início</th>
                <th>Fim</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in events" :key="event.id">
                <td class="ps-4">
                  <span class="badge" :class="getTypeBadgeClass(event.type)">
                    {{ getTypeLabel(event.type) }}
                  </span>
                </td>
                <td>
                  <strong>{{ event.title }}</strong>
                  <br>
                  <small class="text-muted">{{ event.description }}</small>
                  <br>
                  <span v-if="event.restricted_access && event.restricted_access.length > 0" class="badge bg-info mt-1">
                    <i class="bi bi-person-check"></i>
                    {{ event.restricted_access.length }} colaborador(es) autorizado(s)
                  </span>
                </td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(event.status)">
                    {{ getStatusLabel(event.status) }}
                  </span>
                </td>
                <td>{{ formatDateTime(event.start_at) }}</td>
                <td>{{ event.end_at ? formatDateTime(event.end_at) : '-' }}</td>
                <td class="text-center">
                  <button 
                    v-if="event.status === 'scheduled'" 
                    @click="activateEvent(event.id)" 
                    class="btn btn-sm btn-success me-2"
                    title="Ativar Evento"
                  >
                    <i class="bi bi-play-circle"></i>
                  </button>
                  <button 
                    v-if="event.status === 'active'" 
                    @click="deactivateEvent(event.id)" 
                    class="btn btn-sm btn-warning me-2"
                    title="Finalizar Evento"
                  >
                    <i class="bi bi-stop-circle"></i>
                  </button>
                  <button 
                    v-if="event.status !== 'active'" 
                    @click="prepareDelete(event)" 
                    class="btn btn-sm btn-danger"
                    title="Excluir Evento"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="events.length === 0">
                <td colspan="6" class="text-center text-muted py-4">
                  Nenhum evento cadastrado.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <ConfirmModal
      id="confirmDeleteModal"
      title="Confirmar Exclusão"
      :message="`Tem certeza que deseja excluir o evento ${itemToDelete?.title}?`"
      confirm-text="Excluir"
      confirm-icon="bi bi-trash"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import PageHeader from '@/components/PageHeader.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

const loading = ref(true);
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const events = ref([]);
const activeEvent = ref(null);
const itemToDelete = ref(null);
const users = ref([]);
let confirmModalInstance = null;

const form = ref({
  type: 'maintenance',
  title: '',
  description: '',
  start_at: '',
  end_at: '',
  block_student_portal: true,
  block_teacher_portal: true,
  block_admin_portal: false,
  restricted_access: []
});

const resetForm = () => {
  form.value = {
    type: 'maintenance',
    title: '',
    description: '',
    start_at: '',
    end_at: '',
    block_student_portal: true,
    block_teacher_portal: true,
    block_admin_portal: false,
    restricted_access: []
  };
};

const getTypeLabel = (type) => {
  const labels = {
    maintenance: 'Manutenção',
    import: 'Importação',
    backup: 'Backup',
    migration: 'Migração'
  };
  return labels[type] || type;
};

const getStatusLabel = (status) => {
  const labels = {
    scheduled: 'Agendado',
    active: 'Ativo',
    completed: 'Concluído',
    cancelled: 'Cancelado'
  };
  return labels[status] || status;
};

const getTypeBadgeClass = (type) => {
  const classes = {
    maintenance: 'bg-warning',
    import: 'bg-info',
    backup: 'bg-secondary',
    migration: 'bg-primary'
  };
  return classes[type] || 'bg-secondary';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    scheduled: 'bg-info',
    active: 'bg-danger',
    completed: 'bg-success',
    cancelled: 'bg-secondary'
  };
  return classes[status] || 'bg-secondary';
};

const formatDateTime = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};



const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/v1/users/colaboradores');
    users.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
  }
};

const fetchEvents = async () => {
  try {
    loading.value = true;
    const [eventsRes, currentRes] = await Promise.all([
      axios.get('/api/v1/system-events'),
      axios.get('/api/v1/system-events/current')
    ]);
    
    activeEvent.value = currentRes.data && currentRes.data.id ? currentRes.data : null;
    
    // A API pode retornar dados paginados, então acessamos `data.data`
    let allEvents = (eventsRes.data && eventsRes.data.data) ? eventsRes.data.data : (Array.isArray(eventsRes.data) ? eventsRes.data : []);
    
    // Filtra eventos sem título
    allEvents = allEvents.filter(e => e.title);

    if (activeEvent.value) {
      // Filtra o evento ativo da lista principal para evitar duplicação
      events.value = allEvents.filter(event => event.id !== activeEvent.value.id);
    } else {
      events.value = allEvents;
    }
  } catch (error) {
    console.error('Erro ao buscar eventos:', error);
    events.value = []; // Garante que events seja sempre um array em caso de erro
  } finally {
    loading.value = false;
  }
};

const showCreateForm = () => {
  isEditing.value = false;
  resetForm();
  showForm.value = true;
};

const hideForm = () => {
  showForm.value = false;
  isEditing.value = false;
};

const createEvent = async () => {
  try {
    await axios.post('/api/v1/system-events', form.value);
    await fetchEvents();
    hideForm();
  } catch (error) {
    console.error('Erro ao criar evento:', error);
    alert('Erro ao criar evento.');
  }
};

const activateEvent = async (id) => {
  if (!confirm('Tem certeza que deseja ativar este evento? Isso irá bloquear acessos conforme configurado.')) {
    return;
  }

  try {
    await axios.post(`/api/v1/system-events/${id}/activate`);
    await fetchEvents();
  } catch (error) {
    console.error('Erro ao ativar evento:', error);
    alert('Erro ao ativar evento.');
  }
};

const deactivateEvent = async (id) => {
  if (!id) {
    console.error('ID do evento não fornecido');
    return;
  }

  if (!confirm('Tem certeza que deseja finalizar este evento?')) {
    return;
  }

  try {
    await axios.post(`/api/v1/system-events/${id}/deactivate`);
    await fetchEvents();
  } catch (error) {
    console.error('Erro ao finalizar evento:', error);
    alert('Erro ao finalizar evento.');
  }
};

const prepareDelete = (event) => {
  itemToDelete.value = event;
  confirmModalInstance?.show();
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/v1/system-events/${itemToDelete.value.id}`);
    await fetchEvents();
    itemToDelete.value = null;
  } catch (error) {
    console.error('Erro ao excluir evento:', error);
    alert('Erro ao excluir evento.');
  }
};

onMounted(() => {
  fetchUsers();
  fetchEvents();
  
  const modalEl = document.getElementById('confirmDeleteModal');
  if (modalEl) {
    confirmModalInstance = new Modal(modalEl);
  }
});
</script>

<style scoped>
.alert-warning-glass {
  background: rgba(255, 193, 7, 0.15);
  border: 1px solid rgba(255, 193, 7, 0.4);
  border-radius: 0.75rem;
  padding: 1.25rem;
  color: #ffc107;
  backdrop-filter: blur(10px);
}

.alert-warning-glass strong {
  color: white;
}

.alert-warning-glass p {
  color: rgba(255, 255, 255, 0.8);
}

.selected-users {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.selected-users .badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
}

.selected-users .badge i {
  font-size: 1rem;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.selected-users .badge i:hover {
  opacity: 1;
}

.form-select[multiple] {
  height: auto;
}

/* Melhorar legibilidade da tabela */
.table thead th {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table tbody td {
  color: rgba(255, 255, 255, 0.85);
  vertical-align: middle;
}

.table tbody td strong {
  color: rgba(255, 255, 255, 0.95);
  font-weight: 600;
}

.table tbody td .text-muted {
  color: rgba(255, 255, 255, 0.5) !important;
}

.table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* Estilização do v-select para o tema escuro */
:deep(.vs__dropdown-toggle) {
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.5rem;
}

:deep(.vs__search::placeholder),
:deep(.vs__search),
:deep(.vs__selected-options),
:deep(.vs__selected) {
  color: rgba(255, 255, 255, 0.9);
}

:deep(.vs__selected) {
  background-color: rgba(0, 123, 255, 0.5); /* Azul primário com transparência */
  border: none;
  padding: 0.25em 0.6em;
}

:deep(.vs__deselect) {
  fill: rgba(255, 255, 255, 0.9);
  margin-left: 6px;
}

:deep(.vs__deselect:hover) {
  fill: white;
}

:deep(.vs__clear),
:deep(.vs__open-indicator) {
  fill: rgba(255, 255, 255, 0.5);
}

:deep(.vs__dropdown-menu) {
  background: #1a202c; /* Um fundo escuro sólido */
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
}

:deep(.vs__dropdown-option) {
  color: rgba(255, 255, 255, 0.9);
}

:deep(.vs__dropdown-option--highlight) {
  background: #007bff;
  color: white;
}

:deep(.vs__no-options) {
  color: rgba(255, 255, 255, 0.5);
}
</style>
