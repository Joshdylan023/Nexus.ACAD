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

        <!-- Filtros -->
        <div class="notification-filters">
          <button 
            v-for="filter in filters" 
            :key="filter.value"
            class="filter-btn"
            :class="{ active: activeFilter === filter.value }"
            @click="activeFilter = filter.value"
          >
            <i :class="filter.icon"></i>
            {{ filter.label }}
          </button>
        </div>

        <div class="notification-list" @scroll="handleScroll">
          <div 
            v-for="notification in filteredNotifications" 
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

          <div v-if="isLoadingMore" class="notification-loading">
            <div class="spinner-border spinner-border-sm" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>

          <div v-if="!filteredNotifications || filteredNotifications.length === 0" class="notification-empty">
            <i class="bi bi-inbox fs-1"></i>
            <p class="mt-2">Nenhuma notificação {{ activeFilter !== 'all' ? `do tipo ${activeFilter}` : '' }}</p>
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

    <!-- Toast Notifications -->
    <transition-group name="toast-list" tag="div" class="toast-container">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast-notification"
        :class="`toast-${toast.type}`"
      >
        <div class="toast-icon">
          <i :class="getIconClass(toast.type)"></i>
        </div>
        <div class="toast-content">
          <h6>{{ toast.title }}</h6>
          <p>{{ toast.message }}</p>
        </div>
        <button class="toast-close" @click="removeToast(toast.id)">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useRouter } from 'vue-router';

const router = useRouter();
const showPanel = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const userId = ref(null);
const currentPage = ref(1);
const lastPage = ref(1);
const activeFilter = ref('all');
const toasts = ref([]);
let toastId = 0;

const filters = [
  { value: 'all', label: 'Todas', icon: 'bi-list' },
  { value: 'success', label: 'Sucesso', icon: 'bi-check-circle' },
  { value: 'warning', label: 'Avisos', icon: 'bi-exclamation-triangle' },
  { value: 'error', label: 'Erros', icon: 'bi-x-circle' },
  { value: 'info', label: 'Info', icon: 'bi-info-circle' }
];

const filteredNotifications = computed(() => {
  if (activeFilter.value === 'all') {
    return notifications.value;
  }
  return notifications.value.filter(n => n.type === activeFilter.value);
});

const getCurrentUser = async () => { // Adicionado async aqui
  try {
    const response = await axios.get('/api/user');
    userId.value = response.data.id;
    return response.data.id;
  } catch (error) {
    console.error('Erro ao buscar usuário:', error);
    return null;
  }
};

const fetchNotifications = async (page = 1) => {
  if (isLoading.value || isLoadingMore.value) return;
  
  if (page === 1) {
    isLoading.value = true;
  } else {
    isLoadingMore.value = true;
  }

  try {
    const response = await axios.get(`/api/v1/notifications?page=${page}`);
    
    if (page === 1) {
      notifications.value = response.data?.notifications?.data || [];
    } else {
      notifications.value.push(...(response.data?.notifications?.data || []));
    }
    
    unreadCount.value = response.data?.unread_count || 0;
    currentPage.value = response.data?.notifications?.current_page || 1;
    lastPage.value = response.data?.notifications?.last_page || 1;
  } catch (error) {
    console.error('Erro ao buscar notificações:', error);
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const handleScroll = (e) => {
  const { scrollTop, scrollHeight, clientHeight } = e.target;
  
  if (scrollTop + clientHeight >= scrollHeight - 10) {
    if (currentPage.value < lastPage.value) {
      fetchNotifications(currentPage.value + 1);
    }
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
    // Atualização otimista: atualiza a UI imediatamente
    notifications.value.forEach(n => n.is_read = true);
    unreadCount.value = 0;
    
    // Envia a requisição para o backend em segundo plano
    await axios.post('/api/v1/notifications/read-all');
  } catch (error) {
    console.error('Erro ao marcar notificações como lidas:', error);
    // Em caso de erro, podemos reverter o estado ou notificar o usuário
    fetchNotifications(); // Recarrega para garantir a consistência
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    try {
      // Atualização otimista
      const index = notifications.value.findIndex(n => n.id === notification.id);
      if (index !== -1) {
        notifications.value[index].is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
      // Envia a requisição em segundo plano
      await axios.post(`/api/v1/notifications/${notification.id}/read`);
    } catch (error) {
      console.error('Erro ao marcar notificação como lida:', error);
      fetchNotifications(); // Recarrega em caso de erro
    }
  }

  if (notification.action_url) {
    router.push(notification.action_url);
    togglePanel();
  }
};

const deleteNotification = async (id) => {
  try {
    // Atualização otimista
    const index = notifications.value.findIndex(n => n.id === id);
    if (index !== -1) {
      if (!notifications.value[index].is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
      notifications.value.splice(index, 1);
    }
    // Envia a requisição em segundo plano
    await axios.delete(`/api/v1/notifications/${id}`);
  } catch (error) {
    console.error('Erro ao excluir notificação:', error);
    fetchNotifications(); // Recarrega em caso de erro
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
  if (!date) return '';
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: ptBR });
  } catch (error) {
    return '';
  }
};

// 🔊 SOM DE NOTIFICAÇÃO
const playNotificationSound = () => {
  try {
    // O arquivo de som deve estar na pasta `public/sounds/`
    const audio = new Audio('/sounds/notification.mp3');

    // Tenta tocar o som. A reprodução automática pode ser bloqueada por navegadores
    // até a primeira interação do usuário com a página.
    audio.play().catch(error => {
      console.warn('Não foi possível tocar o som da notificação automaticamente:', error);
    });
  } catch (error) {
    console.error('Erro ao tocar som:', error);
  }
};

// 🍞 TOAST NOTIFICATIONS
const addToast = (notification) => {
  const id = toastId++;
  toasts.value.push({
    id,
    ...notification
  });

  setTimeout(() => {
    removeToast(id);
  }, 5000);
};

const removeToast = (id) => {
  const index = toasts.value.findIndex(t => t.id === id);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
};

// 🔌 WEBSOCKET
const setupWebSocket = async () => {
  const id = await getCurrentUser();
  if (!id || !window.Echo) {
    console.warn('❌ Echo não disponível ou userId inválido');
    return;
  }

  console.log('🔌 Conectando ao WebSocket para notificações do usuário:', id);

  window.Echo.private(`notifications.${id}`)
    .listen('NotificationSent', (data) => {
      console.log('🔔 NOTIFICAÇÃO RECEBIDA VIA WEBSOCKET:', data);
      
      const notification = {
        id: data.notification.id,
        type: data.notification.type,
        title: data.notification.title,
        message: data.notification.message,
        is_read: false,
        created_at: data.notification.created_at
      };

      notifications.value.unshift(notification);
      unreadCount.value++;
      
      // 🔊 Tocar som
      playNotificationSound();
      
      // 🍞 Mostrar toast
      addToast(notification);
      
      // 📱 Notificação do navegador
      if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(notification.title, {
          body: notification.message,
          icon: '/favicon.ico'
        });
      }
    })
    .error((error) => {
      console.error('❌ Erro no WebSocket:', error);
    });

  console.log('✅ Listener configurado com sucesso!');
};

onMounted(async () => {
  await fetchNotifications();
  await setupWebSocket();
  
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }
});

onUnmounted(() => {
  if (userId.value && window.Echo) {
    window.Echo.leave(`notifications.${userId.value}`);
  }
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
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 1rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  z-index: 1050;
  overflow: hidden;
}

.notification-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1040;
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

.notification-filters {
  display: flex;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  overflow-x: auto;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-btn {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
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

.notification-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-loading {
  text-align: center;
  padding: 1rem;
  color: rgba(255, 255, 255, 0.5);
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

/* TOAST NOTIFICATIONS */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}

.toast-notification {
  min-width: 300px;
  max-width: 400px;
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  border-left: 4px solid;
  pointer-events: all;
}

.toast-success {
  border-left-color: #28a745;
}

.toast-warning {
  border-left-color: #ffc107;
}

.toast-error {
  border-left-color: #dc3545;
}

.toast-info {
  border-left-color: #007bff;
}

.toast-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.toast-success .toast-icon {
  background: rgba(40, 167, 69, 0.2);
  color: #28a745;
}

.toast-warning .toast-icon {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
}

.toast-error .toast-icon {
  background: rgba(220, 53, 69, 0.2);
  color: #dc3545;
}

.toast-info .toast-icon {
  background: rgba(0, 123, 255, 0.2);
  color: #007bff;
}

.toast-content {
  flex: 1;
}

.toast-content h6 {
  margin: 0 0 0.25rem 0;
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
}

.toast-content p {
  margin: 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.85rem;
}

.toast-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.toast-list-enter-active {
  animation: toast-in 0.3s;
}

.toast-list-leave-active {
  animation: toast-out 0.3s;
}

@keyframes toast-in {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes toast-out {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(100%);
  }
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
