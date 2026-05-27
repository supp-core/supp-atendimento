<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="page-container">
          <h2 class="text-h5 font-weight-medium mb-4">Relatório de Atividades</h2>

          <v-card class="mb-4">
            <v-card-text>
              <v-row>
                <v-col v-if="isAdmin" cols="12" sm="4">
                  <v-autocomplete
                    v-model="form.attendant_id"
                    :items="attendants"
                    item-title="name"
                    item-value="id"
                    label="Atendente *"
                    clearable
                  />
                </v-col>
                <v-col cols="12" sm="3">
                  <v-text-field v-model="form.date_from" label="Data Início *" type="date" />
                </v-col>
                <v-col cols="12" sm="3">
                  <v-text-field v-model="form.date_to" label="Data Fim *" type="date" />
                </v-col>
                <v-col cols="12" sm="3">
                  <v-autocomplete
                    v-model="form.project_id"
                    :items="projects"
                    :item-title="p => `[${p.acronym}] ${p.name}`"
                    item-value="id"
                    label="Projeto (opcional)"
                    clearable
                  />
                </v-col>
                <v-col cols="12" class="d-flex align-center">
                  <v-alert v-if="formError" type="error" density="compact" class="mr-4">{{ formError }}</v-alert>
                  <v-btn color="primary" :loading="reportStore.loading" @click="generate">
                    Gerar Relatório
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <v-alert v-if="reportStore.error" type="error" class="mb-4">{{ reportStore.error }}</v-alert>

          <ActivityReportExport
            v-if="reportStore.activityReport"
            :report="reportStore.activityReport"
          />

          <div v-else-if="!reportStore.loading" class="empty-state">
            <v-icon size="80" color="grey-lighten-1">mdi-file-document-outline</v-icon>
            <p class="text-h6 text-grey mt-4">Preencha o formulário e clique em Gerar Relatório</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useReportStore } from '@/stores/reportStore'
import { authService } from '@/services/auth.service'
import { useSidebar } from '@/composables/useSidebar'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import ActivityReportExport from '@/components/reports/ActivityReportExport.vue'
import api from '@/services/api'

const reportStore = useReportStore()
const { sidebarCollapsed } = useSidebar()

const isAdmin = computed(() => authService.isAdmin())
const attendants = ref([])
const projects = ref([])
const formError = ref(null)

const form = ref({
  attendant_id: null,
  date_from: null,
  date_to: null,
  project_id: null,
})

async function generate() {
  formError.value = null
  if (!form.value.date_from || !form.value.date_to) {
    formError.value = 'Período obrigatório.'
    return
  }
  const attendantId = form.value.attendant_id || authService.getAttendantData()?.id
  if (!attendantId) {
    formError.value = 'Selecione um atendente.'
    return
  }
  const params = {
    date_from: form.value.date_from,
    date_to: form.value.date_to,
    project_id: form.value.project_id,
  }
  await reportStore.fetchActivityReport(attendantId, params)
}

onMounted(async () => {
  try {
    const [aRes, pRes] = await Promise.all([api.get('/attendants'), api.get('/project')])
    attendants.value = aRes.data.data || []
    projects.value = pRes.data.data || []
  } catch (e) {
    console.error(e)
  }
  if (!isAdmin.value) {
    form.value.attendant_id = authService.getAttendantData()?.id
  }
})
</script>

<style scoped>
.page-container { padding: 24px; }
.dashboard { min-height: 100vh; background-color: #f3f4f6; }
.dashboard-layout { padding-top: 60px; }
.dashboard-content { transition: margin-left 0.3s ease; padding: 24px; }
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px; }
</style>
