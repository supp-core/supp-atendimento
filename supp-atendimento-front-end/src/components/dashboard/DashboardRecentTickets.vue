<script setup>
defineProps({
  tickets: { type: Array, required: true },
  isAttendant: { type: Boolean, default: false }
})

const STATUS_LABEL = {
  NOVO: 'Novo', OPEN: 'Aberto', IN_PROGRESS: 'Em Andamento',
  RESOLVED: 'Resolvido', CONCLUDED: 'Concluído', CANCELADO: 'Cancelado', RETORNO: 'Retorno'
}

const PRIORITY_CLASS = {
  BAIXA: 'priority-low', NORMAL: 'priority-normal', ALTA: 'priority-high', URGENTE: 'priority-urgent'
}

const formatDate = (ticket) => {
  const date = ticket.dates?.created ?? ticket.date_create
  return date ? new Date(date).toLocaleDateString('pt-BR') : '-'
}
</script>

<template>
  <div class="tickets-table">
    <table v-if="tickets.length">
      <thead>
        <tr>
          <th>#</th>
          <th>Título</th>
          <th v-if="isAttendant">Solicitante</th>
          <th>Status</th>
          <th>Prioridade</th>
          <th>Data</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ticket in tickets" :key="ticket.id">
          <td class="id-col">{{ ticket.id }}</td>
          <td class="title-col">{{ ticket.title }}</td>
          <td v-if="isAttendant">{{ ticket.requester?.name || '-' }}</td>
          <td><span class="status-badge">{{ STATUS_LABEL[ticket.status] || ticket.status }}</span></td>
          <td>
            <span v-if="ticket.priority" :class="['priority-badge', PRIORITY_CLASS[ticket.priority]]">{{ ticket.priority }}</span>
            <span v-else class="priority-badge priority-normal">—</span>
          </td>
          <td>{{ formatDate(ticket) }}</td>
        </tr>
      </tbody>
    </table>
    <p v-else class="empty">Nenhum ticket encontrado.</p>
  </div>
</template>

<style scoped>
.tickets-table { overflow-x: auto; }

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

th {
  text-align: left;
  padding: 8px 12px;
  border-bottom: 2px solid #e5e7eb;
  color: #374151;
  font-weight: 600;
  white-space: nowrap;
}

td {
  padding: 10px 12px;
  border-bottom: 1px solid #f3f4f6;
  color: #4b5563;
}

tr:hover td { background: #f9fafb; }

.id-col { color: #9ca3af; font-size: 0.8rem; }

.title-col {
  max-width: 220px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-badge {
  background: #e0e7ff;
  color: #3730a3;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  white-space: nowrap;
}

.priority-badge {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
}

.priority-low    { background: #d1fae5; color: #065f46; }
.priority-normal { background: #dbeafe; color: #1e40af; }
.priority-high   { background: #fef3c7; color: #92400e; }
.priority-urgent { background: #fee2e2; color: #991b1b; }

.empty { color: #9ca3af; text-align: center; padding: 20px 0; }
</style>
