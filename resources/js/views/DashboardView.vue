<template>
  <AppLayout>
    <div class="py-10">
      <header>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 class="text-3xl font-bold leading-tight text-gray-900">{{ $t('nav.dashboard') }}</h1>
        </div>
      </header>
      <main>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="px-4 py-8 sm:px-0">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Admin-only stats -->
              <template v-if="authStore.isAdmin">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.totalEmployees') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.totalEmployees || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.activeTeams') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.activeTeams || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.totalEquipment') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.totalEquipment || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.pendingLeaves') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.pendingLeaves || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Employee-only stats -->
              <template v-else>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.myPendingLeaves') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.myPendingLeaves || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.myApprovedLeaves') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.myApprovedLeaves || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.myEquipment') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.myEquipment || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">{{ $t('dashboard.myTeam') }}</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ stats?.myTeam || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Admin Quick Start -->
            <div v-if="authStore.isAdmin" class="mt-8 bg-white shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $t('dashboard.quickStart') }}</h2>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <router-link
                  to="/employees"
                  class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition"
                >
                  <h3 class="font-medium text-gray-900">{{ $t('dashboard.manageEmployees') }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.manageEmployeesDesc') }}</p>
                </router-link>
                
                <router-link
                  to="/teams"
                  class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition"
                >
                  <h3 class="font-medium text-gray-900">{{ $t('dashboard.manageTeams') }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.manageTeamsDesc') }}</p>
                </router-link>
                
                <router-link
                  to="/equipment"
                  class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition"
                >
                  <h3 class="font-medium text-gray-900">{{ $t('dashboard.equipmentInventory') }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.equipmentInventoryDesc') }}</p>
                </router-link>
                
                <router-link
                  to="/leaves"
                  class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition"
                >
                  <h3 class="font-medium text-gray-900">{{ $t('dashboard.leaveManagement') }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.leaveManagementDesc') }}</p>
                </router-link>
              </div>
            </div>

            <!-- Employee Quick Actions -->
            <div v-else class="mt-8 bg-white shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $t('dashboard.quickActions') }}</h2>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <router-link
                  to="/leaves"
                  class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition"
                >
                  <h3 class="font-medium text-gray-900">{{ $t('dashboard.myLeaves') }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.myLeavesDesc') }}</p>
                </router-link>
                
                <router-link
                  to="/profile"
                  class="block p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition"
                >
                  <h3 class="font-medium text-gray-900">{{ $t('dashboard.myProfile') }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ $t('dashboard.myProfileDesc') }}</p>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { api } from '@/services/api'
import AppLayout from '@/layouts/AppLayout.vue'

const authStore = useAuthStore()
const stats = ref<any>(null)

async function fetchStats() {
  try {
    const response = await api.get('/dashboard/stats')
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

onMounted(() => {
  fetchStats()
})
</script>

