import api from './api'

export const reportService = {
  getProductivity(params) {
    return api.get('/report/productivity', { params })
  },
  getAttendantProductivity(attendantId, params) {
    return api.get(`/report/productivity/${attendantId}`, { params })
  },
  getActivityReport(attendantId, params) {
    return api.get(`/report/activity/${attendantId}`, { params })
  },
}
