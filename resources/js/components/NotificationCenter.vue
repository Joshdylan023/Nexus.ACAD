<template>
  <div class="notification-center">
    <button 
      class="notification-btn" 
      @click="togglePanel"
      :class="{ 'has-unread': unreadCount > 0 }"
    >
      <i class="bi bi-bell"></i>
      <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
    </button>

    <!-- Panel de Notificações -->
    <transition name="slide-fade">
      <div v-if="showPanel" class="notification-panel">
        <div class="notification-header">
          <h5>Notificações</h5>
          <div class="notification-actions">
            <button 
              v-if="unreadCount > 0" 
              class="btn btn-sm btn-link" 
              @click="markAllAsRead"
            >
              Marcar todas como lidas
            </button>
            <button class="btn btn-sm btn-link" @click="togglePanel">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <div class="notification-list">
          <div 
            v-for="notification in notifications" 
            :key="notification.id"
            class="notification-item"
            :class="{ 'unread': !notification.is_read }"
            @click="handleNotificationClick(notification)"
          >
            <div class="notification-icon" :class="`icon-${notification.type}`">
              <i :class="getIconClass(notification.type)"></i>
            </div>
            <div class="notification-content">
              <h6>{{ notification.title }}</h6>
              <p>{{ notification.message }}</p>
              <small>{{ formatDate(notification.created_at) }}</small>
            </div>
            <button 
              class="btn btn-sm btn-link notification-delete" 
              @click.stop="deleteNotification(notification.id)"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>

          <div v-if="notifications.length === 0" class="notification-empty">
            <i class="bi bi-inbox fs-1"></i>
            <p class="mt-2">Nenhuma notificação</p>
          </div>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <div 
      v-if="showPanel" 
      class="notification-overlay" 
      @click="togglePanel"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useRouter } from 'vue-router';

const router = useRouter();
const showPanel = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/api/v1/notifications');
    notifications.value = response.data.notifications;
    unreadCount.value = response.data.unread_count;
  } catch (error) {
    console.error('Erro ao buscar notificações:', error);
  }
};

const togglePanel = () => {
  showPanel.value = !showPanel.value;
  if (showPanel.value) {
    fetchNotifications();
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post('/api/v1/notifications/read-all');
    fetchNotifications();
  } catch (error) {
    console.error('Erro ao marcar notificações como lidas:', error);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    try {
      await axios.post(`/api/v1/notifications/${notification.id}/read`);
      fetchNotifications();
    } catch (error) {
      console.error('Erro ao marcar notificação como lida:', error);
    }
  }

  if (notification.action_url) {
    router.push(notification.action_url);
    togglePanel();
  }
};

const deleteNotification = async (id) => {
  try {
    await axios.delete(`/api/v1/notifications/${id}`);
    fetchNotifications();
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
  
  // Atualizar a cada 30 segundos
  setInterval(() => {
    fetchNotifications();
  }, 30000);
});
</script>

<style scoped>
.notification-center {
  position: relative;
}

.notification-btn {
  position: relative;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.9);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s;
}

.notification-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.notification-btn.has-unread {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
  }
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #dc3545;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: bold;
}

.notification-panel {
  position: fixed;
  top: 70px;
  right: 20px;
  width: 400px;
  max-height: 600px;
  background: rgba(28, 28, 35, 0.98);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  z-index: 1000;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.notification-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

.notification-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-header h5 {
  margin: 0;
  color: rgba(255, 255, 255, 0.95);
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
}

.notification-list {
  max-height: 500px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: background 0.2s;
  position: relative;
}

.notification-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.notification-item.unread {
  background: rgba(102, 126, 234, 0.1);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.2rem;
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

.notification-content {
  flex: 1;
}

.notification-content h6 {
  margin: 0 0 0.25rem 0;
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.9rem;
  font-weight: 600;
}

.notification-content p {
  margin: 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  line-height: 1.4;
}

.notification-content small {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.75rem;
}

.notification-delete {
  opacity: 0;
  transition: opacity 0.2s;
  color: rgba(255, 255, 255, 0.5);
}

.notification-item:hover .notification-delete {
  opacity: 1;
}

.notification-empty {
  text-align: center;
  padding: 3rem 1rem;
  color: rgba(255, 255, 255, 0.5);
}

.slide-fade-enter-active {
  transition: all 0.3s ease;
}

.slide-fade-leave-active {
  transition: all 0.2s ease;
}

.slide-fade-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
