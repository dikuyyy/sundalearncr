import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'

export interface QuizQuestion {
  id: number
  type: 'sunda_to_latin' | 'latin_to_sunda' | 'multiple_choice'
  difficulty: string
  question_text: string
  options?: Record<string, string>
}

export interface QuizAttempt {
  attempt_id: number
  total_questions: number
  duration_minutes: number
  questions: QuizQuestion[]
}

export interface QuizResult {
  score: number
  correct_answers: number
  wrong_answers: number
  total_questions: number
  time_spent: number
  attempt_id: number
}

export interface QuizSetting {
  id: number
  title: string
  description: string
  total_questions: number
  duration_minutes: number
  difficulty: string
  creator: string
}

export const useQuizStore = defineStore('quiz', () => {
  const available     = ref<QuizSetting[]>([])
  const currentAttempt = ref<QuizAttempt | null>(null)
  const lastResult    = ref<QuizResult | null>(null)
  const history       = ref<any[]>([])
  const loading       = ref(false)
  const error         = ref<string | null>(null)

  async function fetchAvailable() {
    loading.value = true
    try {
      const { data } = await api.get('/quiz/available')
      available.value = data.data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Gagal memuat daftar quiz.'
    } finally {
      loading.value = false
    }
  }

  async function startQuiz(quizSettingId: number): Promise<QuizAttempt> {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.post('/quiz/start', { quiz_setting_id: quizSettingId })
      currentAttempt.value = data
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Gagal memulai quiz.'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function submitQuiz(attemptId: number, answers: Array<{
    question_id: number
    answer: string
    time_spent?: number
  }>): Promise<QuizResult> {
    loading.value = true
    error.value   = null
    try {
      const { data } = await api.post('/quiz/submit', {
        attempt_id: attemptId,
        answers,
      })
      lastResult.value     = data
      currentAttempt.value = null
      return data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Gagal mengumpulkan jawaban.'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchHistory() {
    const { data } = await api.get('/quiz/history')
    history.value  = data.data ?? data
    return data
  }

  async function fetchReview(attemptId: number) {
    const { data } = await api.get(`/quiz/attempts/${attemptId}/review`)
    return data.data
  }

  // ─── Guru: pengaturan quiz ───
  async function fetchSettings() {
    const { data } = await api.get('/quiz/settings')
    return data
  }

  async function createSetting(payload: Partial<QuizSetting>) {
    const { data } = await api.post('/quiz/settings', payload)
    return data
  }

  async function updateSetting(id: number, payload: Partial<QuizSetting>) {
    const { data } = await api.put(`/quiz/settings/${id}`, payload)
    return data
  }

  async function deleteSetting(id: number) {
    return api.delete(`/quiz/settings/${id}`)
  }

  async function fetchTeacherStats() {
    const { data } = await api.get('/quiz/teacher/stats')
    return data.data
  }

  async function fetchStudentResults() {
    const { data } = await api.get('/quiz/teacher/students')
    return data
  }

  function clearAttempt() {
    currentAttempt.value = null
  }

  return {
    available, currentAttempt, lastResult, history, loading, error,
    fetchAvailable, startQuiz, submitQuiz, fetchHistory, fetchReview,
    fetchSettings, createSetting, updateSetting, deleteSetting,
    fetchTeacherStats, fetchStudentResults, clearAttempt,
  }
})
