<template>
  <v-card>
    <v-card-title v-if="projectName" class="text-h6 pa-4">
      Cronograma — {{ projectName }}
    </v-card-title>
    <v-progress-linear v-if="loading" indeterminate color="primary" />
    <div v-if="!loading && items.length === 0" class="pa-8 text-center text-grey">
      <v-icon size="48" color="grey-lighten-1">mdi-calendar-blank-outline</v-icon>
      <p class="mt-2">Nenhuma demanda encontrada para os filtros selecionados.</p>
    </div>
    <v-data-table
      v-else
      :headers="headers"
      :items="items"
      :loading="loading"
      item-value="id"
      hover
    >
      <template #item.date_start="{ item }">{{ formatDate(item.date_start) }}</template>
      <template #item.date_conclusion="{ item }">
        <v-chip v-if="!item.date_conclusion && item.status === 'EM ANDAMENTO'" size="small" color="orange">
          Em andamento
        </v-chip>
        <v-chip v-else-if="!item.date_conclusion" size="small" color="blue-grey">
          Não iniciado
        </v-chip>
        <span v-else>{{ formatDate(item.date_conclusion) }}</span>
      </template>
      <template #item.status="{ item }">
        <v-chip :color="statusColor(item.status)" size="small">{{ item.status }}</v-chip>
      </template>
      <template #item.observation="{ item }">
        <v-tooltip :text="item.observation" location="top">
          <template #activator="{ props }">
            <span v-bind="props" class="observation-text">{{ truncate(item.observation, 60) }}</span>
          </template>
        </v-tooltip>
      </template>
      <template #item.priority="{ item }">
        <v-chip :color="priorityColor(item.priority)" size="x-small">{{ item.priority }}</v-chip>
      </template>
      <template #tbody>
        <tr
          v-for="item in items"
          :key="item.id"
          :style="rowStyle(item.status)"
        >
          <td>{{ item.id }}</td>
          <td>{{ item.title }}</td>
          <td>{{ formatDate(item.date_start) }}</td>
          <td>
            <v-chip v-if="!item.date_conclusion && item.status === 'EM ANDAMENTO'" size="small" color="orange">Em andamento</v-chip>
            <span v-else-if="item.date_conclusion">{{ formatDate(item.date_conclusion) }}</span>
            <span v-else class="text-grey">Não iniciado</span>
          </td>
          <td><v-chip :color="statusColor(item.status)" size="small">{{ item.status }}</v-chip></td>
          <td class="observation-cell">
            <v-tooltip :text="item.observation" location="top">
              <template #activator="{ props }">
                <span v-bind="props">{{ truncate(item.observation, 60) }}</span>
              </template>
            </v-tooltip>
          </td>
        </tr>
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup>
defineProps({
  items: { type: Array, default: () => [] },
  projectName: { type: String, default: '' },
  loading: { type: Boolean, default: false },
})

const headers = [
  { title: 'ID', key: 'id', sortable: true, width: '60px' },
  { title: 'Atividade', key: 'title', sortable: true },
  { title: 'Início', key: 'date_start', sortable: true },
  { title: 'Conclusão', key: 'date_conclusion', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Observação', key: 'observation', sortable: false },
]

function statusColor(s) {
  return { 'ABERTO': 'blue', 'EM ANDAMENTO': 'orange', 'CONCLUIDO': 'green', 'CANCELADO': 'grey' }[s] || 'grey'
}
function priorityColor(p) {
  return { URGENTE: 'red', ALTA: 'orange', NORMAL: 'blue', BAIXA: 'grey' }[p] || 'grey'
}
function rowStyle(status) {
  const styles = {
    'ABERTO': '',
    'EM ANDAMENTO': 'background-color:#FFFDE7',
    'CONCLUIDO': 'background-color:#E8F5E9',
    'CANCELADO': 'background-color:#FAFAFA',
  }
  return styles[status] || ''
}
function formatDate(d) {
  if (!d) return ''
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}
function truncate(text, max) {
  if (!text) return ''
  return text.length > max ? text.slice(0, max) + '…' : text
}
</script>

<style scoped>
.observation-text { cursor: help; }
.observation-cell { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
