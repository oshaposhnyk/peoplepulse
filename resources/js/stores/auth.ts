import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/services/api'

export interface User {
  id: number
  email: string
  role: 'Admin' | 'Employee'
  employee_id?: number
  employee_id_string?: string
  employee: {
    id: string
    name: string
    position: string
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'Admin')

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/auth/login', { email, password })
      
      if (response.data.success) {
        token.value = response.data.data.token
        user.value = response.data.data.user
        
        localStorage.setItem('token', token.value)
        
        return true
      }
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'Login failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      // Only call logout API if we have a valid token
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      // Ignore logout errors (e.g., token already expired)
      console.error('Logout API error (ignored):', err)
    } finally {
      // Always clear local state
      token.value = null
      user.value = null
      localStorage.removeItem('token')
    }
  }

  async function fetchUser() {
    if (!token.value) return

    try {
      const response = await api.get('/auth/me')
      
      if (response.data.success) {
        user.value = response.data.data
      }
    } catch (err) {
      // Token invalid, logout
      await logout()
    }
  }

  async function refreshToken() {
    try {
      const response = await api.post('/auth/refresh')
      
      if (response.data.success) {
        token.value = response.data.data.token
        localStorage.setItem('token', token.value)
      }
    } catch (err) {
      await logout()
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    fetchUser,
    refreshToken,
  }
})

