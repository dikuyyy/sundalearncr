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
            <span>Pemilihan Soal:</span>
            <span class="badge text-xs" :class="q.selection_mode === 'manual' ? 'badge-blue' : 'badge-green'">
              {{ q.selection_mode === 'manual' ? '✋ Manual' : '🎲 Acak' }}
            </span>
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
          <button type="button" @click="modal = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
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

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
            <input v-model.number="form.duration_minutes" type="number" min="1" max="180" required class="input-field" />
          </div>

          <!-- Mode Pemilihan Soal -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cara Memilih Soal</label>
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                @click="switchToRandom"
                class="p-3 rounded-lg border-2 text-left transition-all"
                :class="form.selection_mode === 'random'
                  ? 'border-sunda-500 bg-sunda-50'
                  : 'border-gray-200 hover:border-sunda-300'"
              >
                <p class="font-medium text-sm text-gray-800">🎲 Acak Otomatis</p>
                <p class="text-xs text-gray-500 mt-0.5">Pilih pool soal, acak per siswa</p>
              </button>
              <button
                type="button"
                @click="switchToManual"
                class="p-3 rounded-lg border-2 text-left transition-all"
                :class="form.selection_mode === 'manual'
                  ? 'border-sunda-500 bg-sunda-50'
                  : 'border-gray-200 hover:border-sunda-300'"
              >
                <p class="font-medium text-sm text-gray-800">✋ Pilih Manual</p>
                <p class="text-xs text-gray-500 mt-0.5">Tentukan soal sendiri</p>
              </button>
            </div>
          </div>

          <!-- Jumlah soal per siswa — hanya untuk mode Acak -->
          <div v-if="form.selection_mode === 'random'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Soal per Siswa</label>
            <input
              v-model.number="form.total_questions"
              type="number"
              min="1"
              :max="form.question_ids.length || 999"
              required
              class="input-field"
              placeholder="Contoh: 10"
            />
            <p v-if="form.question_ids.length > 0 && form.total_questions > form.question_ids.length" class="text-xs text-red-500 mt-1">
              Tidak boleh melebihi jumlah soal yang dipilih ({{ form.question_ids.length }}).
            </p>
            <p v-else-if="form.question_ids.length > 0" class="text-xs text-gray-400 mt-1">
              Setiap siswa mendapat {{ form.total_questions }} soal acak dari {{ form.question_ids.length }} soal di bawah.
            </p>
          </div>

          <!-- Pilih soal (kedua mode) -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-sm font-medium text-gray-700">
                {{ form.selection_mode === 'random' ? 'Pool Soal' : 'Pilih Soal' }}
                <span class="text-sunda-600 font-semibold">({{ form.question_ids.length }} dipilih)</span>
              </label>
              <input
                v-model="questionSearch"
                type="text"
                placeholder="🔍 Cari soal..."
                class="input-field w-40 text-xs py-1"
              />
            </div>

            <div class="border border-gray-200 rounded-lg max-h-64 overflow-y-auto divide-y divide-gray-100">
              <p v-if="questionsLoading" class="text-center text-sm text-gray-400 py-6">Memuat soal...</p>
              <p v-else-if="filteredQuestions.length === 0" class="text-center text-sm text-gray-400 py-6">
                Tidak ada soal ditemukan.
              </p>
              <label
                v-for="qq in filteredQuestions"
                :key="qq.id"
                class="flex items-start gap-3 px-3 py-2.5 hover:bg-sunda-50 cursor-pointer transition-colors"
                :class="isSelected(qq.id) ? 'bg-sunda-50' : ''"
              >
                <input
                  type="checkbox"
                  :checked="isSelected(qq.id)"
                  @change="toggleQuestion(qq.id)"
                  class="w-4 h-4 rounded text-sunda-600 mt-0.5 flex-shrink-0"
                />
                <div class="flex-1 min-w-0">
                  <p
                    class="text-sm text-gray-800 truncate"
                    :class="qq.type === 'sunda_to_latin' ? 'font-sunda text-base' : ''"
                  >{{ qq.question_text }}</p>
                  <div class="flex gap-1.5 mt-1">
                    <span class="badge badge-blue text-[10px]">{{ typeLabel(qq.type) }}</span>
                    <span class="badge text-[10px]" :class="diffBadge(qq.difficulty)">{{ qq.difficulty }}</span>
                  </div>
                </div>
              </label>
            </div>
            <p class="text-xs text-gray-400 mt-1">
              <template v-if="form.selection_mode === 'random'">
                Sistem mengambil {{ form.total_questions || '...' }} soal acak dari pool ini — tiap siswa bisa dapat soal berbeda.
              </template>
              <template v-else>
                Semua soal yang dipilih akan ditampilkan. Jumlah soal mengikuti banyaknya yang dipilih.
              </template>
            </p>
          </div>

          <!-- Opsi lain -->
          <div class="space-y-2 pt-2">
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
import { ref, computed, onMounted } from 'vue'
import { useQuizStore } from '@/stores/quiz'
import api from '@/api/axios'

const quiz     = useQuizStore()
const settings = ref<any[]>([])
const loading  = ref(true)
const modal    = ref(false)
const saving   = ref(false)
const formError = ref<string | null>(null)

// Bank soal untuk pemilihan manual
const allQuestions   = ref<any[]>([])
const questionsLoading = ref(false)
const questionSearch = ref('')

const emptyForm = () => ({
  id: null as number | null,
  title: '',
  description: '',
  total_questions: 10,
  duration_minutes: 30,
  difficulty: 'campuran',
  selection_mode: 'random' as 'random' | 'manual',
  question_ids: [] as number[],
  shuffle_questions: true,
  shuffle_options: true,
  is_active: true,
})

const form = ref(emptyForm())

const filteredQuestions = computed(() => {
  const s = questionSearch.value.trim().toLowerCase()
  if (!s) return allQuestions.value
  return allQuestions.value.filter((q) => q.question_text?.toLowerCase().includes(s))
})

function isSelected(id: number) {
  return form.value.question_ids.includes(id)
}

function toggleQuestion(id: number) {
  const idx = form.value.question_ids.indexOf(id)
  if (idx === -1) form.value.question_ids.push(id)
  else form.value.question_ids.splice(idx, 1)
}

async function fetchQuestions() {
  if (allQuestions.value.length) return // cache: sudah dimuat
  questionsLoading.value = true
  try {
    const { data } = await api.get('/questions', { params: { all: 1 } })
    allQuestions.value = data.data ?? []
  } finally {
    questionsLoading.value = false
  }
}

function switchToRandom() {
  form.value.selection_mode = 'random'
  fetchQuestions()
}

function switchToManual() {
  form.value.selection_mode = 'manual'
  fetchQuestions()
}

function openModal(q?: any) {
  formError.value = null
  questionSearch.value = ''
  if (q) {
    form.value = {
      ...emptyForm(),
      ...q,
      question_ids: q.question_ids ?? [],
      selection_mode: q.selection_mode ?? 'random',
    }
    fetchQuestions()
  } else {
    form.value = emptyForm()
    fetchQuestions()
  }
  modal.value = true
}

async function fetchSettings() {
  loading.value = true
  const data = await quiz.fetchSettings()
  settings.value = data.data ?? []
  loading.value  = false
}

async function saveSetting() {
  if (form.value.question_ids.length === 0) {
    formError.value = 'Pilih minimal 1 soal.'
    return
  }
  if (form.value.selection_mode === 'random') {
    if (!form.value.total_questions || form.value.total_questions < 1) {
      formError.value = 'Jumlah soal per siswa minimal 1.'
      return
    }
    if (form.value.total_questions > form.value.question_ids.length) {
      formError.value = `Jumlah soal (${form.value.total_questions}) tidak boleh melebihi soal yang dipilih (${form.value.question_ids.length}).`
      return
    }
  }
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
  try {
    await quiz.deleteSetting(id)
    fetchSettings()
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'Gagal menghapus quiz.')
  }
}

const typeLabel = (t: string) => ({ sunda_to_latin: 'Sunda→Latin', latin_to_sunda: 'Latin→Sunda', multiple_choice: 'Pilihan Ganda' }[t] ?? t)
const diffBadge = (d: string) => ({ mudah: 'badge-green', sedang: 'badge-yellow', sulit: 'badge-red', campuran: 'badge-blue' }[d] ?? '')

onMounted(fetchSettings)
</script>
