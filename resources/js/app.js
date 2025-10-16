import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import App from './App.vue';
import '../css/app.css'; 
import '@fortawesome/fontawesome-free/css/all.css'; // Importa o Font Awesome
import permissionsPlugin from './plugins/permissions';
import axios from './plugins/axios'; // ⭐ IMPORTAR AXIOS CONFIGURADO
import { VueMaskDirective } from 'v-mask';

const app = createApp(App);

app.use(router);
app.use(permissionsPlugin);
app.directive('mask', VueMaskDirective);


// ⭐ DISPONIBILIZAR AXIOS GLOBALMENTE
app.config.globalProperties.$axios = axios;

app.mount('#app');
