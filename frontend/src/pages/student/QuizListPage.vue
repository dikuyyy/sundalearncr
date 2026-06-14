<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Daftar Quiz</h1>
      <p class="text-gray-500 mt-1">Pilih quiz untuk menguji kemampuan aksara Sunda kamu.</p>
    </div>

    <div v-if="quiz.loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="card animate-pulse h-48 bg-gray-100" />
    </div>

    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="q in quiz.available"
        :key="q.id"
        class="card hover:shadow-md transition-all border-2 border-transparent hover:border-sunda-200"
      >
        <!-- Difficulty Badge -->
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-sunda-100 rounded-xl flex items-center justify-center text-2xl">✏️</div>
          <span class="badge" :class="difficultyBadge(q.difficulty)">{{ q.difficulty }}</span>
        </div>

        <h3 class="font-semibold text-gray-900">{{ q.title }}</h3>
        <p v-if="q.description" class="text-sm text-gray-500 mt-1 line-clamp-2">{{ q.description }}</p>

        <div class="flex gap-4 mt-4 text-sm text-gray-500">
          <span>📝 {{ q.total_questions }} soal</span>
          <span>⏱️ {{ q.duration_minutes }} menit</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Dibuat oleh: {{ q.creator }}</p>

        <button
          @click="startQuiz(q.id)"
          :disabled="starting === q.id"
          class="btn-primary w-full mt-4"
        >
          {{ starting === q.id ? '⏳ Memulai...' : '🚀 Mulai Quiz' }}
        </button>
      </div>
    </div>

    <div v-if="!quiz.loading && quiz.available.length === 0" class="text-center py-16 text-gray-400">
      <p class="text-4xl mb-3">✏️</p>
      <p class="font-medium">Belum ada quiz yang tersedia.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const quiz    = useQuizStore()
const router  = useRouter()
const starting = ref<number | null>(null)

const difficultyBadge = (d: string) => ({
  mudah:    'badge-green',
  sedang:   'badge-yellow',
  sulit:    'badge-red',
  campuran: 'badge-blue',
}[d] ?? 'badge')

async function startQuiz(id: number) {
  starting.value = id
  try {
    const attempt = await quiz.startQuiz(id)
    router.push({ name: 'quiz.start', params: { id }, query: { attempt: attempt.attempt_id } })
  } catch {
    // Error sudah ditangani di store
  } finally {
    starting.value = null
  }
}

onMounted(() => quiz.fetchAvailable())
</script>
