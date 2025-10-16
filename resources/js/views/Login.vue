<template>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-6">
      <div class="card card-glass">
        <div class="card-header text-center h4">Login - Portal Administrativo</div>
        <div class="card-body">
          <form @submit.prevent="handleLogin">
            <div class="mb-3">
              <label for="matricula" class="form-label">Matrícula Funcional</label>
              <input 
                type="text" 
                class="form-control" 
                id="matricula" 
                v-model="form.matricula" 
                required
                :disabled="loading"
              >
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Senha</label>
              <input 
                type="password" 
                class="form-control" 
                id="password" 
                v-model="form.password" 
                required
                :disabled="loading"
              >
            </div>
            <div v-if="errorMessage" class="alert alert-danger">
              {{ errorMessage }}
            </div>
            <button 
              type="submit" 
              class="btn btn-primary w-100"
              :disabled="loading"
            >
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Entrando...
              </span>
              <span v-else>Entrar</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const form = ref({
  matricula: '',
  password: '',
});
const errorMessage = ref('');
const loading = ref(false);
const router = useRouter();

const handleLogin = async () => {
  errorMessage.value = '';
  loading.value = true;
  
  try {
    const response = await axios.post('/api/login', form.value);
    
    const { access_token, user } = response.data;
    
    // ⭐ SALVAR TOKEN E DADOS DO USUÁRIO (COM PERMISSÕES)
    localStorage.setItem('authToken', access_token);
    localStorage.setItem('token', access_token); // Para o interceptor do axios
    localStorage.setItem('user', JSON.stringify(user));
    
    // ⭐ CONFIGURAR O TOKEN NO AXIOS GLOBALMENTE
    axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
    
    // Redirecionar para o admin
    router.push('/admin/dashboard');
    
  } catch (error) {
    loading.value = false;
    
    if (error.response && error.response.status === 401) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Ocorreu um erro. Tente novamente mais tarde.';
    }
    console.error('Falha no login:', error);
  }
};
</script>

<style scoped>
.card-glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
