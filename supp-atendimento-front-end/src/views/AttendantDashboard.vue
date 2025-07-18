<!-- src/views/AttendantDashboard.vue -->
<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="stats-grid">
          <div class="stats-card">
            <div class="card-header">
              <h3>Total de Tickets</h3>
              <p>Todos os tickets do sistema</p>
            </div>
            <div class="card-content tasks-content">
              <div class="number">{{ totalTicketsCount }}</div>
              <p class="subtitle">Total</p>
            </div>
          </div>

          <div class="stats-card">
            <div class="card-header">
              <h3>Tickets Pendentes</h3>
              <p>Em andamento</p>
            </div>
            <div class="card-content tasks-content">
              <div class="number pending">{{ pendingTasksCount }}</div>
              <p class="subtitle">Pendentes</p>
            </div>
          </div>

          <div class="stats-card">
            <div class="card-header">
              <h3>Tickets Concluídos</h3>
              <p>Finalizados</p>
            </div>
            <div class="card-content tasks-content">
              <div class="number completed">{{ completedTasksCount }}</div>
              <p class="subtitle">Concluídos</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSidebar } from '@/composables/useSidebar';
import AttendantHeader from '@/components/common/AttendantHeader.vue';
import AttendantSidebar from '@/components/common/AttendantSidebar.vue';
import api from '@/services/api';
import { attendantAuthService } from '@/services/attendant-auth.service';

const { sidebarCollapsed } = useSidebar();
const totalTicketsCount = ref(0);
const pendingTasksCount = ref(0);
const completedTasksCount = ref(0);

// Função para carregar estatísticas dos tickets (todos os tickets do sistema)
const loadTicketStats = async () => {
  try {
    if (!attendantAuthService.isAuthenticated()) {
      return;
    }

    // Buscar total de tickets (todos os tickets do sistema)
    const totalResponse = await api.get('/service/all-tickets-stats');
    if (totalResponse.data.success) {
      totalTicketsCount.value = totalResponse.data.data.total || 0;
      pendingTasksCount.value = totalResponse.data.data.pending || 0;
      completedTasksCount.value = totalResponse.data.data.completed || 0;
    } else {
      // Fallback - usar endpoint alternativo se não existir
      const fallbackResponse = await api.get('/service?per_page=1');
      if (fallbackResponse.data.success) {
        totalTicketsCount.value = fallbackResponse.data.meta.total || 0;
      }

      // Buscar tickets pendentes
      const pendingResponse = await api.get('/service?status=new,OPEN,IN_PROGRESS,RESOLVED&per_page=1');
      if (pendingResponse.data.success) {
        pendingTasksCount.value = pendingResponse.data.meta.total || 0;
      }

      // Buscar tickets concluídos
      const completedResponse = await api.get('/service?status=CONCLUDED&per_page=1');
      if (completedResponse.data.success) {
        completedTasksCount.value = completedResponse.data.meta.total || 0;
      }
    }
  } catch (error) {
    console.error('Erro ao carregar estatísticas dos tickets:', error);
    totalTicketsCount.value = 0;
    pendingTasksCount.value = 0;
    completedTasksCount.value = 0;
  }
};

onMounted(() => {
  loadTicketStats();
});
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
  padding: 40px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
}

.stats-card {
  background: white;
  border-radius: 12px;
  padding: 32px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: 100%;
  min-height: 250px;
  display: flex;
  flex-direction: column;
}

.card-header {
  margin-bottom: 24px;
}

.card-header h3 {
  font-size: 1.25rem;
  font-weight: 500;
  color: #1a237e;
  margin-bottom: 8px;
}

.card-header p {
  color: #666;
  font-size: 1rem;
}

.tasks-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 24px 0;
  flex-grow: 1;
  justify-content: center;
}

.number {
  font-size: 3.5rem;
  font-weight: 600;
  color: #4F46E5;
  line-height: 1;
  margin-bottom: 16px;
}

.number.pending {
  color: #f59e0b;
}

.number.completed {
  color: #10b981;
}

.subtitle {
  color: #666;
  font-size: 1rem;
}
</style>