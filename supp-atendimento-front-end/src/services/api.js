// src/services/api.js

import axios from 'axios';
import router from '@/router'; // Importe o roteador do Vue

const api = axios.create({
    // Use um caminho relativo. O proxy do Vite irá redirecionar para o backend.
    // Isso funciona tanto em desenvolvimento (com Vite) quanto em produção (com Nginx).
    baseURL: '/api', 
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// --- Interceptor de Requisição (Request) ---
// Anexa o token de autenticação a cada requisição
api.interceptors.request.use(
    config => {
        // Verifica primeiro o token de atendente, depois o token de usuário
        const attendantToken = localStorage.getItem('attendant_token');
        const userToken = localStorage.getItem('token');
        
        const token = attendantToken || userToken;

        // Se algum token existir, adiciona ao header de autorização
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        // Retorna a configuração modificada para a requisição continuar
        return config;
    },
    error => {
        // Se houver um erro na configuração da requisição, rejeita a promise
        console.error('Erro na configuração da requisição:', error);
        return Promise.reject(error);
    }
);

// --- Interceptor de Resposta (Response) - AQUI ESTÁ A LÓGICA NOVA ---
// Intercepta as respostas da API para tratamento de erros globais
api.interceptors.response.use(
    // Se a resposta for bem-sucedida (status 2xx), não faz nada e a deixa passar
    response => response,

    // Se a resposta resultar em um erro...
    error => {
        // Verifica se o erro é o que esperamos: uma resposta de erro da API
        // e se o status é 401 (Não Autorizado / Token Expirado)
        if (error.response && error.response.status === 401) {
            
            console.warn("Sessão expirada ou token inválido (401). Realizando logout...");

            // Limpa todos os dados de autenticação (usuário e atendente)
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('attendant_token');
            localStorage.removeItem('attendant_data');

            // Remove o header de autorização
            delete api.defaults.headers.common['Authorization'];

            // Redireciona para a página de login usando o roteador do Vue
            router.push('/login');
        }

        // Para todos os outros erros (404, 500, etc.), rejeita a promise
        // para que possam ser tratados localmente no componente que fez a chamada.
        return Promise.reject(error);
    }
);

export default api;