<template>
  <RouterLink
    :to="to"
    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sunda-200 hover:bg-sunda-700 hover:text-white transition-colors text-sm"
    :class="{ 'bg-sunda-700 !text-white font-medium': isActive }"
  >
    <span>{{ icon }}</span>
    <span>{{ label }}</span>
  </RouterLink>
</template>

<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import { computed } from 'vue'

const props = defineProps<{ to: string; icon: string; label: string }>()
const route = useRoute()
const isActive = computed(() => route.path === props.to || route.path.startsWith(props.to + '/'))
</script>
