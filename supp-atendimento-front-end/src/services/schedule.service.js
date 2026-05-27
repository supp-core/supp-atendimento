import api from './api'

export const scheduleService = {
  async getSchedule(params = {}) {
    if (!params.project_id) {
      throw new Error('project_id é obrigatório para buscar o cronograma')
    }
    const response = await api.get('/schedule', { params })
    return response.data
  },
}
