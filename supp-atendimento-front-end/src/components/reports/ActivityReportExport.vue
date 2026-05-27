<template>
  <div>
    <v-card class="mb-4">
      <v-card-title class="text-subtitle-1 d-flex align-center justify-space-between">
        <span>Relatório gerado — {{ report.activities?.length || 0 }} atividade(s)</span>
        <v-btn color="primary" prepend-icon="mdi-printer" @click="print">
          Imprimir / Exportar PDF
        </v-btn>
      </v-card-title>
    </v-card>

    <ActivityReportTemplate :report="report" />
  </div>
</template>

<script setup>
import ActivityReportTemplate from './ActivityReportTemplate.vue'

const props = defineProps({
  report: { type: Object, required: true },
})

function print() {
  const el = document.querySelector('.report-page')
  if (!el) return
  const html = el.outerHTML
  const win = window.open('', '_blank')
  win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Relatório de Atividades</title>
<style>
  body { margin: 0; font-family: Arial, sans-serif; }
  @page { size: A4 portrait; margin: 0; }
  @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
  .report-page { font-family: Arial, sans-serif; font-size: 11px; color: #000; padding: 30px; width: 210mm; min-height: 297mm; box-sizing: border-box; }
  .report-header { display: flex; align-items: center; gap: 20px; padding-bottom: 16px; border-bottom: 2px solid #1a237e; margin-bottom: 20px; }
  .brasao { width: 80px; height: auto; }
  .header-text { flex: 1; text-align: center; }
  .institution { font-size: 14px; font-weight: 700; color: #1a237e; text-transform: uppercase; }
  .department { font-size: 12px; color: #333; margin-top: 2px; }
  .report-title { font-size: 16px; font-weight: 700; color: #1a237e; margin-top: 8px; text-transform: uppercase; letter-spacing: 1px; }
  .report-meta { margin-bottom: 16px; }
  .meta-row { display: flex; gap: 8px; margin-bottom: 4px; font-size: 11px; }
  .meta-label { font-weight: 700; min-width: 80px; }
  .activities-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
  .activities-table th, .activities-table td { border: 1px solid #333; padding: 5px 7px; vertical-align: top; text-align: left; }
  .activities-table th { background-color: #1a237e; color: white; font-weight: 600; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; }
  .activities-table tbody tr:nth-child(even) { background-color: #f5f5f5; }
  .col-date { width: 80px; white-space: nowrap; }
  .col-ticket { width: 180px; }
  .col-type { width: 100px; }
  .col-category { width: 100px; }
  .summary-row { font-size: 11px; margin-bottom: 40px; }
  .signatures { display: flex; justify-content: space-around; margin-top: 60px; margin-bottom: 30px; }
  .signature-block { text-align: center; width: 200px; }
  .signature-line { border-top: 1px solid #000; margin-bottom: 6px; }
  .signature-name { font-weight: 600; font-size: 11px; }
  .signature-role { font-size: 10px; color: #555; }
  .report-footer { text-align: right; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 6px; }
</style></head><body>${html}</body></html>`)
  win.document.close()
  win.focus()
  setTimeout(() => win.print(), 500)
}
</script>
