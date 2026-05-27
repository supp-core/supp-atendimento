import { defineStore } from 'pinia'
import { reportService } from '@/services/report.service'

export const useReportStore = defineStore('report', {
  state: () => ({
    productivity: null,
    activityReport: null,
    filters: {
      date_from: null,
      date_to: null,
      project_id: null,
      sector_id: null,
      attendant_id: null,
    },
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProductivity(filters) {
      this.loading = true
      this.error = null
      try {
        const res = await reportService.getProductivity(filters)
        this.productivity = res.data.data
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async fetchAttendantProductivity(attendantId, filters) {
      this.loading = true
      this.error = null
      try {
        const res = await reportService.getAttendantProductivity(attendantId, filters)
        this.productivity = res.data.data
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async fetchActivityReport(attendantId, params) {
      this.loading = true
      this.error = null
      try {
        const res = await reportService.getActivityReport(attendantId, params)
        this.activityReport = res.data.data
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },
  },
})
