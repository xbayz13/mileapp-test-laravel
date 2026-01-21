/**
 * Single Responsibility: Handle task sorting logic only
 */
import { ref } from 'vue'

export function useTaskSorting() {
  const sortBy = ref('created_at')
  const sortDir = ref('desc')

  const setSort = (by, dir = 'desc') => {
    sortBy.value = by
    sortDir.value = dir
  }

  const resetSort = () => {
    sortBy.value = 'created_at'
    sortDir.value = 'desc'
  }

  return {
    sortBy,
    sortDir,
    setSort,
    resetSort,
  }
}
