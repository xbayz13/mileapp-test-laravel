<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Button from '../components/ui/button.vue'
import Input from '../components/ui/input.vue'
import Card from '../components/ui/card.vue'
import CardHeader from '../components/ui/card-header.vue'
import CardTitle from '../components/ui/card-title.vue'
import CardDescription from '../components/ui/card-description.vue'
import CardContent from '../components/ui/card-content.vue'
import { Lock, Mail, User, Loader2 } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const formErrors = ref({})

const validateForm = () => {
  formErrors.value = {}
  
  if (!name.value) {
    formErrors.value.name = 'Name is required'
  } else if (name.value.length < 2) {
    formErrors.value.name = 'Name must be at least 2 characters'
  }
  
  if (!email.value) {
    formErrors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    formErrors.value.email = 'Please enter a valid email address'
  }
  
  if (!password.value) {
    formErrors.value.password = 'Password is required'
  } else if (password.value.length < 6) {
    formErrors.value.password = 'Password must be at least 6 characters'
  }
  
  if (!passwordConfirmation.value) {
    formErrors.value.password_confirmation = 'Password confirmation is required'
  } else if (password.value !== passwordConfirmation.value) {
    formErrors.value.password_confirmation = 'Passwords do not match'
  }
  
  return Object.keys(formErrors.value).length === 0
}

const handleSubmit = async (e) => {
  e.preventDefault()
  
  if (!validateForm()) {
    return
  }
  
  formErrors.value = {}
  const result = await authStore.register(
    name.value,
    email.value,
    password.value,
    passwordConfirmation.value
  )
  
  if (result.success) {
    router.push('/dashboard')
  } else {
    formErrors.value.general = result.error || 'Registration failed. Please try again.'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <!-- Logo/Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
          Create Account
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Sign up to get started with your account
        </p>
      </div>

      <!-- Register Card -->
      <Card class="shadow-xl border-0 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm">
        <CardHeader class="space-y-1 pb-4">
          <CardTitle class="text-2xl font-semibold text-center">
            Register
          </CardTitle>
          <CardDescription class="text-center">
            Enter your information to create an account
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit="handleSubmit" class="space-y-4">
            <!-- General Error -->
            <div v-if="formErrors.general" class="rounded-md bg-destructive/10 border border-destructive/20 p-3">
              <p class="text-sm text-destructive font-medium">{{ formErrors.general }}</p>
            </div>

            <!-- Name Field -->
            <div class="space-y-2">
              <label for="name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Full Name
              </label>
              <div class="relative">
                <User class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="name"
                  v-model="name"
                  type="text"
                  placeholder="John Doe"
                  :error="!!formErrors.name"
                  class="pl-10"
                  :disabled="authStore.loading"
                />
              </div>
              <p v-if="formErrors.name" class="text-sm text-destructive">{{ formErrors.name }}</p>
            </div>

            <!-- Email Field -->
            <div class="space-y-2">
              <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Email
              </label>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="email"
                  v-model="email"
                  type="email"
                  placeholder="name@example.com"
                  :error="!!formErrors.email"
                  class="pl-10"
                  :disabled="authStore.loading"
                />
              </div>
              <p v-if="formErrors.email" class="text-sm text-destructive">{{ formErrors.email }}</p>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
              <label for="password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Password
              </label>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter your password"
                  :error="!!formErrors.password"
                  class="pl-10 pr-10"
                  :disabled="authStore.loading"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  tabindex="-1"
                >
                  <svg
                    v-if="!showPassword"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    />
                  </svg>
                </button>
              </div>
              <p v-if="formErrors.password" class="text-sm text-destructive">{{ formErrors.password }}</p>
            </div>

            <!-- Password Confirmation Field -->
            <div class="space-y-2">
              <label for="password_confirmation" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Confirm Password
              </label>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="password_confirmation"
                  v-model="passwordConfirmation"
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  placeholder="Confirm your password"
                  :error="!!formErrors.password_confirmation"
                  class="pl-10 pr-10"
                  :disabled="authStore.loading"
                />
                <button
                  type="button"
                  @click="showPasswordConfirmation = !showPasswordConfirmation"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  tabindex="-1"
                >
                  <svg
                    v-if="!showPasswordConfirmation"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    />
                  </svg>
                </button>
              </div>
              <p v-if="formErrors.password_confirmation" class="text-sm text-destructive">{{ formErrors.password_confirmation }}</p>
            </div>

            <!-- Submit Button -->
            <Button
              type="submit"
              class="w-full"
              :disabled="authStore.loading"
            >
              <Loader2 v-if="authStore.loading" class="mr-2 h-4 w-4 animate-spin" />
              <span v-if="authStore.loading">Creating account...</span>
              <span v-else>Create Account</span>
            </Button>

            <!-- Link to Login -->
            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
              <span>Already have an account? </span>
              <router-link
                to="/login"
                class="text-primary hover:underline font-medium"
              >
                Sign in
              </router-link>
            </div>
          </form>
        </CardContent>
      </Card>

    </div>
  </div>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
