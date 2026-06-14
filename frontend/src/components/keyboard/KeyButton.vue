<template>
  <button
    type="button"
    @click="$emit('click')"
    :title="description"
    class="flex flex-col items-center justify-center p-2 min-h-[56px] bg-gray-50 hover:bg-sunda-50 hover:border-sunda-300 border border-gray-200 rounded-lg transition-all active:scale-95 active:bg-sunda-100 cursor-pointer select-none"
  >
    <span class="font-sunda text-xl text-gray-800 leading-none">{{ sunda }}</span>
    <span class="text-[10px] text-gray-500 mt-0.5 leading-none">{{ label }}</span>
  </button>
</template>

<script setup lang="ts">
defineProps<{
  sunda: string
  label: string
  description?: string
}>()
defineEmits(['click'])
</script>
