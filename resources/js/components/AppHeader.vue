<script setup>
/**
 * AppHeader Component - Single Responsibility: Render application header UI only
 * Dependency Inversion: Uses stores (abstractions) instead of concrete implementations
 */
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Button from './ui/button.vue'
import { LogOut, User, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  showBackButton: {
    type: Boolean,
    default: false,
  },
  backRoute: {
    type: String,
    default: '/dashboard',
  },
})

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
}

const handleBack = () => {
  router.push(props.backRoute)
}

const isActiveRoute = (path) => {
  return router.currentRoute.value.path === path
}
</script>

<template>
  <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-b border-gray-200 dark:border-slate-700 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center gap-6">
          <Button
            v-if="showBackButton"
            variant="ghost"
            size="sm"
            @click="handleBack"
          >
            <ArrowLeft class="mr-2 h-4 w-4" />
            Back
          </Button>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
          <nav class="hidden md:flex gap-4">
            <Button
              variant="ghost"
              size="sm"
              @click="router.push('/dashboard')"
              :class="{ 'bg-accent': isActiveRoute('/dashboard') }"
            >
              Dashboard
            </Button>
            <Button
              variant="ghost"
              size="sm"
              @click="router.push('/tasks')"
              :class="{ 'bg-accent': isActiveRoute('/tasks') }"
            >
              Tasks
            </Button>
            <Button
              variant="ghost"
              size="sm"
              @click="router.push('/kanban')"
              :class="{ 'bg-accent': isActiveRoute('/kanban') }"
            >
              Kanban
            </Button>
          </nav>
        </div>
        <div class="flex items-center gap-4">
          <slot name="actions" />
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
</template>
