<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="page-container">
          <div class="d-flex align-center mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="$router.back()" class="mr-2" />
            <h2 class="text-h5 font-weight-medium">
              <span v-if="project">
                <v-chip color="primary" class="mr-2">{{ project.acronym }}</v-chip>
                {{ project.name }}
              </span>
              <span v-else>Carregando...</span>
            </h2>
            <v-spacer />
            <v-chip v-if="project" :color="statusColor(project.status)" class="ml-2">{{ project.status }}</v-chip>
          </div>

          <v-card v-if="project" class="mb-4">
            <v-card-text>
              <v-row>
                <v-col cols="12" md="8">
                  <p v-if="project.description">{{ project.description }}</p>
                  <p v-else class="text-grey">Sem descrição</p>
                </v-col>
                <v-col cols="12" md="4">
                  <div><strong>Início:</strong> {{ formatDate(project.date_start) }}</div>
                  <div><strong>Fim:</strong> {{ project.date_end ? formatDate(project.date_end) : '—' }}</div>
                  <div><strong>Criado por:</strong> {{ project.created_by?.name }}</div>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <div class="d-flex justify-space-between align-center mb-3">
            <h3 class="text-h6">Demandas do Projeto</h3>
            <v-btn color="primary" size="small" prepend-icon="mdi-plus" @click="newTicket">
              Nova Demanda neste Projeto
            </v-btn>
          </div>

          <v-card>
            <v-card-text>
              <v-row class="mb-2">
                <v-col cols="12" sm="3">
                  <v-select v-model="filters.status" :items="statusOptions" label="Status" clearable @update:model-value="loadServices" />
                </v-col>
                <v-col cols="12" sm="3">
                  <v-select v-model="filters.priority" :items="priorityOptions" label="Prioridade" clearable @update:model-value="loadServices" />
                </v-col>
              </v-row>
            </v-card-text>
            <v-progress-linear v-if="loading" indeterminate color="primary" />
            <v-data-table
              :headers="serviceHeaders"
              :items="services"
              :loading="loading"
              item-value="id"
              hover
            >
              <template #item.status="{ item }">
                <v-chip :color="ticketStatusColor(item.status)" size="small">{{ item.status }}</v-chip>
              </template>
              <template #item.priority="{ item }">
                <v-chip :color="priorityColor(item.priority)" size="small">{{ item.priority }}</v-chip>
              </template>
              <template #item.dates.created="{ item }">{{ formatDate(item.dates?.created?.split(' ')[0]) }}</template>
              <template #item.dates.concluded="{ item }">{{ item.dates?.concluded ? formatDate(item.dates.concluded.split(' ')[0]) : '—' }}</template>
            </v-data-table>
          </v-card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { projectService } from '@/services/project.service'
import { useSidebar } from '@/composables/useSidebar'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()
const { sidebarCollapsed } = useSidebar()

const project = ref(null)
const services = ref([])
const loading = ref(false)
const filters = ref({ status: null, priority: null })

const statusOptions = ['ABERTO', 'EM ANDAMENTO', 'CONCLUIDO', 'CANCELADO']
const priorityOptions = ['BAIXA', 'NORMAL', 'ALTA', 'URGENTE']

const serviceHeaders = [
  { title: '#', key: 'id', sortable: true },
  { title: 'Título', key: 'title', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Prioridade', key: 'priority', sortable: true },
  { title: 'Responsável', key: 'responsible.name', sortable: false },
  { title: 'Abertura', key: 'dates.created', sortable: true },
  { title: 'Conclusão', key: 'dates.concluded', sortable: true },
]

function statusColor(s) {
  return { ATIVO: 'green', INATIVO: 'red', CONCLUIDO: 'blue' }[s] || 'grey'
}
function ticketStatusColor(s) {
  return { 'ABERTO': 'blue', 'EM ANDAMENTO': 'orange', 'CONCLUIDO': 'green', 'CANCELADO': 'red' }[s] || 'grey'
}
function priorityColor(p) {
  return { URGENTE: 'red', ALTA: 'orange', NORMAL: 'blue', BAIXA: 'grey' }[p] || 'grey'
}
function formatDate(date) {
  if (!date) return '—'
  const [y, m, d] = date.split('-')
  return `${d}/${m}/${y}`
}

async function loadServices() {
  if (!project.value) return
  loading.value = true
  try {
    const params = Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v))
    const res = await projectService.getServices(project.value.id, params)
    services.value = res.data.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function newTicket() {
  router.push({ path: '/attendant/tickets/create', query: { project_id: project.value.id } })
}

onMounted(async () => {
  const id = Number(route.params.id)
  await projectStore.fetchProjectById(id)
  project.value = projectStore.currentProject
  await loadServices()
})
</script>

<style scoped>
.page-container { padding: 24px; }
.dashboard { min-height: 100vh; background-color: #f3f4f6; }
.dashboard-layout { padding-top: 60px; min-height: calc(100vh - 60px); }
.dashboard-content { transition: margin-left 0.3s ease; padding: 24px; }
</style>
