import { defineStore } from 'pinia'
import { ref } from 'vue'
import { employeeApi } from '@/services/employeeApi'

export interface Employee {
  id: string
  firstName: string
  lastName: string
  fullName: string
  email: string
  position: string
  department: string
  status: string
}

export const useEmployeeStore = defineStore('employee', () => {
  const employees = ref<Employee[]>([])
  const currentEmployee = ref<Employee | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref<any>({
    total: 0,
    currentPage: 1,
    perPage: 15,
    totalPages: 1
  })

  async function fetchEmployees(filters = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await employeeApi.getEmployees(filters)
      employees.value = response.data
      
      // Update pagination info from response meta
      if (response.meta) {
        pagination.value = {
          total: response.meta.total || employees.value.length,
          currentPage: response.meta.current_page || 1,
          perPage: response.meta.per_page || 15,
          totalPages: response.meta.last_page || 1
        }
      } else {
        // Fallback if no meta
        pagination.value.total = employees.value.length
      }
      
      return true
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'Failed to fetch employees'
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchEmployee(id: string) {
    loading.value = true
    error.value = null

    try {
      const response = await employeeApi.getEmployee(id)
      currentEmployee.value = response.data
      return true
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'Failed to fetch employee'
      return false
    } finally {
      loading.value = false
    }
  }

  async function createEmployee(data: any) {
    loading.value = true
    error.value = null

    try {
      await employeeApi.createEmployee(data)
      await fetchEmployees()
      return true
    } catch (err: any) {
      error.value = err.response?.data?.error?.message || 'Failed to create employee'
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    employees,
    currentEmployee,
    loading,
    error,
    pagination,
    fetchEmployees,
    fetchEmployee,
    createEmployee,
  }
})

