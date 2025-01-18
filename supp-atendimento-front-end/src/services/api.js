import axios from 'axios'

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
})

// Interceptor para adicionar o token em todas as requisições
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`
        }
        return config
    },
    error => {
        return Promise.reject(error)
    }
)

// Interceptor para tratar erros de resposta
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Se receber unauthorized, limpa os dados e redireciona
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api