import { ref } from 'vue'

export function useApi<T = any>(apiCall: (...args: any[]) => Promise<T>) {
  const data = ref<T | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function execute(...args: any[]): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      data.value = await apiCall(...args)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'An error occurred'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    data,
    loading,
    error,
    execute,
  }
}

