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
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard label="Nilai Terakhir"  :value="`${data.stats.nilai_terakhir}%`" icon="🎯" color="blue" />
        <StatCard label="Rata-rata Nilai" :value="`${data.stats.rata_nilai}%`"     icon="📊" color="green" />
        <StatCard label="Total Quiz"      :value="data.stats.total_quiz"            icon="✏️" color="yellow" />
        <StatCard label="Materi Selesai"  :value="`${data.stats.materi_selesai}/${data.stats.total_materi}`" icon="📚" color="purple" />
      </div>

      <!-- Progress Belajar -->
      <div class="card mb-6">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-gray-800">Progress Belajar</h3>
          <span class="text-sunda-600 font-bold">{{ data.stats.progress_persen }}%</span>
        </div>
        <div class="bg-gray-100 rounded-full h-4">
          <div
            class="bg-gradient-to-r from-sunda-400 to-sunda-600 rounded-full h-4 transition-all duration-700"
            :style="{ width: data.stats.progress_persen + '%' }"
          />
        </div>
        <p class="text-sm text-gray-500 mt-2">
          {{ data.stats.materi_selesai }} dari {{ data.stats.total_materi }} materi selesai dipelajari
        </p>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <RouterLink to="/materi" class="card flex items-center gap-4 hover:border-sunda-300 hover:shadow-md transition-all border-2 border-transparent cursor-pointer">
          <div class="w-12 h-12 bg-sunda-100 rounded-xl flex items-center justify-center text-2xl">📚</div>
          <div>
            <p class="font-semibold text-gray-800">Belajar Materi</p>
            <p class="text-xs text-gray-500">Pelajari aksara Sunda</p>
          </div>
        </RouterLink>
        <RouterLink to="/transliterasi" class="card flex items-center gap-4 hover:border-sunda-300 hover:shadow-md transition-all border-2 border-transparent cursor-pointer">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-2xl">🔄</div>
          <div>
            <p class="font-semibold text-gray-800">Transliterasi</p>
            <p class="text-xs text-gray-500">Konversi teks</p>
          </div>
        </RouterLink>
        <RouterLink to="/quiz" class="card flex items-center gap-4 hover:border-sunda-300 hover:shadow-md transition-all border-2 border-transparent cursor-pointer">
          <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center text-2xl">✏️</div>
          <div>
            <p class="font-semibold text-gray-800">Ikuti Quiz</p>
            <p class="text-xs text-gray-500">Uji kemampuanmu</p>
          </div>
        </RouterLink>
      </div>

      <!-- Riwayat Terbaru -->
      <div class="card">
        <h3 class="font-semibold text-gray-800 mb-4">Riwayat Quiz Terakhir</h3>
        <div class="space-y-2">
          <div
            v-for="a in data.recent_attempts"
            :key="a.tanggal + a.quiz"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div>
              <p class="font-medium text-sm text-gray-800">{{ a.quiz }}</p>
              <p class="text-xs text-gray-500">{{ a.tanggal }} · {{ a.benar }}/{{ a.benar + a.salah }} benar</p>
            </div>
            <span
              class="text-xl font-bold"
              :class="a.skor >= 70 ? 'text-sunda-600' : 'text-red-500'"
            >{{ a.skor }}%</span>
          </div>
          <p v-if="!data.recent_attempts?.length" class="text-gray-400 text-sm text-center py-4">Belum ada riwayat quiz.</p>
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

onMounted(async () => {
  try {
    const { data: res } = await api.get('/dashboard')
    data.value = res
  } finally {
    loading.value = false
  }
})
</script>
