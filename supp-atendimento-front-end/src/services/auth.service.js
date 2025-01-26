import api from './api'

export const authService = {
    async login(email, password) {
        try {
            const response = await api.post('/login', { email, password })


            console.log(response.data?.data?.token);

            if (response.data?.data?.token) {
                localStorage.setItem('token', response.data.token)
                localStorage.setItem('user', JSON.stringify(response.data.user))
                // Configura o token para todas as requisições futuras
                api.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
                return response.data
            }
            throw new Error('Credenciais inválidas')
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erro ao fazer login')
        }
    },

    async logout() {
        try {
            const token = localStorage.getItem('token')
            
            // Configura os headers específicos para a requisição de logout
            const config = {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }
            
            // Faz a requisição de logout
            await api.post('/logout', {}, config)
        } catch (error) {
            console.error('Erro durante logout:', error)
        } finally {
            // Limpa os dados locais
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            // Remove o header de autorização
            delete api.defaults.headers.common['Authorization']
            // Força o redirecionamento para a página de login
            window.location.href = '/login'
        }
    },

    getUser() {
        try {
            const user = localStorage.getItem('user')
            return user ? JSON.parse(user) : null
        } catch {
            return null
        }
    }
}