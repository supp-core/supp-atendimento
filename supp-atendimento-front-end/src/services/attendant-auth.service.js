// src/services/attendant-auth.service.js
import api from './api'

export const attendantAuthService = {
    async login(email, password) {
        try {
            // Faz a requisição de login para a rota específica de atendentes
            const response = await api.post('/attendant/login', { email, password })
            
            if (response.data?.data?.token) {
                const { token, attendant } = response.data.data
                
                // Armazena os dados com prefixo específico para atendentes
                localStorage.setItem('attendant_token', token)
                localStorage.setItem('attendant_data', JSON.stringify(attendant))
                
                // Configura o token para requisições futuras
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`
                
                return response.data
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
                await api.post('/attendant/logout', {}, {
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
        localStorage.removeItem('attendant_token')
        localStorage.removeItem('attendant_data')
        delete api.defaults.headers.common['Authorization']
    },

    getToken() {
        return localStorage.getItem('attendant_token')
    },

    getAttendantData() {
        try {
            const data = localStorage.getItem('attendant_data')
            return data ? JSON.parse(data) : null
        } catch {
            return null
        }
    },

    isAuthenticated() {
        return !!this.getToken()
    }
}

// Configuração dos interceptors específicos para atendentes
export const setupAttendantAuthInterceptors = () => {
    api.interceptors.request.use(
        config => {
            const token = attendantAuthService.getToken()
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
                attendantAuthService.clearSession()
                window.location.href = '/attendant/login'
            }
            return Promise.reject(error)
        }
    )
}