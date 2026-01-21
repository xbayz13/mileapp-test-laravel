import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000, // 10 seconds timeout
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Only redirect to login if it's NOT the login endpoint itself
    // and user has a token (meaning token expired/invalid)
    if (error.response?.status === 401) {
      const isLoginEndpoint = error.config?.url?.includes('/login')
      const hasToken = localStorage.getItem('auth_token')
      
      // Only redirect if user was previously authenticated and token is invalid
      // Don't redirect if login itself fails
      if (!isLoginEndpoint && hasToken) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        // Only redirect if we're not already on login page
        if (window.location.pathname !== '/login') {
          window.location.href = '/login'
        }
      }
    }
    return Promise.reject(error)
  }
)

export default api
