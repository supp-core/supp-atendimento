<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="tickets-page">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-medium">Meus Atendimentos</h2>
          </div>

          <v-card class="mb-4">
            <v-card-text>
              <v-row>
                <!-- Campo de pesquisa por título -->
                <v-col cols="12" sm="3">
                  <v-text-field v-model="searchTitle" label="Pesquisar por Título" outlined dense
                    @input="handleSearchInput"></v-text-field>
                </v-col>

                <!-- Campo de pesquisa por solicitante -->
                <v-col cols="12" sm="3">
                  <v-text-field v-model="searchRequester" label="Pesquisar por Solicitante" outlined dense
                    @input="handleSearchInput"></v-text-field>
                </v-col>

                <!-- Filtro de Status -->
                <v-col cols="12" sm="3">
                  <v-select v-model="searchStatus" :items="statusOptions" label="Status" outlined dense
                    @change="handleFilter"></v-select>
                </v-col>

                <!-- Filtro de Prioridade -->
                <v-col cols="12" sm="3">
                  <v-select v-model="searchPriority" :items="priorityOptions" label="Prioridade" outlined dense
                    @change="handleFilter"></v-select>
                </v-col>

                <!-- Nova linha para botões -->
                <v-col cols="12" class="d-flex align-center mt-2">
                  <v-btn color="primary" @click="handleSearch" :loading="loading" class="me-2">
                    <v-icon start>mdi-magnify</v-icon>
                    Pesquisar
                  </v-btn>

                  <v-btn variant="outlined" @click="resetFilters" :disabled="loading">
                    <v-icon start>mdi-refresh</v-icon>
                    Limpar
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
          <v-card class="tickets-table">
            <!-- Barra de carregamento -->
            <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-4"></v-progress-linear>


            <!-- Tabela de tickets -->
            <v-table hover>
              <thead>
                <tr>
                  <th class="text-left">Título</th>
                  <th class="text-left">Solicitante</th>
                  <th class="text-left">Prioridade</th>
                  <th class="text-left">Status</th>
                  <th class="text-left">Data Criação</th>
                  <th class="text-left">Data Conclusão</th>
                  <th class="text-center">Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in sortedTickets" :key="ticket.id">
                  <td>{{ ticket.title }}</td>
                  <td>{{ ticket.requester?.name }}</td> <!-- Nova célula -->
                  <td>
                    <v-chip :color="getPriorityColor(ticket.priority)"
                      :text-color="getPriorityTextColor(ticket.priority)" size="small" class="priority-chip">
                      {{ ticket.priority }}
                    </v-chip>
                  </td>
                  <td>
                    <v-chip :color="getStatusColor(ticket.status)" size="small">
                      {{ translateStatus(ticket.status) }}
                    </v-chip>
                  </td>
                  <td>{{ formatDate(ticket.dates.created) }}</td>
                  <td>{{ ticket.dates.concluded ? formatDate(ticket.dates.concluded) : '-' }}</td>
                  <td class="text-center">
                    <v-btn :prepend-icon="mdi - pencil - box - outline" size="small" color="primary" class="mr-2"
                      @click="openEvolveDialog(ticket)" :disabled="ticket.status === 'CONCLUDED'">
                      Evoluir
                    </v-btn>

                    <v-btn :prepend-icon="mdi - pencil - box - outline" size="small" color="primary" class="mr-2"
                      @click="openTransferDialog(ticket)" :disabled="ticket.status === 'CONCLUDED'">
                      Transferir
                    </v-btn>

                  </td>
                </tr>
              </tbody>
            </v-table>

            <div class="pagination-wrapper">
              <div class="pagination-info">
                Mostrando {{ meta.per_page }} de {{ meta.total }} registros
              </div>
              <div class="pagination-controls">
                <!-- Botão Anterior -->
                <v-btn :disabled="currentPage === 1" @click="handlePageChange(currentPage - 1)" size="small"
                  variant="text" class="pagination-button">
                  Anterior
                </v-btn>

                <!-- Números das páginas -->
                <v-btn v-for="page in meta.last_page" :key="page" :color="currentPage === page ? 'primary' : ''"
                  :variant="currentPage === page ? 'flat' : 'text'" size="small" @click="handlePageChange(page)"
                  class="pagination-button">
                  {{ page }}
                </v-btn>

                <!-- Botão Próximo -->
                <v-btn :disabled="currentPage === meta.last_page" @click="handlePageChange(currentPage + 1)"
                  size="small" variant="text" class="pagination-button">
                  Próximo
                </v-btn>
              </div>
            </div>



          </v-card>
        </div>

        <!-- Modal para Evolução do Ticket -->
        <v-dialog v-model="evolveDialog.show" max-width="600px">
          <v-card>
            <v-card-title class="headline">
              Evoluir Atendimento #{{ evolveDialog.ticket?.id }}
            </v-card-title>

            <v-card-text>




              <div class="ticket-details mb-6">
                <div class="ticket-header">
                  <h3 class="ticket-title">{{ evolveDialog.ticket?.title }}</h3>
                  <v-chip :color="getPriorityColor(evolveDialog.ticket?.priority)" size="small" class="priority-chip">
                    {{ evolveDialog.ticket?.priority }}
                  </v-chip>
                </div>

              </div>
              <div class="description-container">

                <div class="description-label">
                  <v-icon icon="mdi-text-box-outline" class="mr-2"></v-icon>
                  Descrição do Atendimento
                </div>
                <div class="description-content">
                  {{ evolveDialog.ticket?.description }}
                </div>
                <div class="ticket-metadata">

                  <div class="metadata-item">
                    <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
                    <span class="metadata-label">Aberto em:</span>
                    {{ formatDate(evolveDialog.ticket?.dates?.created) }}
                  </div>
                  <div class="metadata-item">
                    <v-icon size="small" class="mr-1">mdi-account</v-icon>
                    <span class="metadata-label">Solicitante:</span>
                    {{ evolveDialog.ticket?.requester?.name }}
                  </div>
                </div>

              </div>








              <!-- Formulário de nova evolução -->
              <div class="new-update-form mb-6">
                <v-select v-model="evolveDialog.newStatus" :items="availableStatuses" label="Novo Status" required
                  class="mb-4"></v-select>

                <v-textarea v-model="evolveDialog.comment" label="Comentário" required rows="3"
                  class="mb-4"></v-textarea>
              </div>

              <!-- Linha do tempo -->
              <div class="timeline">
                <div v-for="(history, index) in evolveDialog.ticket?.histories" :key="index" class="timeline-item">
                  <div class="timeline-header d-flex align-center">
                    <v-avatar size="32" class="mr-3">
                      <v-img src="/assets/user-avatar.png"></v-img>
                    </v-avatar>
                    <div>
                      <span class="font-weight-medium">{{ history.responsible?.name }}</span>
                      <span class="text-caption ml-2 grey--text">
                        {{ formatDate(history.date) }}
                      </span>
                    </div>
                  </div>

                  <div class="timeline-content ml-11">
                    <v-chip size="small" :color="getStatusColor(history.status_post)" class="mb-2">
                      {{ translateStatus(history.status_post) }}
                    </v-chip>
                    <p class="text-body-2 mb-0">{{ history.comment }}</p>
                  </div>
                </div>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="grey" text @click="evolveDialog.show = false">
                Cancelar
              </v-btn>
              <v-btn color="primary" @click="evolveTicket" :loading="evolveDialog.loading">
                Confirmar
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Modal para Transferência do Ticket -->
        <v-dialog v-model="transferDialog.show" max-width="500px">
          <v-card>
            <v-card-title>Transferir Atendimento</v-card-title>
            <v-card-text>
              <v-select v-model="transferDialog.newAttendantId" :items="availableAttendants" item-title="name"
                item-value="id" label="Novo Responsável" required></v-select>
              <v-textarea v-model="transferDialog.comment" label="Motivo da Transferência" required
                rows="3"></v-textarea>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="grey" text @click="transferDialog.show = false">
                Cancelar
              </v-btn>
              <v-btn color="primary" @click="transferTicket" :loading="transferDialog.loading">
                Confirmar
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { useSidebar } from '@/composables/useSidebar'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import api from '@/services/api'
import { attendantAuthService } from '@/services/attendant-auth.service'

const { sidebarCollapsed } = useSidebar()
const loading = ref(false)
const tickets = ref([])

// Função principal de pesquisa
const handleSearch = async () => {
  try {
    // Reseta para a primeira página antes de pesquisar
    currentPage.value = 1;
    
    // Inicia o carregamento
    loading.value = true;
    
    // Carrega os tickets com os filtros atuais
    await loadTickets(1);
    
  } catch (error) {
    console.error('Erro ao pesquisar tickets:', error);
  } finally {
    loading.value = false;
  }
};


const resetFilters = () => {
  // Limpa todos os campos de filtro
  searchTitle.value = '';
  searchRequester.value = '';
  searchStatus.value = '';
  searchPriority.value = '';
  
  // Recarrega os dados sem filtros
  currentPage.value = 1;
  loadTickets(1);
};

const searchTitle = ref('');
const searchRequester = ref('');
const searchStatus = ref('');
const searchPriority = ref('');


// Estado para os diálogos
const evolveDialog = ref({
  show: false,
  ticket: null,
  newStatus: '',
  comment: '',
  loading: false
})

const transferDialog = ref({
  show: false,
  ticket: null,
  newAttendantId: null,
  comment: '',
  loading: false
})

// Lista de status disponíveis
const availableStatuses = [
  'OPEN',
  'IN_PROGRESS',
  'RESOLVED',
  'CONCLUDED'
]

const currentPage = ref(1);

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0
});


// Lista de atendentes disponíveis (deve ser carregada do backend)
const availableAttendants = ref([])

// Ordena tickets por prioridade
const priorityOrder = {
  'URGENTE': 0,
  'ALTA': 1,
  'NORMAL': 2,
  'BAIXA': 3
}

const sortedTickets = computed(() => {
  return [...tickets.value].sort((a, b) => {
    return priorityOrder[a.priority] - priorityOrder[b.priority]
  })
})

// Funções auxiliares
const getPriorityColor = (priority) => {
    if (!priority) return 'grey'
  const colors = {
    'URGENTE': 'red',
    'ALTA': 'orange',
    'NORMAL': 'blue',
    'BAIXA': 'green'
  }
  return colors[priority] || 'grey'
}

const getPriorityTextColor = (priority) => {
  return ['URGENTE', 'ALTA'].includes(priority) ? 'white' : 'white'
}

const getStatusColor = (status) => {
  if (!status) return 'grey'
  const colors = {
    'NEW': 'grey',
    'OPEN': 'blue',
    'IN_PROGRESS': 'orange',
    'RESOLVED': 'green',
    'CONCLUDED': 'purple'
  }
  return colors[status] || 'grey'
}

const translateStatus = (status) => {
  if (!status) return '-'
  const translations = {
    'new': 'Novo',
    'OPEN': 'Aberto',
    'IN_PROGRESS': 'Em Andamento',
    'RESOLVED': 'Resolvido',
    'CONCLUDED': 'Concluído',
    'CLOSED': 'Fechado'
  }
  return translations[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
    locale: ptBR
  })
}


const loadServiceHistory = async (serviceId) => {
  try {
    const response = await api.get(`/service/${serviceId}/history`);
    if (response.data.success) {
      evolveDialog.value.ticket.histories = response.data.data;
    }
  } catch (error) {
    console.error('Erro ao carregar histórico:', error);
  }
};


// Funções de ação
const openEvolveDialog = async (ticket) => {
  evolveDialog.value = {
    show: true,
    ticket: { ...ticket },
    newStatus: ticket.status,
    comment: '',
    loading: false
  };

  await loadServiceHistory(ticket.id);
};

const openTransferDialog = (ticket) => {
  transferDialog.value = {
    show: true,
    ticket,
    newAttendantId: null,
    comment: '',
    loading: false
  }
}

const evolveTicket = async () => {
  evolveDialog.value.loading = true
  try {
    await api.put(`/service/${evolveDialog.value.ticket.id}/status`, {
      status: evolveDialog.value.newStatus,
      comment: evolveDialog.value.comment
    })
    await loadTickets()
    evolveDialog.value.show = false
  } catch (error) {
    console.error('Erro ao evoluir ticket:', error)
  } finally {
    evolveDialog.value.loading = false
  }
}

const transferTicket = async () => {
  transferDialog.value.loading = true
  try {
    await api.put(`/service/${transferDialog.value.ticket.id}/transfer`, {
      attendant_id: transferDialog.value.newAttendantId,
      comment: transferDialog.value.comment
    })
    await loadTickets()
    transferDialog.value.show = false
  } catch (error) {
    console.error('Erro ao transferir ticket:', error)
  } finally {
    transferDialog.value.loading = false
  }
}


const handlePageChange = async (page) => {
  await loadTickets(page);
};


const loadTickets = async (page = 1) => {
    loading.value = true;
    try {
        // Verificação de autenticação
        if (!attendantAuthService.isAuthenticated()) {
            console.log('Usuário não autenticado');
            router.push('/attendant/login');
            return;
        }

        // Obter dados do atendente
        const attendant = attendantAuthService.getAttendantData();
        if (!attendant || !attendant.id) {
            console.error('Dados do atendente inválidos');
            return;
        }

        // Criar objeto URLSearchParams para construir a query string
        const params = new URLSearchParams();
        params.append('page', page.toString());
        params.append('per_page', '10');

        // Adicionar filtros somente se existirem valores
        if (searchTitle.value) {
            params.append('title', searchTitle.value);
        }
        if (searchRequester.value) {
            params.append('requester', searchRequester.value);
        }
        if (searchStatus.value) {
            params.append('status', searchStatus.value);
        }
        if (searchPriority.value) {
            params.append('priority', searchPriority.value);
        }

        // Log para debug da URL construída
        console.log('URL da requisição:', `/service/attendant/${attendant.id}?${params.toString()}`);

        // Fazer a requisição
        const response = await api.get(`/service/attendant/${attendant.id}?${params.toString()}`);
        
        // Log da resposta para debug
        console.log('Dados recebidos:', response.data);

        // Processar a resposta
        if (response.data.success) {
            tickets.value = response.data.data;
            meta.value = response.data.meta;
        } else {
            console.error('Resposta sem sucesso:', response.data);
            tickets.value = [];
        }

    } catch (error) {
        // Tratamento de erros mais detalhado
        console.error('Erro ao carregar tickets:', error);
        if (error.response) {
            console.error('Detalhes do erro:', error.response.data);
        }
        tickets.value = [];

        // Tratamento de erro de autenticação
        if (error.response?.status === 401) {
            attendantAuthService.logout();
            router.push('/attendant/login');
        }
    } finally {
        loading.value = false;
    }
};






const loadAttendants = async () => {
  try {
    const response = await api.get('/attendants')
    availableAttendants.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar atendentes:', error)
  }
}


// Replace the current statusOptions with:
const statusOptions = [
  { title: 'Novo', value: 'NEW' },
  { title: 'Aberto', value: 'OPEN' },
  { title: 'Em Andamento', value: 'IN_PROGRESS' },
  { title: 'Resolvido', value: 'RESOLVED' },
  { title: 'Concluído', value: 'CONCLUDED' }
]

// Replace the current priorityOptions with:
const priorityOptions = [
  { title: 'Baixa', value: 'BAIXA' },
  { title: 'Normal', value: 'NORMAL' },
  { title: 'Alta', value: 'ALTA' },
  { title: 'Urgente', value: 'URGENTE' }
]

// Carrega dados iniciais
onMounted(() => {
  loadTickets()
  loadAttendants()
})
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f3f4f6;
}

.ticket-details {
  background-color: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
}


.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background-color: #fff;
  border-bottom: 1px solid #e9ecef;
}


.ticket-title {
  font-size: 1.1rem;
  font-weight: 500;
  color: #1a237e;
  margin: 0;
}

.priority-chip {
  font-weight: 500;
}

.description-container {
  padding: 20px;
  background-color: #ffffff;
  border-radius: 0 0 8px 8px;
}

.description-label {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
  font-weight: 500;
  color: #495057;
  margin-bottom: 12px;
}

.description-content {
  font-size: 0.95rem;
  line-height: 1.6;
  color: #212529;
  padding: 16px;
  background-color: #f8f9fa;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  white-space: pre-wrap;
  margin-bottom: 16px;
}

.ticket-metadata {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding-top: 12px;
  border-top: 1px solid #e9ecef;
}

.metadata-item {
  display: flex;
  align-items: center;
  font-size: 0.85rem;
  color: #6c757d;
}

.metadata-label {
  font-weight: 500;
  margin-right: 4px;
  margin-left: 4px;
}

/* Efeito hover suave */
.description-container:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.3s ease;
}

/* Estilo para texto selecionado */
.description-content::selection {
  background-color: #e3f2fd;
  color: #1a237e;
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


.pagination-wrapper {
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}

.pagination-info {
  color: rgba(0, 0, 0, 0.6);
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  gap: 8px;
  align-items: center;
}

.pagination-button {
  min-width: 40px !important;
  height: 40px !important;
  border-radius: 20px !important;
}

.pagination-button.v-btn--variant-flat {
  background-color: #1a237e !important;
  color: #ffffff !important;
}

.timeline {
  position: relative;
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

.timeline-item {
  position: relative;
  padding-bottom: 24px;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 16px;
  top: 40px;
  bottom: 0;
  width: 2px;
  background: #e0e0e0;
}

.timeline-item:last-child::before {
  display: none;
}

.timeline-content {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 16px;
  margin-top: 8px;
}

/* Estilo para o hover nos itens da timeline */
.timeline-item:hover {
  background: rgba(0, 0, 0, 0.02);
}

/* Estilo para o conteúdo quando houver hover */
.timeline-item:hover .timeline-content {
  background: #f0f2f5;
}
</style>