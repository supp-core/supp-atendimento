<template>
  <v-row>
    <v-col cols="12" sm="6" md="3">
      <v-card color="blue" dark>
        <v-card-text class="text-center">
          <v-icon size="40">mdi-ticket-outline</v-icon>
          <div class="text-h4 mt-2">{{ totalAssigned }}</div>
          <div class="text-body-2">Total Atribuídas</div>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" sm="6" md="3">
      <v-card color="green" dark>
        <v-card-text class="text-center">
          <v-icon size="40">mdi-check-circle-outline</v-icon>
          <div class="text-h4 mt-2">{{ totalConcluded }}</div>
          <div class="text-body-2">Total Concluídas</div>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" sm="6" md="3">
      <v-card color="orange" dark>
        <v-card-text class="text-center">
          <v-icon size="40">mdi-progress-clock</v-icon>
          <div class="text-h4 mt-2">{{ totalInProgress }}</div>
          <div class="text-body-2">Em Andamento</div>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" sm="6" md="3">
      <v-card color="purple" dark>
        <v-card-text class="text-center">
          <v-icon size="40">mdi-timer-outline</v-icon>
          <div class="text-h4 mt-2">{{ avgResolution }}</div>
          <div class="text-body-2">Média de Resolução (dias)</div>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  attendants: { type: Array, default: () => [] },
})

const allMetrics = computed(() => props.attendants.map(a => a.metrics || a))

const totalAssigned = computed(() => allMetrics.value.reduce((s, m) => s + (m.total_assigned || 0), 0))
const totalConcluded = computed(() => allMetrics.value.reduce((s, m) => s + (m.total_concluded || 0), 0))
const totalInProgress = computed(() => allMetrics.value.reduce((s, m) => s + (m.total_in_progress || 0), 0))
const avgResolution = computed(() => {
  const vals = allMetrics.value.map(m => m.avg_resolution_days || 0).filter(v => v > 0)
  if (!vals.length) return '—'
  return (vals.reduce((s, v) => s + v, 0) / vals.length).toFixed(1)
})
</script>
