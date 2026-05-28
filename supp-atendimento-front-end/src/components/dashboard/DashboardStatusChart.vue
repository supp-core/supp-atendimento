<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const props = defineProps({
  byStatus: { type: Object, required: true }
})

const STATUS_LABELS = {
  NOVO: 'Novo', OPEN: 'Aberto', IN_PROGRESS: 'Em Andamento',
  RESOLVED: 'Resolvido', CONCLUDED: 'Concluído', CANCELADO: 'Cancelado', RETORNO: 'Retorno'
}

const STATUS_COLORS = {
  NOVO: '#4F46E5', OPEN: '#06b6d4', IN_PROGRESS: '#f59e0b',
  RESOLVED: '#8b5cf6', CONCLUDED: '#10b981', CANCELADO: '#ef4444', RETORNO: '#f97316'
}

const chartData = computed(() => {
  const keys = Object.keys(props.byStatus)
  return {
    labels: keys.map(k => STATUS_LABELS[k] || k),
    datasets: [{
      label: 'Tickets',
      data: keys.map(k => props.byStatus[k] || 0),
      backgroundColor: keys.map(k => STATUS_COLORS[k] || '#6b7280'),
      borderRadius: 4,
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1 } }
  }
}
</script>

<template>
  <div class="chart-wrapper">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<style scoped>
.chart-wrapper {
  height: 250px;
  position: relative;
}
</style>
