<template>
  <div class="notification-center">
    <button 
      class="notification-btn" 
      @click="togglePanel"
      :class="{ 'has-unread': unreadCount > 0 }"
      title="Notificações"
    >
      <i class="bi bi-bell"></i>
      <span v-if="unreadCount > 0" class="notification-badge">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Panel de Notificações -->
    <transition name="slide-fade">
      <div v-if="showPanel" class="notification-panel">
        <div class="notification-header">
          <h5>
            <i class="bi bi-bell-fill me-2"></i>
            Notificações
          </h5>
          <div class="notification-actions">
            <button 
              v-if="unreadCount > 0" 
              class="btn btn-sm btn-outline-light" 
              @click="markAllAsRead"
              title="Marcar todas como lidas"
            >
              <i class="bi bi-check-all"></i>
            </button>
            <button 
              class="btn btn-sm btn-outline-light" 
              @click="togglePanel"
              title="Fechar"
            >
              <i class="bi bi-x-lg"></i>
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
            <span>{{ filter.label }}</span>
          </button>
        </div>

        <!-- Lista de Notificações -->
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
              <small>
                <i class="bi bi-clock me-1"></i>
                {{ formatDate(notification.created_at) }}
              </small>
            </div>
            <button 
              class="notification-delete" 
              @click.stop="deleteNotification(notification.id)"
              title="Excluir"
            >
              <i class="bi bi-trash"></i>
            </button>
          </div>

          <!-- Loading More -->
          <div v-if="isLoadingMore" class="notification-loading">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
            <span class="ms-2 text-white-50">Carregando mais...</span>
          </div>

          <!-- Empty State -->
          <div v-if="!isLoading && filteredNotifications.length === 0" class="notification-empty">
            <i class="bi bi-inbox fs-1"></i>
            <p class="mt-3">
              {{ activeFilter !== 'all' 
                ? `Nenhuma notificação do tipo "${filters.find(f => f.value === activeFilter)?.label}"` 
                : 'Nenhuma notificação' 
              }}
            </p>
          </div>

          <!-- Initial Loading -->
          <div v-if="isLoading" class="notification-loading">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <transition name="fade">
      <div 
        v-if="showPanel" 
        class="notification-overlay" 
        @click="togglePanel"
      ></div>
    </transition>

    <!-- Toast Notifications -->
    <teleport to="body">
      <transition-group name="toast-list" tag="div" class="toast-container">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast-notification"
          :class="`toast-${toast.type}`"
          @click="handleToastClick(toast)"
        >
          <div class="toast-icon">
            <i :class="getIconClass(toast.type)"></i>
          </div>
          <div class="toast-content">
            <h6>{{ toast.title }}</h6>
            <p>{{ toast.message }}</p>
          </div>
          <button class="toast-close" @click.stop="removeToast(toast.id)">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </transition-group>
    </teleport>
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
  { value: 'all', label: 'Todas', icon: 'bi-list-ul' },
  { value: 'success', label: 'Sucesso', icon: 'bi-check-circle-fill' },
  { value: 'warning', label: 'Avisos', icon: 'bi-exclamation-triangle-fill' },
  { value: 'error', label: 'Erros', icon: 'bi-x-circle-fill' },
  { value: 'info', label: 'Informações', icon: 'bi-info-circle-fill' }
];

const filteredNotifications = computed(() => {
  if (activeFilter.value === 'all') {
    return notifications.value;
  }
  return notifications.value.filter(n => n.type === activeFilter.value);
});

const getCurrentUser = async () => {
  try {
    const response = await axios.get('/api/user');
    userId.value = response.data.id;
    return response.data.id;
  } catch (error) {
    console.error('❌ Erro ao buscar usuário:', error);
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
    console.error('❌ Erro ao buscar notificações:', error);
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const handleScroll = (e) => {
  const { scrollTop, scrollHeight, clientHeight } = e.target;
  
  if (scrollTop + clientHeight >= scrollHeight - 10) {
    if (currentPage.value < lastPage.value && !isLoadingMore.value) {
      fetchNotifications(currentPage.value + 1);
    }
  }
};

const togglePanel = () => {
  showPanel.value = !showPanel.value;
  if (showPanel.value && notifications.value.length === 0) {
    fetchNotifications();
  }
};

const markAllAsRead = async () => {
  try {
    // Atualização otimista
    notifications.value.forEach(n => n.is_read = true);
    const previousCount = unreadCount.value;
    unreadCount.value = 0;
    
    await axios.post('/api/v1/notifications/read-all');
  } catch (error) {
    console.error('❌ Erro ao marcar notificações como lidas:', error);
    // Reverter em caso de erro
    fetchNotifications();
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
      
      await axios.post(`/api/v1/notifications/${notification.id}/read`);
    } catch (error) {
      console.error('❌ Erro ao marcar notificação como lida:', error);
      fetchNotifications();
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
    
    await axios.delete(`/api/v1/notifications/${id}`);
  } catch (error) {
    console.error('❌ Erro ao excluir notificação:', error);
    fetchNotifications();
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
    return new Date(date).toLocaleDateString('pt-BR');
  }
};

// 🔊 SOM DE NOTIFICAÇÃO
const playNotificationSound = () => {
  try {
    const audio = new Audio('/sounds/notification.mp3');
    audio.volume = 0.3; // Volume reduzido para 30%
    audio.play().catch(error => {
      console.warn('⚠️ Não foi possível tocar o som da notificação:', error.message);
    });
  } catch (error) {
    console.error('❌ Erro ao tocar som:', error);
  }
};

// 🍞 TOAST NOTIFICATIONS
const addToast = (notification) => {
  const id = toastId++;
  toasts.value.push({
    id,
    ...notification
  });

  // Auto-remover após 5 segundos
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

const handleToastClick = (toast) => {
  if (toast.action_url) {
    router.push(toast.action_url);
  }
  removeToast(toast.id);
};

// 🔌 WEBSOCKET
const setupWebSocket = async () => {
  const id = await getCurrentUser();
  if (!id || !window.Echo) {
    console.warn('⚠️ Echo não disponível ou userId inválido');
    return;
  }

  console.log('🔌 Conectando ao WebSocket para notificações do usuário:', id);

  window.Echo.private(`notifications.${id}`)
    .listen('NotificationSent', (data) => {
      console.log('🔔 NOTIFICAÇÃO RECEBIDA VIA WEBSOCKET:', data);
      
      const notification = {
        id: data.notification?.id || Date.now(),
        type: data.notification?.type || 'info',
        title: data.notification?.title || 'Nova Notificação',
        message: data.notification?.message || '',
        is_read: false,
        created_at: data.notification?.created_at || new Date().toISOString(),
        action_url: data.notification?.action_url || null
      };

      // Adicionar à lista
      notifications.value.unshift(notification);
      unreadCount.value++;
      
      // 🔊 Tocar som
      playNotificationSound();
      
      // 🍞 Mostrar toast
      addToast(notification);
      
      // 📱 Notificação do navegador
      if ('Notification' in window && Notification.permission === 'granted') {
        const browserNotif = new Notification(notification.title, {
          body: notification.message,
          icon: '/favicon.ico',
          badge: '/favicon.ico',
          tag: `notification-${notification.id}`,
          requireInteraction: false
        });

        browserNotif.onclick = () => {
          if (notification.action_url) {
            window.focus();
            router.push(notification.action_url);
          }
          browserNotif.close();
        };
      }
    })
    .error((error) => {
      console.error('❌ Erro no WebSocket:', error);
    });

  console.log('✅ Listener de notificações configurado com sucesso!');
};

// 🎬 LIFECYCLE HOOKS
onMounted(async () => {
  await fetchNotifications();
  await setupWebSocket();
  
  // Solicitar permissão para notificações do navegador
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission().then(permission => {
      if (permission === 'granted') {
        console.log('✅ Permissão de notificações concedida');
      }
    });
  }
});

onUnmounted(() => {
  if (userId.value && window.Echo) {
    window.Echo.leave(`notifications.${userId.value}`);
    console.log('👋 Desconectado do canal de notificações');
  }
});
</script>

<style scoped>
/* ============================================
   NOTIFICATION BUTTON
   ============================================ */
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
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: linear-gradient(135deg, #dc2626, #ef4444);
  color: white;
  border-radius: 10px;
  min-width: 20px;
  height: 20px;
  padding: 0 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.5);
}

/* ============================================
   NOTIFICATION PANEL
   ============================================ */
.notification-panel {
  position: fixed;
  top: 70px;
  right: 20px;
  width: 420px;
  max-height: 600px;
  background: rgba(17, 24, 39, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  z-index: 1050;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.notification-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1040;
  backdrop-filter: blur(2px);
}

.notification-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.03);
}

.notification-header h5 {
  margin: 0;
  color: white;
  font-size: 1.1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
}

.notification-actions .btn {
  padding: 0.4rem 0.8rem;
  font-size: 0.85rem;
}

/* ============================================
   FILTERS
   ============================================ */
.notification-filters {
  display: flex;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  overflow-x: auto;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}

.notification-filters::-webkit-scrollbar {
  height: 4px;
}

.notification-filters::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
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
  color: white;
}

.filter-btn.active {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.5);
  color: #60a5fa;
}

/* ============================================
   NOTIFICATION LIST
   ============================================ */
.notification-list {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}

.notification-list::-webkit-scrollbar {
  width: 6px;
}

.notification-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 3px;
}

.notification-loading {
  text-align: center;
  padding: 1.5rem;
  color: rgba(255, 255, 255, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.notification-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.notification-item.unread {
  background: rgba(59, 130, 246, 0.08);
  border-left: 3px solid #3b82f6;
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  background: #3b82f6;
  border-radius: 50%;
  margin-left: -4px;
}

.notification-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.2rem;
}

.icon-success {
  background: rgba(34, 197, 94, 0.15);
  color: #22c55e;
}

.icon-warning {
  background: rgba(251, 191, 36, 0.15);
  color: #fbbf24;
}

.icon-error {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.icon-info {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-content h6 {
  margin: 0 0 0.25rem 0;
  color: white;
  font-size: 0.9rem;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.notification-content p {
  margin: 0 0 0.5rem 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.notification-content small {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.75rem;
  display: flex;
  align-items: center;
}

.notification-delete {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  padding: 0.25rem;
  transition: all 0.2s;
  opacity: 0;
  border-radius: 4px;
}

.notification-item:hover .notification-delete {
  opacity: 1;
}

.notification-delete:hover {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.notification-empty {
  text-align: center;
  padding: 4rem 2rem;
  color: rgba(255, 255, 255, 0.5);
}

.notification-empty i {
  color: rgba(255, 255, 255, 0.3);
}

.notification-empty p {
  margin: 0;
  font-size: 0.95rem;
}

/* ============================================
   TOAST NOTIFICATIONS
   ============================================ */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
  max-width: 420px;
}

.toast-notification {
  min-width: 320px;
  max-width: 420px;
  background: rgba(17, 24, 39, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  gap: 1rem;
  align-items: start;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.1);
  pointer-events: all;
  cursor: pointer;
  transition: all 0.3s;
}

.toast-notification:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
}

.toast-success {
  border-left: 4px solid #22c55e;
}

.toast-warning {
  border-left: 4px solid #fbbf24;
}

.toast-error {
  border-left: 4px solid #ef4444;
}

.toast-info {
  border-left: 4px solid #3b82f6;
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
  background: rgba(34, 197, 94, 0.15);
  color: #22c55e;
}

.toast-warning .toast-icon {
  background: rgba(251, 191, 36, 0.15);
  color: #fbbf24;
}

.toast-error .toast-icon {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.toast-info .toast-icon {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.toast-content {
  flex: 1;
  min-width: 0;
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
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.toast-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
  flex-shrink: 0;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

/* ============================================
   ANIMATIONS
   ============================================ */
.slide-fade-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 1, 1);
}

.slide-fade-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.toast-list-enter-active {
  animation: toast-in 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-list-leave-active {
  animation: toast-out 0.3s cubic-bezier(0.4, 0, 1, 1);
}

@keyframes toast-in {
  from {
    opacity: 0;
    transform: translateX(100%) scale(0.8);
  }
  to {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
}

@keyframes toast-out {
  from {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
  to {
    opacity: 0;
    transform: translateX(100%) scale(0.8);
  }
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
  .notification-panel {
    width: calc(100vw - 40px);
    right: 20px;
    left: 20px;
    max-height: calc(100vh - 100px);
  }

  .toast-container {
    right: 10px;
    left: 10px;
    max-width: none;
  }

  .toast-notification {
    min-width: auto;
    max-width: none;
  }
}
</style>
