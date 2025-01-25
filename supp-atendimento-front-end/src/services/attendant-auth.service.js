import api from './api'

// Adicione este método ao attendant-auth.service.js
const isAuthenticated = () => {
    const token = localStorage.getItem('attendant_token')
    const attendant = localStorage.getItem('attendant_data')
    return !!(token && attendant)
}

// E um método para obter os dados do atendente
const getAttendantData = () => {
    const attendantData = localStorage.getItem('attendant_data')
    return attendantData ? JSON.parse(attendantData) : null
}


export const attendantAuthService = {
    // attendant-auth.service.js
    isAuthenticated,
    getAttendantData,
// attendant-auth.service.js
async login(email, password) {
    try {
        // Faz a requisição para o servidor
        const response = await api.post('/attendant/login', { email, password })
        
        // Verifica se recebemos uma resposta do servidor
        if (!response.data) {
            throw new Error('Não foi possível conectar ao servidor')
        }

        // Extrai o token da resposta
        const token = response.data.token

        if (!token) {
            throw new Error('Token de autenticação não encontrado')
        }

        // Configura o token para futuras requisições
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        // Armazena o token e os dados do atendente
        localStorage.setItem('attendant_token', token)

        // Se tivermos dados do atendente na resposta, vamos armazená-los também
        if (response.data.attendant) {
            localStorage.setItem('attendant_data', JSON.stringify(response.data.attendant))
        }

        return response.data
    } catch (error) {
        // Tratamento mais específico de erros
        if (error.response?.status === 401) {
            throw new Error('Email ou senha incorretos')
        } else if (error.response?.status === 404) {
            throw new Error('Servidor não encontrado')
        } else if (error.response?.data?.message) {
            throw new Error(error.response.data.message)
        } else {
            throw new Error('Erro ao conectar com o servidor. Por favor, tente novamente.')
        }
    }
}


}