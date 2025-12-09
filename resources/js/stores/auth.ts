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

export interface OriginalUser {
  id: number
  email: string
  role: 'Admin' | 'Employee'
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)
  const isImpersonating = ref(false)
  const originalUser = ref<OriginalUser | null>(null)

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
        isImpersonating.value = response.data.data.isImpersonating || false
        originalUser.value = response.data.data.originalUser || null
        
        if (token.value) {
          localStorage.setItem('token', token.value)
        }
        
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
      isImpersonating.value = false
      originalUser.value = null
      localStorage.removeItem('token')
    }
  }

  async function fetchUser() {
    if (!token.value) return

    try {
      const response = await api.get('/auth/me')
      
      if (response.data.success) {
        user.value = response.data.data
        isImpersonating.value = response.data.data.isImpersonating || false
        originalUser.value = response.data.data.originalUser || null
      }
    } catch (err) {
      // Token invalid, logout
      await logout()
    }
  }

  async function impersonate(employeeId: string) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post(`/auth/impersonate/${employeeId}`)
      
      if (response.data.success) {
        token.value = response.data.data.token
        user.value = response.data.data.user
        isImpersonating.value = response.data.data.isImpersonating || false
        originalUser.value = response.data.data.originalUser || null
        
        if (token.value) {
          localStorage.setItem('token', token.value)
        }
        
        return true
      }
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'Impersonation failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function stopImpersonating() {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/auth/stop-impersonating')
      
      if (response.data.success) {
        token.value = response.data.data.token
        user.value = response.data.data.user
        isImpersonating.value = false
        originalUser.value = null
        
        if (token.value) {
          localStorage.setItem('token', token.value)
        }
        
        return true
      }
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'Stop impersonation failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function refreshToken() {
    try {
      const response = await api.post('/auth/refresh')
      
      if (response.data.success) {
        token.value = response.data.data.token
        if (token.value) {
          localStorage.setItem('token', token.value)
        }
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
    isImpersonating,
    originalUser,
    login,
    logout,
    fetchUser,
    refreshToken,
    impersonate,
    stopImpersonating,
  }
})

