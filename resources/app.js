import './bootstrap';
import { createApp } from 'vue';
import router from './router';

// Importa o nosso arquivo SASS principal
import '../sass/app.scss';

// Importa o nosso componente Vue principal
import App from './App.vue';

const app = createApp(App);
app.use(router);
app.mount('#app');