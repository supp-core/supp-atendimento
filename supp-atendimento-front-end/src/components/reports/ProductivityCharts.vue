<template>
  <v-row>
    <v-col cols="12" md="8">
      <v-card>
        <v-card-title class="text-subtitle-1">Conclusões por Mês</v-card-title>
        <v-card-text>
          <Bar v-if="barData.datasets[0].data.length" :data="barData" :options="barOptions" style="max-height:250px" />
          <p v-else class="text-grey text-body-2">Sem dados para o período.</p>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" md="4">
      <v-card>
        <v-card-title class="text-subtitle-1">Distribuição por Prioridade</v-card-title>
        <v-card-text>
          <Doughnut v-if="doughnutData.datasets[0].data.some(v => v > 0)" :data="doughnutData" :options="{ responsive: true }" style="max-height:250px" />
          <p v-else class="text-grey text-body-2">Sem dados.</p>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script setup>
import { computed } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend,
  ArcElement,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const props = defineProps({
  attendants: { type: Array, default: () => [] },
})

const allMetrics = computed(() => props.attendants.map(a => a.metrics || a))

const barData = computed(() => {
  const monthsMap = {}
  allMetrics.value.forEach(m => {
    ;(m.monthly_series || []).forEach(s => {
      monthsMap[s.month] = (monthsMap[s.month] || 0) + s.concluded
    })
  })
  const labels = Object.keys(monthsMap).sort()
  return {
    labels,
    datasets: [{
      label: 'Conclusões',
      data: labels.map(l => monthsMap[l]),
      backgroundColor: '#1976D2',
    }],
  }
})

const barOptions = {
  responsive: true,
  plugins: { legend: { display: false } },
}

const doughnutData = computed(() => {
  const bp = { BAIXA: 0, NORMAL: 0, ALTA: 0, URGENTE: 0 }
  allMetrics.value.forEach(m => {
    const byp = m.by_priority || {}
    Object.keys(bp).forEach(k => { bp[k] += byp[k] || 0 })
  })
  return {
    labels: ['Baixa', 'Normal', 'Alta', 'Urgente'],
    datasets: [{
      data: [bp.BAIXA, bp.NORMAL, bp.ALTA, bp.URGENTE],
      backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336'],
    }],
  }
})
</script>
