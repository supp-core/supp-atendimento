<template>
  <div class="dashboard">
    <component :is="headerComponent" />
    <div class="dashboard-layout">
      <component :is="sidebarComponent" />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="page-container" v-if="ticket">
          <div class="d-flex align-center mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="$router.back()" />
            <h2 class="text-h6 ml-2">Demanda #{{ ticket.id }} — {{ ticket.title }}</h2>
            <v-spacer />
            <v-chip v-if="ticket.project" color="primary" class="mr-2">{{ ticket.project.acronym }}</v-chip>
            <v-chip :color="statusColor(ticket.status)" class="mr-2">{{ ticket.status }}</v-chip>
            <v-chip :color="priorityColor(ticket.priority)">{{ ticket.priority }}</v-chip>
          </div>

          <v-row>
            <v-col cols="12" md="8">
              <v-card class="mb-4">
                <v-card-title>Descrição</v-card-title>
                <v-card-text>{{ ticket.description }}</v-card-text>
              </v-card>

              <v-card>
                <v-tabs v-model="activeTab">
                  <v-tab value="history">Histórico</v-tab>
                  <v-tab value="team">Equipe</v-tab>
                  <v-tab value="evolution">Evoluções</v-tab>
                  <v-tab value="attachments">Anexos</v-tab>
                </v-tabs>
                <v-card-text>
                  <v-tabs-window v-model="activeTab">
                    <v-tabs-window-item value="history">
                      <div v-for="h in ticket.history" :key="h.id" class="timeline-item mb-3">
                        <div class="d-flex align-center">
                          <v-icon size="small" class="mr-1">mdi-clock-outline</v-icon>
                          <span class="text-caption text-grey">{{ formatDate(h.date) }}</span>
                          <strong class="ml-2">{{ h.responsible?.name }}</strong>
                        </div>
                        <v-chip size="x-small" :color="statusColor(h.status_post)" class="my-1">
                          {{ h.status_prev }} → {{ h.status_post }}
                        </v-chip>
                        <p class="text-body-2 mb-0">{{ h.comment }}</p>
                        <v-divider class="mt-2" />
                      </div>
                      <div v-if="!ticket.history?.length" class="text-grey text-body-2">Sem histórico.</div>
                    </v-tabs-window-item>

                    <v-tabs-window-item value="team">
                      <TicketAttendants :service-id="ticket.id" />
                    </v-tabs-window-item>

                    <v-tabs-window-item value="evolution">
                      <TicketEvolution :service-id="ticket.id" />
                    </v-tabs-window-item>

                    <v-tabs-window-item value="attachments">
                      <div v-if="ticket.attachments?.length">
                        <v-list>
                          <v-list-item v-for="att in ticket.attachments" :key="att.id">
                            <template #prepend><v-icon>mdi-file-document-outline</v-icon></template>
                            <v-list-item-title>{{ att.originalFilename }}</v-list-item-title>
                            <template #append>
                              <v-btn variant="text" size="small" @click="download(att)">
                                <v-icon>mdi-download</v-icon>
                              </v-btn>
                            </template>
                          </v-list-item>
                        </v-list>
                      </div>
                      <div v-else class="text-grey text-body-2">Sem anexos.</div>
                    </v-tabs-window-item>
                  </v-tabs-window>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card class="mb-4">
                <v-card-title class="text-subtitle-1">Informações</v-card-title>
                <v-card-text>
                  <div class="info-row"><strong>Solicitante:</strong> {{ ticket.requester?.name }}</div>
                  <div class="info-row"><strong>Responsável:</strong> {{ ticket.responsible?.name || '—' }}</div>
                  <div class="info-row"><strong>Setor:</strong> {{ ticket.sector?.name }}</div>
                  <div class="info-row"><strong>Categoria:</strong> {{ ticket.category?.name || '—' }}</div>
                  <div class="info-row"><strong>Tipo:</strong> {{ ticket.serviceType?.name || '—' }}</div>
                  <div class="info-row">
                    <strong>Projeto:</strong>
                    <v-chip v-if="ticket.project" size="small" color="primary" class="ml-1">{{ ticket.project.acronym }}</v-chip>
                    <span v-else class="ml-1">—</span>
                  </div>
                  <div class="info-row"><strong>Criado em:</strong> {{ formatDate(ticket.dates?.created) }}</div>
                  <div class="info-row"><strong>Concluído em:</strong> {{ ticket.dates?.concluded ? formatDate(ticket.dates.concluded) : '—' }}</div>
                </v-card-text>
              </v-card>

              <v-card v-if="isAttendant && !ticket.project" class="mb-4">
                <v-card-title class="text-subtitle-1">Vincular Projeto</v-card-title>
                <v-card-text>
                  <v-autocomplete
                    v-model="linkProjectId"
                    :items="activeProjects"
                    :item-title="p => `[${p.acronym}] ${p.name}`"
                    item-value="id"
                    label="Selecionar Projeto"
                    clearable
                  />
                  <v-btn color="primary" size="small" class="mt-2" @click="linkProject" :disabled="!linkProjectId">
                    Vincular
                  </v-btn>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </div>

        <div v-else-if="loading" class="d-flex justify-center mt-8">
          <v-progress-circular indeterminate color="primary" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { authService } from '@/services/auth.service'
import { ticketsService } from '@/services/tickets.service'
import { useProjectStore } from '@/stores/projectStore'
import { useSidebar } from '@/composables/useSidebar'
import AppHeader from '@/components/common/AppHeader.vue'
import AppSidebar from '@/components/common/AppSidebar.vue'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import TicketAttendants from '@/components/ticket/TicketAttendants.vue'
import TicketEvolution from '@/components/ticket/TicketEvolution.vue'
import api from '@/services/api'

const route = useRoute()
const { sidebarCollapsed } = useSidebar()
const projectStore = useProjectStore()

const ticket = ref(null)
const loading = ref(false)
const activeTab = ref('history')
const linkProjectId = ref(null)

const isAttendant = computed(() => authService.isAttendant())
const headerComponent = computed(() => isAttendant.value ? AttendantHeader : AppHeader)
const sidebarComponent = computed(() => isAttendant.value ? AttendantSidebar : AppSidebar)
const activeProjects = computed(() => projectStore.activeProjects)

function statusColor(s) {
  return { 'ABERTO': 'blue', 'EM ANDAMENTO': 'orange', 'CONCLUIDO': 'green', 'CANCELADO': 'red' }[s] || 'grey'
}
function priorityColor(p) {
  return { URGENTE: 'red', ALTA: 'orange', NORMAL: 'blue', BAIXA: 'grey' }[p] || 'grey'
}
function formatDate(dt) {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

async function loadTicket() {
  loading.value = true
  try {
    const id = route.params.id
    const [detailRes, historyRes] = await Promise.all([
      ticketsService.getTicketDetails(id),
      ticketsService.getTicketHistory(id),
    ])
    ticket.value = { ...detailRes.data, history: historyRes.data }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function linkProject() {
  if (!linkProjectId.value || !ticket.value) return
  try {
    await api.patch(`/service/${ticket.value.id}/project`, { project_id: linkProjectId.value })
    await loadTicket()
  } catch (e) {
    console.error(e)
  }
}

async function download(att) {
  try {
    const blob = await ticketsService.downloadAttachment(att.id)
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = att.originalFilename
    a.click()
    URL.revokeObjectURL(url)
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  loadTicket()
  projectStore.fetchProjects()
})
</script>

<style scoped>
.page-container { padding: 24px; }
.dashboard { min-height: 100vh; background-color: #f3f4f6; }
.dashboard-layout { padding-top: 60px; }
.dashboard-content { transition: margin-left 0.3s ease; padding: 24px; }
.info-row { margin-bottom: 8px; }
.timeline-item { padding: 8px 0; }
</style>
