<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $t('nav.profile') }}</h1>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Profile Content -->
        <div v-else-if="profile" class="space-y-6">
          <!-- Profile Header Card -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center space-x-6">
                <!-- Avatar -->
                <Avatar :name="profile.fullName || authStore.user?.email || 'User'" size="xl" />
                
                <div class="flex-1">
                  <h2 class="text-2xl font-bold text-gray-900">{{ profile.fullName }}</h2>
                  <p class="text-lg text-gray-600">{{ profile.position }}</p>
                  <p class="text-sm text-gray-500">{{ profile.department }}</p>
                  
                  <div class="mt-3 flex items-center space-x-4">
                    <span
                      :class="{
                        'bg-green-100 text-green-800': profile.status === 'Active',
                        'bg-red-100 text-red-800': profile.status === 'Terminated',
                      }"
                      class="px-3 py-1 text-sm font-semibold rounded-full"
                    >
                      {{ profile.status }}
                    </span>
                    <span class="text-sm text-gray-500">
                      {{ $t('employee.hireDate') }}: {{ profile.hireDate }}
                    </span>
                  </div>
                </div>

                <button
                  v-if="isOwnProfile"
                  @click="showEditModal = true"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                  {{ $t('common.edit') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ $t('profile.contactInformation') }}
              </h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.email') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.email }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.phone') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.phone || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.location') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.location || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('profile.employeeId') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.id }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Employment Details -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ $t('profile.employmentDetails') }}
              </h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.position') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.position }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.department') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.department }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.hireDate') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ profile.hireDate }}</dd>
                </div>
                <div v-if="profile.salary && authStore.isAdmin">
                  <dt class="text-sm font-medium text-gray-500">{{ $t('employee.salary') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ profile.salary.currency }} {{ profile.salary.amount?.toLocaleString() }} / {{ profile.salary.frequency }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Teams -->
          <div v-if="profile.teams && profile.teams.length > 0" class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ $t('profile.myTeams') }}
              </h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                  v-for="team in profile.teams"
                  :key="team.id"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer"
                  @click="$router.push(`/teams/${team.id}`)"
                >
                  <h4 class="font-semibold text-gray-900">{{ team.name }}</h4>
                  <p class="text-sm text-gray-500">{{ team.role }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else class="bg-white shadow sm:rounded-lg p-6">
          <p class="text-red-600">{{ error || $t('profile.failedToLoad') }}</p>
        </div>

        <!-- Edit Profile Modal -->
        <Modal v-model="showEditModal">
          <h3 class="text-lg font-bold mb-4 text-gray-900">{{ $t('profile.editProfile') }}</h3>
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('employee.phone') }}</label>
              <input 
                v-model="editForm.phone" 
                type="tel" 
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
                :disabled="updating"
                class="flex-1 bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ updating ? $t('common.saving') : $t('common.save') }}
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
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { showToast } from '@/utils/toast'
import AppLayout from '@/layouts/AppLayout.vue'
import Avatar from '@/components/Avatar.vue'
import Modal from '@/components/Modal.vue'
import { api } from '@/services/api'

const route = useRoute()
const authStore = useAuthStore()
const { t } = useI18n()
const profile = ref<any>(null)
const loading = ref(false)
const error = ref('')

// Check if viewing own profile or someone else's
const employeeId = route.params.id as string | undefined
const isOwnProfile = computed(() => !employeeId || employeeId === authStore.user?.employee_id_string)

// Edit Modal
const showEditModal = ref(false)
const updating = ref(false)
const editForm = ref({
  phone: '',
  location: ''
})

async function fetchProfile() {
  loading.value = true
  error.value = ''
  try {
    let response
    if (employeeId) {
      // View specific employee's profile
      response = await api.get(`/profile/${employeeId}`)
    } else {
      // View own profile
      response = await api.get('/profile')
    }
    
    profile.value = response.data.data
    
    // Populate edit form only for own profile
    if (isOwnProfile.value) {
      editForm.value = {
        phone: profile.value.phone || '',
        location: profile.value.location || ''
      }
    }
  } catch (err: any) {
    console.error('Failed to fetch profile:', err)
    error.value = err.response?.data?.message || 'Failed to load profile'
  } finally {
    loading.value = false
  }
}

async function updateProfile() {
  updating.value = true
  try {
    await api.put('/profile', editForm.value)
    showEditModal.value = false
    await fetchProfile()
    showToast({ message: t('profile.updated'), type: 'success' })
  } catch (err: any) {
    console.error('Failed to update profile:', err)
    showToast({ message: err.response?.data?.message || t('profile.updateFailed'), type: 'error' })
  } finally {
    updating.value = false
  }
}

onMounted(() => {
  fetchProfile()
})
</script>
