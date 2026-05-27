<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="page-container">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-medium">Projetos</h2>
            <v-btn v-if="isAdmin" color="primary" prepend-icon="mdi-plus" @click="openForm(null)">
              Novo Projeto
            </v-btn>
          </div>

          <v-card class="mb-4">
            <v-card-text>
              <v-row>
                <v-col cols="12" sm="4">
                  <v-select
                    v-model="statusFilter"
                    :items="statusOptions"
                    label="Filtrar por Status"
                    clearable
                    @update:model-value="applyFilter"
                  />
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <v-card>
            <v-progress-linear v-if="projectStore.loading" indeterminate color="primary" />
            <v-data-table
              :headers="headers"
              :items="filteredProjects"
              :loading="projectStore.loading"
              item-value="id"
              hover
            >
              <template #item.acronym="{ item }">
                <v-chip color="primary" size="small">{{ item.acronym }}</v-chip>
              </template>
              <template #item.status="{ item }">
                <v-chip :color="statusColor(item.status)" size="small">{{ item.status }}</v-chip>
              </template>
              <template #item.date_start="{ item }">{{ formatDate(item.date_start) }}</template>
              <template #item.date_end="{ item }">{{ item.date_end ? formatDate(item.date_end) : '—' }}</template>
              <template #item.actions="{ item }">
                <v-btn size="small" variant="text" icon="mdi-eye" @click="viewDetail(item)" title="Ver Detalhes" />
                <v-btn v-if="isAdmin" size="small" variant="text" icon="mdi-pencil" @click="openForm(item)" title="Editar" />
              </template>
            </v-data-table>
          </v-card>
        </div>
      </div>
    </div>

    <ProjectForm
      v-model="formDrawer"
      :project="editingProject"
      @saved="onSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '@/stores/projectStore'
import { useSidebar } from '@/composables/useSidebar'
import { authService } from '@/services/auth.service'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import ProjectForm from './ProjectForm.vue'

const router = useRouter()
const projectStore = useProjectStore()
const { sidebarCollapsed } = useSidebar()
const isAdmin = computed(() => authService.isAdmin())

const formDrawer = ref(false)
const editingProject = ref(null)
const statusFilter = ref(null)

const statusOptions = ['ATIVO', 'INATIVO', 'CONCLUIDO']

const headers = [
  { title: 'Nome', key: 'name', sortable: true },
  { title: 'Sigla', key: 'acronym', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Data Início', key: 'date_start', sortable: true },
  { title: 'Data Fim', key: 'date_end', sortable: true },
  { title: 'Ações', key: 'actions', sortable: false, align: 'center' },
]

const filteredProjects = computed(() => {
  if (!statusFilter.value) return projectStore.projects
  return projectStore.projects.filter(p => p.status === statusFilter.value)
})

function statusColor(status) {
  return { ATIVO: 'green', INATIVO: 'red', CONCLUIDO: 'blue' }[status] || 'grey'
}

function formatDate(date) {
  if (!date) return '—'
  const [y, m, d] = date.split('-')
  return `${d}/${m}/${y}`
}

function openForm(project) {
  editingProject.value = project
  formDrawer.value = true
}

function viewDetail(project) {
  router.push(`/projects/${project.id}`)
}

function applyFilter() {}

function onSaved() {
  projectStore.fetchProjects()
}

onMounted(() => {
  projectStore.fetchProjects()
})
</script>

<style scoped>
.page-container {
  padding: 24px;
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
</style>
