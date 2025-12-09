<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('equipment.list') }}</h1>
          <button
            v-if="authStore.isAdmin"
            @click="showAddEquipmentModal = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
          >
            {{ $t('equipment.add') }}
          </button>
        </div>
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <!-- Filters -->
            <div class="mb-4 flex gap-4">
              <select
                v-model="statusFilter"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="fetchEquipment"
              >
                <option value="">{{ $t('equipment.allStatus') }}</option>
                <option value="Available">{{ $t('equipment.available') }}</option>
                <option value="Assigned">{{ $t('equipment.assigned') }}</option>
                <option value="InMaintenance">{{ $t('equipment.inMaintenance') }}</option>
                <option value="Decommissioned">{{ $t('equipment.decommissioned') }}</option>
              </select>
              <select
                v-model="typeFilter"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @change="fetchEquipment"
              >
                <option value="">{{ $t('equipment.allTypes') }}</option>
                <option value="Laptop">{{ $t('equipment.laptop') }}</option>
                <option value="Desktop">{{ $t('equipment.desktop') }}</option>
                <option value="Monitor">{{ $t('equipment.monitor') }}</option>
                <option value="Phone">{{ $t('equipment.phone') }}</option>
                <option value="Keyboard">{{ $t('equipment.keyboard') }}</option>
                <option value="Mouse">{{ $t('equipment.mouse') }}</option>
              </select>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Equipment Grid -->
            <div v-else-if="equipment.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="item in equipment"
                :key="item.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition"
              >
                <div class="flex justify-between items-start mb-3">
                  <div>
                    <h3 class="font-semibold text-gray-900">{{ item.brand }} {{ item.model }}</h3>
                    <p class="text-sm text-gray-500">{{ item.type }}</p>
                  </div>
                  <span
                    :class="{
                      'bg-green-100 text-green-800': item.status === 'Available',
                      'bg-blue-100 text-blue-800': item.status === 'Assigned',
                      'bg-yellow-100 text-yellow-800': item.status === 'InMaintenance',
                      'bg-gray-100 text-gray-800': item.status === 'Decommissioned',
                    }"
                    class="px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ $t(`equipment.status${item.status}`) }}
                  </span>
                </div>

                <div class="space-y-1 text-sm">
                  <p class="text-gray-600">
                    <span class="font-medium">{{ $t('equipment.assetTag') }}:</span> {{ item.assetTag }}
                  </p>
                  <p class="text-gray-600">
                    <span class="font-medium">{{ $t('equipment.serialNumber') }}:</span> {{ item.serialNumber }}
                  </p>
                  <p v-if="item.currentAssignee" class="text-gray-600">
                    <span class="font-medium">{{ $t('equipment.assignedTo') }}:</span> {{ item.currentAssignee.name }}
                  </p>
                  <p class="text-gray-600">
                    <span class="font-medium">{{ $t('equipment.condition') }}:</span> {{ item.condition }}
                  </p>
                </div>

                <div v-if="authStore.isAdmin" class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                  <button
                    v-if="item.status === 'Available'"
                    @click="openIssueModal(item)"
                    class="text-sm text-indigo-600 hover:text-indigo-900 font-medium"
                  >
                    {{ $t('equipment.issue') }}
                  </button>
                  <button
                    v-if="item.status === 'Assigned'"
                    @click="returnEquipment(item.id)"
                    class="text-sm text-indigo-600 hover:text-indigo-900 font-medium"
                  >
                    {{ $t('equipment.return') }}
                  </button>
                  <button
                    v-if="item.status === 'Available' || item.status === 'Assigned'"
                    @click="openMaintenanceModal(item)"
                    class="text-sm text-yellow-600 hover:text-yellow-900 font-medium"
                  >
                    {{ $t('equipment.maintenance') }}
                  </button>
                  <button
                    v-if="item.status === 'InMaintenance'"
                    @click="openCompleteMaintenanceModal(item)"
                    class="text-sm text-green-600 hover:text-green-900 font-medium"
                  >
                    {{ $t('equipment.completeMaintenance') }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <p class="text-gray-500">{{ $t('equipment.noEquipment') }}</p>
            </div>
          </div>
        </div>

        <!-- Add Equipment Modal -->
        <Modal v-model="showAddEquipmentModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('equipment.add') }}</h3>
          <form @submit.prevent="addEquipment" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.type') }}</label>
                <select 
                  v-model="addEquipmentForm.type" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">{{ $t('common.select') }}</option>
                  <option>Laptop</option>
                  <option>Desktop</option>
                  <option>Monitor</option>
                  <option>Phone</option>
                  <option>Keyboard</option>
                  <option>Mouse</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.brand') }}</label>
                <input 
                  v-model="addEquipmentForm.brand" 
                  type="text" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.model') }}</label>
              <input 
                v-model="addEquipmentForm.model" 
                type="text" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.serialNumber') }}</label>
                <input 
                  v-model="addEquipmentForm.serialNumber" 
                  type="text" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.assetTag') }}</label>
                <input 
                  v-model="addEquipmentForm.assetTag" 
                  type="text" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.purchaseDate') }}</label>
                <input 
                  v-model="addEquipmentForm.purchaseDate" 
                  type="date" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.purchaseCost') }} (USD)</label>
                <input 
                  v-model.number="addEquipmentForm.purchaseCost" 
                  type="number" 
                  required 
                  min="0"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.condition') }}</label>
              <select 
                v-model="addEquipmentForm.condition" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option>New</option>
                <option>Good</option>
                <option>Fair</option>
                <option>Poor</option>
              </select>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="addEquipmentSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ addEquipmentSubmitting ? $t('common.adding') : $t('common.add') }}
              </button>
              <button 
                type="button"
                @click="showAddEquipmentModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Issue Equipment Modal -->
        <Modal v-model="showIssueModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('equipment.issueEquipment') }}</h3>
          <div v-if="selectedEquipment" class="mb-4 p-3 bg-gray-50 rounded">
            <p class="text-sm font-medium text-gray-700">
              {{ selectedEquipment.brand }} {{ selectedEquipment.model }}
            </p>
            <p class="text-xs text-gray-500">{{ selectedEquipment.assetTag }}</p>
          </div>
          <form @submit.prevent="issueEquipment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.assignToEmployee') }}</label>
              <select 
                v-model="issueForm.employeeId" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">{{ $t('equipment.selectEmployee') }}</option>
                <option 
                  v-for="employee in availableEmployees" 
                  :key="employee.id" 
                  :value="employee.id"
                >
                  {{ employee.firstName }} {{ employee.lastName }} - {{ employee.position }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('equipment.accessories') }}</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" value="Charger" v-model="issueForm.accessories" class="rounded mr-2">
                  Charger
                </label>
                <label class="flex items-center">
                  <input type="checkbox" value="Cable" v-model="issueForm.accessories" class="rounded mr-2">
                  Cable
                </label>
                <label class="flex items-center">
                  <input type="checkbox" value="Adapter" v-model="issueForm.accessories" class="rounded mr-2">
                  Adapter
                </label>
                <label class="flex items-center">
                  <input type="checkbox" value="Case" v-model="issueForm.accessories" class="rounded mr-2">
                  Case
                </label>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
              <textarea 
                v-model="issueForm.notes" 
                rows="2"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Any additional notes..."
              ></textarea>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="issueSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ issueSubmitting ? $t('equipment.issuing') : $t('equipment.issueEquipment') }}
              </button>
              <button 
                type="button"
                @click="showIssueModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Maintenance Modal -->
        <Modal v-model="showMaintenanceModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('equipment.sendToMaintenance') }}</h3>
          <div v-if="selectedEquipment" class="mb-4 p-3 bg-gray-50 rounded">
            <p class="text-sm font-medium text-gray-700">
              {{ selectedEquipment.brand }} {{ selectedEquipment.model }}
            </p>
            <p class="text-xs text-gray-500">{{ selectedEquipment.assetTag }}</p>
          </div>
          <form @submit.prevent="sendToMaintenance" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.reasonForMaintenance') }}</label>
              <textarea 
                v-model="maintenanceForm.reason" 
                required
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :placeholder="$t('equipment.describeIssue')"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.expectedReturnDate') }}</label>
              <input 
                v-model="maintenanceForm.expectedReturnDate" 
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="maintenanceSubmitting"
                class="flex-1 bg-yellow-600 text-white py-2 rounded-md hover:bg-yellow-700 disabled:opacity-50"
              >
                {{ maintenanceSubmitting ? $t('equipment.sending') : $t('equipment.sendToMaintenance') }}
              </button>
              <button 
                type="button"
                @click="showMaintenanceModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                {{ $t('common.cancel') }}
              </button>
            </div>
          </form>
        </Modal>

        <!-- Complete Maintenance Modal -->
        <Modal v-model="showCompleteMaintenanceModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('equipment.completeMaintenance') }}</h3>
          <div v-if="selectedEquipment" class="mb-4 p-3 bg-gray-50 rounded">
            <p class="text-sm font-medium text-gray-700">
              {{ selectedEquipment.brand }} {{ selectedEquipment.model }}
            </p>
            <p class="text-xs text-gray-500">{{ selectedEquipment.assetTag }}</p>
          </div>
          <form @submit.prevent="completeMaintenance" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.completedDate') }}</label>
              <input 
                v-model="completeMaintenanceForm.completedDate" 
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.actualCost') }} (USD)</label>
              <input 
                v-model.number="completeMaintenanceForm.actualCost" 
                type="number"
                min="0"
                step="0.01"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('equipment.workPerformed') }}</label>
              <textarea 
                v-model="completeMaintenanceForm.workPerformed" 
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :placeholder="$t('equipment.workPerformedPlaceholder')"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">{{ $t('equipment.partsReplaced') }}</label>
              <div class="space-y-2">
                <div v-for="(part, index) in completeMaintenanceForm.partsReplaced" :key="index" class="flex gap-2">
                  <input 
                    v-model="completeMaintenanceForm.partsReplaced[index]" 
                    type="text"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :placeholder="$t('equipment.partName')"
                  />
                  <button 
                    type="button"
                    @click="completeMaintenanceForm.partsReplaced.splice(index, 1)"
                    class="px-3 py-1 text-red-600 hover:text-red-900"
                  >
                    {{ $t('common.delete') }}
                  </button>
                </div>
                <button 
                  type="button"
                  @click="completeMaintenanceForm.partsReplaced.push('')"
                  class="text-sm text-indigo-600 hover:text-indigo-900"
                >
                  + {{ $t('equipment.addPart') }}
                </button>
              </div>
            </div>
            <div>
              <label class="flex items-center">
                <input 
                  type="checkbox" 
                  v-model="completeMaintenanceForm.warrantyWork" 
                  class="rounded mr-2"
                >
                <span class="text-sm text-gray-700">{{ $t('equipment.warrantyWork') }}</span>
              </label>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="completeMaintenanceSubmitting"
                class="flex-1 bg-green-600 text-white py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
              >
                {{ completeMaintenanceSubmitting ? $t('equipment.completing') : $t('equipment.completeMaintenance') }}
              </button>
              <button 
                type="button"
                @click="showCompleteMaintenanceModal = false"
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
const equipment = ref<any[]>([])
const loading = ref(false)
const statusFilter = ref('')
const typeFilter = ref('')

// Add Equipment Modal
const showAddEquipmentModal = ref(false)
const addEquipmentSubmitting = ref(false)
const addEquipmentForm = ref({
  type: '',
  brand: '',
  model: '',
  serialNumber: '',
  assetTag: '',
  purchaseDate: new Date().toISOString().split('T')[0],
  purchaseCost: 0,
  condition: 'New'
})

async function addEquipment() {
  addEquipmentSubmitting.value = true
  try {
    await api.post('/equipment', {
      equipment_type: addEquipmentForm.value.type,
      brand: addEquipmentForm.value.brand,
      model: addEquipmentForm.value.model,
      serial_number: addEquipmentForm.value.serialNumber,
      asset_tag: addEquipmentForm.value.assetTag,
      purchase_date: addEquipmentForm.value.purchaseDate,
      purchase_cost: addEquipmentForm.value.purchaseCost,
      condition: addEquipmentForm.value.condition,
      status: 'Available',
      location: 'Office'
    })
    showAddEquipmentModal.value = false
    // Reset form
    addEquipmentForm.value = {
      type: '',
      brand: '',
      model: '',
      serialNumber: '',
      assetTag: '',
      purchaseDate: new Date().toISOString().split('T')[0],
      purchaseCost: 0,
      condition: 'New'
    }
    await fetchEquipment()
    showToast({ message: t('equipment.added'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to add equipment:', error)
    showToast({ message: error.response?.data?.message || t('equipment.addFailed'), type: 'error' })
  } finally {
    addEquipmentSubmitting.value = false
  }
}

// Issue Equipment Modal
const showIssueModal = ref(false)
const selectedEquipment = ref<any>(null)
const issueSubmitting = ref(false)
const availableEmployees = ref<any[]>([])
const issueForm = ref({
  employeeId: '',
  accessories: [] as string[],
  notes: ''
})

// Maintenance Modal
const showMaintenanceModal = ref(false)
const maintenanceSubmitting = ref(false)
const maintenanceForm = ref({
  reason: '',
  expectedReturnDate: ''
})

// Complete Maintenance Modal
const showCompleteMaintenanceModal = ref(false)
const completeMaintenanceSubmitting = ref(false)
const completeMaintenanceForm = ref({
  completedDate: new Date().toISOString().split('T')[0],
  actualCost: null as number | null,
  workPerformed: '',
  partsReplaced: [] as string[],
  warrantyWork: false
})

async function fetchEquipment() {
  loading.value = true
  try {
    const params: any = {}
    if (statusFilter.value) params['filter[status]'] = statusFilter.value
    if (typeFilter.value) params['filter[equipment_type]'] = typeFilter.value
    
    const response = await api.get('/equipment', { params })
    equipment.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch equipment:', error)
  } finally {
    loading.value = false
  }
}

async function openIssueModal(item: any) {
  selectedEquipment.value = item
  showIssueModal.value = true
  // Load available employees
  try {
    const response = await api.get('/employees', { 
      params: { 
        'filter[employment_status]': 'Active',
        per_page: 1000
      } 
    })
    availableEmployees.value = response.data.data || []
    console.log('Available employees:', availableEmployees.value)
  } catch (error) {
    console.error('Failed to fetch employees:', error)
    showToast({ message: t('equipment.failedToLoadEmployees'), type: 'error' })
  }
}

async function issueEquipment() {
  issueSubmitting.value = true
  try {
    await api.post(`/equipment/${selectedEquipment.value.id}/issue`, {
      employeeId: issueForm.value.employeeId,
      accessories: issueForm.value.accessories,
      notes: issueForm.value.notes
    })
    showIssueModal.value = false
    // Reset form
    issueForm.value = {
      employeeId: '',
      accessories: [],
      notes: ''
    }
    selectedEquipment.value = null
    // Reload equipment
    await fetchEquipment()
    showToast({ message: t('equipment.issued'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to issue equipment:', error)
    showToast({ message: error.response?.data?.message || t('equipment.issueFailed'), type: 'error' })
  } finally {
    issueSubmitting.value = false
  }
}

async function returnEquipment(equipmentId: string) {
  if (!confirm('Are you sure you want to return this equipment?')) {
    return
  }
  
  try {
    await api.post(`/equipment/${equipmentId}/return`, {
      condition: 'Good',
      notes: ''
    })
    await fetchEquipment()
    showToast({ message: t('equipment.returned'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to return equipment:', error)
    showToast({ message: error.response?.data?.message || t('equipment.returnFailed'), type: 'error' })
  }
}

function openMaintenanceModal(item: any) {
  selectedEquipment.value = item
  showMaintenanceModal.value = true
}

async function sendToMaintenance() {
  maintenanceSubmitting.value = true
  try {
    await api.post(`/equipment/${selectedEquipment.value.id}/maintenance`, {
      reason: maintenanceForm.value.reason,
      expected_return_date: maintenanceForm.value.expectedReturnDate
    })
    showMaintenanceModal.value = false
    // Reset form
    maintenanceForm.value = {
      reason: '',
      expectedReturnDate: ''
    }
    selectedEquipment.value = null
    // Reload equipment
    await fetchEquipment()
    showToast({ message: t('equipment.sentToMaintenance'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to send to maintenance:', error)
    showToast({ message: error.response?.data?.message || t('equipment.maintenanceFailed'), type: 'error' })
  } finally {
    maintenanceSubmitting.value = false
  }
}

function openCompleteMaintenanceModal(item: any) {
  selectedEquipment.value = item
  showCompleteMaintenanceModal.value = true
  // Reset form
  completeMaintenanceForm.value = {
    completedDate: new Date().toISOString().split('T')[0],
    actualCost: null,
    workPerformed: '',
    partsReplaced: [],
    warrantyWork: false
  }
}

async function completeMaintenance() {
  completeMaintenanceSubmitting.value = true
  try {
    await api.post(`/equipment/${selectedEquipment.value.id}/maintenance/complete`, {
      completedDate: completeMaintenanceForm.value.completedDate,
      actualCost: completeMaintenanceForm.value.actualCost,
      workPerformed: completeMaintenanceForm.value.workPerformed,
      partsReplaced: completeMaintenanceForm.value.partsReplaced.filter(p => p.trim() !== ''),
      warrantyWork: completeMaintenanceForm.value.warrantyWork
    })
    showCompleteMaintenanceModal.value = false
    selectedEquipment.value = null
    // Reload equipment
    await fetchEquipment()
    showToast({ message: t('equipment.maintenanceCompleted'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to complete maintenance:', error)
    showToast({ message: error.response?.data?.message || t('equipment.completeMaintenanceFailed'), type: 'error' })
  } finally {
    completeMaintenanceSubmitting.value = false
  }
}

onMounted(() => {
  fetchEquipment()
})
</script>
