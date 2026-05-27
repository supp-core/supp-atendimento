<template>
  <div class="report-page">
    <ActivityReportHeader />

    <div class="report-meta">
      <div class="meta-row">
        <span class="meta-label">Atendente:</span>
        <span class="meta-value">{{ report.attendant?.name }}</span>
      </div>
      <div class="meta-row">
        <span class="meta-label">Período:</span>
        <span class="meta-value">{{ formatDate(report.date_from) }} a {{ formatDate(report.date_to) }}</span>
      </div>
      <div v-if="report.project" class="meta-row">
        <span class="meta-label">Projeto:</span>
        <span class="meta-value">[{{ report.project.acronym }}] {{ report.project.name }}</span>
      </div>
    </div>

    <table class="activities-table">
      <thead>
        <tr>
          <th class="col-date">Data</th>
          <th class="col-ticket">Demanda</th>
          <th class="col-type">Tipo</th>
          <th class="col-category">Categoria</th>
          <th class="col-activity">Atividade Realizada</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(activity, i) in report.activities" :key="i">
          <td class="col-date">{{ formatDate(activity.date) }}</td>
          <td class="col-ticket">#{{ activity.service_id }} — {{ activity.title }}</td>
          <td class="col-type">{{ activity.service_type || '—' }}</td>
          <td class="col-category">{{ activity.category || '—' }}</td>
          <td class="col-activity">{{ activity.comment }}</td>
        </tr>
        <tr v-if="!report.activities?.length">
          <td colspan="5" class="empty-row">Nenhuma atividade registrada no período.</td>
        </tr>
      </tbody>
    </table>

    <div class="summary-row">
      Total de atividades: <strong>{{ report.activities?.length || 0 }}</strong>
    </div>

    <div class="signatures">
      <div class="signature-block">
        <div class="signature-line"></div>
        <div class="signature-name">{{ report.attendant?.name }}</div>
        <div class="signature-role">Atendente</div>
      </div>
      <div class="signature-block">
        <div class="signature-line"></div>
        <div class="signature-name">Responsável pelo Setor</div>
        <div class="signature-role">Visto</div>
      </div>
    </div>

    <div class="report-footer">
      Gerado em {{ generatedAt }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ActivityReportHeader from './ActivityReportHeader.vue'

const props = defineProps({
  report: { type: Object, required: true },
})

const generatedAt = new Date().toLocaleString('pt-BR')

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}
</script>

<style scoped>
.report-page {
  font-family: Arial, sans-serif;
  font-size: 11px;
  color: #000;
  padding: 30px;
  background: white;
  width: 210mm;
  min-height: 297mm;
  box-sizing: border-box;
}
.report-meta {
  margin-bottom: 16px;
}
.meta-row {
  display: flex;
  gap: 8px;
  margin-bottom: 4px;
  font-size: 11px;
}
.meta-label {
  font-weight: 700;
  min-width: 80px;
}
.activities-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 12px;
  font-size: 10px;
}
.activities-table th,
.activities-table td {
  border: 1px solid #333;
  padding: 5px 7px;
  vertical-align: top;
  text-align: left;
}
.activities-table th {
  background-color: #1a237e;
  color: white;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 9px;
  letter-spacing: 0.5px;
}
.activities-table tbody tr:nth-child(even) {
  background-color: #f5f5f5;
}
.col-date { width: 80px; white-space: nowrap; }
.col-ticket { width: 180px; }
.col-type { width: 100px; }
.col-category { width: 100px; }
.col-activity { width: auto; }
.empty-row {
  text-align: center;
  font-style: italic;
  color: #666;
  padding: 20px !important;
}
.summary-row {
  font-size: 11px;
  margin-bottom: 40px;
}
.signatures {
  display: flex;
  justify-content: space-around;
  margin-top: 60px;
  margin-bottom: 30px;
}
.signature-block {
  text-align: center;
  width: 200px;
}
.signature-line {
  border-top: 1px solid #000;
  margin-bottom: 6px;
}
.signature-name {
  font-weight: 600;
  font-size: 11px;
}
.signature-role {
  font-size: 10px;
  color: #555;
}
.report-footer {
  text-align: right;
  font-size: 9px;
  color: #666;
  border-top: 1px solid #ccc;
  padding-top: 6px;
}
</style>
