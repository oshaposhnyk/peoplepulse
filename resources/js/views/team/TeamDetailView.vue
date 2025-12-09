<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <button
          @click="$router.push('/teams')"
          class="mb-4 text-indigo-600 hover:text-indigo-900 flex items-center"
        >
          ← {{ $t('team.backToList') }}
        </button>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $t('team.details') }}</h1>
        
        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Team Details -->
        <div v-else-if="team" class="space-y-6">
          <!-- Team Info Card -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900">{{ team.name }}</h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ team.description }}</p>
            </div>
            <div class="border-t border-gray-200">
              <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">{{ $t('team.teamId') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ team.id }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">{{ $t('team.type') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ team.type }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.department') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ team.department }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">{{ $t('team.memberCount') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ team.memberCount || 0 }}</dd>
                </div>
                <div v-if="team.teamLead" class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt class="text-sm font-medium text-gray-500">{{ $t('team.teamLead') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ team.teamLead.name }} ({{ team.teamLead.position }})
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Team Members -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
              <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $t('team.members') }}</h3>
              <button
                v-if="authStore.isAdmin"
                @click="openAddMemberModal"
                class="bg-indigo-600 text-white px-3 py-1 rounded-md hover:bg-indigo-700 text-sm"
              >
                {{ $t('team.addMember') }}
              </button>
            </div>
            <div class="border-t border-gray-200">
              <div v-if="loadingMembers" class="px-4 py-5 text-center">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
              </div>
              <div v-else-if="members.length > 0" class="divide-y divide-gray-200">
                <div 
                  v-for="member in members" 
                  :key="member.id"
                  class="px-4 py-4 sm:px-6 hover:bg-gray-50"
                >
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="font-medium text-gray-900">{{ member.employeeName }}</p>
                      <p class="text-sm text-gray-500">{{ member.role }} • {{ member.allocationPercentage }}% {{ $t('team.allocated') }}</p>
                    </div>
                    <button
                      v-if="authStore.isAdmin"
                      @click="removeMember(member.id)"
                      class="text-red-600 hover:text-red-900 text-sm"
                    >
                      {{ $t('team.removeMember') }}
                    </button>
                  </div>
                </div>
              </div>
              <p v-else class="px-4 py-5 text-gray-500 text-sm">
                {{ $t('team.noMembers') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else class="bg-white shadow sm:rounded-lg p-6">
          <p class="text-red-600">{{ $t('team.notFound') }}</p>
        </div>

        <!-- Add Member Modal -->
        <Modal v-model="showAddMemberModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('team.addMember') }}</h3>
          <form @submit.prevent="assignMember" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('team.employee') }}</label>
              <select 
                v-model="memberForm.employeeId" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">{{ $t('team.selectEmployee') }}</option>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('team.roleInTeam') }}</label>
              <select 
                v-model="memberForm.role" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="Member">{{ $t('team.roleMember') }}</option>
                <option value="TeamLead">{{ $t('team.roleTeamLead') }}</option>
                <option value="TechLead">{{ $t('team.roleTechLead') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $t('team.allocationPercentage') }}
              </label>
              <input 
                v-model.number="memberForm.allocationPercentage" 
                type="number" 
                required 
                min="1" 
                max="100"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
              <p class="mt-1 text-xs text-gray-500">{{ $t('team.allocationHint') }}</p>
            </div>
            <div class="flex gap-3 pt-2">
              <button 
                type="submit" 
                :disabled="memberSubmitting"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ memberSubmitting ? $t('common.adding') : $t('team.addMember') }}
              </button>
              <button 
                type="button"
                @click="showAddMemberModal = false"
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
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/utils/toast'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'

const route = useRoute()
const authStore = useAuthStore()
const { t } = useI18n()

const team = ref<any>(null)
const loading = ref(false)
const members = ref<any[]>([])
const loadingMembers = ref(false)
const availableEmployees = ref<any[]>([])

// Add Member Modal
const showAddMemberModal = ref(false)
const memberSubmitting = ref(false)
const memberForm = ref({
  employeeId: '',
  role: 'Member',
  allocationPercentage: 100
})

async function fetchTeam() {
  loading.value = true
  try {
    const teamId = route.params.id as string
    const response = await api.get(`/teams/${teamId}`)
    team.value = response.data.data
    // Also fetch team members
    await fetchTeamMembers()
  } catch (error) {
    console.error('Failed to fetch team:', error)
  } finally {
    loading.value = false
  }
}

async function fetchTeamMembers() {
  loadingMembers.value = true
  try {
    const teamId = route.params.id as string
    const response = await api.get(`/teams/${teamId}/members`)
    members.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch team members:', error)
    members.value = []
  } finally {
    loadingMembers.value = false
  }
}

async function openAddMemberModal() {
  showAddMemberModal.value = true
  // Load available employees
  try {
    const response = await api.get('/employees', { 
      params: { 
        'filter[employment_status]': 'Active',
        per_page: 1000
      } 
    })
    availableEmployees.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch employees:', error)
    showToast({ message: t('team.failedToLoadEmployees'), type: 'error' })
  }
}

async function assignMember() {
  memberSubmitting.value = true
  try {
    const teamId = route.params.id as string
    await api.post(`/teams/${teamId}/members`, memberForm.value)
    showAddMemberModal.value = false
    // Reset form
    memberForm.value = {
      employeeId: '',
      role: 'Member',
      allocationPercentage: 100
    }
    // Reload team members
    await fetchTeamMembers()
    showToast({ message: t('team.memberAdded'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to assign member:', error)
    showToast({ message: error.response?.data?.message || t('team.addFailed'), type: 'error' })
  } finally {
    memberSubmitting.value = false
  }
}

async function removeMember(memberId: string) {
  if (!confirm(t('team.confirmRemove'))) {
    return
  }
  
  try {
    const teamId = route.params.id as string
    await api.delete(`/teams/${teamId}/members/${memberId}`)
    await fetchTeamMembers()
    showToast({ message: t('team.memberRemoved'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to remove member:', error)
    showToast({ message: error.response?.data?.message || t('team.removeFailed'), type: 'error' })
  }
}

onMounted(() => {
  fetchTeam()
})
</script>
