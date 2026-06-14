<template>
  <div class="card">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ label }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ value }}</p>
      </div>
      <div
        class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
        :class="colorClass"
      >
        {{ icon }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  label: string
  value: string | number
  icon: string
  color?: 'blue' | 'green' | 'yellow' | 'purple' | 'red'
}>()

const colorClass = computed(() => ({
  blue:   'bg-blue-100',
  green:  'bg-green-100',
  yellow: 'bg-yellow-100',
  purple: 'bg-purple-100',
  red:    'bg-red-100',
}[props.color ?? 'green']))
</script>
