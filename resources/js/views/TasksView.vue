<script setup>
/**
 * TasksView Component - Single Responsibility: Render tasks list UI only
 * Dependency Inversion: Uses composables (abstractions) instead of concrete implementations
 */
import { onMounted } from 'vue'
import { useTaskStore } from '../stores/task'
import { useRouter } from 'vue-router'
import Button from '../components/ui/button.vue'
import Card from '../components/ui/card.vue'
import CardHeader from '../components/ui/card-header.vue'
import CardTitle from '../components/ui/card-title.vue'
import CardContent from '../components/ui/card-content.vue'
import Input from '../components/ui/input.vue'
import Select from '../components/ui/select.vue'
import Badge from '../components/ui/badge.vue'
import Dialog from '../components/ui/dialog.vue'
import DialogHeader from '../components/ui/dialog-header.vue'
import DialogTitle from '../components/ui/dialog-title.vue'
import DialogContent from '../components/ui/dialog-content.vue'
import DialogClose from '../components/ui/dialog-close.vue'
import TaskForm from '../components/TaskForm.vue'
import { Plus, Search, Edit, Trash2, Calendar, Loader2, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { useDialog } from '../composables/useDialog'
import { useTaskConstants } from '../composables/useTaskConstants'
import { useDateFormat } from '../composables/useDateFormat'

const router = useRouter()
const taskStore = useTaskStore()
const { isOpen: showDialog, data: editingTask, open: openDialog, close: closeDialog } = useDialog()
const { getStatusColor, getPriorityColor, getStatusLabel, getPriorityLabel, statusOptions, priorityOptions } = useTaskConstants()
const { formatDate } = useDateFormat()

onMounted(() => {
  taskStore.fetchTasks()
})

const handleCreate = () => {
  openDialog(null)
}

const handleEdit = (task) => {
  openDialog(task)
}

const handleDelete = async (id) => {
  if (confirm('Are you sure you want to delete this task?')) {
    const result = await taskStore.deleteTask(id)
    if (result.success) {
      await taskStore.fetchTasks(taskStore.currentPage)
    }
  }
}

const handleSubmit = async (formData) => {
  let result
  if (editingTask.value) {
    result = await taskStore.updateTask(editingTask.value._id, formData)
  } else {
    result = await taskStore.createTask(formData)
  }

  if (result.success) {
    closeDialog()
    await taskStore.fetchTasks(taskStore.currentPage)
  }
}

const handleCancel = () => {
  closeDialog()
}

const handleFilterChange = (key, value) => {
  taskStore.setFilter(key, value)
  taskStore.fetchTasks(1)
}

const handlePageChange = (page) => {
  taskStore.fetchTasks(page)
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <!-- Header -->
    <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-b border-gray-200 dark:border-slate-700 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tasks</h1>
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
            </nav>
          </div>
          <Button @click="handleCreate">
            <Plus class="mr-2 h-4 w-4" />
            <span class="hidden sm:inline">New Task</span>
          </Button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="pt-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                v-model="taskStore.filters.search"
                placeholder="Search tasks..."
                class="pl-10"
                @input="handleFilterChange('search', $event.target.value)"
              />
            </div>

            <!-- Status Filter -->
            <Select
              v-model="taskStore.filters.status"
              @change="handleFilterChange('status', $event.target.value)"
            >
              <option value="">All Status</option>
              <option
                v-for="option in statusOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </Select>

            <!-- Priority Filter -->
            <Select
              v-model="taskStore.filters.priority"
              @change="handleFilterChange('priority', $event.target.value)"
            >
              <option value="">All Priority</option>
              <option
                v-for="option in priorityOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </Select>

            <!-- Reset Filters -->
            <Button
              variant="outline"
              @click="taskStore.resetFilters(); taskStore.fetchTasks(1)"
            >
              Reset Filters
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Tasks List -->
      <div v-if="taskStore.loading && taskStore.tasks.length === 0" class="flex justify-center items-center py-12">
        <Loader2 class="h-8 w-8 animate-spin text-primary" />
      </div>

      <div v-else-if="taskStore.tasks.length === 0" class="text-center py-12">
        <p class="text-muted-foreground">No tasks found. Create your first task!</p>
      </div>

      <div v-else class="space-y-4">
        <Card v-for="task in taskStore.tasks" :key="task._id" class="hover:shadow-lg transition-shadow">
          <CardContent class="pt-6">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold">{{ task.title }}</h3>
                  <Badge :variant="getStatusColor(task.status)">
                    {{ getStatusLabel(task.status) }}
                  </Badge>
                  <Badge :variant="getPriorityColor(task.priority)">
                    {{ getPriorityLabel(task.priority) }}
                  </Badge>
                </div>
                <p v-if="task.description" class="text-sm text-muted-foreground mb-3">
                  {{ task.description }}
                </p>
                <div class="flex items-center gap-4 text-xs text-muted-foreground">
                  <div class="flex items-center gap-1">
                    <Calendar class="h-3 w-3" />
                    {{ formatDate(task.due_date) }}
                  </div>
                  <span>Created {{ new Date(task.created_at).toLocaleDateString() }}</span>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <Button
                  variant="ghost"
                  size="icon"
                  @click="handleEdit(task)"
                >
                  <Edit class="h-4 w-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  @click="handleDelete(task._id)"
                >
                  <Trash2 class="h-4 w-4 text-destructive" />
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Pagination -->
      <div v-if="taskStore.lastPage > 1" class="flex justify-center items-center gap-2 mt-6">
        <Button
          variant="outline"
          size="icon"
          :disabled="taskStore.currentPage === 1 || taskStore.loading"
          @click="handlePageChange(taskStore.currentPage - 1)"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>
        
        <template v-for="page in taskStore.paginationPages" :key="page">
          <Button
            v-if="page !== '...'"
            variant="outline"
            :class="{ 'bg-primary text-primary-foreground': page === taskStore.currentPage }"
            :disabled="taskStore.loading"
            @click="handlePageChange(page)"
          >
            {{ page }}
          </Button>
          <span v-else class="px-2">...</span>
        </template>
        
        <Button
          variant="outline"
          size="icon"
          :disabled="taskStore.currentPage === taskStore.lastPage || taskStore.loading"
          @click="handlePageChange(taskStore.currentPage + 1)"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </main>

    <!-- Create/Edit Dialog -->
    <Dialog v-model="showDialog">
      <DialogHeader>
        <DialogTitle>{{ editingTask ? 'Edit Task' : 'Create New Task' }}</DialogTitle>
        <DialogClose @click="handleCancel" />
      </DialogHeader>
      <DialogContent>
        <TaskForm
          :task="editingTask"
          :loading="taskStore.loading"
          @submit="handleSubmit"
          @cancel="handleCancel"
        />
      </DialogContent>
    </Dialog>
  </div>
</template>
