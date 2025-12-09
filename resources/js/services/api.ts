import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

export const api = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - add auth token
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }
    
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor - handle errors
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const authStore = useAuthStore()
    
    // Handle 401 - unauthorized
    // Skip logout if the failed request was already a logout request (prevent infinite loop)
    if (error.response?.status === 401 && !error.config.url?.includes('/auth/logout')) {
      // Clear local state without making API call
      authStore.token = null
      authStore.user = null
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    
    // Handle 403 - forbidden
    if (error.response?.status === 403) {
      console.error('Forbidden:', error.response.data)
    }
    
    return Promise.reject(error)
  }
)

export default api

