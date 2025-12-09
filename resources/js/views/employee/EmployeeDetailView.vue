<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Back Button -->
        <button
          @click="$router.push('/employees')"
          class="mb-4 text-indigo-600 hover:text-indigo-900 flex items-center"
        >
          ← {{ $t('employee.backToList') }}
        </button>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $t('employee.details') }}</h1>
        
        <!-- Loading State -->
        <div v-if="employeeStore.loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-500">{{ $t('common.loading') }}</p>
        </div>

        <!-- Employee Details -->
        <div v-else-if="employee" class="bg-white shadow overflow-hidden sm:rounded-lg">
          <!-- Header -->
          <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ employee.fullName }}
              </h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">
                {{ employee.position }} • {{ employee.department }}
              </p>
            </div>
            <span
              :class="{
                'bg-green-100 text-green-800': employee.status === 'Active',
                'bg-red-100 text-red-800': employee.status === 'Terminated',
              }"
              class="px-3 py-1 text-sm font-semibold rounded-full"
            >
              {{ employee.status }}
            </span>
          </div>

          <!-- Details -->
          <div class="border-t border-gray-200">
            <dl>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ $t('profile.employeeId') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ employee.id }}</dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ $t('employee.email') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ employee.email }}</dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ $t('employee.phone') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ employee.phone }}</dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ $t('employee.location') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ employee.location }}</dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ $t('employee.hireDate') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ employee.hireDate }}</dd>
              </div>
              <div v-if="employee.salary && authStore.isAdmin" class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ $t('employee.salary') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  {{ employee.salary.currency }} {{ employee.salary.amount.toLocaleString() }} / {{ employee.salary.frequency }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Actions -->
          <div v-if="authStore.isAdmin" class="px-4 py-5 sm:px-6 bg-gray-50 flex gap-3">
            <button 
              @click="showEditModal = true"
              class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm"
            >
              {{ $t('common.edit') }}
            </button>
            <button 
              v-if="employee.status !== 'Terminated'"
              @click="showPositionModal = true"
              class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 text-sm"
            >
              {{ $t('employee.changePosition') }}
            </button>
            <button 
              v-if="employee.status !== 'Terminated'"
              @click="showTerminateModal = true"
              class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm"
            >
              {{ $t('employee.terminate') }}
            </button>
            <button 
              v-if="employee.status === 'Terminated'"
              @click="showReinstateModal = true"
              class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm"
            >
              {{ $t('employee.reinstate') }}
            </button>
          </div>
        </div>

        <!-- Error State -->
        <div v-else class="bg-white shadow sm:rounded-lg p-6">
          <p class="text-red-600">{{ employeeStore.error || 'Employee not found' }}</p>
        </div>

        <!-- Position Change Modal -->
        <Modal v-model="showPositionModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('employee.changePosition') }}</h3>
          <form @submit.prevent="changePosition" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.newPosition') }}</label>
              <select 
                v-model="positionForm.newPosition" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">{{ $t('common.select') }}</option>
                <option>Junior Developer</option>
                <option>Developer</option>
                <option>Senior Developer</option>
                <option>Lead Developer</option>
                <option>Junior QA</option>
                <option>QA Engineer</option>
                <option>Senior QA</option>
                <option>Project Manager</option>
                <option>Product Manager</option>
                <option>DevOps Engineer</option>
                <option>System Administrator</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.newSalary') }} (USD)</label>
              <input 
                v-model.number="positionForm.newSalary" 
                type="number" 
                required 
                min="0"
                step="1000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.effectiveDate') }}</label>
              <input 
                v-model="positionForm.effectiveDate" 
                type="date" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.reason') }}</label>
              <textarea 
                v-model="positionForm.reason" 
                required 
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :placeholder="$t('employee.reasonPlaceholder')"
              ></textarea>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="positionSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ positionSubmitting ? $t('employee.changing') : $t('employee.changePosition') }}
              </button>
              <button 
                type="button"
                @click="showPositionModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Terminate Modal -->
        <Modal v-model="showTerminateModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('employee.terminate') }}</h3>
          <form @submit.prevent="terminateEmployee" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.terminationDate') }}</label>
              <input 
                v-model="terminateForm.terminationDate" 
                type="date" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.lastWorkingDay') }}</label>
              <input 
                v-model="terminateForm.lastWorkingDay" 
                type="date" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.terminationType') }}</label>
              <select 
                v-model="terminateForm.terminationType" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">{{ $t('common.select') }}</option>
                <option value="Resignation">{{ $t('employee.terminationTypeResignation') }}</option>
                <option value="Termination">{{ $t('employee.terminationTypeTermination') }}</option>
                <option value="Retirement">{{ $t('employee.terminationTypeRetirement') }}</option>
                <option value="Contract End">{{ $t('employee.terminationTypeContractEnd') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.reason') }}</label>
              <textarea 
                v-model="terminateForm.reason" 
                required 
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :placeholder="$t('employee.terminationReasonPlaceholder')"
              ></textarea>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
              <p class="text-sm text-yellow-800">
                ⚠️ {{ $t('employee.terminationWarning') }}
              </p>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="terminateSubmitting"
                class="flex-1 bg-red-600 text-white py-2 rounded-md hover:bg-red-700 disabled:opacity-50"
              >
                {{ terminateSubmitting ? $t('employee.terminating') : $t('employee.terminate') }}
              </button>
              <button 
                type="button"
                @click="showTerminateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Reinstate Modal -->
        <Modal v-model="showReinstateModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('employee.reinstate') }}</h3>
          <form @submit.prevent="reinstateEmployee" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.reinstatementDate') }}</label>
              <input 
                v-model="reinstateForm.reinstatementDate" 
                type="date" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('leave.reason') }}</label>
              <textarea 
                v-model="reinstateForm.reason" 
                required 
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :placeholder="$t('employee.reinstatementReasonPlaceholder')"
              ></textarea>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="reinstateSubmitting"
                class="flex-1 bg-green-600 text-white py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
              >
                {{ reinstateSubmitting ? $t('employee.reinstating') : $t('employee.reinstate') }}
              </button>
              <button 
                type="button"
                @click="showReinstateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Edit Employee Modal -->
        <Modal v-model="showEditModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('employee.edit') }}</h3>
          <form @submit.prevent="updateEmployee" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.phone') }}</label>
              <input 
                v-model="editForm.phone" 
                type="tel" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.email') }}</label>
              <input 
                v-model="editForm.email" 
                type="email" 
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.location') }}</label>
              <input 
                v-model="editForm.location" 
                type="text" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="editSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ editSubmitting ? $t('common.saving') : $t('common.save') }}
              </button>
              <button 
                type="button"
                @click="showEditModal = false"
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
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useEmployeeStore } from '@/stores/employee'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/utils/toast'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import { api } from '@/services/api'

const route = useRoute()
const router = useRouter()
const employeeStore = useEmployeeStore()
const authStore = useAuthStore()
const { t } = useI18n()

const employee = computed(() => employeeStore.currentEmployee)

// Position Change Modal
const showPositionModal = ref(false)
const positionSubmitting = ref(false)
const positionForm = ref({
  newPosition: '',
  newSalary: 0,
  effectiveDate: '',
  reason: ''
})

async function changePosition() {
  positionSubmitting.value = true
  try {
    const employeeId = route.params.id as string
    await api.post(`/employees/${employeeId}/position`, positionForm.value)
    showPositionModal.value = false
    // Reload employee data
    await employeeStore.fetchEmployee(employeeId)
    // Reset form
    positionForm.value = {
      newPosition: '',
      newSalary: 0,
      effectiveDate: '',
      reason: ''
    }
    showToast({ message: t('employee.positionChanged'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to change position:', error)
    showToast({ message: error.response?.data?.message || t('employee.positionChangeFailed'), type: 'error' })
  } finally {
    positionSubmitting.value = false
  }
}

// Edit Modal
const showEditModal = ref(false)
const editSubmitting = ref(false)
const editForm = ref({
  email: '',
  phone: '',
  location: ''
})

// Terminate Modal
const showTerminateModal = ref(false)
const terminateSubmitting = ref(false)
const terminateForm = ref({
  terminationDate: '',
  lastWorkingDay: '',
  terminationType: '',
  reason: ''
})

// Reinstate Modal
const showReinstateModal = ref(false)
const reinstateSubmitting = ref(false)
const reinstateForm = ref({
  reinstatementDate: '',
  reason: ''
})

async function updateEmployee() {
  editSubmitting.value = true
  try {
    const employeeId = route.params.id as string
    await api.put(`/employees/${employeeId}`, editForm.value)
    showEditModal.value = false
    // Reload employee data
    await employeeStore.fetchEmployee(employeeId)
    showToast({ message: t('employee.updated'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to update employee:', error)
    showToast({ message: error.response?.data?.message || t('employee.updateFailed'), type: 'error' })
  } finally {
    editSubmitting.value = false
  }
}

async function terminateEmployee() {
  terminateSubmitting.value = true
  try {
    const employeeId = route.params.id as string
    await api.post(`/employees/${employeeId}/terminate`, terminateForm.value)
    showTerminateModal.value = false
    showToast({ message: t('employee.terminatedSuccess'), type: 'success' })
    // Redirect back to employee list
    setTimeout(() => router.push('/employees'), 1000)
  } catch (error: any) {
    console.error('Failed to terminate employee:', error)
    showToast({ message: error.response?.data?.message || t('employee.terminateFailed'), type: 'error' })
  } finally {
    terminateSubmitting.value = false
  }
}

async function reinstateEmployee() {
  reinstateSubmitting.value = true
  try {
    const employeeId = route.params.id as string
    await api.post(`/employees/${employeeId}/reinstate`, reinstateForm.value)
    showReinstateModal.value = false
    // Reload employee data
    await employeeStore.fetchEmployee(employeeId)
    showToast({ message: t('employee.reinstated'), type: 'success' })
    // Reset form
    reinstateForm.value = {
      reinstatementDate: '',
      reason: ''
    }
  } catch (error: any) {
    console.error('Failed to reinstate employee:', error)
    showToast({ message: error.response?.data?.message || t('employee.reinstateFailed'), type: 'error' })
  } finally {
    reinstateSubmitting.value = false
  }
}

onMounted(async () => {
  const employeeId = route.params.id as string
  await employeeStore.fetchEmployee(employeeId)
  
  // Populate edit form
  if (employee.value) {
    editForm.value = {
      email: employee.value.email || '',
      phone: employee.value.phone || '',
      location: employee.value.location || ''
    }
  }
})
</script>
