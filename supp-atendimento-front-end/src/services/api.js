// // services/api.js
// import axios from 'axios';
// import { authService } from './auth.service';

// const api = axios.create({
//     baseURL: 'http://191.232.244.118:8080/api',
//     headers: {
//         'Accept': 'application/json',
//         'Content-Type': 'application/json'
//     }
// });

// // Interceptor com logs detalhados
// api.interceptors.request.use(
//     config => {
//         const token = localStorage.getItem('token');
//         console.log('Preparando requisição para:', config.url);
        
//         if (token) {
//             console.log('Token encontrado, adicionando ao header');
//             config.headers['Authorization'] = `Bearer ${token}`;
//         } else {
//             console.warn('Token não encontrado para requisição:', config.url);
//         }
        
//         console.log('Headers da requisição:', config.headers);
//         return config;
//     },
//     error => {
//         console.error('Erro no interceptor de requisição:', error);
//         return Promise.reject(error);
//     }
// );


// export default api;


// ####ALTERAÇÊOS DOCKER AUZRE#####
// services/api.js
import axios from 'axios';
// Remova a importação de authService se não existir ou ajuste o caminho
// import { authService } from './auth.service';

const api = axios.create({
    // Use um caminho relativo. O browser fará a requisição para
    // https://suppatendimento.pgmbh.org/api
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest' // Frequentemente necessário para CORS/Frameworks
    }
});

/*Rodar local*/
/*const api = axios.create({
    // Se seu backend Symfony está rodando na porta 8000 (padrão do Symfony)
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});*/

// Interceptor (mantido como estava, parece ok)
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token');
        console.log('Preparando requisição para:', config.url); // Mantenha para debug se útil

        if (token) {
            // console.log('Token encontrado, adicionando ao header');
            config.headers['Authorization'] = `Bearer ${token}`;
        } else {
            // console.warn('Token não encontrado para requisição:', config.url);
        }

        // console.log('Headers da requisição:', config.headers);
        return config;
    },
    error => {
        console.error('Erro no interceptor de requisição:', error);
        // Adicionar lógica de tratamento de erro, como redirecionar para login em 401
        if (error.response && error.response.status === 401) {
           console.error("Não autorizado (401). Redirecionando para login ou limpando token...");
           // Exemplo: authService.logout(); window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;
