/**
 * Single Responsibility: Handle date formatting only
 * Open/Closed: Can be extended with new date formats without modifying existing code
 */
export function useDateFormat() {
  const formatDate = (dateString, format = 'display') => {
    if (!dateString) return 'No due date'
    
    try {
      const date = new Date(dateString)
      if (isNaN(date.getTime())) return dateString
      
      if (format === 'display') {
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
        })
      }
      
      if (format === 'short') {
        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
        })
      }
      
      if (format === 'full') {
        return date.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
        })
      }
      
      return dateString
    } catch {
      return dateString
    }
  }

  const formatDateTime = (dateString) => {
    if (!dateString) return ''
    
    try {
      const date = new Date(dateString)
      if (isNaN(date.getTime())) return dateString
      
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    } catch {
      return dateString
    }
  }

  return {
    formatDate,
    formatDateTime,
  }
}
