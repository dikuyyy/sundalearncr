<template>
  <!-- Hasil Quiz -->
  <div v-if="resultData" class="max-w-2xl mx-auto text-center py-8">
    <div class="card">
      <div class="mb-6">
        <div
          class="w-24 h-24 mx-auto rounded-full flex items-center justify-center text-4xl mb-4"
          :class="resultData.score >= 70 ? 'bg-green-100' : 'bg-red-100'"
        >
          {{ resultData.score >= 70 ? '🎉' : '😔' }}
        </div>
        <h1 class="text-3xl font-bold text-gray-900">{{ resultData.score }}%</h1>
        <p class="text-gray-500 mt-2">
          {{ resultData.score >= 90 ? 'Luar Biasa!' : resultData.score >= 70 ? 'Bagus!' : 'Terus Berlatih!' }}
        </p>
      </div>

      <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-green-50 rounded-xl p-4">
          <p class="text-2xl font-bold text-green-600">{{ resultData.correct_answers }}</p>
          <p class="text-xs text-gray-500 mt-1">Jawaban Benar</p>
        </div>
        <div class="bg-red-50 rounded-xl p-4">
          <p class="text-2xl font-bold text-red-500">{{ resultData.wrong_answers }}</p>
          <p class="text-xs text-gray-500 mt-1">Jawaban Salah</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4">
          <p class="text-2xl font-bold text-blue-600">{{ formatTime(resultData.time_spent) }}</p>
          <p class="text-xs text-gray-500 mt-1">Waktu</p>
        </div>
      </div>

      <!-- Rumus Nilai -->
      <div class="bg-sunda-50 border border-sunda-200 rounded-xl p-4 mb-6 text-sm text-left">
        <p class="font-semibold text-sunda-800 mb-2">📐 Perhitungan Nilai:</p>
        <p class="text-sunda-700 font-mono">
          Nilai = ({{ resultData.correct_answers }} / {{ resultData.total_questions }}) × 100 = <strong>{{ resultData.score }}</strong>
        </p>
      </div>

      <div class="flex gap-3">
        <RouterLink to="/quiz" class="btn-secondary flex-1">← Kembali ke Daftar Quiz</RouterLink>
        <RouterLink :to="`/quiz/hasil/${resultData.attempt_id}`" class="btn-primary flex-1">Lihat Review →</RouterLink>
      </div>
    </div>
  </div>

  <!-- Mengerjakan Quiz -->
  <div v-else-if="attempt" class="max-w-3xl mx-auto">
    <!-- Header Quiz -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex items-center gap-4">
      <div class="flex-1">
        <div class="flex justify-between text-sm text-gray-500 mb-2">
          <span>Soal {{ currentIndex + 1 }} dari {{ attempt.total_questions }}</span>
          <span class="font-mono" :class="timeLeft < 60 ? 'text-red-600 font-bold animate-pulse' : ''">
            ⏱️ {{ formatTime(timeLeft) }}
          </span>
        </div>
        <div class="bg-gray-100 rounded-full h-2">
          <div
            class="bg-sunda-500 rounded-full h-2 transition-all"
            :style="{ width: ((currentIndex + 1) / attempt.total_questions) * 100 + '%' }"
          />
        </div>
      </div>
    </div>

    <!-- Soal -->
    <div v-if="currentQuestion" class="card mb-6">
      <!-- Tipe Soal Badge -->
      <div class="flex items-center gap-2 mb-4">
        <span class="badge badge-blue text-xs">{{ questionTypeLabel(currentQuestion.type) }}</span>
        <span class="badge text-xs" :class="difficultyBadge(currentQuestion.difficulty)">
          {{ currentQuestion.difficulty }}
        </span>
      </div>

      <!-- Pertanyaan -->
      <div class="text-center mb-6">
        <div
          class="text-gray-800 leading-relaxed"
          :class="currentQuestion.type === 'sunda_to_latin' ? 'font-sunda text-5xl' : 'text-xl font-medium'"
        >
          {{ currentQuestion.question_text }}
        </div>
      </div>

      <!-- Pilihan Ganda -->
      <div v-if="currentQuestion.type === 'multiple_choice'" class="grid grid-cols-2 gap-3">
        <button
          v-for="(optValue, optKey) in currentQuestion.options"
          :key="optKey"
          @click="selectAnswer(optKey)"
          class="p-4 rounded-xl border-2 text-left transition-all font-medium"
          :class="getOptionClass(optKey)"
        >
          <span class="text-sunda-700 font-bold mr-2">{{ optKey.toUpperCase() }}.</span>
          <span :class="currentQuestion.type === 'sunda_to_latin' ? 'font-sunda text-xl' : ''">
            {{ optValue }}
          </span>
        </button>
      </div>

      <!-- Input Teks (sunda_to_latin / latin_to_sunda) -->
      <div v-else class="space-y-3">
        <div v-if="currentQuestion.type === 'sunda_to_latin'">
          <label class="block text-sm font-medium text-gray-700 mb-2">Tulis dalam Latin:</label>
          <input
            v-model="textAnswer"
            type="text"
            class="input-field text-xl"
            placeholder="Ketik jawaban dalam Latin..."
            @keydown.enter="goNext"
          />
        </div>
        <div v-if="currentQuestion.type === 'latin_to_sunda'">
          <label class="block text-sm font-medium text-gray-700 mb-2">Tulis dalam Aksara Sunda:</label>
          <textarea
            v-model="textAnswer"
            class="input-field font-sunda text-2xl min-h-[80px]"
            placeholder="Ketik atau gunakan keyboard di bawah..."
          />
          <!-- Mini Keyboard -->
          <div class="mt-3 border border-gray-200 rounded-xl overflow-hidden">
            <SundaKeyboard
              @insert="textAnswer += $event"
              @backspace="textAnswer = [...textAnswer].slice(0, -1).join('')"
              @clear="textAnswer = ''"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="flex justify-between items-center">
      <button
        @click="goPrev"
        :disabled="currentIndex === 0"
        class="btn-secondary px-6 py-3 disabled:opacity-40"
      >
        ← Sebelumnya
      </button>

      <span class="text-sm text-gray-500">
        {{ answeredCount }} / {{ attempt.total_questions }} terjawab
      </span>

      <button
        v-if="currentIndex < attempt.total_questions - 1"
        @click="goNext"
        class="btn-primary px-6 py-3"
      >
        Berikutnya →
      </button>
      <button
        v-else
        @click="confirmSubmit"
        :disabled="quiz.loading"
        class="btn-primary px-8 py-3 bg-green-600 hover:bg-green-700"
      >
        {{ quiz.loading ? '⏳ Menyimpan...' : '✅ Selesaikan Quiz' }}
      </button>
    </div>

    <!-- Submit Confirm Modal -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Selesaikan Quiz?</h3>
        <p class="text-gray-600 text-sm mb-4">
          Kamu sudah menjawab <strong>{{ answeredCount }}</strong> dari
          <strong>{{ attempt.total_questions }}</strong> soal.
          <span v-if="answeredCount < attempt.total_questions" class="text-red-500">
            {{ attempt.total_questions - answeredCount }} soal belum dijawab.
          </span>
        </p>
        <div class="flex gap-3">
          <button @click="showConfirm = false" class="btn-secondary flex-1">Batal</button>
          <button @click="handleSubmit" class="btn-primary flex-1">Ya, Kumpulkan</button>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="text-center py-16 text-gray-400">
    <p class="animate-pulse">Memuat quiz...</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useQuizStore } from '@/stores/quiz'
import SundaKeyboard from '@/components/keyboard/SundaKeyboard.vue'

const quiz   = useQuizStore()
const route  = useRoute()
const router = useRouter()

const attempt       = computed(() => quiz.currentAttempt)
const resultData    = ref<any>(null)
const currentIndex  = ref(0)
const answers       = ref<Record<number, string>>({})
const textAnswer    = ref('')
const showConfirm   = ref(false)
const timeLeft      = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

const currentQuestion = computed(() => attempt.value?.questions[currentIndex.value])

const answeredCount = computed(() => Object.keys(answers.value).length)

function selectAnswer(key: string) {
  if (!currentQuestion.value) return
  answers.value[currentQuestion.value.id] = key
}

function getOptionClass(key: string) {
  const qId = currentQuestion.value?.id
  if (!qId) return ''
  const selected = answers.value[qId]
  if (selected === key) return 'border-sunda-500 bg-sunda-50 text-sunda-800'
  return 'border-gray-200 bg-white hover:border-sunda-300 hover:bg-sunda-50 text-gray-700'
}

function saveCurrentTextAnswer() {
  if (currentQuestion.value && textAnswer.value.trim()) {
    answers.value[currentQuestion.value.id] = textAnswer.value.trim()
  }
}

function goNext() {
  saveCurrentTextAnswer()
  if (currentIndex.value < (attempt.value?.total_questions ?? 1) - 1) {
    currentIndex.value++
    textAnswer.value = answers.value[currentQuestion.value?.id ?? 0] ?? ''
  }
}

function goPrev() {
  saveCurrentTextAnswer()
  if (currentIndex.value > 0) {
    currentIndex.value--
    textAnswer.value = answers.value[currentQuestion.value?.id ?? 0] ?? ''
  }
}

function confirmSubmit() {
  saveCurrentTextAnswer()
  showConfirm.value = true
}

async function handleSubmit() {
  showConfirm.value = false
  if (!attempt.value) return

  const payload = attempt.value.questions.map((q) => ({
    question_id: q.id,
    answer: answers.value[q.id] ?? '',
    time_spent: 0,
  }))

  const result = await quiz.submitQuiz(attempt.value.attempt_id, payload)
  resultData.value = result
  clearInterval(timer!)
}

function questionTypeLabel(type: string) {
  return {
    sunda_to_latin: 'Sunda → Latin',
    latin_to_sunda: 'Latin → Sunda',
    multiple_choice: 'Pilihan Ganda',
  }[type] ?? type
}

const difficultyBadge = (d: string) => ({
  mudah: 'badge-green', sedang: 'badge-yellow', sulit: 'badge-red',
}[d] ?? 'badge')

function formatTime(seconds: number) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${String(s).padStart(2, '0')}`
}

function startTimer() {
  if (!attempt.value) return
  timeLeft.value = attempt.value.duration_minutes * 60
  timer = setInterval(() => {
    timeLeft.value--
    if (timeLeft.value <= 0) {
      clearInterval(timer!)
      handleSubmit()
    }
  }, 1000)
}

onMounted(async () => {
  // Jika belum ada attempt di store (mis. refresh page), mulai ulang
  if (!quiz.currentAttempt) {
    const settingId = Number(route.params.id)
    await quiz.startQuiz(settingId)
  }
  startTimer()
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
