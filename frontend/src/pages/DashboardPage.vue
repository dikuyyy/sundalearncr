<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ auth.user?.name }}! 👋</h1>
      <p class="text-gray-500 mt-1">Hari ini adalah waktu yang tepat untuk belajar aksara Sunda.</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="card animate-pulse h-24 bg-gray-100" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card bg-red-50 border border-red-200 text-red-700 text-sm">
      {{ error }}
    </div>

    <!-- Admin Dashboard -->
    <template v-else-if="auth.isAdmin && data">
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <StatCard label="Total Guru"   :value="data.stats.total_guru"   icon="👨‍🏫" color="blue" />
        <StatCard label="Total Siswa"  :value="data.stats.total_siswa"  icon="👨‍🎓" color="green" />
        <StatCard label="Total Materi" :value="data.stats.total_materi" icon="📚" color="yellow" />
        <StatCard label="Bank Soal"    :value="data.stats.total_soal"   icon="🗄️" color="purple" />
        <StatCard label="Quiz Selesai" :value="data.stats.quiz_selesai" icon="✅" color="green" />
      </div>

      <div class="card">
        <h3 class="font-semibold text-gray-800 mb-4">Pengguna Terbaru</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b">
                <th class="pb-2 pr-4">Nama</th>
                <th class="pb-2 pr-4">Email</th>
                <th class="pb-2 pr-4">Role</th>
                <th class="pb-2">Bergabung</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="u in data.recent_users" :key="u.id">
                <td class="py-2 pr-4 font-medium">{{ u.name }}</td>
                <td class="py-2 pr-4 text-gray-600">{{ u.email }}</td>
                <td class="py-2 pr-4"><span class="badge badge-green">{{ u.role }}</span></td>
                <td class="py-2 text-gray-500">{{ u.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Guru Dashboard -->
    <template v-else-if="auth.isGuru && data">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard label="Total Soal"   :value="data.stats.total_soal"      icon="🗄️" color="blue" />
        <StatCard label="Total Quiz"   :value="data.stats.total_quiz"       icon="📝" color="green" />
        <StatCard label="Total Percobaan" :value="data.stats.total_percobaan" icon="✏️" color="yellow" />
        <StatCard label="Rata-rata Nilai" :value="`${data.stats.rata_nilai}%`" icon="📈" color="purple" />
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Grafik Hasil Quiz -->
        <div class="card">
          <h3 class="font-semibold text-gray-800 mb-4">Rata-rata Nilai per Quiz</h3>
          <div v-if="data.quiz_graph?.length">
            <div v-for="quiz in data.quiz_graph" :key="quiz.quiz_title" class="mb-3">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-700 truncate max-w-[60%]">{{ quiz.quiz_title }}</span>
                <span class="font-semibold text-sunda-700">{{ quiz.avg_score }}%</span>
              </div>
              <div class="bg-gray-100 rounded-full h-2">
                <div
                  class="bg-sunda-500 rounded-full h-2 transition-all"
                  :style="{ width: quiz.avg_score + '%' }"
                />
              </div>
            </div>
          </div>
          <p v-else class="text-gray-400 text-sm text-center py-4">Belum ada data quiz.</p>
        </div>

        <!-- Hasil Terbaru -->
        <div class="card">
          <h3 class="font-semibold text-gray-800 mb-4">Hasil Quiz Terbaru</h3>
          <div class="space-y-2">
            <div
              v-for="r in data.recent_results"
              :key="r.tanggal + r.siswa"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg text-sm"
            >
              <div>
                <p class="font-medium text-gray-800">{{ r.siswa }}</p>
                <p class="text-gray-500 text-xs">{{ r.quiz }} · {{ r.tanggal }}</p>
              </div>
              <div class="text-right">
                <span
                  class="text-lg font-bold"
                  :class="r.skor >= 70 ? 'text-sunda-600' : 'text-red-500'"
                >{{ r.skor }}%</span>
                <p class="text-xs text-gray-400">{{ r.benar }}/{{ r.benar + r.salah }} benar</p>
              </div>
            </div>
            <p v-if="!data.recent_results?.length" class="text-gray-400 text-sm text-center py-4">Belum ada hasil.</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Siswa Dashboard -->
    <template v-else-if="auth.isSiswa && data">
      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <StatCard label="Rata-rata Nilai"  :value="`${data.stats.rata_nilai}%`"      icon="📊" color="blue" />
        <StatCard label="Nilai Tertinggi"  :value="`${data.stats.nilai_tertinggi}%`" icon="🏆" color="yellow" />
        <StatCard label="Quiz Selesai"     :value="`${data.stats.quiz_selesai} / ${data.stats.total_quiz_tersedia}`" icon="✏️" color="green" />
        <StatCard label="Ranking"          :value="data.stats.ranking ? `#${data.stats.ranking}` : '-'" icon="🏅" color="purple" />
      </div>

      <!-- Progress Materi -->
      <div class="card mb-6">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">Progress Belajar Materi</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ data.stats.materi_selesai }} dari {{ data.stats.total_materi }} materi selesai</p>
          </div>
          <span class="text-2xl font-bold text-sunda-600">{{ data.stats.progress_persen }}%</span>
        </div>
        <div class="bg-gray-100 rounded-full h-3">
          <div
            class="bg-gradient-to-r from-sunda-400 to-sunda-600 rounded-full h-3 transition-all duration-700"
            :style="{ width: data.stats.progress_persen + '%' }"
          />
        </div>
        <p v-if="data.stats.total_materi === 0" class="text-xs text-gray-400 mt-2">Belum ada materi yang tersedia.</p>
        <p v-else-if="data.stats.progress_persen === 100" class="text-xs text-green-600 font-medium mt-2">Semua materi selesai dipelajari!</p>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <RouterLink to="/materi" class="card flex items-center gap-4 hover:border-sunda-300 hover:shadow-md transition-all border-2 border-transparent">
          <div class="w-12 h-12 bg-sunda-100 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">📚</div>
          <div>
            <p class="font-semibold text-gray-800">Belajar Materi</p>
            <p class="text-xs text-gray-500">Pelajari aksara Sunda</p>
          </div>
        </RouterLink>
        <RouterLink to="/transliterasi" class="card flex items-center gap-4 hover:border-sunda-300 hover:shadow-md transition-all border-2 border-transparent">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">🔄</div>
          <div>
            <p class="font-semibold text-gray-800">Transliterasi</p>
            <p class="text-xs text-gray-500">Konversi teks Latin ↔ Sunda</p>
          </div>
        </RouterLink>
        <RouterLink to="/quiz" class="card flex items-center gap-4 hover:border-sunda-300 hover:shadow-md transition-all border-2 border-transparent">
          <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">✏️</div>
          <div>
            <p class="font-semibold text-gray-800">Ikuti Quiz</p>
            <p class="text-xs text-gray-500">Uji kemampuanmu</p>
          </div>
        </RouterLink>
      </div>

      <!-- Quiz Tersedia & Riwayat -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Daftar Quiz -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Quiz Tersedia</h3>
            <RouterLink to="/quiz" class="text-xs text-sunda-600 hover:text-sunda-800 font-medium">Lihat Semua →</RouterLink>
          </div>
          <div v-if="data.available_quizzes?.length" class="space-y-2">
            <div
              v-for="q in data.available_quizzes.slice(0, 5)"
              :key="q.id"
              class="flex items-center justify-between p-3 rounded-lg border"
              :class="q.is_completed ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'"
            >
              <div class="flex-1 min-w-0 mr-3">
                <p class="text-sm font-medium text-gray-800 truncate">{{ q.title }}</p>
                <p class="text-xs text-gray-500">{{ q.total_questions }} soal · {{ q.duration_minutes }} menit</p>
              </div>
              <span
                v-if="q.is_completed"
                class="flex-shrink-0 text-xs bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full"
              >Selesai</span>
              <RouterLink
                v-else
                :to="`/quiz/${q.id}/mulai`"
                class="flex-shrink-0 text-xs bg-sunda-600 text-white font-semibold px-3 py-1 rounded-full hover:bg-sunda-700 transition-colors"
              >Mulai</RouterLink>
            </div>
          </div>
          <p v-else class="text-gray-400 text-sm text-center py-6">Belum ada quiz tersedia.</p>
        </div>

        <!-- Riwayat Terbaru -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Riwayat Quiz Terakhir</h3>
            <RouterLink to="/quiz/riwayat" class="text-xs text-sunda-600 hover:text-sunda-800 font-medium">Lihat Semua →</RouterLink>
          </div>
          <div v-if="data.recent_attempts?.length" class="space-y-2">
            <div
              v-for="a in data.recent_attempts"
              :key="a.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-1 min-w-0 mr-3">
                <p class="font-medium text-sm text-gray-800 truncate">{{ a.quiz }}</p>
                <p class="text-xs text-gray-500">{{ a.tanggal }} · {{ a.benar }}/{{ a.benar + a.salah }} benar</p>
              </div>
              <span
                class="text-lg font-bold flex-shrink-0"
                :class="a.skor >= 70 ? 'text-sunda-600' : 'text-red-500'"
              >{{ a.skor }}%</span>
            </div>
          </div>
          <div v-else class="text-center py-6">
            <p class="text-gray-400 text-sm">Belum ada riwayat quiz.</p>
            <RouterLink to="/quiz" class="mt-2 inline-block text-sm text-sunda-600 hover:text-sunda-800 font-medium">Mulai quiz pertamamu →</RouterLink>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import StatCard from '@/components/common/StatCard.vue'

const auth    = useAuthStore()
const data    = ref<any>(null)
const loading = ref(true)
const error   = ref<string | null>(null)

onMounted(async () => {
  try {
    const { data: res } = await api.get('/dashboard')
    data.value = res
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Gagal memuat dashboard.'
  } finally {
    loading.value = false
  }
})
</script>
