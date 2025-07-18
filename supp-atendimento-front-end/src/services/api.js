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
        // Pega o token do localStorage
        const token = localStorage.getItem('token');

        // Se o token existir, adiciona ao header de autorização
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

            // Limpa o armazenamento local para remover o token inválido e os dados do usuário
            localStorage.removeItem('token');
            localStorage.removeItem('user'); // Se você também guarda o usuário

            // Redireciona para a página de login usando o roteador do Vue
            // Usar router.push() é melhor do que window.location.href, pois preserva o estado do SPA.
            router.push('/login');

            // Opcional: Recarregar a página para garantir que todo o estado seja limpo
            // setTimeout(() => {
            //   window.location.reload();
            // }, 500);
        }

        // Para todos os outros erros (404, 500, etc.), rejeita a promise
        // para que possam ser tratados localmente no componente que fez a chamada.
        return Promise.reject(error);
    }
);

export default api;