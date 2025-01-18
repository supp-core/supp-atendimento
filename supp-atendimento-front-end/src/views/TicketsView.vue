<template>
  <div class="tickets-page">
    <!-- Cabeçalho da página -->
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h5 font-weight-medium">Chamados</h2>
      <v-btn
        color="primary"
        prepend-icon="mdi-plus"
        @click="createTicket"
      >
        Novo Chamado
      </v-btn>
    </div>

    <!-- Loading state -->
    <v-progress-linear
      v-if="loading"
      indeterminate
      color="primary"
      class="mb-4"
    ></v-progress-linear>

    <!-- Tabela de chamados -->
    <v-card class="tickets-table">
      <v-table hover>
        <thead>
          <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Status</th>
            <th>Setor</th>
            <th>Solicitante</th>
            <th>Responsável</th>
            <th>Data de Criação</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in tickets" :key="ticket.id">
            <td>
              <span class="id-prefix">#</span>{{ ticket.id }}
            </td>
            <td>{{ ticket.title }}</td>
            <td>
              <v-chip
                :color="getStatusColor(ticket.status)"
                :class="['status-chip', ticket.status.toLowerCase()]"
                size="small"
              >
                {{ translateStatus(ticket.status) }}
              </v-chip>
            </td>
            <td>{{ ticket.sector?.name }}</td>
            <td>{{ ticket.requester?.name }}</td>
            <td>{{ ticket.responsible?.name || 'Não atribuído' }}</td>
            <td>{{ formatDate(ticket.dates.created) }}</td>
            <td>
              <v-btn
                icon="mdi-eye"
                variant="text"
                density="comfortable"
                size="small"
                @click="viewTicket(ticket.id)"
              ></v-btn>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import api from '@/services/api';

const router = useRouter();
const tickets = ref([]);
const loading = ref(false);

// Tradução de status
const translateStatus = (status) => {
  const translations = {
    'NEW': 'Novo',
    'OPEN': 'Aberto',
    'IN_PROGRESS': 'Em Andamento',
    'RESOLVED': 'Resolvido',
    'CLOSED': 'CLOSED'
  };
  return translations[status] || status;
};

// Cores dos status
const getStatusColor = (status) => {
  const colors = {
    'NEW': 'info',
    'OPEN': 'warning',
    'IN_PROGRESS': 'primary',
    'RESOLVED': 'success',
    'CLOSED': 'grey'
  };
  return colors[status] || 'grey';
};

// Formatação de data no padrão brasileiro
const formatDate = (dateString) => {
  if (!dateString) return '';
  return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
    locale: ptBR
  });
};

// Carregar chamados
const loadTickets = async () => {
  loading.value = true;
  try {
    const response = await api.get('/service/sector');
    if (response.data.success) {
      tickets.value = response.data.data;
    }
  } catch (error) {
    console.error('Erro ao carregar chamados:', error);
  } finally {
    loading.value = false;
  }
};

// Navegação
const createTicket = () => router.push('/tickets/create');
const viewTicket = (id) => router.push(`/tickets/${id}`);

onMounted(() => {
  loadTickets();
});
</script>

<style scoped>
.tickets-page {
  padding: 24px;
  background-color: #f8f9fa;
  min-height: calc(100vh - 60px);
}

.tickets-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.id-prefix {
  color: #666;
  margin-right: 2px;
}

.status-chip {
  font-size: 0.875rem;
  font-weight: 500;
}

/* Status específicos */
.status-chip.new { background-color: #E3F2FD !important; }
.status-chip.open { background-color: #FFF3E0 !important; }
.status-chip.closed { background-color: #EEEEEE !important; }
.status-chip.resolved { background-color: #E8F5E9 !important; }

/* Estilos da tabela */
:deep(.v-table) {
  background: transparent;
}

:deep(.v-table th) {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
  text-transform: none;
}

:deep(.v-table td) {
  font-size: 0.875rem;
  color: #333;
}
</style>