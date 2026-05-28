<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  byPriority: { type: Object, required: true }
})

const chartData = computed(() => ({
  labels: ['Baixa', 'Normal', 'Alta', 'Urgente'],
  datasets: [{
    data: [
      props.byPriority.BAIXA || 0,
      props.byPriority.NORMAL || 0,
      props.byPriority.ALTA || 0,
      props.byPriority.URGENTE || 0,
    ],
    backgroundColor: ['#10b981', '#4F46E5', '#f59e0b', '#ef4444'],
    borderWidth: 2,
    borderColor: '#fff',
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
  }
}
</script>

<template>
  <div class="chart-wrapper">
    <Doughnut :data="chartData" :options="chartOptions" />
  </div>
</template>

<style scoped>
.chart-wrapper {
  height: 250px;
  position: relative;
}
</style>
