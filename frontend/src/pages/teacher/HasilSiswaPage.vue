<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Hasil Quiz Siswa</h1>
        <p class="text-gray-500 mt-1">
          Peringkat nilai siswa
          <span v-if="selectedQuiz"> — <strong class="text-sunda-700">{{ selectedQuiz.title }}</strong></span>
        </p>
      </div>
      <button v-if="selectedQuiz" @click="showPicker = true" class="btn-secondary text-sm">
        🔀 Ganti Quiz
      </button>
    </div>

    <!-- Stats (quiz terpilih) -->
    <div v-if="selectedQuiz" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <StatCard label="Total Peserta"   :value="filteredResults.length"        icon="👥" color="blue" />
      <StatCard label="Rata-rata Nilai" :value="`${avgScore}%`"                icon="📊" color="green" />
      <StatCard label="Nilai Tertinggi" :value="`${highScore}%`"               icon="🏆" color="yellow" />
      <StatCard label="Nilai Terendah"  :value="`${lowScore}%`"                icon="📉" color="red" />
    </div>

    <!-- Tabel Ranking -->
    <div v-if="selectedQuiz" class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Peringkat</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Siswa</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Nilai</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Benar</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Salah</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Waktu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="6" class="text-center py-8 text-gray-400">Memuat...</td>
            </tr>
            <tr
              v-for="(r, i) in filteredResults"
              :key="r.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3">
                <span class="font-bold text-base">{{ rankLabel(i) }}</span>
              </td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ r.user_name }}</td>
              <td class="px-4 py-3">
                <span
                  class="font-bold text-base"
                  :class="r.score >= 70 ? 'text-green-600' : 'text-red-500'"
                >
                  {{ r.score }}%
                </span>
              </td>
              <td class="px-4 py-3 text-green-600 font-medium">{{ r.correct_answers }}</td>
              <td class="px-4 py-3 text-red-500 font-medium">{{ r.wrong_answers }}</td>
              <td class="px-4 py-3 text-gray-500">{{ formatDuration(r.time_spent_seconds) }}</td>
            </tr>
            <tr v-if="!loading && filteredResults.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-400">Belum ada hasil untuk quiz ini.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Popup Pilih Quiz -->
    <div v-if="showPicker" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="font-bold text-gray-900">Pilih Quiz</h3>
          <button v-if="selectedQuiz" @click="showPicker = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-500 mb-4">Pilih quiz untuk melihat peringkat nilai siswa.</p>

          <div v-if="quizzes.length" class="space-y-2">
            <button
              v-for="q in quizzes"
              :key="q.id"
              @click="selectQuiz(q.id)"
              class="w-full text-left px-4 py-3 rounded-lg border-2 transition-all flex items-center justify-between"
              :class="selectedQuizId === q.id
                ? 'border-sunda-500 bg-sunda-50 text-sunda-800'
                : 'border-gray-200 hover:border-sunda-300 hover:bg-sunda-50'"
            >
              <span class="font-medium">{{ q.title }}</span>
              <span class="text-xs text-gray-400">{{ q.count }} hasil</span>
            </button>
          </div>
          <div v-else class="text-center py-8 text-gray-400">
            <p class="text-3xl mb-2">📊</p>
            <p class="text-sm">Belum ada quiz yang memiliki hasil.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useQuizStore } from '@/stores/quiz'
import StatCard from '@/components/common/StatCard.vue'

const quiz    = useQuizStore()
const results = ref<any[]>([])
const loading = ref(true)
const showPicker     = ref(false)
const selectedQuizId = ref<number | null>(null)

// Daftar quiz unik yang memiliki hasil (untuk popup)
const quizzes = computed(() => {
  const map = new Map<number, { id: number; title: string; count: number }>()
  for (const r of results.value) {
    const existing = map.get(r.quiz_id)
    if (existing) existing.count++
    else map.set(r.quiz_id, { id: r.quiz_id, title: r.quiz_title, count: 1 })
  }
  return [...map.values()]
})

const selectedQuiz = computed(() => quizzes.value.find((q) => q.id === selectedQuizId.value) ?? null)

// Hasil quiz terpilih, diurutkan dari nilai tertinggi (ranking)
const filteredResults = computed(() =>
  results.value
    .filter((r) => r.quiz_id === selectedQuizId.value)
    .sort((a, b) => b.score - a.score || a.time_spent_seconds - b.time_spent_seconds)
)

const avgScore = computed(() => {
  if (!filteredResults.value.length) return 0
  const sum = filteredResults.value.reduce((acc, r) => acc + Number(r.score), 0)
  return Math.round((sum / filteredResults.value.length) * 100) / 100
})
const highScore = computed(() => Math.max(0, ...filteredResults.value.map((r) => Number(r.score))))
const lowScore  = computed(() => filteredResults.value.length ? Math.min(...filteredResults.value.map((r) => Number(r.score))) : 0)

function rankLabel(i: number) {
  return ['🥇', '🥈', '🥉'][i] ?? `#${i + 1}`
}

function selectQuiz(id: number) {
  selectedQuizId.value = id
  showPicker.value = false
}

function formatDuration(s: number) {
  if (!s) return '-'
  const m = Math.floor(s / 60)
  const sec = s % 60
  return `${m}m ${sec}s`
}

onMounted(async () => {
  const data = await quiz.fetchStudentResults()
  results.value = data.data ?? []
  loading.value = false
  // Tampilkan popup pilih quiz saat pertama masuk
  showPicker.value = true
})
</script>
