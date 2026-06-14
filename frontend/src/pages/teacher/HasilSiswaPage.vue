<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Hasil Quiz Siswa</h1>
      <p class="text-gray-500 mt-1">Monitor perkembangan nilai siswa dari setiap quiz.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <StatCard label="Total Percobaan" :value="stats?.total_attempts ?? 0"         icon="✏️"  color="blue" />
      <StatCard label="Rata-rata Nilai"  :value="`${stats?.average_score ?? 0}%`"   icon="📊"  color="green" />
      <StatCard label="Nilai Tertinggi"  :value="`${stats?.highest_score ?? 0}%`"   icon="🏆"  color="yellow" />
      <StatCard label="Nilai Terendah"   :value="`${stats?.lowest_score ?? 0}%`"    icon="📉"  color="red" />
    </div>

    <!-- Tabel Hasil -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Siswa</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Quiz</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Nilai</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Benar</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Salah</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Waktu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="r in results" :key="r.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">{{ r.user?.name }}</td>
              <td class="px-4 py-3 text-gray-600">{{ r.quiz_setting?.title }}</td>
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
            <tr v-if="!loading && results.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-400">Belum ada hasil quiz.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useQuizStore } from '@/stores/quiz'
import StatCard from '@/components/common/StatCard.vue'

const quiz    = useQuizStore()
const results = ref<any[]>([])
const stats   = ref<any>(null)
const loading = ref(true)

function formatDate(dt: string) {
  if (!dt) return '-'
  return new Date(dt).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })
}

function formatDuration(s: number) {
  if (!s) return '-'
  const m = Math.floor(s / 60)
  const sec = s % 60
  return `${m}m ${sec}s`
}

onMounted(async () => {
  const [s, r] = await Promise.all([
    quiz.fetchTeacherStats(),
    quiz.fetchStudentResults(),
  ])
  stats.value   = s
  results.value = r.data ?? []
  loading.value = false
})
</script>
