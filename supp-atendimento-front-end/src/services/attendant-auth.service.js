import api from './api'

// Adicione este método ao attendant-auth.service.js
const isAuthenticated = () => {
    const token = localStorage.getItem('attendant_token')
    const attendant = localStorage.getItem('attendant_data')
    return !!(token && attendant)
}

// E um método para obter os dados do atendente
const getAttendantData = () => {
    try {
        const data = localStorage.getItem('attendant_data')
        return data ? JSON.parse(data) : null
    } catch (error) {
        console.error('Erro ao recuperar dados do atendente:', error)
        return null
    }
}


export const attendantAuthService = {
    // attendant-auth.service.js
    isAuthenticated,
    getAttendantData,
// attendant-auth.service.js
async login(email, password) {
    try {
        console.log('Enviando requisição de login:', { email, password });

        // Faz a requisição para o servidor
        const response = await api.post('/attendant/login', { email, password });        
       // Log da resposta completa
        console.log('Resposta completa:', response);
        console.log('Dados da resposta:', response.data);
        
      

        // Verifica se recebemos uma resposta do servidor
        if (!response.data) {
            throw new Error('Não foi possível conectar ao servidor')
        }

        // Extrai o token da resposta
       
        const { attendant, token } = response.data.data
        console.log('Dados extraídos:', { attendant, token })


        if (!token || !attendant) {
            throw new Error('Dados de autenticação incompletos')
        }


       

        // Configura o token para futuras requisições
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        localStorage.setItem('attendant_token', token)
        localStorage.setItem('attendant_data', JSON.stringify({
            id: attendant.id,
            name: attendant.name,
            email: attendant.email,
            function: attendant.function,
            sector: {                    // Mantém a estrutura completa do objeto
                id: attendant.sector.id,
                name: attendant.sector.name
            }
        }))

      
        return response.data
    } catch (error) {
        // Tratamento mais específico de erros
        if (error.response?.status === 401) {
            throw new Error('Email ou senha incorretos')
        } else if (error.response?.data?.message) {
            throw new Error(error.response.data.message)
        } else {
            throw new Error('Erro ao conectar com o servidor')
        }
    }
}


}