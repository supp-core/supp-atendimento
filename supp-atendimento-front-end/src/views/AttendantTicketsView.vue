<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="tickets-page">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-medium">Meus Atendimentos</h2>
            <v-btn v-if="isAdmin" color="primary" prepend-icon="mdi-plus" @click="openCreateDialog">
              Criar Chamado para Usuário
            </v-btn>


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
                <v-col cols="12" sm="3">
                  <v-select v-model="searchCategory" :items="categoryOptions" item-title="title" item-value="value"
                    label="Categoria" outlined dense @change="handleFilter"></v-select>
                </v-col>

                <v-col cols="12" sm="3">
                  <v-select v-model="searchServiceType" :items="serviceTypeOptions" item-title="title"
                    item-value="value" label="Tipo de Serviço" outlined dense @change="handleFilter"></v-select>
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
                    <v-btn :prepend-icon="mdiPencilBoxOutline" size="small" color="primary" class="mr-2"
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
        <v-dialog v-model="evolveDialog.show" max-width="800px">
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


              <!-- Dentro do v-card-text do evolveDialog -->
              <div class="admin-fields mt-4 mb-4">
                <div class="section-title d-flex align-center mb-2">
                  <v-icon icon="mdi-tune" class="mr-2"></v-icon>
                  <span>Campos administrativos</span>
                </div>

                <v-row>
                  <!-- Para administradores: comboboxes -->
                  <template v-if="isAdmin">
                    <v-col cols="12" md="6">
                      <v-select v-model="evolveDialog.category_id" :items="categories" item-title="name" item-value="id"
                        label="Categoria" :disabled="evolveDialog.loading"></v-select>
                    </v-col>

                    <v-col cols="12" md="6">
                      <v-select v-model="evolveDialog.service_type_id" :items="serviceTypes" item-title="name"
                        item-value="id" label="Tipo de Atendimento" :disabled="evolveDialog.loading"></v-select>
                    </v-col>
                  </template>

                  <!-- Para atendentes comuns: apenas labels -->
                  <template v-else>
                    <v-col cols="12" md="6">
                      <div class="field-label">Categoria:</div>
                      <div class="field-value">
                        {{ getCategoryName(evolveDialog.ticket?.category?.id) || 'Não definida' }}
                      </div>
                    </v-col>

                    <v-col cols="12" md="6">
                      <div class="field-label">Tipo de Atendimento:</div>
                      <div class="field-value">
                        {{ getServiceTypeName(evolveDialog.ticket?.serviceType?.id) || 'Não definido' }}
                      </div>
                    </v-col>
                  </template>
                </v-row>
              </div>

              <!-- Formulário de nova evolução -->
              <div class="new-update-form mb-6">
                <v-select v-model="evolveDialog.newStatus" :items="availableStatuses" item-title="text"
                  item-value="value" label="Novo Status" required />

                <v-textarea v-model="evolveDialog.comment" label="Comentário" required rows="3"
                  class="mb-4"></v-textarea>
              </div>


              <!-- Exibição de anexos -->
              <div v-if="evolveDialog.ticket?.attachments && evolveDialog.ticket.attachments.length > 0"
                class="attachments-section mb-4 mt-4">
                <div class="section-title d-flex align-center mb-2">
                  <v-icon icon="mdi-paperclip" class="mr-2"></v-icon>
                  <span>Anexos ({{ evolveDialog.ticket.attachments.length }})</span>
                </div>
                <v-list class="attachment-list">
                  <v-list-item v-for="attachment in evolveDialog.ticket.attachments" :key="attachment.id"
                    class="attachment-item">
                    <template v-slot:prepend>
                      <v-icon icon="mdi-file-document-outline"></v-icon>
                    </template>
                    <v-list-item-title>{{ attachment.originalFilename }}</v-list-item-title>
                    <template v-slot:append>
                      <v-btn color="primary" variant="text" size="small" @click="downloadAttachment(attachment)"
                        :loading="downloading === attachment.id">
                        <v-icon start>mdi-download</v-icon>
                        Download
                      </v-btn>
                    </template>
                  </v-list-item>
                </v-list>
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


        <AdminCreateTicket v-model="createDialog" @created="handleTicketCreated" />




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
import AdminCreateTicket from '@/components/common/AdminCreateTicket.vue' // Importe o novo componente
import api from '@/services/api'
import { attendantAuthService } from '@/services/attendant-auth.service'
import { mdiPencilBoxOutline } from "@mdi/js";

const { sidebarCollapsed } = useSidebar()
const loading = ref(false)
const tickets = ref([])

// Dentro do script setup
const loadCategoriesview = async () => {
  try {
    const response = await api.get('/categories');
    if (response.data.success) {
      console.log('Categorias carregadas =========>>>>>:', categories.value);
      categories.value = response.data.data;
    }
  } catch (error) {
    console.error('Erro ao carregar categorias:', error);
  }
};


const loadServiceTypes = async () => {
  try {
    const response = await api.get('/service-types');
    if (response.data.success) {
      serviceTypes.value = response.data.data;
    }
  } catch (error) {
    console.error('Erro ao carregar tipos de serviço:', error);
  }
};


// Funções auxiliares para obter nomes baseados nos IDs
const getCategoryName = (categoryId) => {
  if (!categoryId) return null;
  const category = categories.value.find(c => c.id === categoryId);
  return category ? category.name : null;
};

const getServiceTypeName = (serviceTypeId) => {
  if (!serviceTypeId) return null;
  const serviceType = serviceTypes.value.find(st => st.id === serviceTypeId);
  return serviceType ? serviceType.name : null;
};



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

const createDialog = ref(false)
const attendantData = ref(null)
const categories = ref([]);
const serviceTypes = ref([]);

const isAdmin = computed(() => {
  return attendantData.value && attendantData.value.function === 'Admin'
})

const resetFilters = () => {
  // Limpa todos os campos de filtro
  searchTitle.value = '';
  searchRequester.value = '';
  searchStatus.value = '';
  searchPriority.value = '';
  searchCategory.value = '';
  searchServiceType.value = '';

  // Recarrega os dados sem filtros
  currentPage.value = 1;
  loadTickets(1);
};

const searchTitle = ref('');
const searchRequester = ref('');
const searchStatus = ref('');
const searchPriority = ref('');
const searchCategory = ref('');
const searchServiceType = ref('');

const handleTicketCreated = (newTicket) => {
  // Adicionar o novo ticket à lista ou recarregar os dados
  loadTickets()
  // Exibir mensagem de sucesso
  alert('Atendimento criado com sucesso!')
}

// Estado para os diálogos
const evolveDialog = ref({
  show: false,
  ticket: {
    attachments: [] // Inicialização explícita
  }, newStatus: '',
  comment: '',
  category_id: null,
  service_type_id: null,
  loading: false
})

const openCreateDialog = () => {
  createDialog.value = true
}


const transferDialog = ref({
  show: false,
  ticket: null,
  newAttendantId: null,
  comment: '',
  loading: false
})

// Depois (em português com mapeamento)
const availableStatuses = [
  { text: 'Aberto', value: 'OPEN' },
  { text: 'Em Andamento', value: 'IN_PROGRESS' },
  { text: 'Resolvido', value: 'RESOLVED' },
  { text: 'Concluído', value: 'CONCLUDED' }
]

// Adicione estes computed properties para formatar as opções dos selects
const categoryOptions = computed(() => {
  // Adicionar opção vazia no início
  return [
    { title: 'Todas as categorias', value: '' },
    ...categories.value.map(category => ({
      title: category.name,
      value: category.id
    }))
  ];
});


const serviceTypeOptions = computed(() => {
  // Adicionar opção vazia no início
  return [
    { title: 'Todos os tipos de serviço', value: '' },
    ...serviceTypes.value.map(type => ({
      title: type.name,
      value: type.id
    }))
  ];
});

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
    console.log('Carregando histórico para o ticket:', serviceId);

    // Carrega o histórico
    const historyResponse = await api.get(`/service/${serviceId}/history`);

    if (historyResponse.data.success) {
      evolveDialog.value.ticket.histories = historyResponse.data.data;
      console.log('Histórico carregado com sucesso:', historyResponse.data.data);
    } else {
      console.warn('Resposta de histórico sem sucesso:', historyResponse.data);
    }

    // Carrega os detalhes completos do ticket para ter acesso aos anexos
    console.log('Carregando detalhes para o ticket:', serviceId);
    const detailsResponse = await api.get(`/service/${serviceId}`);


    if (detailsResponse.data.success) {
      // Mantém o histórico já carregado, mas atualiza o resto das informações
      evolveDialog.value.ticket = {
        ...detailsResponse.data.data,
        histories: evolveDialog.value.ticket.histories || []
      };
      console.log('Detalhes carregados com sucesso:', detailsResponse.data.data);
    } else {
      console.warn('Resposta de detalhes sem sucesso:', detailsResponse.data);
    }
  } catch (error) {
    console.error('Erro ao carregar dados do ticket aqqqqqqqqq:', error);
    // Não deixe o erro parar a execução - mantenha o que já temos
  }
};




const openEvolveDialog = async (ticket) => {
  try {
    // Inicializa o diálogo com os dados básicos do ticket
    evolveDialog.value = {
      show: true,
      ticket: {
        ...ticket,
        attachments: ticket.attachments || [],
        histories: []
      },
      newStatus: ticket.status === 'NOVO' ? 'OPEN' : ticket.status,
      comment: '',
      // Inicializar com os valores existentes do ticket
      category_id: ticket.category?.id || null,
      service_type_id: ticket.serviceType?.id || null,
      loading: true
    };

    // Carrega detalhes completos do ticket
    await loadServiceHistory(ticket.id);

    const response = await api.get(`/service/${ticket.id}`);

    if (response.data.success) {
      // Importante: preservar os valores de categoria e tipo de atendimento
      const categoryId = response.data.data.category?.id || ticket.category?.id;
      const serviceTypeId = response.data.data.serviceType?.id || ticket.serviceType?.id;

      evolveDialog.value.ticket = {
        ...response.data.data,
        histories: evolveDialog.value.ticket.histories || [],
        attachments: response.data.data.attachments || []
      };

      // Atualizar os valores nos campos do formulário
      evolveDialog.value.category_id = categoryId;
      evolveDialog.value.service_type_id = serviceTypeId;
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes do ticket:', error);
  } finally {
    evolveDialog.value.loading = false;
  }
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
    const updateData = {
      status: evolveDialog.value.newStatus,
      comment: evolveDialog.value.comment
    };

    // Adiciona campos administrativos apenas se o usuário for admin
    if (isAdmin.value) {
      if (evolveDialog.value.category_id) {
        updateData.category_id = evolveDialog.value.category_id;
      }

      if (evolveDialog.value.service_type_id) {
        updateData.service_type_id = evolveDialog.value.service_type_id;
      }
    }

    await api.put(`/service/${evolveDialog.value.ticket.id}/status`, {
      updateData
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
    if (searchCategory.value) {
      params.append('category_id', searchCategory.value);
    }
    if (searchServiceType.value) {
      params.append('service_type_id', searchServiceType.value);
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
  { title: 'Novo', value: 'new' },
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



// Variável de controle para download
// No script setup do componente
// Adicione um novo ref para controlar o download
const downloading = ref(null);

// Adicione a função para download
const downloadAttachment = async (attachment) => {
  try {
    downloading.value = attachment.id;

    // Fazer requisição para baixar o anexo
    const response = await api.get(`/service/attachment/${attachment.id}`, {
      responseType: 'blob'
    });

    console.log('DOnwload====>>>');

    // Criar URL de objeto para o blob
    const url = window.URL.createObjectURL(response.data);

    // Criar elemento de link para download
    const link = document.createElement('a');
    link.href = url;
    link.download = attachment.originalFilename;

    // Adicionar à página, clicar e remover
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Liberar o objeto URL
    window.URL.revokeObjectURL(url);

  } catch (error) {
    console.error('Erro ao baixar anexo:', error);
    // Exibir mensagem de erro se necessário
  } finally {
    downloading.value = null;
  }
};

const loadCategoriesAndServiceTypes = async () => {
  try {
    // Carrega categorias
    const categoryResponse = await api.get('/categories');
    if (categoryResponse.data.success) {
      categories.value = categoryResponse.data.data;
      console.log('Categorias carregadas com sucesso:', categories.value);
    } else {
      console.warn('Falha ao carregar categorias:', categoryResponse.data);
    }

    // Carrega tipos de serviço
    const serviceTypeResponse = await api.get('/service-types');
    if (serviceTypeResponse.data.success) {
      serviceTypes.value = serviceTypeResponse.data.data;
      console.log('Tipos de serviço carregados com sucesso:', serviceTypes.value);
    } else {
      console.warn('Falha ao carregar tipos de serviço:', serviceTypeResponse.data);
    }
  } catch (error) {
    console.error('Erro ao carregar dados de filtro:', error);
  }
};

// Carrega dados iniciais
onMounted(() => {
  attendantData.value = attendantAuthService.getAttendantData()
  loadTickets();
  loadAttendants();
  loadCategoriesview();
  loadServiceTypes();
  loadCategoriesAndServiceTypes(); // Nova função combinada
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


.admin-fields {
  background-color: #f5f5f5;
  border-radius: 8px;
  padding: 16px;
  margin-top: 20px;
  border: 1px dashed #1a237e;
}

.section-title {
  font-weight: 500;
  color: #1a237e;
  margin-bottom: 12px;
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


.field-label {
  font-weight: 500;
  color: rgba(0, 0, 0, 0.6);
  font-size: 0.875rem;
}

.field-value {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 4px;
  font-size: 0.9rem;
  color: rgba(0, 0, 0, 0.87);
  border: 1px solid rgba(0, 0, 0, 0.12);
  min-height: 48px;
  display: flex;
  align-items: center;
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


.attachments-section {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
  margin-top: 20px;
}

.section-title {
  color: #1a237e;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 8px;
}

.attachment-list {
  background-color: transparent !important;
  padding: 0;
}

.attachment-item {
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.attachment-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

/* Adicionar padding à direita para evitar que o botão fique muito próximo do texto */
:deep(.attachment-item .v-list-item__content) {
  padding-right: 16px;
}

.attachments-container {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
  border: 1px solid #e0e0e0;
}

.attachments-header {
  font-weight: 500;
  color: #1a237e;
  display: flex;
  align-items: center;
}

.attachments-list {
  background-color: white;
  border-radius: 4px;
  overflow: hidden;
}

:deep(.v-list-item) {
  border-bottom: 1px solid #f0f0f0;
}

:deep(.v-list-item:last-child) {
  border-bottom: none;
}
</style>