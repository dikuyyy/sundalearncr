<template>
  <div v-if="material" class="max-w-4xl mx-auto">
    <!-- Back -->
    <RouterLink to="/materi" class="inline-flex items-center gap-2 text-sunda-600 hover:text-sunda-800 mb-6 text-sm font-medium">
      ← Kembali ke Daftar Materi
    </RouterLink>

    <!-- Header -->
    <div class="card mb-6 bg-gradient-to-r from-sunda-600 to-sunda-800 text-white">
      <div class="flex items-start gap-4">
        <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center text-3xl flex-shrink-0">
          {{ getCategoryIcon(material.category) }}
        </div>
        <div class="flex-1">
          <span class="text-sunda-200 text-xs font-medium uppercase tracking-wider">
            {{ getCategoryLabel(material.category) }}
          </span>
          <h1 class="text-2xl font-bold mt-1">{{ material.title }}</h1>
          <p class="text-sunda-200 text-sm mt-1">{{ material.description }}</p>
        </div>
      </div>
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <div
        v-for="item in material.items"
        :key="item.id"
        class="card hover:shadow-md transition-all border-2"
        :class="activeItem?.id === item.id ? 'border-sunda-400' : 'border-transparent'"
        @click="activeItem = item"
      >
        <!-- Karakter Aksara -->
        <div class="text-center mb-3">
          <div class="font-sunda text-6xl text-sunda-700 mb-2 leading-none">{{ item.sunda_script }}</div>
          <p class="text-lg font-semibold text-gray-800">{{ item.latin_name }}</p>
          <p class="text-sm text-sunda-600">/ {{ item.pronunciation }} /</p>
        </div>

        <!-- Variasi dengan vokal (huruf dasar) -->
        <template v-if="material.category === 'huruf_dasar'">
          <div class="border-t pt-3 mt-3">
            <p class="text-xs text-gray-500 mb-2 text-center">Dengan vokal:</p>
            <div class="flex justify-center gap-3 flex-wrap">
              <div class="text-center">
                <span class="font-sunda text-xl">{{ item.sunda_script }}</span>
                <span class="block text-xs text-gray-400">a</span>
              </div>
              <div class="text-center" v-for="v in vokalSufixes" :key="v.char">
                <span class="font-sunda text-xl">{{ item.sunda_script }}{{ v.char }}</span>
                <span class="block text-xs text-gray-400">{{ v.latin }}</span>
              </div>
            </div>
          </div>
        </template>

        <!-- Fungsi untuk rarangken -->
        <template v-if="material.category === 'rarangken'">
          <p v-if="item.description" class="text-xs text-gray-600 text-center mt-2">
            {{ item.description }}
          </p>
        </template>

        <!-- Contoh penggunaan -->
        <template v-if="item.examples?.length">
          <div class="border-t pt-3 mt-3">
            <p class="text-xs text-gray-500 mb-2">Contoh:</p>
            <div v-for="ex in item.examples" :key="JSON.stringify(ex)" class="text-sm">
              <span class="font-sunda text-sunda-700">{{ ex.latin || ex }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Mark Complete Button -->
    <div class="flex justify-center">
      <button
        @click="markDone"
        :disabled="marked"
        class="btn-primary px-10 py-3 text-base"
        :class="{ 'bg-green-600 hover:bg-green-700': marked }"
      >
        {{ marked ? '✅ Materi Selesai Dipelajari!' : '✔️ Tandai Selesai' }}
      </button>
    </div>
  </div>

  <div v-else class="text-center py-16 text-gray-400">
    <p v-if="store.loading" class="animate-pulse">Memuat materi...</p>
    <p v-else>Materi tidak ditemukan.</p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useMaterialStore, type MaterialItem } from '@/stores/material'

const store    = useMaterialStore()
const route    = useRoute()
const material = ref(store.current)
const activeItem = ref<MaterialItem | null>(null)
const marked   = ref(false)

const vokalSufixes = [
  { char: 'ᮤ', latin: 'i' },
  { char: 'ᮥ', latin: 'u' },
  { char: 'ᮨ', latin: 'e' },
  { char: 'ᮩ', latin: 'o' },
  { char: 'ᮺ', latin: '(mati)' },
]

const getCategoryIcon  = (c: string) => ({ huruf_dasar: '🔤', rarangken: '✨', angka: '🔢', contoh_kata: '💬' }[c] ?? '📄')
const getCategoryLabel = (c: string) => ({ huruf_dasar: 'Huruf Dasar', rarangken: 'Rarangken', angka: 'Angka Sunda', contoh_kata: 'Contoh Kata' }[c] ?? c)

async function markDone() {
  if (!material.value) return
  await store.markComplete(material.value.id)
  marked.value = true
}

onMounted(async () => {
  const id = Number(route.params.id)
  material.value = await store.fetchOne(id)
})
</script>
