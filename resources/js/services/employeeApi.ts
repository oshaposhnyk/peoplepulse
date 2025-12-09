import { api } from './api'

export const employeeApi = {
  async getEmployees(params = {}) {
    const response = await api.get('/employees', { params })
    return response.data
  },

  async getEmployee(id: string) {
    const response = await api.get(`/employees/${id}`)
    return response.data
  },

  async createEmployee(data: any) {
    const response = await api.post('/employees', data)
    return response.data
  },

  async updateEmployee(id: string, data: any) {
    const response = await api.put(`/employees/${id}`, data)
    return response.data
  },

  async changePosition(id: string, data: any) {
    const response = await api.post(`/employees/${id}/position`, data)
    return response.data
  },

  async changeLocation(id: string, data: any) {
    const response = await api.post(`/employees/${id}/location`, data)
    return response.data
  },

  async terminate(id: string, data: any) {
    const response = await api.post(`/employees/${id}/terminate`, data)
    return response.data
  },

  async getHistory(id: string) {
    const response = await api.get(`/employees/${id}/history`)
    return response.data
  },
}

