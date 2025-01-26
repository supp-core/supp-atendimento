// services/api.js
import axios from 'axios';
import { authService } from './auth.service';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Interceptor com logs detalhados
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token');
        console.log('Preparando requisição para:', config.url);
        
        if (token) {
            console.log('Token encontrado, adicionando ao header');
            config.headers['Authorization'] = `Bearer ${token}`;
        } else {
            console.warn('Token não encontrado para requisição:', config.url);
        }
        
        console.log('Headers da requisição:', config.headers);
        return config;
    },
    error => {
        console.error('Erro no interceptor de requisição:', error);
        return Promise.reject(error);
    }
);


export default api;