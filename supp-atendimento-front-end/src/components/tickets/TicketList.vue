<!-- Em src/components/tickets/TicketList.vue -->
<template>
    <!-- ... resto do código existente ... -->
    <td class="text-center">
      <v-btn
        icon="mdi-eye"
        size="small"
        variant="text"
        @click="openTicketDetails(ticket)"
        title="Ver Detalhes"
      />
    </td>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import TicketDetailsModal from './TicketDetailsModal.vue';
  
  // Adicione estas variáveis de estado
  const selectedTicket = ref(null);
  const showDetailsModal = ref(false);
  
  // Função para abrir o modal
  const openTicketDetails = async (ticket) => {
  try {
    // Mostra o modal com os dados básicos primeiro
    selectedTicket.value = ticket;
    showDetailsModal.value = true;

    // Carrega os detalhes completos do ticket, incluindo anexos
    const response = await ticketsService.getTicketDetails(ticket.id);
    if (response.success) {
      // Atualiza o ticket com informações completas
      selectedTicket.value = response.data;
      
      // Carrega o histórico em segundo plano
      const historyResponse = await ticketsService.getTicketHistory(ticket.id);
      if (historyResponse.success) {
        // Adiciona o histórico ao ticket
        selectedTicket.value.histories = historyResponse.data;
      }
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes do ticket:', error);
  }
};
  
  // Adicione o modal ao final do template
  </script>
  
  <template>
    <!-- Adicione isso ao final do template, após a tabela -->
    <TicketDetailsModal
      v-model="showDetailsModal"
      :ticket="selectedTicket"
    />
  </template>
<script>
// Em TicketList.vue
import { ticketsService } from '@/services/tickets.service';

const openTicketDetails = async (ticket) => {
  try {
    // Mostra o modal com os dados básicos primeiro
    selectedTicket.value = ticket;
    showDetailsModal.value = true;

    // Carrega o histórico em segundo plano
    const response = await ticketsService.getTicketHistory(ticket.id);
    if (response.success) {
      // Atualiza o ticket com o histórico
      selectedTicket.value = {
        ...ticket,
        histories: response.data
      };
    }
  } catch (error) {
    console.error('Erro ao carregar histórico:', error);
  }
};


// Em tickets.service.js
/*async downloadAttachment(attachmentId) {
  try {
    const response = await api.get(`/service/attachment/${attachmentId}`, {
      responseType: 'blob'
    });
    
    return response.data;
  } catch (error) {
    console.error('Erro ao baixar anexo:', error);
    throw error;
  }
}
*/
</script>