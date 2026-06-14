<template>
  <div class="px-4 py-2 mt-3 mb-1">
    <p class="text-xs font-semibold text-sunda-400 uppercase tracking-wider">{{ label }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ label: string }>()
</script>
