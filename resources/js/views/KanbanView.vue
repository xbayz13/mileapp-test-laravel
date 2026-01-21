<script setup>
/**
 * KanbanView Component - Single Responsibility: Render kanban board UI only
 */
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTaskStore } from '../stores/task'
import Button from '../components/ui/button.vue'
import Card from '../components/ui/card.vue'
import Badge from '../components/ui/badge.vue'
import { LogOut, User, Loader2, ArrowLeft } from 'lucide-vue-next'
import { useTaskConstants } from '../composables/useTaskConstants'
import { useDateFormat } from '../composables/useDateFormat'

const router = useRouter()
const authStore = useAuthStore()
const taskStore = useTaskStore()
const { getStatusColor, getPriorityColor, getStatusLabel, getPriorityLabel, statusOptions } = useTaskConstants()
const { formatDate } = useDateFormat()

// Group tasks by status
const tasksByStatus = computed(() => {
  const grouped = {
    pending: [],
    in_progress: [],
    completed: [],
  }
  
  taskStore.tasks.forEach(task => {
    if (grouped[task.status]) {
      grouped[task.status].push(task)
    }
  })
  
  return grouped
})

// Get task count for each status
const getTaskCount = (status) => {
  return tasksByStatus.value[status]?.length || 0
}

// Handle status change
const handleStatusChange = async (taskId, newStatus) => {
  if (taskStore.loading) return
  
  const task = taskStore.tasks.find(t => t._id === taskId)
  if (!task || task.status === newStatus) return
  
  const result = await taskStore.updateTask(taskId, { status: newStatus })
  
  if (result.success) {
    // Refresh tasks to get updated list
    await taskStore.fetchTasks(taskStore.currentPage)
  }
}

// Handle drag start
const handleDragStart = (event, task) => {
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('taskId', task._id)
  event.dataTransfer.setData('currentStatus', task.status)
  event.currentTarget.style.opacity = '0.5'
}

// Handle drag end
const handleDragEnd = (event) => {
  event.currentTarget.style.opacity = '1'
}

// Handle drag over
const handleDragOver = (event) => {
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
  event.currentTarget.classList.add('bg-blue-50', 'dark:bg-blue-950/20')
}

// Handle drag leave
const handleDragLeave = (event) => {
  event.currentTarget.classList.remove('bg-blue-50', 'dark:bg-blue-950/20')
}

// Handle drop
const handleDrop = async (event, targetStatus) => {
  event.preventDefault()
  event.currentTarget.classList.remove('bg-blue-50', 'dark:bg-blue-950/20')
  
  const taskId = event.dataTransfer.getData('taskId')
  const currentStatus = event.dataTransfer.getData('currentStatus')
  
  if (taskId && currentStatus !== targetStatus) {
    await handleStatusChange(taskId, targetStatus)
  }
}

// Handle click to change status (alternative to drag & drop)
const handleStatusClick = async (task, newStatus) => {
  if (task.status !== newStatus) {
    await handleStatusChange(task._id, newStatus)
  }
}

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  
  await taskStore.fetchTasks(1)
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <!-- Header -->
    <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-b border-gray-200 dark:border-slate-700 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-6">
            <Button
              variant="ghost"
              size="sm"
              @click="router.push('/dashboard')"
            >
              <ArrowLeft class="mr-2 h-4 w-4" />
              Back
            </Button>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Kanban Board</h1>
            <nav class="hidden md:flex gap-4">
              <Button
                variant="ghost"
                size="sm"
                @click="router.push('/dashboard')"
                :class="{ 'bg-accent': router.currentRoute.value.path === '/dashboard' }"
              >
                Dashboard
              </Button>
              <Button
                variant="ghost"
                size="sm"
                @click="router.push('/tasks')"
                :class="{ 'bg-accent': router.currentRoute.value.path === '/tasks' }"
              >
                Tasks
              </Button>
              <Button
                variant="ghost"
                size="sm"
                @click="router.push('/kanban')"
                :class="{ 'bg-accent': router.currentRoute.value.path === '/kanban' }"
              >
                Kanban
              </Button>
            </nav>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <User class="h-4 w-4" />
              <span class="hidden sm:inline">{{ authStore.user?.name || authStore.user?.email }}</span>
            </div>
            <Button @click="authStore.logout()" variant="outline" size="sm">
              <LogOut class="mr-2 h-4 w-4" />
              <span class="hidden sm:inline">Logout</span>
            </Button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="taskStore.loading && taskStore.tasks.length === 0" class="flex justify-center items-center py-12">
        <Loader2 class="h-8 w-8 animate-spin text-primary" />
      </div>

      <!-- Kanban Board -->
      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pending Column -->
        <div
          class="flex flex-col transition-colors rounded-lg"
          @dragover="handleDragOver"
          @dragleave="handleDragLeave"
          @drop="handleDrop($event, 'pending')"
        >
          <Card class="flex-1 flex flex-col">
            <div class="p-4 border-b">
              <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">Pending</h2>
                <Badge variant="secondary">{{ getTaskCount('pending') }}</Badge>
              </div>
              <p class="text-xs text-muted-foreground">Tasks to start</p>
            </div>
            <div class="flex-1 p-4 space-y-3 min-h-[400px] max-h-[calc(100vh-300px)] overflow-y-auto">
              <div
                v-for="task in tasksByStatus.pending"
                :key="task._id"
                draggable="true"
                @dragstart="handleDragStart($event, task)"
                @dragend="handleDragEnd"
                class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 hover:shadow-md transition-all cursor-move"
              >
                <h3 class="font-medium text-sm mb-2">{{ task.title }}</h3>
                <p v-if="task.description" class="text-xs text-muted-foreground mb-3 line-clamp-2">
                  {{ task.description }}
                </p>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                  <Badge :variant="getPriorityColor(task.priority)" class="text-xs">
                    {{ getPriorityLabel(task.priority) }}
                  </Badge>
                  <span v-if="task.due_date" class="text-xs text-muted-foreground">
                    {{ formatDate(task.due_date) }}
                  </span>
                </div>
                <div class="flex gap-1 pt-2 border-t border-gray-100 dark:border-slate-700">
                  <Button
                    v-if="task.status !== 'in_progress'"
                    variant="ghost"
                    size="sm"
                    class="flex-1 text-xs"
                    @click="handleStatusClick(task, 'in_progress')"
                    :disabled="taskStore.loading"
                  >
                    → In Progress
                  </Button>
                  <Button
                    v-if="task.status !== 'completed'"
                    variant="ghost"
                    size="sm"
                    class="flex-1 text-xs"
                    @click="handleStatusClick(task, 'completed')"
                    :disabled="taskStore.loading"
                  >
                    → Complete
                  </Button>
                </div>
              </div>
              <div v-if="tasksByStatus.pending.length === 0" class="text-center py-8 text-muted-foreground text-sm">
                No pending tasks
              </div>
            </div>
          </Card>
        </div>

        <!-- In Progress Column -->
        <div
          class="flex flex-col transition-colors rounded-lg"
          @dragover="handleDragOver"
          @dragleave="handleDragLeave"
          @drop="handleDrop($event, 'in_progress')"
        >
          <Card class="flex-1 flex flex-col">
            <div class="p-4 border-b">
              <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">In Progress</h2>
                <Badge variant="default">{{ getTaskCount('in_progress') }}</Badge>
              </div>
              <p class="text-xs text-muted-foreground">Currently working</p>
            </div>
            <div class="flex-1 p-4 space-y-3 min-h-[400px] max-h-[calc(100vh-300px)] overflow-y-auto">
              <div
                v-for="task in tasksByStatus.in_progress"
                :key="task._id"
                draggable="true"
                @dragstart="handleDragStart($event, task)"
                @dragend="handleDragEnd"
                class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 hover:shadow-md transition-all cursor-move"
              >
                <h3 class="font-medium text-sm mb-2">{{ task.title }}</h3>
                <p v-if="task.description" class="text-xs text-muted-foreground mb-3 line-clamp-2">
                  {{ task.description }}
                </p>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                  <Badge :variant="getPriorityColor(task.priority)" class="text-xs">
                    {{ getPriorityLabel(task.priority) }}
                  </Badge>
                  <span v-if="task.due_date" class="text-xs text-muted-foreground">
                    {{ formatDate(task.due_date) }}
                  </span>
                </div>
                <div class="flex gap-1 pt-2 border-t border-gray-100 dark:border-slate-700">
                  <Button
                    v-if="task.status !== 'pending'"
                    variant="ghost"
                    size="sm"
                    class="flex-1 text-xs"
                    @click="handleStatusClick(task, 'pending')"
                    :disabled="taskStore.loading"
                  >
                    ← Pending
                  </Button>
                  <Button
                    v-if="task.status !== 'completed'"
                    variant="ghost"
                    size="sm"
                    class="flex-1 text-xs"
                    @click="handleStatusClick(task, 'completed')"
                    :disabled="taskStore.loading"
                  >
                    → Complete
                  </Button>
                </div>
              </div>
              <div v-if="tasksByStatus.in_progress.length === 0" class="text-center py-8 text-muted-foreground text-sm">
                No tasks in progress
              </div>
            </div>
          </Card>
        </div>

        <!-- Completed Column -->
        <div
          class="flex flex-col transition-colors rounded-lg"
          @dragover="handleDragOver"
          @dragleave="handleDragLeave"
          @drop="handleDrop($event, 'completed')"
        >
          <Card class="flex-1 flex flex-col">
            <div class="p-4 border-b">
              <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-semibold">Completed</h2>
                <Badge variant="outline">{{ getTaskCount('completed') }}</Badge>
              </div>
              <p class="text-xs text-muted-foreground">Tasks finished</p>
            </div>
            <div class="flex-1 p-4 space-y-3 min-h-[400px] max-h-[calc(100vh-300px)] overflow-y-auto">
              <div
                v-for="task in tasksByStatus.completed"
                :key="task._id"
                draggable="true"
                @dragstart="handleDragStart($event, task)"
                @dragend="handleDragEnd"
                class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 hover:shadow-md transition-all cursor-move opacity-75"
              >
                <h3 class="font-medium text-sm mb-2 line-through">{{ task.title }}</h3>
                <p v-if="task.description" class="text-xs text-muted-foreground mb-3 line-clamp-2">
                  {{ task.description }}
                </p>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                  <Badge :variant="getPriorityColor(task.priority)" class="text-xs">
                    {{ getPriorityLabel(task.priority) }}
                  </Badge>
                  <span v-if="task.due_date" class="text-xs text-muted-foreground">
                    {{ formatDate(task.due_date) }}
                  </span>
                </div>
                <div class="flex gap-1 pt-2 border-t border-gray-100 dark:border-slate-700">
                  <Button
                    v-if="task.status !== 'pending'"
                    variant="ghost"
                    size="sm"
                    class="flex-1 text-xs"
                    @click="handleStatusClick(task, 'pending')"
                    :disabled="taskStore.loading"
                  >
                    ← Pending
                  </Button>
                  <Button
                    v-if="task.status !== 'in_progress'"
                    variant="ghost"
                    size="sm"
                    class="flex-1 text-xs"
                    @click="handleStatusClick(task, 'in_progress')"
                    :disabled="taskStore.loading"
                  >
                    ← In Progress
                  </Button>
                </div>
              </div>
              <div v-if="tasksByStatus.completed.length === 0" class="text-center py-8 text-muted-foreground text-sm">
                No completed tasks
              </div>
            </div>
          </Card>
        </div>
      </div>
    </main>
  </div>
</template>
