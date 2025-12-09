<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('employee.list') }}</h1>
          <button
            v-if="authStore.isAdmin"
            @click="showAddModal = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
          >
            {{ $t('employee.add') }}
          </button>
        </div>
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <!-- Search and Filters -->
            <div class="mb-4 flex gap-4">
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="$t('employee.searchPlaceholder')"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @input="debouncedSearch"
              />
              <select
                v-model="statusFilter"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="handleStatusFilterChange"
              >
                <option value="">{{ $t('employee.allStatuses') }}</option>
                <option value="Active">{{ $t('employee.statusActive') }}</option>
                <option value="Terminated">{{ $t('employee.statusTerminated') }}</option>
                <option value="OnLeave">{{ $t('employee.statusOnLeave') }}</option>
              </select>
            </div>

            <!-- Loading State -->
            <div v-if="employeeStore.loading" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
              <p class="mt-2 text-gray-500">{{ $t('common.loading') }}</p>
            </div>

            <!-- Error State -->
            <div v-else-if="employeeStore.error" class="text-center py-12">
              <p class="text-red-600">{{ employeeStore.error }}</p>
              <button
                @click="fetchEmployees"
                class="mt-4 text-indigo-600 hover:text-indigo-900"
              >
                Try again
              </button>
            </div>

            <!-- Employee Table -->
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ $t('employee.fullName') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ $t('employee.email') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ $t('employee.position') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ $t('employee.department') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ $t('employee.status') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ $t('common.actions') }}
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="employee in employeeStore.employees" :key="employee.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ employee.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-2">
                        <Avatar :name="employee.fullName" size="sm" />
                        <router-link 
                          :to="`/profile/${employee.id}`"
                          class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                        >
                          {{ employee.fullName }}
                        </router-link>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ employee.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ employee.position }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ employee.department }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="{
                          'bg-green-100 text-green-800': employee.status === 'Active',
                          'bg-red-100 text-red-800': employee.status === 'Terminated',
                          'bg-yellow-100 text-yellow-800': employee.status === 'OnLeave',
                        }"
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      >
                        {{ $t(`employee.status${employee.status}`) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <router-link
                        :to="`/employees/${employee.id}`"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        {{ $t('common.view') }}
                      </router-link>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Empty State -->
              <div v-if="employeeStore.employees.length === 0" class="text-center py-12">
                <p class="text-gray-500">{{ $t('employee.noEmployees') }}</p>
              </div>

              <!-- Pagination -->
              <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex flex-1 justify-between sm:hidden">
                  <button
                    @click="prevPage"
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                  >
                    {{ $t('common.previous') }}
                  </button>
                  <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                  >
                    {{ $t('common.next') }}
                  </button>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      {{ $t('common.showing') }}
                      <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                      {{ $t('common.to') }}
                      <span class="font-medium">{{ Math.min(currentPage * perPage, total) }}</span>
                      {{ $t('common.of') }}
                      <span class="font-medium">{{ total }}</span>
                      {{ $t('common.results') }}
                    </p>
                  </div>
                  <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                      <button
                        @click="prevPage"
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                      >
                        ←
                      </button>
                      <button
                        v-for="page in visiblePages"
                        :key="page"
                        @click="goToPage(page)"
                        :class="[
                          page === currentPage
                            ? 'z-10 bg-indigo-600 text-white'
                            : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
                          'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                        ]"
                      >
                        {{ page }}
                      </button>
                      <button
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                      >
                        →
                      </button>
                    </nav>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Employee Modal -->
        <Modal v-model="showAddModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('employee.add') }}</h3>
          <form @submit.prevent="addEmployee" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.firstName') }}</label>
                <input 
                  v-model="addForm.firstName" 
                  type="text" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.lastName') }}</label>
                <input 
                  v-model="addForm.lastName" 
                  type="text" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.email') }}</label>
              <input 
                v-model="addForm.email" 
                type="email" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.phone') }}</label>
              <input 
                v-model="addForm.phone" 
                type="tel" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.position') }}</label>
                <select 
                  v-model="addForm.position" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">{{ $t('common.select') }}</option>
                  <option>Junior Developer</option>
                  <option>Developer</option>
                  <option>Senior Developer</option>
                  <option>Lead Developer</option>
                  <option>QA Engineer</option>
                  <option>Project Manager</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.department') }}</label>
                <select 
                  v-model="addForm.department" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">{{ $t('common.select') }}</option>
                  <option>Engineering</option>
                  <option>QA</option>
                  <option>Product</option>
                  <option>HR</option>
                  <option>Finance</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.location') }}</label>
                <input 
                  v-model="addForm.location" 
                  type="text" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.hireDate') }}</label>
                <input 
                  v-model="addForm.hireDate" 
                  type="date" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.salary') }} (USD)</label>
              <input 
                v-model.number="addForm.salary" 
                type="number" 
                required 
                min="0"
                step="1000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="addSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ addSubmitting ? $t('common.adding') : $t('common.add') }}
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
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useEmployeeStore } from '@/stores/employee'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/utils/toast'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import Avatar from '@/components/Avatar.vue'
import { api } from '@/services/api'

const employeeStore = useEmployeeStore()
const authStore = useAuthStore()
const { t } = useI18n()

const searchQuery = ref('')
const statusFilter = ref('')

// Pagination from store
const totalPages = computed(() => employeeStore.pagination.totalPages)
const currentPage = computed(() => employeeStore.pagination.currentPage)
const total = computed(() => employeeStore.pagination.total)
const perPage = computed(() => employeeStore.pagination.perPage)

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

// Add Employee Modal
const showAddModal = ref(false)
const addSubmitting = ref(false)
const addForm = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  position: '',
  department: '',
  location: 'Kyiv',
  hireDate: new Date().toISOString().split('T')[0],
  salary: 0
})

let debounceTimer: ReturnType<typeof setTimeout>

function debouncedSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchEmployees(1)
  }, 300)
}

function handleStatusFilterChange() {
  fetchEmployees(1)
}

async function fetchEmployees(page = 1) {
  const filters: any = {
    page: page,
    per_page: 15
  }
  
  if (searchQuery.value) {
    filters['filter[search]'] = searchQuery.value
  }
  
  if (statusFilter.value) {
    filters['filter[employment_status]'] = statusFilter.value
  }
  
  await employeeStore.fetchEmployees(filters)
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    fetchEmployees(currentPage.value + 1)
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    fetchEmployees(currentPage.value - 1)
  }
}

function goToPage(page: number) {
  fetchEmployees(page)
}

async function addEmployee() {
  addSubmitting.value = true
  try {
    await api.post('/employees', {
      first_name: addForm.value.firstName,
      last_name: addForm.value.lastName,
      email: addForm.value.email,
      phone: addForm.value.phone,
      position: addForm.value.position,
      department: addForm.value.department,
      location: addForm.value.location,
      hire_date: addForm.value.hireDate,
      salary_amount: addForm.value.salary,
      salary_currency: 'USD',
      salary_frequency: 'Monthly'
    })
    showAddModal.value = false
    // Reset form
    addForm.value = {
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      position: '',
      department: '',
      location: 'Kyiv',
      hireDate: new Date().toISOString().split('T')[0],
      salary: 0
    }
    await fetchEmployees()
    showToast({ message: t('employee.employeeAdded'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to add employee:', error)
    showToast({ message: error.response?.data?.message || t('employee.addEmployeeFailed'), type: 'error' })
  } finally {
    addSubmitting.value = false
  }
}

onMounted(() => {
  fetchEmployees(1)
})
</script>
