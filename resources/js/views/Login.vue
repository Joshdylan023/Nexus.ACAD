<template>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-6">
      <div class="card card-glass">
        <div class="card-header text-center h4">Login - Portal Administrativo</div>
        <div class="card-body">
          <form @submit.prevent="handleLogin">
            <div class="mb-3">
              <label for="matricula" class="form-label">Matrícula Funcional</label>
              <input type="text" class="form-control" id="matricula" v-model="form.matricula" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Senha</label>
              <input type="password" class="form-control" id="password" v-model="form.password" required>
            </div>
            <div v-if="errorMessage" class="alert alert-danger">
              {{ errorMessage }}
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
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
const router = useRouter();

const handleLogin = async () => {
  errorMessage.value = '';
  try {
    const response = await axios.post('/api/login', form.value);
    
    const token = response.data.access_token;
    
    // Salvar token
    localStorage.setItem('authToken', token);
    
    // Recarregar a página para garantir que todas as instâncias do Axios tenham o token
    window.location.href = '/admin/institucional/grupos-educacionais';

  } catch (error) {
    if (error.response && error.response.status === 401) {
        errorMessage.value = error.response.data.message;
    } else {
        errorMessage.value = 'Ocorreu um erro. Tente novamente mais tarde.';
    }
    console.error('Falha no login:', error);
  }
};
</script>
