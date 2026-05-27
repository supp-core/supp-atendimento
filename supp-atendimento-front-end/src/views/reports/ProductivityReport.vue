<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="page-container">
          <h2 class="text-h5 font-weight-medium mb-4">Relatório de Produtividade</h2>

          <ProductivityFilters @generate="generateReport" />

          <template v-if="reportStore.loading">
            <div class="d-flex justify-center mt-8">
              <v-progress-circular indeterminate color="primary" />
            </div>
          </template>

          <template v-else-if="reportStore.error">
            <v-alert type="error" class="mt-4">{{ reportStore.error }}</v-alert>
          </template>

          <template v-else-if="reportStore.productivity">
            <div v-if="isConsolidated">
              <ProductivitySummaryCards :attendants="reportStore.productivity.attendants" />
              <ProductivityCharts :attendants="reportStore.productivity.attendants" class="mt-4" />
              <ProductivityTable :attendants="reportStore.productivity.attendants" class="mt-4" />
            </div>
            <div v-else>
              <ProductivitySummaryCards :attendants="[reportStore.productivity]" />
              <ProductivityCharts :attendants="[reportStore.productivity]" class="mt-4" />
            </div>
          </template>

          <template v-else>
            <div class="empty-state mt-8">
              <v-icon size="80" color="grey-lighten-1">mdi-chart-bar</v-icon>
              <p class="text-h6 text-grey mt-4">Selecione o período e clique em Gerar Relatório</p>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useReportStore } from '@/stores/reportStore'
import { authService } from '@/services/auth.service'
import { useSidebar } from '@/composables/useSidebar'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import ProductivityFilters from '@/components/reports/ProductivityFilters.vue'
import ProductivitySummaryCards from '@/components/reports/ProductivitySummaryCards.vue'
import ProductivityCharts from '@/components/reports/ProductivityCharts.vue'
import ProductivityTable from '@/components/reports/ProductivityTable.vue'

const reportStore = useReportStore()
const { sidebarCollapsed } = useSidebar()

const isConsolidated = computed(() => {
  const data = reportStore.productivity
  return data && 'attendants' in data
})

async function generateReport(filters) {
  if (authService.isAdmin() && !filters.attendant_id) {
    await reportStore.fetchProductivity(filters)
  } else {
    const attendantId = filters.attendant_id || authService.getAttendantData()?.id
    if (attendantId) {
      await reportStore.fetchAttendantProductivity(attendantId, filters)
    }
  }
}
</script>

<style scoped>
.page-container { padding: 24px; }
.dashboard { min-height: 100vh; background-color: #f3f4f6; }
.dashboard-layout { padding-top: 60px; }
.dashboard-content { transition: margin-left 0.3s ease; padding: 24px; }
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px; }
</style>
