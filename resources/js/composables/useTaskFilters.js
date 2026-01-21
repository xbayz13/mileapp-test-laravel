/**
 * Single Responsibility: Handle task filtering logic only
 */
import { ref } from 'vue'

export function useTaskFilters() {
  const filters = ref({
    status: '',
    priority: '',
    search: '',
  })

  const setFilter = (key, value) => {
    filters.value[key] = value
  }

  const resetFilters = () => {
    filters.value = {
      status: '',
      priority: '',
      search: '',
    }
  }

  return {
    filters,
    setFilter,
    resetFilters,
  }
}
