import './bootstrap';
console.log('🟢 app.js CARREGADO!');
import { createApp } from 'vue';
import router from './router';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

// Importa o CSS
import '../css/app.css';

// Importa o componente Vue principal
import App from './App.vue';

// Configurar Axios com INTERCEPTOR
axios.defaults.baseURL = 'http://localhost:8000';

axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Configurar Laravel Echo para REVERB
window.Pusher = Pusher;

console.log('🚀 INICIANDO ECHO...');
console.log('Pusher:', Pusher);
console.log('Echo:', Echo);
console.log('VITE_REVERB_APP_KEY:', import.meta.env.VITE_REVERB_APP_KEY);

try {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
        cluster: 'mt1',
        disableStats: true,
        authEndpoint: '/api/broadcasting/auth',
        auth: {
            headers: {
                get Authorization() {
                    return `Bearer ${localStorage.getItem('authToken')}`;
                },
                Accept: 'application/json',
            },
        },
    });
    
    console.log('✅ Echo inicializado com sucesso!', window.Echo);
} catch (error) {
    console.error('❌ Erro ao inicializar Echo:', error);
}

const app = createApp(App);
app.use(router);
app.mount('#app');
