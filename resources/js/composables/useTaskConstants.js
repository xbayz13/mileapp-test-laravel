/**
 * Single Responsibility: Provide task-related constants and mappings only
 * Open/Closed: Can be extended with new statuses/priorities without modifying existing code
 */
export const useTaskConstants = () => {
  const statusColors = {
    pending: 'secondary',
    in_progress: 'default',
    completed: 'outline',
  }

  const priorityColors = {
    low: 'outline',
    medium: 'default',
    high: 'destructive',
  }

  const statusLabels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed',
  }

  const priorityLabels = {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
  }

  const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
  ]

  const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
  ]

  const getStatusColor = (status) => statusColors[status] || 'secondary'
  const getPriorityColor = (priority) => priorityColors[priority] || 'default'
  const getStatusLabel = (status) => statusLabels[status] || status
  const getPriorityLabel = (priority) => priorityLabels[priority] || priority

  return {
    statusColors,
    priorityColors,
    statusLabels,
    priorityLabels,
    statusOptions,
    priorityOptions,
    getStatusColor,
    getPriorityColor,
    getStatusLabel,
    getPriorityLabel,
  }
}
