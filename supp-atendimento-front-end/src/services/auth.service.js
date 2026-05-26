import api from './api'

const TOKEN_KEY = 'token'
const TYPE_KEY = 'auth_type'
const DATA_KEY = 'auth_data'

export const authService = {
    async login(usuario, senha) {
        try {
            const response = await api.post('/login', { email: usuario, password: senha })

            const payload = response.data?.data
            if (!response.data?.success || !payload?.token || !payload?.type) {
                throw new Error('Dados de autenticação inválidos')
            }

            const { token, type } = payload
            const identityData = type === 'attendant' ? payload.attendant : payload.user

            localStorage.setItem(TOKEN_KEY, token)
            localStorage.setItem(TYPE_KEY, type)
            localStorage.setItem(DATA_KEY, JSON.stringify(identityData))

            api.defaults.headers.common['Authorization'] = `Bearer ${token}`

            return { type, data: identityData }
        } catch (error) {
            const message = error.response?.data?.message || error.message || 'Erro ao fazer login'
            throw new Error(message)
        }
    },

    async logout() {
        try {
            const token = this.getToken()
            if (token) {
                await api.post('/logout', {}, {
                    headers: { 'Authorization': `Bearer ${token}` }
                })
            }
        } catch {
            // logout falho no servidor não deve impedir limpeza local
        } finally {
            this.clearSession()
        }
    },

    clearSession() {
        localStorage.removeItem(TOKEN_KEY)
        localStorage.removeItem(TYPE_KEY)
        localStorage.removeItem(DATA_KEY)
        delete api.defaults.headers.common['Authorization']
    },

    getToken() {
        return localStorage.getItem(TOKEN_KEY)
    },

    getAuthType() {
        return localStorage.getItem(TYPE_KEY)
    },

    getAuthData() {
        try {
            const raw = localStorage.getItem(DATA_KEY)
            return raw ? JSON.parse(raw) : null
        } catch {
            return null
        }
    },

    isAuthenticated() {
        return !!this.getToken()
    },

    isAttendant() {
        return this.getAuthType() === 'attendant'
    },

    isAdmin() {
        const data = this.getAuthData()
        return this.isAttendant() && data?.function === 'Admin'
    },

    getUser() {
        return this.getAuthType() === 'user' ? this.getAuthData() : null
    },

    getAttendantData() {
        return this.getAuthType() === 'attendant' ? this.getAuthData() : null
    }
}
