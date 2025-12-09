<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6 z-10 shadow-xl">
          <button 
            @click="close" 
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold"
            type="button"
          >
            ✕
          </button>
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{ modelValue: boolean }>()
const emit = defineEmits(['update:modelValue'])
const close = () => emit('update:modelValue', false)
</script>

