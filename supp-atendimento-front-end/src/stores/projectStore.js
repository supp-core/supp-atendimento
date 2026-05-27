import { defineStore } from 'pinia'
import { projectService } from '@/services/project.service'

export const useProjectStore = defineStore('project', {
  state: () => ({
    projects: [],
    currentProject: null,
    loading: false,
    error: null,
  }),

  getters: {
    activeProjects: (state) => state.projects.filter(p => p.status === 'ATIVO'),
  },

  actions: {
    async fetchProjects() {
      this.loading = true
      this.error = null
      try {
        const res = await projectService.getAll()
        this.projects = res.data.data || []
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async fetchProjectById(id) {
      this.loading = true
      try {
        const res = await projectService.getById(id)
        this.currentProject = res.data.data
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async createProject(payload) {
      const res = await projectService.create(payload)
      this.projects.push(res.data.data)
      return res.data.data
    },

    async updateProject(id, payload) {
      const res = await projectService.update(id, payload)
      const idx = this.projects.findIndex(p => p.id === id)
      if (idx !== -1) this.projects[idx] = res.data.data
      if (this.currentProject?.id === id) this.currentProject = res.data.data
      return res.data.data
    },

    async patchStatus(id, status) {
      const res = await projectService.patchStatus(id, status)
      const idx = this.projects.findIndex(p => p.id === id)
      if (idx !== -1) this.projects[idx] = res.data.data
      return res.data.data
    },

    async deleteProject(id) {
      await projectService.remove(id)
      this.projects = this.projects.filter(p => p.id !== id)
    },
  },
})
