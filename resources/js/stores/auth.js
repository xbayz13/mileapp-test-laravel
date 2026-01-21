import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '../services/authService'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  // Initialize from localStorage
  const initAuth = () => {
    const storedToken = authService.getToken()
    const storedUser = authService.getUser()
    
    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = storedUser
    }
  }

  const login = async (email, password) => {
    loading.value = true
    error.value = null
    
    try {
      const { token: authToken, user: authUser } = await authService.login(email, password)
      token.value = authToken
      user.value = authUser
      return { success: true }
    } catch (err) {
      // Extract error message from various error formats
      let errorMessage = 'Login failed. Please try again.'
      
      if (err.response?.data) {
        // Laravel validation errors
        if (err.response.data.errors) {
          const errorFields = Object.keys(err.response.data.errors)
          errorMessage = err.response.data.errors[errorFields[0]][0] || err.response.data.message || errorMessage
        } else {
          errorMessage = err.response.data.message || errorMessage
        }
      } else if (err.message) {
        errorMessage = err.message
      }
      
      error.value = errorMessage
      console.error('Login error:', err)
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    try {
      await authService.logout()
      token.value = null
      user.value = null
      router.push('/login')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchUser = async () => {
    try {
      const userData = await authService.me()
      user.value = userData
      return userData
    } catch (err) {
      console.error('Failed to fetch user:', err)
      throw err
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    initAuth,
    login,
    logout,
    fetchUser,
  }
})
