<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Impersonation Banner -->
    <ImpersonationBanner />
    
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/dashboard" class="text-xl font-bold text-indigo-600">
                PeoplePulse
              </router-link>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link
                to="/dashboard"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="$route.path === '/dashboard' 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
              >
                {{ $t('nav.dashboard') }}
              </router-link>
              <router-link
                v-if="authStore.isAdmin"
                to="/employees"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="$route.path.startsWith('/employees') 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
              >
                {{ $t('nav.employees') }}
              </router-link>
              <router-link
                v-if="authStore.isAdmin"
                to="/teams"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="$route.path.startsWith('/teams') 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
              >
                {{ $t('nav.teams') }}
              </router-link>
              <router-link
                v-if="authStore.isAdmin"
                to="/equipment"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="$route.path.startsWith('/equipment') 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
              >
                {{ $t('nav.equipment') }}
              </router-link>
              <router-link
                to="/leaves"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="$route.path.startsWith('/leaves') 
                  ? 'border-indigo-500 text-gray-900' 
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
              >
                {{ $t('nav.leave') }}
              </router-link>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <!-- Language Switcher -->
            <select 
              v-model="locale" 
              @change="changeLocale"
              class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option value="en">🇬🇧 EN</option>
              <option value="uk">🇺🇦 UA</option>
            </select>
            
            <!-- User Profile -->
            <router-link
              to="/profile"
              class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900"
            >
              <Avatar 
                :name="authStore.user?.employee?.name || authStore.user?.email || 'User'" 
                :photo-url="authStore.user?.employee?.photoUrl"
                size="sm" 
              />
              <span>{{ authStore.user?.employee?.name || authStore.user?.employee?.email || authStore.user?.email }}</span>
            </router-link>
            
            <span
              v-if="authStore.isAdmin"
              class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded"
            >
              Admin
            </span>
            <button
              @click="handleLogout"
              class="text-sm text-gray-500 hover:text-gray-700"
            >
              {{ $t('nav.logout') }}
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import Avatar from '@/components/Avatar.vue'
import ImpersonationBanner from '@/components/ImpersonationBanner.vue'

const router = useRouter()
const authStore = useAuthStore()
const { locale } = useI18n()

function changeLocale() {
  localStorage.setItem('locale', locale.value)
}

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>
