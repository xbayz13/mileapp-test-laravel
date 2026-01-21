/**
 * Single Responsibility: Handle pagination logic only
 */
import { ref, computed } from 'vue'

export function usePagination(initialPerPage = 15) {
  const currentPage = ref(1)
  const perPage = ref(initialPerPage)
  const total = ref(0)
  const lastPage = ref(1)

  const updatePagination = (meta) => {
    currentPage.value = meta.current_page
    total.value = meta.total
    lastPage.value = meta.last_page
  }

  const goToPage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
      currentPage.value = page
    }
  }

  const nextPage = () => {
    if (currentPage.value < lastPage.value) {
      currentPage.value++
    }
  }

  const previousPage = () => {
    if (currentPage.value > 1) {
      currentPage.value--
    }
  }

  const resetPagination = () => {
    currentPage.value = 1
  }

  const paginationPages = computed(() => {
    const pages = []
    const totalPages = lastPage.value
    const current = currentPage.value
    
    if (totalPages <= 7) {
      for (let i = 1; i <= totalPages; i++) {
        pages.push(i)
      }
    } else {
      if (current <= 3) {
        for (let i = 1; i <= 5; i++) pages.push(i)
        pages.push('...')
        pages.push(totalPages)
      } else if (current >= totalPages - 2) {
        pages.push(1)
        pages.push('...')
        for (let i = totalPages - 4; i <= totalPages; i++) pages.push(i)
      } else {
        pages.push(1)
        pages.push('...')
        for (let i = current - 1; i <= current + 1; i++) pages.push(i)
        pages.push('...')
        pages.push(totalPages)
      }
    }
    
    return pages
  })

  return {
    currentPage,
    perPage,
    total,
    lastPage,
    updatePagination,
    goToPage,
    nextPage,
    previousPage,
    resetPagination,
    paginationPages,
  }
}
