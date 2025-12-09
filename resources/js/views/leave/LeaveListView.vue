<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('leave.list') }}</h1>
          <button 
            @click="showAddModal = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
          >
            {{ $t('leave.create') }}
          </button>
        </div>
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <!-- Filters -->
            <div class="mb-4 flex gap-4">
              <select
                v-model="statusFilter"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="fetchLeaves"
              >
                <option value="">{{ $t('leave.allStatus') }}</option>
                <option value="Pending">{{ $t('leave.pending') }}</option>
                <option value="Approved">{{ $t('leave.approved') }}</option>
                <option value="Rejected">{{ $t('leave.rejected') }}</option>
                <option value="Cancelled">{{ $t('leave.cancelled') }}</option>
              </select>
              <select
                v-model="typeFilter"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="fetchLeaves"
              >
                <option value="">{{ $t('leave.allTypes') }}</option>
                <option value="Vacation">{{ $t('leave.vacation') }}</option>
                <option value="Sick">{{ $t('leave.sick') }}</option>
                <option value="Personal">{{ $t('leave.personal') }}</option>
                <option value="Unpaid">{{ $t('leave.unpaid') }}</option>
              </select>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Leave Requests Table -->
            <div v-else-if="leaves.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('leave.employee') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('leave.type') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('leave.period') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('leave.days') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('leave.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('common.actions') }}</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="leave in leaves" :key="leave.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.employeeName }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ leave.leaveType }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ leave.startDate }} {{ $t('common.to') }} {{ leave.endDate }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ leave.totalDays }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="{
                          'bg-yellow-100 text-yellow-800': leave.status === 'Pending',
                          'bg-green-100 text-green-800': leave.status === 'Approved',
                          'bg-red-100 text-red-800': leave.status === 'Rejected',
                          'bg-gray-100 text-gray-800': leave.status === 'Cancelled',
                        }"
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                      >
                        {{ leave.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <button
                        v-if="leave.status === 'Pending' && authStore.isAdmin"
                        @click="approveLeave(leave.id)"
                        class="text-green-600 hover:text-green-900 mr-3"
                      >
                        {{ $t('leave.approve') }}
                      </button>
                      <button
                        v-if="leave.status === 'Pending'"
                        @click="cancelLeave(leave.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        {{ $t('common.cancel') }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <p class="text-gray-500">{{ $t('leave.noLeaves') }}</p>
            </div>
          </div>
        </div>

        <!-- Request Leave Modal -->
        <Modal v-model="showAddModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('leave.create') }}</h3>
          <form @submit.prevent="requestLeave" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.type') }}</label>
              <select 
                v-model="addForm.leaveType" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">{{ $t('common.select') }}</option>
                <option value="Vacation">Vacation</option>
                <option value="Sick">Sick Leave</option>
                <option value="Personal">Personal</option>
                <option value="Unpaid">Unpaid Leave</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.startDate') }}</label>
                <input 
                  v-model="addForm.startDate" 
                  type="date" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.endDate') }}</label>
                <input 
                  v-model="addForm.endDate" 
                  type="date" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.reason') }}</label>
              <textarea 
                v-model="addForm.reason" 
                required
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Enter reason for leave..."
              ></textarea>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="addSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ addSubmitting ? $t('common.submitting') : $t('common.submit') }}
              </button>
              <button 
                type="button"
                @click="showAddModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/utils/toast'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'

const authStore = useAuthStore()
const { t } = useI18n()
const leaves = ref<any[]>([])
const loading = ref(false)
const statusFilter = ref('')
const typeFilter = ref('')

// Request Leave Modal
const showAddModal = ref(false)
const addSubmitting = ref(false)
const addForm = ref({
  leaveType: '',
  startDate: '',
  endDate: '',
  reason: ''
})

async function fetchLeaves() {
  loading.value = true
  try {
    const params: any = {}
    if (statusFilter.value) params['filter[status]'] = statusFilter.value
    if (typeFilter.value) params['filter[leave_type]'] = typeFilter.value
    
    const response = await api.get('/leaves', { params })
    leaves.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch leaves:', error)
  } finally {
    loading.value = false
  }
}

async function requestLeave() {
  addSubmitting.value = true
  try {
    await api.post('/leaves', {
      leaveType: addForm.value.leaveType,
      startDate: addForm.value.startDate,
      endDate: addForm.value.endDate,
      reason: addForm.value.reason
    })
    showAddModal.value = false
    // Reset form
    addForm.value = {
      leaveType: '',
      startDate: '',
      endDate: '',
      reason: ''
    }
    await fetchLeaves()
    showToast({ message: t('leave.requestSubmitted'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to request leave:', error)
    showToast({ 
      message: error.response?.data?.message || t('leave.requestFailed'), 
      type: 'error' 
    })
  } finally {
    addSubmitting.value = false
  }
}

async function approveLeave(leaveId: string) {
  if (!confirm(t('leave.confirmApprove'))) {
    return
  }
  
  try {
    await api.post(`/leaves/${leaveId}/approve`, {})
    showToast({ message: t('leave.approved'), type: 'success' })
    // Reload leaves to update status
    await fetchLeaves()
  } catch (error: any) {
    console.error('Failed to approve leave:', error)
    showToast({ 
      message: error.response?.data?.message || t('leave.approveFailed'), 
      type: 'error' 
    })
  }
}

async function cancelLeave(leaveId: string) {
  if (!confirm(t('leave.confirmCancel'))) {
    return
  }
  
  try {
    await api.post(`/leaves/${leaveId}/cancel`, {})
    showToast({ message: t('leave.requestCancelled'), type: 'info' })
    // Reload leaves to update status
    await fetchLeaves()
  } catch (error: any) {
    console.error('Failed to cancel leave:', error)
    showToast({ 
      message: error.response?.data?.message || t('leave.cancelFailed'), 
      type: 'error' 
    })
  }
}

onMounted(() => {
  fetchLeaves()
})
</script>
