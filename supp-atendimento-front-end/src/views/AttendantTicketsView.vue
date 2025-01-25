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

          <v-card class="tickets-table">
            <!-- Barra de carregamento -->
            <v-progress-linear
              v-if="loading"
              indeterminate
              color="primary"
              class="mb-4"
            ></v-progress-linear>

            <!-- Tabela de tickets -->
            <v-table hover>
              <thead>
                <tr>
                  <th class="text-left">Título</th>
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
                  <td>
                    <v-chip
                      :color="getPriorityColor(ticket.priority)"
                      :text-color="getPriorityTextColor(ticket.priority)"
                      size="small"
                    >
                      {{ ticket.priority }}
                    </v-chip>
                  </td>
                  <td>
                    <v-chip
                      :color="getStatusColor(ticket.status)"
                      size="small"
                    >
                      {{ translateStatus(ticket.status) }}
                    </v-chip>
                  </td>
                  <td>{{ formatDate(ticket.dates.created) }}</td>
                  <td>{{ ticket.dates.concluded ? formatDate(ticket.dates.concluded) : '-' }}</td>
                  <td class="text-center">
                    <v-btn
                      icon="mdi-progression"
                      size="small"
                      color="primary"
                      class="mr-2"
                      @click="openEvolveDialog(ticket)"
                      :disabled="ticket.status === 'CONCLUDED'"
                    >
                      <v-tooltip activator="parent" location="top">
                        Evoluir
                      </v-tooltip>
                    </v-btn>
                    <v-btn
                      icon="mdi-account-switch"
                      size="small"
                      color="secondary"
                      @click="openTransferDialog(ticket)"
                      :disabled="ticket.status === 'CONCLUDED'"
                    >
                      <v-tooltip activator="parent" location="top">
                        Transferir
                      </v-tooltip>
                    </v-btn>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card>
        </div>

        <!-- Modal para Evolução do Ticket -->
        <v-dialog v-model="evolveDialog.show" max-width="500px">
          <v-card>
            <v-card-title>Evoluir Atendimento</v-card-title>
            <v-card-text>
              <v-select
                v-model="evolveDialog.newStatus"
                :items="availableStatuses"
                label="Novo Status"
                required
              ></v-select>
              <v-textarea
                v-model="evolveDialog.comment"
                label="Comentário"
                required
                rows="3"
              ></v-textarea>
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
              <v-select
                v-model="transferDialog.newAttendantId"
                :items="availableAttendants"
                item-title="name"
                item-value="id"
                label="Novo Responsável"
                required
              ></v-select>
              <v-textarea
                v-model="transferDialog.comment"
                label="Motivo da Transferência"
                required
                rows="3"
              ></v-textarea>
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
  const translations = {
    'NEW': 'Novo',
    'OPEN': 'Aberto',
    'IN_PROGRESS': 'Em Andamento',
    'RESOLVED': 'Resolvido',
    'CONCLUDED': 'Concluído'
  }
  return translations[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
    locale: ptBR
  })
}

// Funções de ação
const openEvolveDialog = (ticket) => {
  evolveDialog.value = {
    show: true,
    ticket,
    newStatus: ticket.status,
    comment: '',
    loading: false
  }
}

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

const loadTickets = async () => {
  loading.value = true
  try {
    const attendant = attendantAuthService.getAttendantData()
    const response = await api.get(`/service/attendant/${attendant.id}`)
    tickets.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar tickets:', error)
  } finally {
    loading.value = false
  }
}

const loadAttendants = async () => {
  try {
    const response = await api.get('/attendants')
    availableAttendants.value = response.data.data
  } catch (error) {
    console.error('Erro ao carregar atendentes:', error)
  }
}

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
</style>