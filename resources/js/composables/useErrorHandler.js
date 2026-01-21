/**
 * Single Responsibility: Handle error processing and formatting only
 * Open/Closed: Can be extended with different error formatters without modifying existing code
 */
export function useErrorHandler() {
  const extractErrorMessage = (error) => {
    if (!error) return 'An unexpected error occurred'

    // Handle axios errors
    if (error.response?.data) {
      const responseData = error.response.data
      
      // Handle Laravel validation errors
      if (error.response.status === 422 && responseData.errors) {
        const firstError = Object.values(responseData.errors)[0]
        return Array.isArray(firstError) ? firstError[0] : firstError
      }
      
      // Handle other API errors
      if (responseData.message) {
        return responseData.message
      }
    }
    
    // Handle network errors
    if (error.request) {
      return 'Network error. Please check your connection and try again.'
    }
    
    // Handle other errors
    if (error.message) {
      return error.message
    }
    
    return 'An unexpected error occurred'
  }

  const extractValidationErrors = (error) => {
    if (error.response?.status === 422 && error.response.data?.errors) {
      return error.response.data.errors
    }
    return {}
  }

  return {
    extractErrorMessage,
    extractValidationErrors,
  }
}
