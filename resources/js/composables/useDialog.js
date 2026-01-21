/**
 * Single Responsibility: Handle dialog state management only
 */
import { ref } from 'vue'

export function useDialog() {
  const isOpen = ref(false)
  const data = ref(null)

  const open = (dialogData = null) => {
    data.value = dialogData
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
    data.value = null
  }

  const toggle = () => {
    isOpen.value = !isOpen.value
    if (!isOpen.value) {
      data.value = null
    }
  }

  return {
    isOpen,
    data,
    open,
    close,
    toggle,
  }
}
