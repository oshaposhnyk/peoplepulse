<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('team.list') }}</h1>
          <button
            v-if="authStore.isAdmin"
            @click="showAddModal = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
          >
            {{ $t('team.add') }}
          </button>
        </div>
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <!-- Loading -->
            <div v-if="loading" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Teams Grid -->
            <div v-else-if="teams.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="team in teams"
                :key="team.id"
                class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition cursor-pointer"
                @click="$router.push(`/teams/${team.id}`)"
              >
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ team.name }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ team.type }} • {{ team.department }}</p>
                
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">
                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ team.memberCount || 0 }} {{ $t('team.membersCount') }}
                  </span>
                  <span
                    v-if="team.isActive"
                    class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs"
                  >
                    {{ $t('team.active') }}
                  </span>
                </div>

                <div v-if="team.teamLead" class="mt-3 pt-3 border-t border-gray-200">
                  <p class="text-xs text-gray-500 mb-1">{{ $t('team.teamLead') }}</p>
                  <router-link
                    :to="`/profile/${team.teamLead.id}`"
                    class="flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-900"
                  >
                    <Avatar 
                      :name="team.teamLead.name" 
                      :photo-url="team.teamLead.photoUrl" 
                      size="sm" 
                    />
                    <span>{{ team.teamLead.name }}</span>
                  </router-link>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <p class="text-gray-500">{{ $t('team.noTeams') }}</p>
            </div>
          </div>
        </div>

        <!-- Add Team Modal -->
        <Modal v-model="showAddModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('team.add') }}</h3>
          <form @submit.prevent="addTeam" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('team.name') }}</label>
              <input 
                v-model="addForm.name" 
                type="text" 
                required 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('team.description') }}</label>
              <textarea 
                v-model="addForm.description" 
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Enter team description..."
              ></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('team.type') }}</label>
                <select 
                  v-model="addForm.type" 
                  required 
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">{{ $t('common.select') }}</option>
                  <option>Product</option>
                  <option>Project</option>
                  <option>CrossFunctional</option>
                  <option>Support</option>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { api } from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/utils/toast'
import AppLayout from '@/layouts/AppLayout.vue'
import Modal from '@/components/Modal.vue'
import Avatar from '@/components/Avatar.vue'

const authStore = useAuthStore()
const router = useRouter()

// Redirect non-admin users
if (!authStore.isAdmin) {
  router.push({ name: 'dashboard' })
}
const { t } = useI18n()
const teams = ref<any[]>([])
const loading = ref(false)

// Add Team Modal
const showAddModal = ref(false)
const addSubmitting = ref(false)
const addForm = ref({
  name: '',
  description: '',
  type: '',
  department: ''
})

async function fetchTeams() {
  loading.value = true
  try {
    const response = await api.get('/teams')
    teams.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch teams:', error)
  } finally {
    loading.value = false
  }
}

async function addTeam() {
  addSubmitting.value = true
  try {
    await api.post('/teams', addForm.value)
    showAddModal.value = false
    // Reset form
    addForm.value = {
      name: '',
      description: '',
      type: '',
      department: ''
    }
    await fetchTeams()
    showToast({ message: t('team.teamCreated'), type: 'success' })
  } catch (error: any) {
    console.error('Failed to create team:', error)
    showToast({ message: error.response?.data?.message || t('team.createFailed'), type: 'error' })
  } finally {
    addSubmitting.value = false
  }
}

onMounted(() => {
  fetchTeams()
})
</script>
