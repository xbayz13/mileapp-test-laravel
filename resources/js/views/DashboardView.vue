<script setup>
/**
 * DashboardView Component - Single Responsibility: Render dashboard UI only
 * Dependency Inversion: Uses composables (abstractions) instead of concrete implementations
 */
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTaskStore } from '../stores/task'
import Button from '../components/ui/button.vue'
import Card from '../components/ui/card.vue'
import CardHeader from '../components/ui/card-header.vue'
import CardTitle from '../components/ui/card-title.vue'
import CardContent from '../components/ui/card-content.vue'
import Badge from '../components/ui/badge.vue'
import { LogOut, User, CheckCircle2, Clock, AlertCircle, ListTodo, ArrowRight, Loader2, Calendar, AlertTriangle } from 'lucide-vue-next'
import { useTaskConstants } from '../composables/useTaskConstants'
import { useDateFormat } from '../composables/useDateFormat'

const router = useRouter()
const authStore = useAuthStore()
const taskStore = useTaskStore()
const { getStatusColor, getPriorityColor, getStatusLabel, getPriorityLabel } = useTaskConstants()
const { formatDate } = useDateFormat()

const handleLogout = async () => {
  await authStore.logout()
}

// Helper function to check if date is today
const isToday = (dateString) => {
  if (!dateString) return false
  const date = new Date(dateString)
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

// Helper function to check if date is past due
const isPastDue = (dateString) => {
  if (!dateString) return false
  const date = new Date(dateString)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  date.setHours(0, 0, 0, 0)
  return date < today
}

const stats = computed(() => {
  const allTasks = taskStore.tasks
  return {
    total: taskStore.total || allTasks.length,
    pending: allTasks.filter(t => t.status === 'pending').length,
    inProgress: allTasks.filter(t => t.status === 'in_progress').length,
    completed: allTasks.filter(t => t.status === 'completed').length,
    dueToday: allTasks.filter(t => 
      t.due_date && 
      isToday(t.due_date) && 
      t.status !== 'completed'
    ).length,
    pastDue: allTasks.filter(t => 
      t.due_date && 
      isPastDue(t.due_date) && 
      t.status !== 'completed'
    ).length,
  }
})

// Get tasks due today
const tasksDueToday = computed(() => {
  return taskStore.tasks.filter(t => 
    t.due_date && 
    isToday(t.due_date) && 
    t.status !== 'completed'
  )
})

// Get tasks past due
const tasksPastDue = computed(() => {
  return taskStore.tasks.filter(t => 
    t.due_date && 
    isPastDue(t.due_date) && 
    t.status !== 'completed'
  ).sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
})

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  
  // Fetch tasks for statistics
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
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
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
            <Button @click="handleLogout" variant="outline" size="sm">
              <LogOut class="mr-2 h-4 w-4" />
              <span class="hidden sm:inline">Logout</span>
            </Button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
          Welcome back, {{ authStore.user?.name || 'User' }}!
        </h2>
        <p class="text-muted-foreground">
          Here's an overview of your tasks
        </p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 mb-8">
        <Card class="hover:shadow-lg transition-shadow">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
            <ListTodo class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total }}</div>
            <p class="text-xs text-muted-foreground">All your tasks</p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-lg transition-shadow">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending</CardTitle>
            <Clock class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.pending }}</div>
            <p class="text-xs text-muted-foreground">Tasks to start</p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-lg transition-shadow">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">In Progress</CardTitle>
            <AlertCircle class="h-4 w-4 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.inProgress }}</div>
            <p class="text-xs text-muted-foreground">Currently working</p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-lg transition-shadow">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Completed</CardTitle>
            <CheckCircle2 class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.completed }}</div>
            <p class="text-xs text-muted-foreground">Tasks finished</p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-lg transition-shadow border-orange-200 dark:border-orange-800" :class="{ 'bg-orange-50 dark:bg-orange-950/20': stats.dueToday > 0 }">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Due Today</CardTitle>
            <Calendar class="h-4 w-4 text-orange-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold" :class="{ 'text-orange-600 dark:text-orange-400': stats.dueToday > 0 }">{{ stats.dueToday }}</div>
            <p class="text-xs text-muted-foreground">Due today</p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-lg transition-shadow border-red-200 dark:border-red-800" :class="{ 'bg-red-50 dark:bg-red-950/20': stats.pastDue > 0 }">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Past Due</CardTitle>
            <AlertTriangle class="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold" :class="{ 'text-red-600 dark:text-red-400': stats.pastDue > 0 }">{{ stats.pastDue }}</div>
            <p class="text-xs text-muted-foreground">Overdue tasks</p>
          </CardContent>
        </Card>
      </div>

      <!-- Urgent Tasks: Past Due & Due Today -->
      <div class="grid gap-6 md:grid-cols-2 mb-8">
        <!-- Past Due Tasks -->
        <Card class="border-red-200 dark:border-red-800" :class="{ 'border-2': tasksPastDue.length > 0 }">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <AlertTriangle class="h-5 w-5 text-red-500" />
              Past Due Tasks
              <Badge v-if="tasksPastDue.length > 0" variant="destructive" class="ml-auto">
                {{ tasksPastDue.length }}
              </Badge>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="taskStore.loading" class="flex justify-center py-4">
              <Loader2 class="h-6 w-6 animate-spin text-primary" />
            </div>
            <div v-else-if="tasksPastDue.length === 0" class="text-center py-4">
              <CheckCircle2 class="h-8 w-8 text-green-500 mx-auto mb-2" />
              <p class="text-sm text-muted-foreground">No overdue tasks. Great job!</p>
            </div>
            <div v-else class="space-y-3 max-h-64 overflow-y-auto">
              <div
                v-for="task in tasksPastDue"
                :key="task._id"
                class="flex items-start justify-between p-3 rounded-md border border-red-200 dark:border-red-800 bg-red-50/50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-950/40 transition-colors cursor-pointer"
                @click="router.push('/tasks')"
              >
                <div class="flex-1">
                  <p class="text-sm font-medium mb-1">{{ task.title }}</p>
                  <p v-if="task.description" class="text-xs text-muted-foreground mb-2 line-clamp-2">
                    {{ task.description }}
                  </p>
                  <div class="flex items-center gap-2 flex-wrap">
                    <Badge :variant="getStatusColor(task.status)">
                      {{ getStatusLabel(task.status) }}
                    </Badge>
                    <Badge :variant="getPriorityColor(task.priority)">
                      {{ getPriorityLabel(task.priority) }}
                    </Badge>
                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">
                      Due: {{ formatDate(task.due_date) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <Button
              v-if="tasksPastDue.length > 0"
              variant="outline"
              size="sm"
              class="w-full mt-4 border-red-300 dark:border-red-700"
              @click="router.push('/tasks')"
            >
              View All Tasks
            </Button>
          </CardContent>
        </Card>

        <!-- Due Today Tasks -->
        <Card class="border-orange-200 dark:border-orange-800" :class="{ 'border-2': tasksDueToday.length > 0 }">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Calendar class="h-5 w-5 text-orange-500" />
              Due Today
              <Badge v-if="tasksDueToday.length > 0" variant="outline" class="ml-auto border-orange-300 dark:border-orange-700 text-orange-600 dark:text-orange-400">
                {{ tasksDueToday.length }}
              </Badge>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="taskStore.loading" class="flex justify-center py-4">
              <Loader2 class="h-6 w-6 animate-spin text-primary" />
            </div>
            <div v-else-if="tasksDueToday.length === 0" class="text-center py-4">
              <CheckCircle2 class="h-8 w-8 text-green-500 mx-auto mb-2" />
              <p class="text-sm text-muted-foreground">No tasks due today. Keep it up!</p>
            </div>
            <div v-else class="space-y-3 max-h-64 overflow-y-auto">
              <div
                v-for="task in tasksDueToday"
                :key="task._id"
                class="flex items-start justify-between p-3 rounded-md border border-orange-200 dark:border-orange-800 bg-orange-50/50 dark:bg-orange-950/20 hover:bg-orange-100 dark:hover:bg-orange-950/40 transition-colors cursor-pointer"
                @click="router.push('/tasks')"
              >
                <div class="flex-1">
                  <p class="text-sm font-medium mb-1">{{ task.title }}</p>
                  <p v-if="task.description" class="text-xs text-muted-foreground mb-2 line-clamp-2">
                    {{ task.description }}
                  </p>
                  <div class="flex items-center gap-2 flex-wrap">
                    <Badge :variant="getStatusColor(task.status)">
                      {{ getStatusLabel(task.status) }}
                    </Badge>
                    <Badge :variant="getPriorityColor(task.priority)">
                      {{ getPriorityLabel(task.priority) }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
            <Button
              v-if="tasksDueToday.length > 0"
              variant="outline"
              size="sm"
              class="w-full mt-4 border-orange-300 dark:border-orange-700"
              @click="router.push('/tasks')"
            >
              View All Tasks
            </Button>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <div class="grid gap-6 md:grid-cols-2">
        <Card class="hover:shadow-lg transition-shadow cursor-pointer" @click="router.push('/tasks')">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              Manage Tasks
              <ArrowRight class="h-5 w-5 text-muted-foreground" />
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground mb-4">
              View, create, edit, and manage all your tasks in one place.
            </p>
            <Button variant="outline" class="w-full">
              Go to Tasks
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="taskStore.loading" class="flex justify-center py-4">
              <Loader2 class="h-6 w-6 animate-spin text-primary" />
            </div>
            <div v-else-if="taskStore.tasks.length === 0" class="text-center py-4">
              <p class="text-sm text-muted-foreground">No tasks yet. Create your first task!</p>
            </div>
            <div v-else class="space-y-3 max-h-64 overflow-y-auto">
              <div
                v-for="task in taskStore.tasks.slice(0, 5)"
                :key="task._id"
                class="flex items-center justify-between p-2 rounded-md hover:bg-accent transition-colors cursor-pointer"
                @click="router.push('/tasks')"
              >
                <div class="flex-1">
                  <p class="text-sm font-medium">{{ task.title }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <Badge :variant="getStatusColor(task.status)">
                      {{ getStatusLabel(task.status) }}
                    </Badge>
                    <Badge :variant="getPriorityColor(task.priority)">
                      {{ getPriorityLabel(task.priority) }}
                    </Badge>
                    <span v-if="task.due_date" class="text-xs text-muted-foreground">
                      Due: {{ formatDate(task.due_date) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </main>
  </div>
</template>
