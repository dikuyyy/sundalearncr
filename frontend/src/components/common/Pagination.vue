<template>
  <div v-if="lastPage > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 px-1">
    <p class="text-xs text-gray-500">
      Halaman {{ currentPage }} dari {{ lastPage }}
      <span v-if="total"> · {{ total }} data</span>
    </p>
    <div class="flex items-center gap-1">
      <button
        type="button"
        :disabled="currentPage <= 1"
        @click="$emit('change', currentPage - 1)"
        class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
      >
        ←
      </button>
      <button
        v-for="p in pages"
        :key="p"
        type="button"
        @click="$emit('change', p)"
        class="min-w-[32px] px-2 py-1.5 text-xs rounded-lg border transition-colors"
        :class="p === currentPage
          ? 'bg-sunda-600 border-sunda-600 text-white'
          : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
      >
        {{ p }}
      </button>
      <button
        type="button"
        :disabled="currentPage >= lastPage"
        @click="$emit('change', currentPage + 1)"
        class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
      >
        →
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  currentPage: number
  lastPage: number
  total?: number
}>()

defineEmits<{ change: [page: number] }>()

// Jendela maksimal 5 nomor halaman di sekitar halaman aktif
const pages = computed(() => {
  const max = 5
  let start = Math.max(1, props.currentPage - Math.floor(max / 2))
  const end = Math.min(props.lastPage, start + max - 1)
  start = Math.max(1, end - max + 1)
  const arr: number[] = []
  for (let i = start; i <= end; i++) arr.push(i)
  return arr
})
</script>
