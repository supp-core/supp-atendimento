<!-- src/views/AttendantDashboard.vue -->
<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">

        <div class="page-header">
          <h2>Dashboard {{ isAdmin ? '— Administração' : '' }}</h2>
          <div class="header-actions">
            <button class="btn-primary" @click="$router.push('/attendant/tickets')">Evoluir Atendimentos</button>
            <button v-if="isAdmin" class="btn-secondary" @click="$router.push('/attendant/admin/users')">Gerenciar Usuários</button>
          </div>
        </div>

        <!-- Cards base do atendente -->
        <div :class="['stats-grid', isAdmin ? 'stats-grid-6' : 'stats-grid-4']">
          <div class="stats-card">
            <div class="card-icon">👤</div>
            <div class="card-body">
              <div class="card-number color-blue">{{ stats.assigned_to_me }}</div>
              <div class="card-label">Atribuídos a Mim</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon">🏢</div>
            <div class="card-body">
              <div class="card-number">{{ stats.in_my_sector }}</div>
              <div class="card-label">No Meu Setor</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon">⏳</div>
            <div class="card-body">
              <div class="card-number color-yellow">{{ stats.in_progress }}</div>
              <div class="card-label">Em Andamento</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon">🚨</div>
            <div class="card-body">
              <div class="card-number color-red">{{ stats.urgent }}</div>
              <div class="card-label">Urgentes</div>
            </div>
          </div>

          <!-- Cards extras somente admin -->
          <template v-if="isAdmin && stats.admin">
            <div class="stats-card">
              <div class="card-icon">📊</div>
              <div class="card-body">
                <div class="card-number color-purple">{{ stats.admin.system_total }}</div>
                <div class="card-label">Total do Sistema</div>
              </div>
            </div>
            <div class="stats-card">
              <div class="card-icon">❗</div>
              <div class="card-body">
                <div class="card-number color-orange">{{ stats.admin.no_responsible }}</div>
                <div class="card-label">Sem Responsável</div>
              </div>
            </div>
          </template>
        </div>

        <!-- Gráficos -->
        <div :class="['charts-grid', isAdmin ? 'charts-grid-2' : 'charts-grid-1']">
          <div class="chart-card">
            <h3 class="chart-title">Meus Tickets por Status</h3>
            <DashboardStatusChart v-if="statsLoaded" :by-status="stats.by_status" />
          </div>
          <div v-if="isAdmin && stats.admin?.by_sector?.length" class="chart-card">
            <h3 class="chart-title">Tickets Ativos por Setor</h3>
            <DashboardSectorChart :by-sector="stats.admin.by_sector" />
          </div>
        </div>

        <!-- Tickets recentes -->
        <div class="section-card">
          <div class="section-header">
            <h3>{{ isAdmin ? 'Tickets Recentes do Sistema' : 'Meus Tickets Recentes' }}</h3>
            <button class="btn-link" @click="$router.push('/attendant/tickets')">Ver todos →</button>
          </div>
          <DashboardRecentTickets :tickets="recentTickets" :is-attendant="true" />
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSidebar } from '@/composables/useSidebar'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import DashboardStatusChart from '@/components/dashboard/DashboardStatusChart.vue'
import DashboardSectorChart from '@/components/dashboard/DashboardSectorChart.vue'
import DashboardRecentTickets from '@/components/dashboard/DashboardRecentTickets.vue'
import api from '@/services/api'
import { authService } from '@/services/auth.service'

const { sidebarCollapsed } = useSidebar()

const isAdmin = computed(() => authService.isAdmin())
const statsLoaded = ref(false)

const stats = ref({
  assigned_to_me: 0,
  in_my_sector: 0,
  in_progress: 0,
  urgent: 0,
  by_status: { NOVO: 0, OPEN: 0, IN_PROGRESS: 0, RESOLVED: 0, RETORNO: 0 },
  admin: null,
})

const recentTickets = ref([])

const loadStats = async () => {
  if (!authService.isAttendant()) return
  try {
    const res = await api.get('/dashboard/attendant-stats')
    if (res.data.success) {
      stats.value = res.data.data
      statsLoaded.value = true
    }
  } catch {
    // silently fail
  }
}

const loadRecentTickets = async () => {
  try {
    const attendantData = authService.getAttendantData()
    if (!attendantData?.id) return

    let res
    if (isAdmin.value) {
      res = await api.get('/service/sector')
    } else {
      res = await api.get(`/service/attendant/${attendantData.id}`)
    }

    const data = res.data?.data ?? res.data
    recentTickets.value = Array.isArray(data) ? data.slice(0, 5) : []
  } catch {
    recentTickets.value = []
  }
}

onMounted(() => {
  loadStats()
  loadRecentTickets()
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
  padding: 32px 40px;
  max-width: 1400px;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 12px;
}

.page-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a237e;
  margin: 0;
}

.header-actions { display: flex; gap: 10px; }

.btn-primary {
  background: #1a237e;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover { background: #283593; }

.btn-secondary {
  background: #fff;
  color: #1a237e;
  border: 1px solid #1a237e;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-secondary:hover { background: #f0f4ff; }

.stats-grid {
  display: grid;
  gap: 16px;
  margin-bottom: 28px;
}

.stats-grid-4 { grid-template-columns: repeat(4, 1fr); }
.stats-grid-6 { grid-template-columns: repeat(6, 1fr); }

@media (max-width: 1200px) {
  .stats-grid-6 { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
  .stats-grid-4, .stats-grid-6 { grid-template-columns: repeat(2, 1fr); }
}

.stats-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  gap: 16px;
}

.card-icon {
  font-size: 1.75rem;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: #f0f4ff;
  flex-shrink: 0;
}

.card-body { flex: 1; }

.card-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.card-label {
  font-size: 0.8rem;
  color: #6b7280;
  margin-top: 4px;
}

.color-blue   { color: #4F46E5; }
.color-yellow { color: #f59e0b; }
.color-red    { color: #ef4444; }
.color-purple { color: #8b5cf6; }
.color-orange { color: #f97316; }

.charts-grid {
  display: grid;
  gap: 20px;
  margin-bottom: 28px;
}

.charts-grid-1 { grid-template-columns: 1fr; }
.charts-grid-2 { grid-template-columns: 1fr 1fr; }

@media (max-width: 900px) {
  .charts-grid-2 { grid-template-columns: 1fr; }
}

.chart-card {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1a237e;
  margin: 0 0 16px 0;
}

.section-card {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.section-header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1a237e;
  margin: 0;
}

.btn-link {
  background: none;
  border: none;
  color: #4F46E5;
  font-size: 0.875rem;
  cursor: pointer;
  padding: 0;
}

.btn-link:hover { text-decoration: underline; }
</style>
