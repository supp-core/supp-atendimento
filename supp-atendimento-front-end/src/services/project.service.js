import api from './api'

export const projectService = {
  getAll() {
    return api.get('/project')
  },
  getById(id) {
    return api.get(`/project/${id}`)
  },
  getServices(id, params = {}) {
    return api.get(`/project/${id}/services`, { params })
  },
  create(data) {
    return api.post('/project', data)
  },
  update(id, data) {
    return api.put(`/project/${id}`, data)
  },
  patchStatus(id, status) {
    return api.patch(`/project/${id}/status`, { status })
  },
  remove(id) {
    return api.delete(`/project/${id}`)
  },
}
