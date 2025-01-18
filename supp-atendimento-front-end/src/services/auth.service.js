import api from './api'

export const authService = {
    async login(email, password) {
        try {
            const response = await api.post('/login', { email, password })
            console.log('reponse====>> ', response);
            if (response.data?.token) {
              
                localStorage.setItem('token', response.data.token)
                localStorage.setItem('user', JSON.stringify(response.data.user))
                return response.data
            }
            
            throw new Error(response.data.message || 'Erro ao fazer login')
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erro ao fazer login')
        }
    },

    logout() {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    },

    getToken() {
        return localStorage.getItem('token')
    },

    getUser() {
        const user = localStorage.getItem('user')
        return user ? JSON.parse(user) : null
    },

    isAuthenticated() {
        return !!this.getToken()
    }
}