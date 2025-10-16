import axios from 'axios';
import router from '../router'; // ⭐ IMPORTAR O ROUTER

// Define a URL base para todas as requisições
axios.defaults.baseURL = 'http://127.0.0.1:8000';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ⭐ INTERCEPTOR DE REQUEST (adiciona token)
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('authToken') || localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// ⭐ INTERCEPTOR DE RESPONSE (trata erros de autenticação)
axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Se o erro for 401 (Não autorizado)
        if (error.response && error.response.status === 401) {
            // Limpar dados de autenticação
            localStorage.removeItem('authToken');
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            
            // Redirecionar para o login
            router.push('/login');
        }
        
        // Se o erro for 403 (Acesso negado/sem permissão)
        if (error.response && error.response.status === 403) {
            router.push({
                name: 'forbidden',
                query: {
                    message: error.response.data.message || 'Você não tem permissão para realizar esta ação.'
                }
            });
        }
        
        return Promise.reject(error);
    }
);

export default axios;
