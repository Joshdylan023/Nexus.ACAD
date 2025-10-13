<template>
  <div class="autocomplete-wrapper">
    <label v-if="label" class="form-label">{{ label }}</label>
    <div class="position-relative">
      <input
        type="text"
        class="form-control"
        :placeholder="placeholder"
        v-model="searchTerm"
        @input="handleSearch"
        @focus="showDropdown = true"
        @blur="handleBlur"
        autocomplete="off"
      >
      <i v-if="loading" class="bi bi-arrow-repeat spin position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);"></i>
      
      <!-- Dropdown de Resultados -->
      <div v-if="showDropdown && (filteredUsers.length > 0 || searchTerm.length > 0)" class="autocomplete-dropdown">
        <div v-if="loading" class="autocomplete-item text-center">
          <small class="text-muted">Buscando...</small>
        </div>
        <div 
          v-else-if="filteredUsers.length === 0 && searchTerm.length > 0" 
          class="autocomplete-item text-center"
        >
          <small class="text-muted">Nenhum resultado encontrado</small>
        </div>
        <div 
          v-else
          v-for="user in filteredUsers" 
          :key="user.id"
          class="autocomplete-item"
          @mousedown.prevent="selectUser(user)"
        >
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>{{ user.name }}</strong>
              <small class="d-block text-muted">{{ user.email }}</small>
            </div>
            <span v-if="user.matricula" class="badge bg-secondary">{{ user.matricula }}</span>
          </div>
        </div>
      </div>

      <!-- Usuário Selecionado -->
      <div v-if="selectedUser" class="selected-user mt-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <i class="bi bi-person-check text-success me-2"></i>
            <strong>{{ selectedUser.name }}</strong>
            <small class="d-block ms-4 text-muted">{{ selectedUser.email }}</small>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger" @click="clearSelection">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';

const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Digite o nome ou matrícula...'
  },
  initialUser: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const searchTerm = ref('');
const users = ref([]);
const loading = ref(false);
const showDropdown = ref(false);
const selectedUser = ref(props.initialUser);

const filteredUsers = computed(() => {
  if (!searchTerm.value || searchTerm.value.length < 2) return [];
  return users.value;
});

const handleSearch = debounce(async () => {
  if (searchTerm.value.length < 2) {
    users.value = [];
    return;
  }

  loading.value = true;
  try {
    const response = await axios.get('/api/v1/users/search', {
      params: { q: searchTerm.value }
    });
    users.value = response.data;
    showDropdown.value = true;
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
    users.value = [];
  } finally {
    loading.value = false;
  }
}, 300);

const selectUser = (user) => {
  selectedUser.value = user;
  searchTerm.value = '';
  users.value = [];
  showDropdown.value = false;
  emit('update:modelValue', user.id);
};

const clearSelection = () => {
  selectedUser.value = null;
  searchTerm.value = '';
  users.value = [];
  emit('update:modelValue', null);
};

const handleBlur = () => {
  setTimeout(() => {
    showDropdown.value = false;
  }, 200);
};

watch(() => props.initialUser, (newUser) => {
  selectedUser.value = newUser;
});
</script>

<style scoped>
.autocomplete-wrapper {
  position: relative;
}

.autocomplete-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: rgba(28, 28, 35, 0.98);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
  margin-top: 0.25rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.autocomplete-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.9);
}

.autocomplete-item:last-child {
  border-bottom: none;
}

.autocomplete-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.autocomplete-item strong {
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.9rem;
}

.autocomplete-item small {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
}

.selected-user {
  padding: 0.75rem;
  background: rgba(40, 167, 69, 0.15);
  border: 1px solid rgba(40, 167, 69, 0.4);
  border-radius: 0.5rem;
  color: rgba(255, 255, 255, 0.9);
}

.selected-user strong {
  color: rgba(255, 255, 255, 0.95);
}

.selected-user small {
  color: rgba(255, 255, 255, 0.7);
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: translateY(-50%) rotate(0deg); }
  to { transform: translateY(-50%) rotate(360deg); }
}

.autocomplete-dropdown::-webkit-scrollbar {
  width: 8px;
}

.autocomplete-dropdown::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

.autocomplete-dropdown::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.autocomplete-dropdown::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
