import api from './api'

export const taskService = {
  async getAllTasks(filters = {}, sortBy = 'created_at', sortDir = 'desc', perPage = 15, page = 1) {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.toString(),
      sort_by: sortBy,
      sort_dir: sortDir,
    })

    if (filters.status) params.append('status', filters.status)
    if (filters.priority) params.append('priority', filters.priority)
    if (filters.search) params.append('search', filters.search)

    const response = await api.get(`/tasks?${params.toString()}`)
    return response.data
  },

  async getTaskById(id) {
    const response = await api.get(`/tasks/${id}`)
    return response.data.data
  },

  async createTask(taskData) {
    const response = await api.post('/tasks', taskData)
    return response.data.data
  },

  async updateTask(id, taskData) {
    const response = await api.put(`/tasks/${id}`, taskData)
    return response.data.data
  },

  async deleteTask(id) {
    await api.delete(`/tasks/${id}`)
    return true
  },
}
