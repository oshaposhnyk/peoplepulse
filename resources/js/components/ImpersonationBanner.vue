<template>
  <div v-if="authStore.isImpersonating" class="bg-yellow-50 border-b border-yellow-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between py-2">
        <div class="flex items-center gap-2">
          <span class="text-yellow-800 text-sm font-medium">
            {{ $t('impersonation.loggedInAs') }}: 
            <span class="font-semibold">{{ authStore.user?.employee?.name || authStore.user?.email }}</span>
          </span>
        </div>
        <button
          @click="stopImpersonating"
          class="bg-yellow-600 text-white px-3 py-1 rounded-md text-sm hover:bg-yellow-700 font-medium"
        >
          {{ $t('impersonation.stopImpersonating') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { showToast } from '@/utils/toast'

const authStore = useAuthStore()
const { t } = useI18n()
const router = useRouter()

async function stopImpersonating() {
  const success = await authStore.stopImpersonating()
  
  if (success) {
    showToast({ 
      message: t('impersonation.impersonationStopped'), 
      type: 'success' 
    })
    // Redirect to dashboard after stopping impersonation
    router.push('/dashboard')
  } else {
    showToast({ 
      message: t('impersonation.stopImpersonationFailed'), 
      type: 'error' 
    })
  }
}
</script>

