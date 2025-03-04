// Em src/services/tickets.service.js

import api from './api';

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
    },

    // Em tickets.service.js
async downloadAttachment(attachmentId) {

  console.log('attachmentId======>>',attachmentId);

  try {
    const response = await api.get(`/service/attachment/${attachmentId}`, {
      responseType: 'blob'
    });
    

    return response.data;
  } catch (error) {
    console.error('Erro ao baixar anexo: aqqqq', error);
    throw error;
  }
}, 
async getTicketDetails(ticketId) {
  try {
    const response = await api.get(`/service/${ticketId}`);
    return response.data;
  } catch (error) {
    console.error('Erro ao buscar detalhes do ticket:', error);
    throw error;
  }
}
  };