/**
 * Single Responsibility: Handle form validation logic only
 * Open/Closed: Can be extended for different validation rules without modifying existing code
 */
import { ref } from 'vue'

export function useFormValidation() {
  const errors = ref({})

  const validateField = (field, value, rules) => {
    const fieldErrors = []
    
    for (const rule of rules) {
      const error = rule(value)
      if (error) {
        fieldErrors.push(error)
      }
    }
    
    if (fieldErrors.length > 0) {
      errors.value[field] = fieldErrors[0] // Return first error
      return false
    } else {
      delete errors.value[field]
      return true
    }
  }

  const validateForm = (fields) => {
    let isValid = true
    
    for (const [field, value] of Object.entries(fields)) {
      const rules = fields[`${field}Rules`] || []
      if (!validateField(field, value, rules)) {
        isValid = false
      }
    }
    
    return isValid
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const setError = (field, message) => {
    errors.value[field] = message
  }

  const clearError = (field) => {
    delete errors.value[field]
  }

  // Common validation rules (can be extended)
  const rules = {
    required: (message = 'This field is required') => (value) => {
      if (!value || (typeof value === 'string' && !value.trim())) {
        return message
      }
      return null
    },
    maxLength: (max, message) => (value) => {
      if (value && value.length > max) {
        return message || `Must not exceed ${max} characters`
      }
      return null
    },
    minLength: (min, message) => (value) => {
      if (value && value.length < min) {
        return message || `Must be at least ${min} characters`
      }
      return null
    },
  }

  return {
    errors,
    validateField,
    validateForm,
    clearErrors,
    setError,
    clearError,
    rules,
  }
}
