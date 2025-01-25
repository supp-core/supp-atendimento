import api from './api'

export const attendantAuthService = {
    async login(email, password) {
        try {
            const response = await api.post('/attendant/login', { email, password })
            if (response.data?.data?.token) {
                localStorage.setItem('attendant_token', response.data.data.token)
                localStorage.setItem('attendant_data', JSON.stringify(response.data.data.attendant))
                return response.data
            }
            throw new Error('Credenciais inválidas')
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erro ao fazer login')
        }
    },

    logout() {
        localStorage.removeItem('attendant_token')
        localStorage.removeItem('attendant_data')
    }
}