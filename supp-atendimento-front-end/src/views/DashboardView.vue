<script setup>
import { ref, onMounted } from 'vue';
import AppHeader from '@/components/common/AppHeader.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement
} from 'chart.js';

// Registrando os componentes necessários do Chart.js
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement
);

// Dados para o gráfico
const chartData = ref({
  labels: ['28/12', '04/01', '11/01', '18/01'],
  datasets: [{
    label: 'Distribuição',
    data: [0, 0, 0, 2],
    borderColor: '#4F46E5',
    tension: 0.4,
    fill: false
  }]
});

// Opções do gráfico
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true
    }
  }
});

// Outros dados
const totalTasks = ref(2);

// Função de inicialização
onMounted(() => {
  // Por enquanto, deixaremos vazio para testar
  console.log('Dashboard montado');
});
</script>

<template>
  <div class="dashboard">
    <AppHeader />
    <div class="dashboard-content">
      <AppSidebar />
      <div class="main-content">
        <!-- Cards de Estatísticas -->
        <div class="stats-grid">
          <!-- Card de Avisos -->
          <div class="stats-card">
            <div class="card-header">
              <h3>Avisos</h3>
              <p>Informações relevantes</p>
            </div>
            <div class="card-content">
              <p>Nenhum aviso disponível</p>
            </div>
          </div>

          <!-- Card do Gráfico -->
          <div class="stats-card">
            <div class="card-header">
              <h3>Distribuição</h3>
              <p>Últimas 4 semanas</p>
            </div>
            <div class="card-content chart">
              <Line 
                :data="chartData"
                :options="chartOptions"
                class="chart-container"
              />
            </div>
          </div>

          <!-- Card de Tarefas -->
          <div class="stats-card">
            <div class="card-header">
              <h3>Minhas Tarefas</h3>
              <p>Pendentes de conclusão</p>
            </div>
            <div class="card-content">
              <div class="big-number">{{ totalTasks }}</div>
              <p>Todas as espécies</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f3f4f6;
}

.dashboard-content {
  display: flex;
  padding: 20px;
  gap: 20px;
}

.main-content {
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 20px;
}

.stats-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
  margin-bottom: 1rem;
}

.card-header h3 {
  color: #1a237e;
  margin-bottom: 0.25rem;
}

.card-header p {
  color: #666;
  font-size: 0.875rem;
}

.chart-container {
  height: 200px;
  width: 100%;
}

.big-number {
  font-size: 2.5rem;
  font-weight: bold;
  color: #4F46E5;
  margin: 10px 0;
}
</style>