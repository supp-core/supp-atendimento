// Em src/services/tickets.service.js
export const ticketsService = {
    // ... outros métodos existentes ...
  
    async getTicketHistory(ticketId) {
      try {
        const response = await api.get(`/service/${ticketId}/history`);
        return response.data;
      } catch (error) {
        console.error('Erro ao buscar histórico:', error);
        throw error;
      }
    }
  };