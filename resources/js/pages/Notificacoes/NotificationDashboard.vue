<template>
  <div class="notification-dashboard">
    <div class="dashboard-header">
      <h2>Central de Notificações</h2>
      <div class="header-actions">
        <button class="btn btn-primary" @click="showPreferencesModal = true">
          <i class="bi bi-gear"></i> Preferências
        </button>
        <button 
          class="btn btn-secondary" 
          @click="markAllAsRead" 
          v-if="unreadCount > 0"
        >
          <i class="bi bi-check-all"></i> Marcar todas como lidas
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div class="dashboard-filters">
      <button 
        v-for="filter in filters" 
        :key="filter.value"
        class="filter-btn"
        :class="{ active: activeFilter === filter.value }"
        @click="activeFilter = filter.value"
      >
        <i :class="filter.icon"></i>
        {{ filter.label }}
        <span v-if="filter.count > 0" class="badge">{{ filter.count }}</span>
      </button>
    </div>

    <!-- Lista de Notificações -->
    <div class="notifications-grid">
      <div 
        v-for="notification in filteredNotifications" 
        :key="notification.id"
        class="notification-card"
        :class="{ 'unread': !notification.is_read }"
        @click="handleNotificationClick(notification)"
      >
        <div class="notification-icon" :class="`icon-${notification.type}`">
          <i :class="getIconClass(notification.type)"></i>
        </div>
        <div class="notification-body">
          <h5>{{ notification.title }}</h5>
          <p>{{ notification.message }}</p>
          <small>{{ formatDate(notification.created_at) }}</small>
        </div>
        <button class="btn-delete" @click.stop="deleteNotification(notification.id)">
          <i class="bi bi-trash"></i>
        </button>
      </div>

      <div v-if="filteredNotifications.length === 0" class="empty-state">
        <i class="bi bi-inbox fs-1"></i>
        <p>Nenhuma notificação encontrada</p>
      </div>
    </div>

    <!-- Paginação -->
    <div class="dashboard-pagination" v-if="lastPage > 1">
      <button 
        class="btn btn-secondary" 
        @click="loadPage(currentPage - 1)"
        :disabled="currentPage === 1"
      >
        <i class="bi bi-chevron-left"></i> Anterior
      </button>
      <span>Página {{ currentPage }} de {{ lastPage }}</span>
      <button 
        class="btn btn-secondary" 
        @click="loadPage(currentPage + 1)"
        :disabled="currentPage === lastPage"
      >
        Próxima <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <!-- Modal de Preferências -->
    <NotificationPreferences 
      v-if="showPreferencesModal" 
      @close="showPreferencesModal = false"
      @updated="fetchNotifications"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useRouter } from 'vue-router';
import NotificationPreferences from './NotificationPreferences.vue';

const router = useRouter();
const notifications = ref([]);
const unreadCount = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);
const activeFilter = ref('all');
const showPreferencesModal = ref(false);

const filters = ref([
  { value: 'all', label: 'Todas', icon: 'bi-list', count: 0 },
  { value: 'unread', label: 'Não lidas', icon: 'bi-envelope', count: 0 },
  { value: 'success', label: 'Sucesso', icon: 'bi-check-circle', count: 0 },
  { value: 'warning', label: 'Avisos', icon: 'bi-exclamation-triangle', count: 0 },
  { value: 'error', label: 'Erros', icon: 'bi-x-circle', count: 0 },
  { value: 'info', label: 'Info', icon: 'bi-info-circle', count: 0 },
]);

const filteredNotifications = computed(() => {
  if (activeFilter.value === 'all') return notifications.value;
  if (activeFilter.value === 'unread') return notifications.value.filter(n => !n.is_read);
  return notifications.value.filter(n => n.type === activeFilter.value);
});

const fetchNotifications = async (page = 1) => {
  try {
    const response = await axios.get(`/api/v1/notifications?page=${page}`);
    notifications.value = response.data.notifications.data;
    unreadCount.value = response.data.unread_count;
    currentPage.value = response.data.notifications.current_page;
    lastPage.value = response.data.notifications.last_page;

    // Atualizar contadores
    filters.value[0].count = notifications.value.length;
    filters.value[1].count = unreadCount.value;
    filters.value[2].count = notifications.value.filter(n => n.type === 'success').length;
    filters.value[3].count = notifications.value.filter(n => n.type === 'warning').length;
    filters.value[4].count = notifications.value.filter(n => n.type === 'error').length;
    filters.value[5].count = notifications.value.filter(n => n.type === 'info').length;
  } catch (error) {
    console.error('Erro ao buscar notificações:', error);
  }
};

const loadPage = (page) => {
  fetchNotifications(page);
};

const markAllAsRead = async () => {
  try {
    await axios.post('/api/v1/notifications/read-all');
    fetchNotifications(currentPage.value);
  } catch (error) {
    console.error('Erro ao marcar notificações:', error);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await axios.post(`/api/v1/notifications/${notification.id}/read`);
    fetchNotifications(currentPage.value);
  }

  if (notification.action_url) {
    router.push(notification.action_url);
  }
};

const deleteNotification = async (id) => {
  if (!confirm('Deseja excluir esta notificação?')) return;

  try {
    await axios.delete(`/api/v1/notifications/${id}`);
    fetchNotifications(currentPage.value);
  } catch (error) {
    console.error('Erro ao excluir notificação:', error);
  }
};

const getIconClass = (type) => {
  const icons = {
    success: 'bi-check-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    error: 'bi-x-circle-fill',
    info: 'bi-info-circle-fill'
  };
  return icons[type] || 'bi-bell-fill';
};

const formatDate = (date) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: ptBR });
};

onMounted(() => {
  fetchNotifications();
});
</script>

<style scoped>
.notification-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h2 {
  margin: 0;
  color: rgba(255, 255, 255, 0.95);
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.dashboard-filters {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.filter-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.filter-btn.active {
  background: rgba(102, 126, 234, 0.3);
  border-color: rgba(102, 126, 234, 0.5);
  color: white;
}

.filter-btn .badge {
  background: rgba(220, 53, 69, 0.8);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
}

.notifications-grid {
  display: grid;
  gap: 1rem;
}

.notification-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  cursor: pointer;
  transition: all 0.2s;
}

.notification-card:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateY(-2px);
}

.notification-card.unread {
  background: rgba(102, 126, 234, 0.1);
  border-color: rgba(102, 126, 234, 0.3);
}

.notification-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.icon-success {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.icon-warning {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.icon-error {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.icon-info {
  background: rgba(0, 123, 255, 0.2);
  color: #007bff;
}

.notification-body {
  flex: 1;
}

.notification-body h5 {
  margin: 0 0 0.5rem 0;
  color: rgba(255, 255, 255, 0.95);
  font-size: 1.1rem;
}

.notification-body p {
  margin: 0 0 0.5rem 0;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.5;
}

.notification-body small {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.85rem;
}

.btn-delete {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-delete:hover {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: rgba(255, 255, 255, 0.5);
}

.dashboard-pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.dashboard-pagination span {
  color: rgba(255, 255, 255, 0.7);
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: rgba(102, 126, 234, 0.8);
  color: white;
}

.btn-primary:hover {
  background: rgba(102, 126, 234, 1);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.9);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
