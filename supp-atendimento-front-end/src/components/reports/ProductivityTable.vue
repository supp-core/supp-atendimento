<template>
  <v-card>
    <v-card-title class="text-subtitle-1">Comparativo por Atendente</v-card-title>
    <v-data-table
      :headers="headers"
      :items="tableItems"
      item-value="id"
      hover
    >
      <template #item.name="{ item }">
        <span :class="{ 'text-green font-weight-bold': item.id === topConcluder, 'text-red': item.id === slowest }">
          {{ item.name }}
        </span>
      </template>
      <template #item.avg_resolution_days="{ item }">
        {{ item.avg_resolution_days || '—' }}
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  attendants: { type: Array, default: () => [] },
})

const headers = [
  { title: 'Atendente', key: 'name', sortable: true },
  { title: 'Atribuídas', key: 'total_assigned', sortable: true },
  { title: 'Concluídas', key: 'total_concluded', sortable: true },
  { title: 'Em Andamento', key: 'total_in_progress', sortable: true },
  { title: 'Média (dias)', key: 'avg_resolution_days', sortable: true },
  { title: 'Evoluções', key: 'evolution_count', sortable: true },
]

const tableItems = computed(() =>
  props.attendants.map(a => ({
    id: a.attendant?.id,
    name: a.attendant?.name,
    ...(a.metrics || a),
  }))
)

const topConcluder = computed(() => {
  const max = Math.max(...tableItems.value.map(i => i.total_concluded || 0))
  return tableItems.value.find(i => i.total_concluded === max)?.id
})

const slowest = computed(() => {
  const vals = tableItems.value.filter(i => i.avg_resolution_days > 0)
  if (!vals.length) return null
  const max = Math.max(...vals.map(i => i.avg_resolution_days))
  return vals.find(i => i.avg_resolution_days === max)?.id
})
</script>
