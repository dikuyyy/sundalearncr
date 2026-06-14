<template>
  <div class="max-w-3xl mx-auto">
    <RouterLink to="/quiz/riwayat" class="inline-flex items-center gap-2 text-sunda-600 hover:text-sunda-800 mb-6 text-sm font-medium">
      ← Riwayat Quiz
    </RouterLink>

    <div v-if="loading" class="card animate-pulse h-96 bg-gray-100" />

    <div v-else-if="review">
      <!-- Score Header -->
      <div class="card mb-6 text-center bg-gradient-to-br from-sunda-50 to-green-50 border-sunda-200">
        <div
          class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-4xl mb-4"
          :class="review.score >= 70 ? 'bg-green-100' : 'bg-red-100'"
        >
          {{ review.score >= 70 ? '🏆' : '💪' }}
        </div>
        <h1 class="text-4xl font-bold text-gray-900">{{ review.score }}%</h1>
        <p class="text-gray-500 mt-2">
          {{ review.attempt.correct_answers }} benar · {{ review.attempt.wrong_answers }} salah
          · {{ review.attempt.total_questions }} total soal
        </p>
      </div>

      <!-- Answers Review -->
      <div class="space-y-4">
        <h2 class="font-semibold text-gray-800 text-lg">Kunci Jawaban</h2>

        <div
          v-for="(answer, i) in review.answers"
          :key="i"
          class="card border-l-4"
          :class="answer.is_correct ? 'border-green-500' : 'border-red-500'"
        >
          <div class="flex items-start gap-3">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
              :class="answer.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ i + 1 }}
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-800">{{ answer.question }}</p>

              <div class="flex flex-col sm:flex-row gap-4 mt-3 text-sm">
                <div class="flex-1">
                  <p class="text-xs text-gray-500 mb-1">Jawabanmu:</p>
                  <p
                    :class="[
                      'font-medium px-3 py-1 rounded-lg inline-block',
                      answer.is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'
                    ]"
                  >
                    {{ answer.user_answer || '(tidak dijawab)' }}
                  </p>
                </div>
                <div v-if="!answer.is_correct" class="flex-1">
                  <p class="text-xs text-gray-500 mb-1">Jawaban benar:</p>
                  <p class="font-medium bg-green-100 text-green-800 px-3 py-1 rounded-lg inline-block">
                    {{ answer.correct_answer }}
                  </p>
                </div>
              </div>

              <p v-if="answer.explanation" class="text-xs text-gray-500 mt-3 bg-blue-50 border border-blue-100 rounded p-2">
                💡 {{ answer.explanation }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-3 mt-8">
        <RouterLink to="/quiz" class="btn-secondary flex-1 text-center">Ikuti Quiz Lain</RouterLink>
        <RouterLink to="/materi" class="btn-primary flex-1 text-center">📚 Kembali Belajar</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'

const quiz    = useQuizStore()
const route   = useRoute()
const review  = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  const id = Number(route.params.id)
  review.value = await quiz.fetchReview(id)
  loading.value = false
})
</script>
