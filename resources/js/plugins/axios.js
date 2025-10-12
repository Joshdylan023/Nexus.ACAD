import axios from 'axios';

// Define a URL base para todas as requisições
axios.defaults.baseURL = 'http://127.0.0.1:8000';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Este é o "interceptador". Ele executa antes de cada requisição.
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('authToken');
    if (token) {
        // Se temos um token, adiciona-o ao header de Authorization
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

export default axios;