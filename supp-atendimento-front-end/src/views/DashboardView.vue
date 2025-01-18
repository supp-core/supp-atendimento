<template>
    <div class="dashboard">
      <AppHeader />
      <div class="dashboard-layout">
        <AppSidebar />
        <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
          <div class="stats-grid">
            <div class="stats-card">
              <div class="card-header">
                <h3>Avisos</h3>
                <p>Informações relevantes</p>
              </div>
              <div class="card-content">
                <p class="no-data">Nenhum aviso disponível</p>
              </div>
            </div>
  
            <div class="stats-card chart-card">
              <div class="card-header">
                <h3>Distribuição</h3>
                <p>Últimas 4 semanas</p>
              </div>
              <div class="card-content chart-wrapper">
                <Line 
                  :data="chartData"
                  :options="chartOptions"
                  class="chart-container"
                />
              </div>
            </div>
  
            <div class="stats-card">
              <div class="card-header">
                <h3>Minhas Tarefas</h3>
                <p>Pendentes de conclusão</p>
              </div>
              <div class="card-content tasks-content">
                <div class="number">2</div>
                <p class="subtitle">Todas as espécies</p>
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
  import AppHeader from '@/components/common/AppHeader.vue';
  import AppSidebar from '@/components/common/AppSidebar.vue';
  import { Line } from 'vue-chartjs';
  import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
  } from 'chart.js';
  
  ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
  );
  
  const { sidebarCollapsed } = useSidebar();
  
  // Define static chart data to prevent unnecessary re-renders
  const chartData = {
    labels: ['28/12', '04/01', '11/01', '18/01'],
    datasets: [{
      label: 'Distribuição',
      data: [0, 0, 0, 2],
      borderColor: '#4F46E5',
      backgroundColor: '#4F46E5',
      tension: 0.4,
      pointRadius: 4,
      pointHoverRadius: 6,
      borderWidth: 2
    }]
  };
  
  // Enhanced chart options with fixed scales and animations
  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      duration: 0 // Disable animations to prevent movement
    },
    plugins: {
      legend: {
        position: 'top',
        align: 'start',
        labels: {
          boxWidth: 12,
          usePointStyle: true,
          pointStyle: 'circle',
          font: {
            size: 12
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        max: 2.5, // Fixed maximum value
        ticks: {
          stepSize: 0.5,
          font: {
            size: 11
          }
        },
        grid: {
          color: '#e2e8f0'
        }
      },
      x: {
        grid: {
          display: false
        },
        ticks: {
          font: {
            size: 11
          }
        }
      }
    }
  };
  </script>
  
  <style scoped>
  /* Previous styles remain the same */
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
    grid-template-columns: minmax(300px, 1fr) minmax(400px, 1.5fr) minmax(300px, 1fr);
    gap: 40px;
    max-width: 1800px;
    margin: 0 auto;
  }
  
  /* Enhanced card styles with specific chart handling */
  .stats-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 100%;
    min-height: 350px;
    display: flex;
    flex-direction: column;
  }
  
  .chart-card {
    overflow: hidden; /* Prevent any potential overflow */
  }
  
  .chart-wrapper {
    flex-grow: 1;
    position: relative;
    min-height: 250px; /* Ensure minimum height for the chart */
    margin-top: 16px;
  }
  
  .chart-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
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
  
  /* Rest of the styles remain the same */
  .tasks-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 24px 0;
  }
  
  .number {
    font-size: 3.5rem;
    font-weight: 600;
    color: #4F46E5;
    line-height: 1;
    margin-bottom: 16px;
  }
  
  .subtitle {
    color: #666;
    font-size: 1rem;
  }
  
  .no-data {
    color: #666;
    font-size: 1rem;
    font-style: italic;
    margin-top: 24px;
  }
  </style>