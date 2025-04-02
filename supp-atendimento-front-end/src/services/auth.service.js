// src/services/attendant-auth.service.js
import api from './api'

export const authService = {
    isAuthenticated() {
        const token = localStorage.getItem('token')
        const user = localStorage.getItem('data')
        return !!(token && user)
      },
    async login(email, password) {
        try {
            console.log('Iniciando login com email:', email);
            const response = await api.post('/login', { email, password });
            console.log('Resposta do login:', response.data);
    
            
            if (response.data?.success && response.data?.data?.token) {
                const token = response.data.data.token;
                console.log('Token recebido:', token.substring(0, 20) + '...');
            
                // Armazena o token
                localStorage.setItem('token', token);
            
                // Armazena dados do usuário
                localStorage.setItem('user', JSON.stringify(response.data.data.user));
            
                // Configura o token no Axios
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            
                console.log('Configuração completa do login realizada');
                return response.data;
            }
            throw new Error('Dados de autenticação inválidos')
        } catch (error) {
            console.error('Erro no login do atendente:', error)
            throw new Error(error.response?.data?.message || 'Erro ao fazer login')
        }
    },

    async logout() {
        try {
            const token = this.getToken()
            if (token) {
                // Usa a rota específica de logout para atendentes
                await api.post('/logout', {}, {
                    headers: { 'Authorization': `Bearer ${token}` }
                })
            }
        } catch (error) {
            console.error('Erro no logout do atendente:', error)
        } finally {
            this.clearSession()
        }
    },

    clearSession() {
        // Remove apenas os dados relacionados ao atendente
        localStorage.removeItem('token')
        localStorage.removeItem('data')
        delete api.defaults.headers.common['Authorization']
    },

    getToken() {
        return localStorage.getItem('token')
    },

    getUserData() {
        try {
            const data = localStorage.getItem('data')
            return data ? JSON.parse(data) : null
        } catch {
            return null
        }
    },
    getUser() {
        try {
            const user = localStorage.getItem('user')
            return user ? JSON.parse(user) : null
        } catch {
            return null
        }
    },

    isAuthenticated() {
        return !!this.getToken()
    }
}

// Configuração dos interceptors específicos para atendentes
export const setupAuthInterceptors = () => {
    api.interceptors.request.use(
        config => {
            const token = AuthService.getToken()
            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`
            }
            return config
        },
        error => Promise.reject(error)
    )

    api.interceptors.response.use(
        response => response,
        async error => {
            if (error.response?.status === 401) {
                authService.clearSession()
                window.location.href = '/login'
            }
            return Promise.reject(error)
        }
    )
}
