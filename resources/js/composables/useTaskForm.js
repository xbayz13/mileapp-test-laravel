/**
 * Single Responsibility: Handle task form state and operations only
 * Dependency Inversion: Depends on validation abstraction, not concrete implementation
 */
import { ref, watch } from 'vue'
import { useFormValidation } from './useFormValidation'

export function useTaskForm(initialTask = null) {
  const { errors, validateField, clearErrors, rules } = useFormValidation()

  const formData = ref({
    title: '',
    description: '',
    status: 'pending',
    priority: 'medium',
    due_date: '',
  })

  const resetForm = () => {
    formData.value = {
      title: '',
      description: '',
      status: 'pending',
      priority: 'medium',
      due_date: '',
    }
    clearErrors()
  }

  const loadTask = (task) => {
    if (task) {
      formData.value = {
        title: task.title || '',
        description: task.description || '',
        status: task.status || 'pending',
        priority: task.priority || 'medium',
        due_date: task.due_date ? task.due_date.split(' ')[0] : '',
      }
    } else {
      resetForm()
    }
  }

  const validate = () => {
    clearErrors()
    
    const titleValid = validateField('title', formData.value.title, [
      rules.required('Title is required'),
      rules.maxLength(255, 'Title must not exceed 255 characters'),
    ])
    
    return titleValid
  }

  const getFormData = () => {
    return {
      ...formData.value,
      due_date: formData.value.due_date || null,
    }
  }

  // Watch for initial task changes
  if (initialTask !== null) {
    watch(() => initialTask, (newTask) => {
      loadTask(newTask)
    }, { immediate: true })
  }

  return {
    formData,
    errors,
    resetForm,
    loadTask,
    validate,
    getFormData,
  }
}
