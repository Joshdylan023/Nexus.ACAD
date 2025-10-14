<template>
  <div class="online-users">
    <button 
      class="online-users-btn" 
      @click="togglePanel"
      :class="{ 'has-users': onlineCount > 0 }"
    >
      <i class="bi bi-people"></i>
      <span v-if="onlineCount > 0" class="online-badge">{{ onlineCount }}</span>
    </button>

    <!-- Panel de Usuários Online -->
    <transition name="slide-fade">
      <div v-if="showPanel" class="online-panel">
        <div class="online-header">
          <h5>Usuários Online</h5>
          <button class="btn btn-sm btn-link" @click="togglePanel">
            <i class="bi bi-x"></i>
          </button>
        </div>

        <div class="online-list">
          <div 
            v-for="user in onlineUsers" 
            :key="user.id"
            class="online-item"
          >
            <div class="user-avatar">
              <img 
                v-if="user.avatar" 
                :src="user.avatar" 
                :alt="user.name"
              >
              <div v-else class="avatar-placeholder">
                {{ getInitials(user.name) }}
              </div>
              <span class="status-indicator"></span>
            </div>
            <div class="user-info">
              <div class="user-name">{{ user.name }}</div>
              <div class="user-email">{{ user.email }}</div>
            </div>
          </div>

          <div v-if="onlineUsers.length === 0" class="online-empty">
            <i class="bi bi-person-x fs-1"></i>
            <p class="mt-2">Nenhum usuário online</p>
          </div>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <div 
      v-if="showPanel" 
      class="online-overlay" 
      @click="togglePanel"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const showPanel = ref(false);
const onlineUsers = ref([]);

const onlineCount = computed(() => onlineUsers.value.length);

const togglePanel = () => {
  showPanel.value = !showPanel.value;
};

const getInitials = (name) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

// Configurar Presence Channel
const setupPresence = () => {
  if (!window.Echo) {
    console.warn('Echo não disponível');
    return;
  }

  console.log('Conectando ao canal de presença...');

  window.Echo.join('online-users')
    .here((users) => {
      console.log('Usuários online:', users);
      onlineUsers.value = users;
    })
    .joining((user) => {
      console.log('Usuário entrou:', user);
      if (!onlineUsers.value.find(u => u.id === user.id)) {
        onlineUsers.value.push(user);
      }
    })
    .leaving((user) => {
      console.log('Usuário saiu:', user);
      onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id);
    })
    .error((error) => {
      console.error('Erro no canal de presença:', error);
    });
};

onMounted(() => {
  setupPresence();
});

onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leave('online-users');
  }
});
</script>

<style scoped>
.online-users {
  position: relative;
}

.online-users-btn {
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

.online-users-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.online-users-btn.has-users {
  color: #28a745;
}

.online-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #28a745;
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

.online-panel {
  position: fixed;
  top: 70px;
  right: 80px;
  width: 350px;
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

.online-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

.online-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.online-header h5 {
  margin: 0;
  color: rgba(255, 255, 255, 0.95);
}

.online-list {
  max-height: 500px;
  overflow-y: auto;
  padding: 0.5rem;
}

.online-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  transition: background 0.2s;
}

.online-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.user-avatar {
  position: relative;
  width: 45px;
  height: 45px;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

.status-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #28a745;
  border: 2px solid rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  animation: pulse-status 2s infinite;
}

@keyframes pulse-status {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
  }
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.online-empty {
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
