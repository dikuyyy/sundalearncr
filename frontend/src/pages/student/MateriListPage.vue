<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Materi Pembelajaran</h1>
        <p class="text-gray-500 mt-1">Pelajari aksara Sunda dari dasar hingga mahir.</p>
      </div>
    </div>

    <!-- Filter Kategori -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button
        v-for="cat in categories"
        :key="cat.value"
        @click="activeCategory = cat.value"
        class="px-4 py-2 rounded-full text-sm font-medium transition-colors"
        :class="activeCategory === cat.value
          ? 'bg-sunda-600 text-white'
          : 'bg-white text-gray-600 border border-gray-200 hover:border-sunda-300'"
      >
        {{ cat.icon }} {{ cat.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="card animate-pulse h-40 bg-gray-100" />
    </div>

    <!-- Grid Materi -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <RouterLink
        v-for="material in filteredMaterials"
        :key="material.id"
        :to="`/materi/${material.id}`"
        class="card hover:shadow-md hover:border-sunda-200 border-2 border-transparent transition-all cursor-pointer group"
      >
        <!-- Category Badge -->
        <div class="flex items-start justify-between mb-4">
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
            :class="getCategoryColor(material.category)"
          >
            {{ getCategoryIcon(material.category) }}
          </div>
          <span class="badge" :class="getCategoryBadge(material.category)">
            {{ getCategoryLabel(material.category) }}
          </span>
        </div>

        <h3 class="font-semibold text-gray-900 group-hover:text-sunda-700 transition-colors">
          {{ material.title }}
        </h3>
        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ material.description }}</p>

        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
          <span class="text-xs text-gray-400">{{ material.items_count }} item</span>
          <span class="text-xs text-sunda-600 font-medium group-hover:underline">Pelajari →</span>
        </div>
      </RouterLink>
    </div>

    <div v-if="!store.loading && filteredMaterials.length === 0" class="text-center py-16 text-gray-400">
      <p class="text-4xl mb-3">📚</p>
      <p class="font-medium">Belum ada materi untuk kategori ini.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useMaterialStore } from '@/stores/material'

const store = useMaterialStore()
const activeCategory = ref('all')

const categories = [
  { value: 'all',          label: 'Semua',         icon: '📋' },
  { value: 'huruf_dasar',  label: 'Huruf Dasar',   icon: '🔤' },
  { value: 'rarangken',    label: 'Rarangken',      icon: '✨' },
  { value: 'angka',        label: 'Angka Sunda',    icon: '🔢' },
  { value: 'contoh_kata',  label: 'Contoh Kata',    icon: '💬' },
]

const filteredMaterials = computed(() =>
  activeCategory.value === 'all'
    ? store.materials
    : store.materials.filter((m) => m.category === activeCategory.value)
)

const getCategoryIcon  = (c: string) => ({ huruf_dasar: '🔤', rarangken: '✨', angka: '🔢', contoh_kata: '💬' }[c] ?? '📄')
const getCategoryLabel = (c: string) => ({ huruf_dasar: 'Huruf Dasar', rarangken: 'Rarangken', angka: 'Angka', contoh_kata: 'Contoh Kata' }[c] ?? c)
const getCategoryColor = (c: string) => ({ huruf_dasar: 'bg-blue-100', rarangken: 'bg-purple-100', angka: 'bg-yellow-100', contoh_kata: 'bg-green-100' }[c] ?? 'bg-gray-100')
const getCategoryBadge = (c: string) => ({ huruf_dasar: 'badge-blue', rarangken: 'badge bg-purple-100 text-purple-800', angka: 'badge-yellow', contoh_kata: 'badge-green' }[c] ?? 'badge')

onMounted(() => store.fetchAll())
</script>
