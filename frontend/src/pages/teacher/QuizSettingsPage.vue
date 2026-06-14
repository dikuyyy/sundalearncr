<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Quiz</h1>
        <p class="text-gray-500 mt-1">Buat dan kelola konfigurasi quiz untuk siswa.</p>
      </div>
      <button @click="openModal()" class="btn-primary">+ Buat Quiz Baru</button>
    </div>

    <!-- Cards -->
    <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="card animate-pulse h-48 bg-gray-100" />
    </div>

    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="q in settings"
        :key="q.id"
        class="card hover:shadow-md transition-all"
      >
        <div class="flex items-start justify-between mb-3">
          <h3 class="font-semibold text-gray-900 flex-1">{{ q.title }}</h3>
          <span
            class="w-3 h-3 rounded-full flex-shrink-0 ml-2 mt-1"
            :class="q.is_active ? 'bg-green-400' : 'bg-gray-300'"
            :title="q.is_active ? 'Aktif' : 'Nonaktif'"
          />
        </div>
        <p v-if="q.description" class="text-sm text-gray-500 mb-4 line-clamp-2">{{ q.description }}</p>

        <div class="space-y-2 text-sm text-gray-600 mb-4">
          <div class="flex justify-between">
            <span>Jumlah Soal:</span>
            <strong>{{ q.total_questions }}</strong>
          </div>
          <div class="flex justify-between">
            <span>Durasi:</span>
            <strong>{{ q.duration_minutes }} menit</strong>
          </div>
          <div class="flex justify-between">
            <span>Kesulitan:</span>
            <span class="badge text-xs" :class="diffBadge(q.difficulty)">{{ q.difficulty }}</span>
          </div>
          <div class="flex justify-between">
            <span>Acak Soal:</span>
            <span>{{ q.shuffle_questions ? '✅ Ya' : '❌ Tidak' }}</span>
          </div>
        </div>

        <div class="flex gap-2">
          <button @click="openModal(q)" class="btn-secondary flex-1 text-sm py-2">Edit</button>
          <button @click="deleteSetting(q.id)" class="btn-danger flex-1 text-sm py-2">Hapus</button>
        </div>
      </div>
    </div>

    <div v-if="!loading && settings.length === 0" class="text-center py-16 text-gray-400">
      <p class="text-4xl mb-3">⚙️</p>
      <p class="font-medium">Belum ada pengaturan quiz. Buat yang pertama!</p>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="font-bold text-gray-900">{{ form.id ? 'Edit Quiz' : 'Buat Quiz Baru' }}</h3>
          <button @click="modal = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
        </div>
        <form @submit.prevent="saveSetting" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Quiz</label>
            <input v-model="form.title" required class="input-field" placeholder="Contoh: Quiz Huruf Dasar Level Mudah" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea v-model="form.description" class="input-field min-h-[80px]" placeholder="Deskripsi singkat quiz..." />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Soal</label>
              <input v-model.number="form.total_questions" type="number" min="1" max="100" required class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
              <input v-model.number="form.duration_minutes" type="number" min="1" max="180" required class="input-field" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kesulitan</label>
            <select v-model="form.difficulty" class="input-field">
              <option value="mudah">Mudah</option>
              <option value="sedang">Sedang</option>
              <option value="sulit">Sulit</option>
              <option value="campuran">Campuran (Semua Level)</option>
            </select>
          </div>
          <div class="space-y-2">
            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="form.shuffle_questions" type="checkbox" class="w-4 h-4 rounded text-sunda-600" />
              <span class="text-sm text-gray-700">Acak urutan soal (Fisher-Yates Shuffle)</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="form.shuffle_options" type="checkbox" class="w-4 h-4 rounded text-sunda-600" />
              <span class="text-sm text-gray-700">Acak pilihan jawaban</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded text-sunda-600" />
              <span class="text-sm text-gray-700">Aktifkan quiz (siswa bisa mengerjakan)</span>
            </label>
          </div>
          <div v-if="formError" class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded text-sm">{{ formError }}</div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="modal = false" class="btn-secondary flex-1">Batal</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Menyimpan...' : (form.id ? 'Perbarui' : 'Buat Quiz') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useQuizStore } from '@/stores/quiz'

const quiz     = useQuizStore()
const settings = ref<any[]>([])
const loading  = ref(true)
const modal    = ref(false)
const saving   = ref(false)
const formError = ref<string | null>(null)

const emptyForm = () => ({
  id: null as number | null,
  title: '',
  description: '',
  total_questions: 10,
  duration_minutes: 30,
  difficulty: 'campuran',
  shuffle_questions: true,
  shuffle_options: true,
  is_active: true,
})

const form = ref(emptyForm())

function openModal(q?: any) {
  formError.value = null
  form.value = q ? { ...q } : emptyForm()
  modal.value = true
}

async function fetchSettings() {
  loading.value = true
  const data = await quiz.fetchSettings()
  settings.value = data.data ?? []
  loading.value  = false
}

async function saveSetting() {
  saving.value = true
  formError.value = null
  try {
    if (form.value.id) {
      await quiz.updateSetting(form.value.id, form.value)
    } else {
      await quiz.createSetting(form.value)
    }
    modal.value = false
    fetchSettings()
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Gagal menyimpan quiz.'
  } finally {
    saving.value = false
  }
}

async function deleteSetting(id: number) {
  if (!confirm('Hapus pengaturan quiz ini?')) return
  await quiz.deleteSetting(id)
  fetchSettings()
}

const diffBadge = (d: string) => ({ mudah: 'badge-green', sedang: 'badge-yellow', sulit: 'badge-red', campuran: 'badge-blue' }[d] ?? '')

onMounted(fetchSettings)
</script>
