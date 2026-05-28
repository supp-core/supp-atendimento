<template>
  <div class="dashboard">
    <AppHeader />
    <div class="dashboard-layout">
      <AppSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">

        <div class="page-header">
          <h2>Dashboard</h2>
          <button class="btn-primary" @click="$router.push('/tickets/create')">+ Abrir Novo Ticket</button>
        </div>

        <div class="stats-grid">
          <div class="stats-card">
            <div class="card-icon icon-total">📋</div>
            <div class="card-body">
              <div class="card-number">{{ stats.total }}</div>
              <div class="card-label">Total de Tickets</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon icon-new">🆕</div>
            <div class="card-body">
              <div class="card-number color-blue">{{ stats.by_status.NOVO }}</div>
              <div class="card-label">Novos</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon icon-progress">⏳</div>
            <div class="card-body">
              <div class="card-number color-yellow">{{ stats.by_status.IN_PROGRESS }}</div>
              <div class="card-label">Em Andamento</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon icon-resolved">🔍</div>
            <div class="card-body">
              <div class="card-number color-purple">{{ stats.by_status.RESOLVED }}</div>
              <div class="card-label">Resolvidos</div>
            </div>
          </div>
          <div class="stats-card">
            <div class="card-icon icon-done">✅</div>
            <div class="card-body">
              <div class="card-number color-green">{{ stats.by_status.CONCLUDED }}</div>
              <div class="card-label">Concluídos</div>
            </div>
          </div>
        </div>

        <div class="charts-grid">
          <div class="chart-card">
            <h3 class="chart-title">Distribuição por Prioridade</h3>
            <DashboardPriorityChart v-if="statsLoaded" :by-priority="stats.by_priority" />
          </div>
          <div class="chart-card">
            <h3 class="chart-title">Tickets por Status</h3>
            <DashboardStatusChart v-if="statsLoaded" :by-status="stats.by_status" />
          </div>
        </div>

        <div class="section-card">
          <div class="section-header">
            <h3>Últimos Tickets</h3>
            <button class="btn-link" @click="$router.push('/tickets')">Ver todos →</button>
          </div>
          <DashboardRecentTickets :tickets="recentTickets" :is-attendant="false" />
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useSidebar } from '@/composables/useSidebar'
import AppHeader from '@/components/common/AppHeader.vue'
import AppSidebar from '@/components/common/AppSidebar.vue'
import DashboardPriorityChart from '@/components/dashboard/DashboardPriorityChart.vue'
import DashboardStatusChart from '@/components/dashboard/DashboardStatusChart.vue'
import DashboardRecentTickets from '@/components/dashboard/DashboardRecentTickets.vue'
import api from '@/services/api'
import { authService } from '@/services/auth.service'

const { sidebarCollapsed } = useSidebar()

const statsLoaded = ref(false)
const stats = ref({
  total: 0,
  by_status: { NOVO: 0, OPEN: 0, IN_PROGRESS: 0, RESOLVED: 0, CONCLUDED: 0, CANCELADO: 0, RETORNO: 0 },
  by_priority: { BAIXA: 0, NORMAL: 0, ALTA: 0, URGENTE: 0 },
})
const recentTickets = ref([])

const loadStats = async () => {
  if (!authService.isAuthenticated()) return
  try {
    const res = await api.get('/dashboard/user-stats')
    if (res.data.success) {
      stats.value = res.data.data
      statsLoaded.value = true
    }
  } catch {
    // silently fail — numbers stay at 0
  }
}

const loadRecentTickets = async () => {
  try {
    const res = await api.get('/service/my-tickets?per_page=5&page=1')
    if (res.data.success) {
      recentTickets.value = res.data.data || []
    }
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
}

.page-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a237e;
  margin: 0;
}

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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
  margin-bottom: 28px;
}

@media (max-width: 1100px) {
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
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
  font-size: 2rem;
  width: 48px;
  height: 48px;
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
.color-purple { color: #8b5cf6; }
.color-green  { color: #10b981; }

.charts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 28px;
}

@media (max-width: 900px) {
  .charts-grid { grid-template-columns: 1fr; }
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
