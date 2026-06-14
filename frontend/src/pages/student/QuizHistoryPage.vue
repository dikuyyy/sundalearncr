<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Riwayat Quiz</h1>
      <p class="text-gray-500 mt-1">Semua hasil quiz yang pernah kamu kerjakan.</p>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="card animate-pulse h-20 bg-gray-100" />
    </div>

    <div v-else-if="attempts.length" class="space-y-4">
      <RouterLink
        v-for="attempt in attempts"
        :key="attempt.id"
        :to="`/quiz/hasil/${attempt.id}`"
        class="card flex items-center gap-4 hover:shadow-md hover:border-sunda-200 border-2 border-transparent transition-all"
      >
        <div
          class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl font-bold flex-shrink-0"
          :class="attempt.score >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
        >
          {{ Math.round(attempt.score) }}
        </div>
        <div class="flex-1">
          <p class="font-semibold text-gray-800">{{ attempt.quiz_setting?.title }}</p>
          <p class="text-sm text-gray-500">
            {{ attempt.correct_answers }} benar · {{ attempt.wrong_answers }} salah ·
            {{ attempt.total_questions }} soal
          </p>
          <p class="text-xs text-gray-400">{{ formatDate(attempt.finished_at) }}</p>
        </div>
        <div class="text-sunda-600 text-sm font-medium">Review →</div>
      </RouterLink>
    </div>

    <div v-else class="text-center py-16 text-gray-400">
      <p class="text-4xl mb-3">📊</p>
      <p class="font-medium">Belum ada riwayat quiz.</p>
      <RouterLink to="/quiz" class="btn-primary mt-4 inline-block">Ikuti Quiz Sekarang</RouterLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const quiz     = useQuizStore()
const attempts = ref<any[]>([])
const loading  = ref(true)

function formatDate(dt: string) {
  if (!dt) return '-'
  return new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(async () => {
  const data = await quiz.fetchHistory()
  attempts.value = data.data ?? data
  loading.value  = false
})
</script>
