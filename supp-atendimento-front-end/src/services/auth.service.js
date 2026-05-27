import api from './api'

const TYPE_KEY = 'auth_type'
const DATA_KEY = 'auth_data'

export const authService = {
    async login(usuario, senha) {
        try {
            const response = await api.post('/login', { email: usuario, password: senha })

            const payload = response.data?.data
            if (!response.data?.success || !payload?.type) {
                throw new Error('Dados de autenticação inválidos')
            }

            const { type } = payload
            const identityData = type === 'attendant' ? payload.attendant : payload.user

            sessionStorage.setItem(TYPE_KEY, type)
            sessionStorage.setItem(DATA_KEY, JSON.stringify(identityData))

            return { type, data: identityData }
        } catch (error) {
            const message = error.response?.data?.message || error.message || 'Erro ao fazer login'
            throw new Error(message)
        }
    },

    async logout() {
        try {
            await api.post('/logout')
        } catch {
            // logout falho no servidor não deve impedir limpeza local
        } finally {
            this.clearSession()
        }
    },

    clearSession() {
        sessionStorage.removeItem(TYPE_KEY)
        sessionStorage.removeItem(DATA_KEY)
    },

    getAuthType() {
        return sessionStorage.getItem(TYPE_KEY)
    },

    getAuthData() {
        try {
            const raw = sessionStorage.getItem(DATA_KEY)
            return raw ? JSON.parse(raw) : null
        } catch {
            return null
        }
    },

    isAuthenticated() {
        return !!this.getAuthType()
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
