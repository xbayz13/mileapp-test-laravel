import api from './api'

export const authService = {
  async login(email, password) {
    try {
      const response = await api.post('/login', {
        email,
        password,
      })
      
      if (response.data.success && response.data.data) {
        const { token, user } = response.data.data
        localStorage.setItem('auth_token', token)
        localStorage.setItem('auth_user', JSON.stringify(user))
        return { token, user }
      }
      
      throw new Error(response.data.message || 'Login failed')
    } catch (error) {
      console.error('Auth service error:', error)
      
      if (error.response) {
        // Server responded with error status
        const responseData = error.response.data
        
        // Handle validation errors (422)
        if (error.response.status === 422 && responseData.errors) {
          const firstError = Object.values(responseData.errors)[0]
          const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
          throw new Error(errorMessage || responseData.message || 'Validation failed')
        }
        
        // Handle other errors
        const errorMessage = responseData?.message || 'Login failed'
        throw new Error(errorMessage)
      } else if (error.request) {
        // Request was made but no response received
        console.error('No response received:', error.request)
        throw new Error('Network error. Please check your connection and try again.')
      } else {
        // Something else happened
        throw error
      }
    }
  },

  async logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  },

  async me() {
    const response = await api.get('/me')
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error('Failed to get user data')
  },

  async refreshToken() {
    const response = await api.post('/refresh')
    if (response.data.success && response.data.data) {
      const { token } = response.data.data
      localStorage.setItem('auth_token', token)
      return token
    }
    throw new Error('Failed to refresh token')
  },

  getToken() {
    return localStorage.getItem('auth_token')
  },

  getUser() {
    const userStr = localStorage.getItem('auth_user')
    return userStr ? JSON.parse(userStr) : null
  },

  isAuthenticated() {
    return !!this.getToken()
  },
}
