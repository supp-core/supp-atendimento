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
          <!-- Filtros de Pesquisa -->
          <v-card class="mb-4">
            <v-card-text>
              <v-row>
                <!-- Campo de pesquisa por título -->
                <v-col cols="12" sm="3">
                  <v-text-field v-model="searchName" label="Pesquisar por Nome" outlined dense
                    @input="handleSearchInput"></v-text-field>
                </v-col>

                <!-- Campo de filtro por status -->
                <v-col cols="12" sm="3">
                  <v-select v-model="searchStatus" :items="statusOptions" item-title="title" item-value="value"
                    label="Status" outlined dense @change="handleFilter"></v-select>
                </v-col>

                <!-- Intervalo de datas compacto -->
                <v-col cols="12" sm="3">
                  <div class="date-compact">
                    <div class="date-label mb-1">Período</div>
                    <div class="date-inputs">
                      <v-menu v-model="startDateMenu" :close-on-content-click="false" min-width="auto">
                        <template v-slot:activator="{ props }">
                          <v-text-field v-model="formattedStartDate" dense hide-details placeholder="De"
                            prepend-inner-icon="mdi-calendar" readonly v-bind="props" variant="outlined"
                            class="date-input"></v-text-field>
                        </template>
                        <v-date-picker v-model="startDate" @update:model-value="startDateMenu = false" locale="pt-BR"></v-date-picker>
                      </v-menu>

                      <v-menu v-model="endDateMenu" :close-on-content-click="false" min-width="auto">
                        <template v-slot:activator="{ props }">
                          <v-text-field v-model="formattedEndDate" dense hide-details placeholder="Até"
                            prepend-inner-icon="mdi-calendar" readonly v-bind="props" variant="outlined"
                            class="date-input"></v-text-field>
                        </template>
                        <v-date-picker v-model="endDate" @update:model-value="endDateMenu = false" locale="pt-BR"></v-date-picker>
                      </v-menu>
                    </div>
                  </div>
                </v-col>

                <!-- Filtro de Prioridade -->
                <v-col cols="12" sm="3">
                  <v-select v-model="searchPriority" :items="priorityOptions" item-title="title" item-value="value"
                    label="Prioridade" outlined dense @change="handleFilter"></v-select>
                </v-col>

                <!-- Nova linha para botões (mantida como estava) -->
                <v-col cols="12" class="d-flex align-center mt-2">
                  <v-btn color="primary" @click="handleFilter" :loading="loading" class="me-2">
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
            <v-table hover>
              <thead>
                <tr>
                  <th class="px-4 py-3">ID</th>
                  <th class="px-4 py-3">Título</th>
                  <th class="text-left">Prioridade</th> <!-- Nova coluna -->
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">Setor</th>
                  <th class="px-4 py-3">Responsável</th>
                  <th class="px-4 py-3">Data de Criação</th>
                  <th class="px-4 py-3">Acompanhar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in tickets" :key="ticket.id">
                  <td class="px-4 py-3">
                    <span class="id-prefix">#</span>{{ ticket.id }}
                  </td>
                  <td class="px-4 py-3">{{ ticket.title }}</td>
                  <td>
                    <v-chip :color="getPriorityColor(ticket.priority)"
                      :text-color="getPriorityTextColor(ticket.priority)" size="small" class="priority-chip">
                      {{ ticket.priority }}
                    </v-chip>
                  </td>
                  <td class="px-4 py-3">
                    <v-chip :color="getStatusColor(ticket.status)" :class="['status-chip', ticket.status.toLowerCase()]"
                      size="small">
                      {{ translateStatus(ticket.status) }}
                    </v-chip>
                  </td>
                  <td class="px-4 py-3">{{ ticket.responsible.sector?.name ?? ticket.sector.name }}</td>

                  <!-- <td class="px-4 py-3">{{ ticket.requester?.name }}</td> -->
                  <td class="px-4 py-3">{{ ticket.responsible?.name || 'Não atribuído' }}</td>
                  <td class="px-4 py-3">{{ formatDate(ticket.dates.created) }}</td>
                  <td class="px-4 py-3">
                    <div class="d-flex gap-2">
                      <v-btn variant="text" density="comfortable" size="small" @click="openTicketDetails(ticket)"
                        class="action-button" title="Acompanhar Ticket">
                        <span class="icon-text">📄</span>

                      </v-btn>

                    </div>
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
      </div>
    </div>
  </div>

  <!-- Adicione o componente do modal ao final do template, antes do fechamento da última div -->
  <TicketDetailsModal v-model="showDetailsModal" :ticket="selectedTicket" />
</template>

<script setup>

import TicketDetailsModal from '@/components/tickets/TicketDetailsModal.vue'
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import api from '@/services/api';
import AppHeader from '@/components/common/AppHeader.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import { useSidebar } from '@/composables/useSidebar';
import { authService } from '@/services/auth.service';
import DateRangeSelector from '@/components/common/DateRangeSelector.vue';


// Estado para os menus de data
const startDateMenu = ref(false);
const endDateMenu = ref(false);

// Valores das datas
const startDate = ref(null);
const endDate = ref(null);

// Formatação para exibição
// Substitua as funções computed existentes por estas versões corrigidas
const formattedStartDate = computed(() => {
  if (!startDate.value) return '';

  try {
    // Verificar se é um objeto Date
    if (startDate.value instanceof Date) {
      return format(startDate.value, 'dd/MM/yyyy', { locale: ptBR });
    }

    // Verificar se é uma string ISO
    if (typeof startDate.value === 'string') {
      return format(new Date(startDate.value), 'dd/MM/yyyy', { locale: ptBR });
    }

    return '';
  } catch (error) {
    console.error('Erro ao formatar data inicial:', error);
    return '';
  }
});

const formattedEndDate = computed(() => {
  if (!endDate.value) return '';

  try {
    // Verificar se é um objeto Date
    if (endDate.value instanceof Date) {
      return format(endDate.value, 'dd/MM/yyyy', { locale: ptBR });
    }

    // Verificar se é uma string ISO
    if (typeof endDate.value === 'string') {
      return format(new Date(endDate.value), 'dd/MM/yyyy', { locale: ptBR });
    }

    return '';
  } catch (error) {
    console.error('Erro ao formatar data final:', error);
    return '';
  }
});

// Adicione também um tratamento mais robusto na função watch
watch([startDate, endDate], () => {
  if (startDate.value || endDate.value) {
    console.log('Datas selecionadas:', { startDate: startDate.value, endDate: endDate.value });
    handleFilter();
  }
});

watch([startDate, endDate], () => {
  if (startDate.value || endDate.value) {
    handleFilter();
  }
});


const selectedTicket = ref(null)
const showDetailsModal = ref(false)

// Função para carregar os dados do usuário
const carregarDadosUsuario = () => {
  const userData = authService.getUser();
  if (userData) {
    nomeUsuario.value = userData.name; // Assume que o usuário tem uma propriedade 'name'
    console.log('Dados do usuário carregados:', userData);
  } else {
    console.error('Dados do usuário não encontrados');
    router.push('/login');
  }
};

const getPriorityColor = (priority) => {
  const colors = {
    'URGENTE': 'red-darken-1',
    'ALTA': 'orange-darken-1',
    'NORMAL': 'blue',
    'BAIXA': 'green'
  }
  return colors[priority] || 'grey'
}

const getPriorityTextColor = (priority) => {
  return ['URGENTE', 'ALTA'].includes(priority) ? 'white' : 'white'
}


const getStatusColor = (status) => {
  const colors = {
    'NEW': 'grey',
    'OPEN': 'blue',
    'IN_PROGRESS': 'orange',
    'RESOLVED': 'green',
    'CONCLUDED': 'purple'
  }
  return colors[status] || 'grey'
}
// Adicione a função que abre o modal
const openTicketDetails = async (ticket) => {
  try {
    console.log('Abrindo detalhes do ticket aq:', ticket) // Log para debug
    selectedTicket.value = ticket
    showDetailsModal.value = true

    // Carrega o histórico do ticket
    const response = await api.get(`/service/${ticket.id}/history`)
    if (response.data.success) {
      selectedTicket.value = {
        ...ticket,
        histories: response.data.data
      }
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes do ticket:', error)
  }
}

const { sidebarCollapsed } = useSidebar();
const router = useRouter();
const tickets = ref([]);
const loading = ref(false);
const nomeUsuario = ref(''); // Adicionando a referência ao nome do usuário

const currentPage = ref(1);
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0
});


// Filtros de pesquisa
const searchName = ref('');
const searchStatus = ref('');
/*const searchDateRange = ref([]);*/
const dateRange = ref({ startDate: null, endDate: null });

const searchPriority = ref('');
const dateMenu = ref(false);

const statusOptions = [
  { title: 'Novo', value: 'new' },
  { title: 'Aberto', value: 'OPEN' },
  { title: 'Em Andamento', value: 'IN_PROGRESS' },
  { title: 'Resolvido', value: 'RESOLVED' },
  { title: 'Concluído', value: 'CONCLUDED' }
];
const priorityOptions = [
  { title: 'Baixa', value: 'BAIXA' },
  { title: 'Normal', value: 'NORMAL' },
  { title: 'Alta', value: 'ALTA' },
  { title: 'Urgente', value: 'URGENTE' }
];

const applyFilters = () => {
  filteredTickets.value = tickets.value.filter(ticket => {
    const matchesName = ticket.title.toLowerCase().includes(searchName.value.toLowerCase());
    const matchesStatus = searchStatus.value ? ticket.status === searchStatus.value : true;
    const matchesPriority = searchPriority.value ? ticket.priority === searchPriority.value : true;
    const matchesDateRange = searchDateRange.value.length === 2 ?
      new Date(ticket.dates.created) >= new Date(searchDateRange.value[0]) &&
      new Date(ticket.dates.created) <= new Date(searchDateRange.value[1]) : true;

    return matchesName && matchesStatus && matchesPriority && matchesDateRange;
  });
};



const resetFilters = () => {
  // Limpa todos os campos de filtro
  searchName.value = '';
  searchStatus.value = '';
  searchPriority.value = '';
  startDate.value = null;
  endDate.value = null;

  // Recarrega os dados
  currentPage.value = 1;
  loadTickets(1);
};



const handlePageChange = async (page) => {
  await loadTickets(page);
};

const handleDateRangeChange = () => {
  // Quando o intervalo de datas mudar, acionar a pesquisa
  handleFilter();
};

const translateStatus = (status) => {
  const translations = {
    'new': 'Novo',
    'OPEN': 'Aberto',
    'IN_PROGRESS': 'Em Andamento',
    'RESOLVED': 'Resolvido',
    'CLOSED': 'Fechado',
    'CONCLUDED': 'Concluído'
  };
  return translations[status] || status;
};



const formatDate = (dateString) => {
  if (!dateString) return '';
  return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
    locale: ptBR
  });
};

const loadTickets = async (page = 1) => {
  loading.value = true;
  try {
    if (!authService.isAuthenticated()) {
      console.log('Usuário não autenticado');
      router.push('/login');
      return;
    }

    // Construir os parâmetros de filtro
    const params = new URLSearchParams({
      page: page.toString()
    });
    // Adicionar filtros apenas se tiverem valor
    if (searchName.value) {
      params.append('title', searchName.value);
    }
    if (searchStatus.value) {
      params.append('status', searchStatus.value);
    }
    if (searchPriority.value) {
      params.append('priority', searchPriority.value);
    }

    
    if (startDate.value) {
      // Certifique-se de que a data está no formato ISO
      let formattedStartDate = startDate.value;
      if (startDate.value instanceof Date) {
        formattedStartDate = startDate.value.toISOString().split('T')[0];
      }
      params.append('start_date', formattedStartDate);
    }
    
    if (endDate.value) {
      // Certifique-se de que a data está no formato ISO
      let formattedEndDate = endDate.value;
      if (endDate.value instanceof Date) {
        formattedEndDate = endDate.value.toISOString().split('T')[0];
      }
      params.append('end_date', formattedEndDate);
    }

    const response = await api.get(`/service/my-tickets?${params.toString()}`);


    if (response.data.success) {
      tickets.value = response.data.data;
      meta.value = response.data.meta;
    } else {
      tickets.value = [];
    }
  } catch (error) {
    console.error('Erro ao carregar tickets:', error);
    tickets.value = [];

    if (error.response?.status === 401) {
      authService.logout();
      router.push('/login');
    }
  } finally {
    loading.value = false;
  }
};

// Função para acionar a pesquisa
const handleFilter = async () => {
  try {
    // Reseta para a primeira página
    currentPage.value = 1;

    // Inicia o carregamento
    loading.value = true;

    // Carrega os tickets com os filtros
    await loadTickets(1);

  } catch (error) {
    console.error('Erro ao filtrar tickets:', error);
  } finally {
    loading.value = false;
  }
};

// Adicione um debounce para o campo de pesquisa por nome
let searchTimeout;
const handleSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleFilter();
  }, 500); // Aguarda 500ms após o usuário parar de digitar
};

const createTicket = () => router.push('/tickets/create');
const viewTicket = (id) => router.push(`/tickets/${id}`);
const editTicket = (id) => router.push(`/tickets/${id}/edit`);

onMounted(() => {
  carregarDadosUsuario(); // Carrega os dados do usuário quando o componente é montado
  loadTickets();
});
</script>

<style scoped>
.intervalo-datas {
  width: 100%;
}

.date-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-field {
  flex: 1;
}

.date-separator {
  margin: 0 4px;
  color: rgba(0, 0, 0, 0.6);
}

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

/* Adicione estes novos estilos */
.v-btn {
  text-transform: none !important;
  font-weight: 500 !important;
}

.v-btn:hover {
  opacity: 0.9;
}

.d-flex {
  gap: 8px;
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


.pagination-container {
  border-top: 1px solid #e0e0e0;
}

:deep(.v-pagination__item) {
  color: #1a237e;
}

:deep(.v-pagination__item--active) {
  background-color: #1a237e !important;
}


.pagination-wrapper {
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
  background-color: #ffffff;
}

.pagination-info {
  color: rgba(0, 0, 0, 0.6);
  font-size: 0.875rem;
  font-weight: 400;
  letter-spacing: 0.15px;
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
  font-weight: 500 !important;
  letter-spacing: 0.0892857143em !important;
  text-transform: none !important;
  transition: background-color 0.2s ease-in-out !important;
  cursor: pointer !important;
  /* Adiciona a maozinha em todos os botões */
}

/* Estilo para o botão da página atual */
.pagination-button.v-btn--variant-flat {
  background-color: #1a237e !important;
  color: #ffffff !important;
  box-shadow: 0 3px 1px -2px rgba(0, 0, 0, .2), 0 2px 2px 0 rgba(0, 0, 0, .14), 0 1px 5px 0 rgba(0, 0, 0, .12) !important;
  cursor: default !important;
  /* Remove a maozinha do botão ativo */

}

.pagination-button:hover {
  background-color: rgba(26, 35, 126, 0.04) !important;
}

.pagination-button:disabled {
  color: rgba(0, 0, 0, 0.38) !important;
  background-color: rgba(0, 0, 0, 0.12) !important;
  cursor: default !important;
}

.pagination-button:hover:not(:disabled):not(.v-btn--variant-flat) {
  background-color: rgba(26, 35, 126, 0.04) !important;
  transform: translateY(-1px);
  /* Leve efeito de elevação ao passar o mouse */
}

/* Solução revisada para o componente de período */
.date-compact {
  display: flex;
  flex-direction: column;
  height: 56px; /* Altura fixa para corresponder aos outros campos */
  padding-top: 0; /* Remove o padding superior */
}

.date-label {
  font-size: 12px; /* Tamanho de fonte reduzido para corresponder aos labels do Vuetify */
  color: rgba(0, 0, 0, 0.6);
  padding-top: 0;
  margin-bottom: 3px; /* Espaçamento menor entre o label e os campos */
  line-height: 12px; /* Altura da linha reduzida */

  transform: translateY(-4px); /* Move o label 4px para cima */

}

.date-inputs {
  display: flex;
  gap: 8px;
  height: 40px; /* Altura fixa para os inputs */
}

.date-input {
  flex: 1;
  margin-top: 0 !important; /* Remove margens automáticas */
  margin-bottom: 0 !important;
}

/* Remover padding interno dos campos para alinhar corretamente */
:deep(.date-input .v-field__field) {
  padding-top: 0 !important;
  padding-bottom: 0 !important;
}

/* Garantir que os campos de data tenham o mesmo estilo que os outros */
:deep(.date-input .v-field__outline) {
  --v-field-border-width: 1px !important;
  border-width: var(--v-field-border-width) !important;
}

/* Aplicar estilo consistente aos inputs */
:deep(.v-col) {
  padding-top: 6px;
  padding-bottom: 6px;
}

/* Corrige o alinhamento do container do período */

</style>