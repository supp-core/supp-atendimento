<template>
  <div class="dashboard">
    <AppHeader />
    <div class="dashboard-layout">
      <AppSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="tickets-page">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-medium">Atendimentos</h2>
            <v-btn color="primary" @click="createTicket" class="create-button">
              <span class="icon-text me-2">➕</span>
              Novo Atendimento
            </v-btn>
          </div>

          <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-4"></v-progress-linear>

          <v-card class="tickets-table">
            <v-table hover>
              <thead>
                <tr>
                  <th class="px-4 py-3">ID</th>
                  <th class="px-4 py-3">Título</th>
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">Setor</th>
                  <th class="px-4 py-3">Solicitante</th>
                  <th class="px-4 py-3">Responsável</th>
                  <th class="px-4 py-3">Data de Criação</th>
                  <th class="px-4 py-3">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in tickets" :key="ticket.id">
                  <td class="px-4 py-3">
                    <span class="id-prefix">#</span>{{ ticket.id }}
                  </td>
                  <td class="px-4 py-3">{{ ticket.title }}</td>
                  <td class="px-4 py-3">
                    <v-chip :color="getStatusColor(ticket.status)" :class="['status-chip', ticket.status.toLowerCase()]"
                      size="small">
                      {{ translateStatus(ticket.status) }}
                    </v-chip>
                  </td>
                  <td class="px-4 py-3">{{ ticket.sector?.name }}</td>
                  <td class="px-4 py-3">{{ ticket.requester?.name }}</td>
                  <td class="px-4 py-3">{{ ticket.responsible?.name || 'Não atribuído' }}</td>
                  <td class="px-4 py-3">{{ formatDate(ticket.dates.created) }}</td>
                  <td class="px-4 py-3">
                    <div class="d-flex gap-2">
                      <v-btn variant="text" density="comfortable" size="small" @click="viewTicket(ticket.id)"
                        class="action-button">
                        <span class="icon-text">📄</span>
                      </v-btn>
                      <v-btn variant="text" density="comfortable" size="small" @click="editTicket(ticket.id)"
                        class="action-button">
                        <span class="icon-text">✏️</span>
                      </v-btn>
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import api from '@/services/api';
import AppHeader from '@/components/common/AppHeader.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import { useSidebar } from '@/composables/useSidebar';

const { sidebarCollapsed } = useSidebar();
const router = useRouter();
const tickets = ref([]);
const loading = ref(false);

const translateStatus = (status) => {
  const translations = {
    'NEW': 'Novo',
    'OPEN': 'Aberto',
    'IN_PROGRESS': 'Em Andamento',
    'RESOLVED': 'Resolvido',
    'CLOSED': 'Fechado'
  };
  return translations[status] || status;
};

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

const formatDate = (dateString) => {
  if (!dateString) return '';
  return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
    locale: ptBR
  });
};

const loadTickets = async () => {
  loading.value = true;
  try {
    const response = await api.get('/service/my-tickets');
    if (response.data.success) {
      tickets.value = response.data.data;
    }
  } catch (error) {
    console.error('Erro ao carregar chamados:', error);
  } finally {
    loading.value = false;
  }
};

const createTicket = () => router.push('/tickets/create');
const viewTicket = (id) => router.push(`/tickets/${id}`);
const editTicket = (id) => router.push(`/tickets/${id}/edit`);

onMounted(() => {
  loadTickets();
});
</script>

<style scoped>

.create-button {
  display: flex !important;
  align-items: center !important;
  gap: 4px !important;
}

.create-button .icon-text {
  font-size: 1rem;
  line-height: 1;
  display: flex;
  align-items: center;
}

.dashboard {
  min-height: 100vh;
  background-color: #f3f4f6;
}

.dashboard-layout {
  padding-top: 60px;
  min-height: calc(100vh - 60px);
}

.dashboard-content {
  transition: margin-left 0.3s ease;
  padding: 24px;
}

.tickets-page {
  padding: 24px;
  background-color: #f8f9fa;
  min-height: calc(100vh - 60px);
}

.tickets-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.id-prefix {
  color: #666;
  margin-right: 2px;
}

.status-chip {
  font-size: 0.875rem;
  font-weight: 500;
}

.status-chip.new {
  background-color: #E3F2FD !important;
}

.status-chip.open {
  background-color: #FFF3E0 !important;
}

.status-chip.closed {
  background-color: #EEEEEE !important;
}

.status-chip.resolved {
  background-color: #E8F5E9 !important;
}

:deep(.v-table) {
  background: transparent;
}

:deep(.v-table th) {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
  text-transform: none;
  background-color: #f5f5f5;
}

:deep(.v-table td) {
  font-size: 0.875rem;
  color: #333;
}

.action-button {
  min-width: 36px !important;
  padding: 0 8px !important;
  cursor: pointer !important;
  /* Adiciona o cursor de mãozinha */
}

.icon-text {
  font-size: 1.2rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  /* Adiciona também ao texto do ícone para garantir */
}

.action-button:hover {
  background-color: rgba(0, 0, 0, 0.04) !important;
}

/* Ajuste de alinhamento vertical */
.d-flex {
  align-items: center;
}
</style>