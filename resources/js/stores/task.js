/**
 * Task Store - Single Responsibility: Manage task state and coordinate with service layer
 * Dependency Inversion: Depends on taskService abstraction (can be injected)
 */
/**
 * Task Store - Single Responsibility: Manage task state and coordinate with service layer
 * Dependency Inversion: Depends on taskService abstraction (can be injected)
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { taskService } from '../services/taskService'
import { useErrorHandler } from '../composables/useErrorHandler'
import { usePagination } from '../composables/usePagination'

export const useTaskStore = defineStore('task', () => {
  const { extractErrorMessage, extractValidationErrors } = useErrorHandler()
  
  const tasks = ref([])
  const currentTask = ref(null)
  const loading = ref(false)
  const error = ref(null)
  
  // Pagination - Using composable for better separation of concerns
  const {
    currentPage,
    perPage,
    total,
    lastPage,
    updatePagination,
    resetPagination,
    paginationPages,
  } = usePagination(15)
  
  // Filters
  const filters = ref({
    status: '',
    priority: '',
    search: '',
  })
  
  // Sorting
  const sortBy = ref('created_at')
  const sortDir = ref('desc')

  const fetchTasks = async (page = 1) => {
    loading.value = true
    error.value = null
    
    try {
      currentPage.value = page
      const response = await taskService.getAllTasks(
        filters.value,
        sortBy.value,
        sortDir.value,
        perPage.value,
        page
      )
      
      tasks.value = response.data
      updatePagination(response.meta)
      
      return { success: true }
    } catch (err) {
      error.value = extractErrorMessage(err)
      console.error('Fetch tasks error:', err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const fetchTask = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const task = await taskService.getTaskById(id)
      currentTask.value = task
      return { success: true, data: task }
    } catch (err) {
      error.value = extractErrorMessage(err)
      console.error('Fetch task error:', err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const createTask = async (taskData) => {
    loading.value = true
    error.value = null
    
    try {
      const task = await taskService.createTask(taskData)
      tasks.value.unshift(task)
      total.value++
      return { success: true, data: task }
    } catch (err) {
      const validationErrors = extractValidationErrors(err)
      if (Object.keys(validationErrors).length > 0) {
        error.value = Object.values(validationErrors).flat().join(', ')
      } else {
        error.value = extractErrorMessage(err)
      }
      console.error('Create task error:', err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const updateTask = async (id, taskData) => {
    loading.value = true
    error.value = null
    
    try {
      const task = await taskService.updateTask(id, taskData)
      const index = tasks.value.findIndex(t => t._id === id)
      if (index !== -1) {
        tasks.value[index] = task
      }
      if (currentTask.value?._id === id) {
        currentTask.value = task
      }
      return { success: true, data: task }
    } catch (err) {
      const validationErrors = extractValidationErrors(err)
      if (Object.keys(validationErrors).length > 0) {
        error.value = Object.values(validationErrors).flat().join(', ')
      } else {
        error.value = extractErrorMessage(err)
      }
      console.error('Update task error:', err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const deleteTask = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await taskService.deleteTask(id)
      tasks.value = tasks.value.filter(t => t._id !== id)
      total.value--
      if (currentTask.value?._id === id) {
        currentTask.value = null
      }
      return { success: true }
    } catch (err) {
      error.value = extractErrorMessage(err)
      console.error('Delete task error:', err)
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const setFilter = (key, value) => {
    filters.value[key] = value
    resetPagination()
  }

  const setSort = (by, dir = 'desc') => {
    sortBy.value = by
    sortDir.value = dir
    resetPagination()
  }

  const resetFilters = () => {
    filters.value = {
      status: '',
      priority: '',
      search: '',
    }
    sortBy.value = 'created_at'
    sortDir.value = 'desc'
    resetPagination()
  }

  return {
    tasks,
    currentTask,
    loading,
    error,
    currentPage,
    perPage,
    total,
    lastPage,
    filters,
    sortBy,
    sortDir,
    fetchTasks,
    fetchTask,
    createTask,
    updateTask,
    deleteTask,
    setFilter,
    setSort,
    resetFilters,
    paginationPages,
  }
})
