<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const props = defineProps({
  bySector: { type: Array, required: true }
})

const chartData = computed(() => ({
  labels: props.bySector.map(s => s.sector),
  datasets: [{
    label: 'Tickets Ativos',
    data: props.bySector.map(s => s.count),
    backgroundColor: '#4F46E5',
    borderRadius: 4,
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: { legend: { display: false } },
  scales: {
    x: { beginAtZero: true, ticks: { stepSize: 1 } }
  }
}

const chartHeight = computed(() => Math.max(200, props.bySector.length * 40))
</script>

<template>
  <div class="chart-wrapper" :style="{ height: chartHeight + 'px' }">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<style scoped>
.chart-wrapper {
  position: relative;
  min-height: 200px;
}
</style>
