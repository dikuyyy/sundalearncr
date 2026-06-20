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
          <div class="flex items-center gap-2">
            <span v-if="q.is_completed" class="badge badge-green">✓ Selesai</span>
            <span class="badge" :class="difficultyBadge(q.difficulty)">{{ q.difficulty }}</span>
          </div>
        </div>

        <h3 class="font-semibold text-gray-900">{{ q.title }}</h3>
        <p v-if="q.description" class="text-sm text-gray-500 mt-1 line-clamp-2">{{ q.description }}</p>

        <div class="flex gap-4 mt-4 text-sm text-gray-500">
          <span>📝 {{ q.total_questions }} soal</span>
          <span>⏱️ {{ q.duration_minutes }} menit</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Dibuat oleh: {{ q.creator }}</p>

        <button
          v-if="q.is_completed"
          disabled
          class="btn-primary w-full mt-4 opacity-50 cursor-not-allowed"
        >
          ✓ Quiz Selesai
        </button>
        <button
          v-else
          @click="startQuiz(q.id)"
          class="btn-primary w-full mt-4"
        >
          🚀 Mulai Quiz
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
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const quiz   = useQuizStore()
const router = useRouter()

const difficultyBadge = (d: string) => ({
  mudah:    'badge-green',
  sedang:   'badge-yellow',
  sulit:    'badge-red',
  campuran: 'badge-blue',
}[d] ?? 'badge')

// Langsung navigasi ke halaman quiz — proses "start" ditangani di QuizPlayPage
function startQuiz(id: number) {
  quiz.clearAttempt()
  router.push({ name: 'quiz.start', params: { id } })
}

onMounted(() => quiz.fetchAvailable())
</script>
