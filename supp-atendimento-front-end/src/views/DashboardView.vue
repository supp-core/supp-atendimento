<template>
     <div class="dashboard">
    <AppHeader />
    <AppSidebar />
      <div class="dashboard-content">
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
  
            <!-- Card de Distribuição -->
            <div class="stats-card">
              <div class="card-header">
                <h3>Distribuição</h3>
                <p>Últimas 4 semanas</p>
              </div>
              <div class="card-content">
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
                <div class="big-number">2</div>
                <p>Todas as espécies</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
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
  
  // Registrando componentes do Chart.js
  ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    CategoryScale,
    PointElement
  );
  
  // Dados do gráfico
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
  
  // Função de inicialização
  onMounted(() => {
    console.log('Dashboard montado');
  });
  </script>
  
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
  /* Alterando para usar frações maiores do espaço disponível */
  grid-template-columns: minmax(300px, 1fr) minmax(400px, 1.5fr) minmax(300px, 1fr);
  gap: 30px; /* Aumentando o espaçamento entre os cards */
  margin: 30px; /* Aumentando a margem externa */
}
  .stats-card {
    background: white;
  border-radius: 8px;
  padding: 25px; /* Aumentando o padding interno */
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  min-height: 200px; /* Garantindo altura mínima */
  }
  
  .card-header {
    margin-bottom: 1rem;
  }
  
  .card-header h3 {
    color: #1a237e;
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
    font-weight: 500;
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
  
  .card-content {
    color: #666;
  }
  </style>