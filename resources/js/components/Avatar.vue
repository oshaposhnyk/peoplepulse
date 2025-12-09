<template>
  <div 
    v-if="!photoUrl"
    :class="[
      'flex items-center justify-center font-semibold text-white rounded-full',
      sizeClasses[size]
    ]"
    :style="{ backgroundColor: color }"
  >
    {{ initials }}
  </div>
  <img
    v-else
    :src="photoUrl"
    :alt="name"
    :class="[
      'rounded-full object-cover',
      sizeClasses[size]
    ]"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  name: string
  size?: 'sm' | 'md' | 'lg' | 'xl'
  photoUrl?: string
}>()

const size = props.size || 'md'

const sizeClasses = {
  sm: 'w-8 h-8 text-xs',
  md: 'w-12 h-12 text-base',
  lg: 'w-16 h-16 text-xl',
  xl: 'w-24 h-24 text-3xl'
}

const initials = computed(() => {
  const parts = props.name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return props.name.substring(0, 2).toUpperCase()
})

const color = computed(() => {
  // Generate consistent color based on name
  const colors = [
    '#EF4444', // red
    '#F59E0B', // amber
    '#10B981', // green
    '#3B82F6', // blue
    '#6366F1', // indigo
    '#8B5CF6', // purple
    '#EC4899', // pink
    '#14B8A6', // teal
  ]
  
  const hash = props.name.split('').reduce((acc, char) => {
    return char.charCodeAt(0) + ((acc << 5) - acc)
  }, 0)
  
  return colors[Math.abs(hash) % colors.length]
})
</script>

