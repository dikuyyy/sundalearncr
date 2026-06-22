<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Bank Soal</h1>
        <p class="text-gray-500 mt-1">Kelola semua soal quiz aksara Sunda.</p>
      </div>
      <button @click="openModal()" class="btn-primary">+ Tambah Soal</button>
    </div>

    <!-- Filter -->
    <div class="flex flex-wrap gap-3 mb-6">
      <select v-model="filterType" class="input-field w-auto text-sm">
        <option value="">Semua Tipe</option>
        <option value="sunda_to_latin">Sunda → Latin</option>
        <option value="latin_to_sunda">Latin → Sunda</option>
        <option value="multiple_choice">Pilihan Ganda</option>
      </select>
      <select v-model="filterDiff" class="input-field w-auto text-sm">
        <option value="">Semua Kesulitan</option>
        <option value="mudah">Mudah</option>
        <option value="sedang">Sedang</option>
        <option value="sulit">Sulit</option>
      </select>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">#</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Soal</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Tipe</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Kesulitan</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Jawaban</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="6" class="text-center py-8 text-gray-400">Memuat...</td>
            </tr>
            <tr v-for="(q, i) in questions" :key="q.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-500">{{ i + 1 }}</td>
              <td class="px-4 py-3 max-w-xs">
                <span
                  class="text-gray-800 line-clamp-2 block"
                  :class="q.type === 'sunda_to_latin' ? 'font-sunda text-lg leading-relaxed' : 'text-sm'"
                >{{ q.question_text }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="badge badge-blue text-xs">{{ typeLabel(q.type) }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="badge text-xs" :class="diffBadge(q.difficulty)">{{ q.difficulty }}</span>
              </td>
              <td class="px-4 py-3 text-sunda-700 font-medium">
                <span :class="q.type === 'latin_to_sunda' ? 'font-sunda text-lg leading-relaxed' : 'text-sm'">
                  {{ q.correct_answer }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button @click="openModal(q)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                  <button @click="deleteQuestion(q.id)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="!loading && questions.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-400">Belum ada soal.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :current-page="page" :last-page="lastPage" :total="total" @change="goPage" />

    <!-- Modal Tambah/Edit Soal -->
    <div v-if="modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="font-bold text-gray-900">{{ form.id ? 'Edit Soal' : 'Tambah Soal Baru' }}</h3>
          <button @click="modal = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
        </div>
        <form @submit.prevent="saveQuestion" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="form-type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Soal</label>
              <select id="form-type" v-model="form.type" class="input-field">
                <option value="sunda_to_latin">Sunda → Latin</option>
                <option value="latin_to_sunda">Latin → Sunda</option>
                <option value="multiple_choice">Pilihan Ganda</option>
              </select>
            </div>
            <div>
              <label for="form-difficulty" class="block text-sm font-medium text-gray-700 mb-1">Kesulitan</label>
              <select id="form-difficulty" v-model="form.difficulty" class="input-field">
                <option value="mudah">Mudah</option>
                <option value="sedang">Sedang</option>
                <option value="sulit">Sulit</option>
              </select>
            </div>
          </div>

          <div>
            <label for="form-question" class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
            <textarea
              id="form-question"
              v-model="form.question_text"
              @focus="kbField = 'question'"
              class="input-field min-h-[80px] font-sunda text-lg"
              :placeholder="form.type === 'sunda_to_latin' ? 'Tulis aksara Sunda...' : 'Tulis pertanyaan...'"
            />
          </div>

          <div>
            <label for="form-answer" class="block text-sm font-medium text-gray-700 mb-1">Jawaban Benar</label>
            <input
              id="form-answer"
              v-model="form.correct_answer"
              @focus="kbField = 'answer'"
              class="input-field font-sunda text-lg"
              :placeholder="form.type === 'latin_to_sunda' ? 'Tulis aksara Sunda...' : 'Jawaban yang benar'"
            />
          </div>

          <!-- Opsi Pilihan Ganda -->
          <div v-if="form.type === 'multiple_choice'" class="space-y-2">
            <p class="text-sm font-medium text-gray-700">Pilihan Jawaban (A-D)</p>
            <div v-for="key in optionKeys" :key="key" class="flex gap-2 items-center">
              <label :for="`form-option-${key}`" class="w-6 font-bold text-gray-500">{{ key.toUpperCase() }}</label>
              <input
                :id="`form-option-${key}`"
                v-model="form.options[key]"
                @focus="kbField = key"
                class="input-field font-sunda text-lg"
                :placeholder="`Pilihan ${key.toUpperCase()}`"
              />
            </div>
          </div>

          <div>
            <label for="form-explanation" class="block text-sm font-medium text-gray-700 mb-1">Penjelasan (Opsional)</label>
            <textarea
              id="form-explanation"
              v-model="form.explanation"
              @focus="kbField = 'explanation'"
              class="input-field min-h-[60px] font-sunda"
              placeholder="Penjelasan jawaban... (boleh campuran Latin & aksara Sunda)"
            />
          </div>

          <!-- Keyboard Aksara Sunda — mengikuti kolom yang sedang dipilih -->
          <div>
            <p class="text-xs text-gray-500 mb-1">
              ⌨️ Keyboard mengisi ke: <strong class="text-sunda-700">{{ kbFieldLabel }}</strong>
              — klik kolom yang ingin diisi, lalu ketik aksara.
            </p>
            <SundaKeyboard @insert="kbInsert" @backspace="kbBackspace" @clear="kbClear" />
          </div>

          <div v-if="formError" class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded text-sm">{{ formError }}</div>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="modal = false" class="btn-secondary flex-1">Batal</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Menyimpan...' : (form.id ? 'Perbarui Soal' : 'Simpan Soal') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/api/axios'
import SundaKeyboard from '@/components/keyboard/SundaKeyboard.vue'
import Pagination from '@/components/common/Pagination.vue'

const questions              = ref<any[]>([])
const loading                = ref(true)
const modal                  = ref(false)
const saving                 = ref(false)
const formError              = ref<string | null>(null)
const filterType  = ref('')
const filterDiff  = ref('')
const page        = ref(1)
const lastPage    = ref(1)
const total       = ref(0)

// Keyboard mengikuti field yang sedang fokus (pertanyaan, jawaban, opsi, atau penjelasan)
const optionKeys = ['a', 'b', 'c', 'd'] as const
type KbField = 'question' | 'answer' | 'a' | 'b' | 'c' | 'd' | 'explanation'
const kbField = ref<KbField>('question')
const kbFieldLabel = computed(() => {
  const f = kbField.value
  if (f === 'question') return 'Pertanyaan'
  if (f === 'answer') return 'Jawaban Benar'
  if (f === 'explanation') return 'Penjelasan'
  return `Pilihan ${f.toUpperCase()}`
})
function kbGet(): string {
  const f = kbField.value
  if (f === 'question') return form.value.question_text
  if (f === 'answer') return form.value.correct_answer
  if (f === 'explanation') return form.value.explanation
  return form.value.options[f]
}
function kbSet(v: string) {
  const f = kbField.value
  if (f === 'question') form.value.question_text = v
  else if (f === 'answer') form.value.correct_answer = v
  else if (f === 'explanation') form.value.explanation = v
  else form.value.options[f] = v
}
function kbInsert(char: string) { kbSet(kbGet() + char) }
function kbBackspace() { kbSet(kbGet().slice(0, -1)) }
function kbClear() { kbSet('') }

const emptyForm = () => ({
  id: null as number | null,
  type: 'sunda_to_latin',
  difficulty: 'sedang',
  question_text: '',
  correct_answer: '',
  options: { a: '', b: '', c: '', d: '' } as Record<string, string>,
  explanation: '',
})

const form = ref(emptyForm())

function openModal(q?: any) {
  formError.value = null
  kbField.value = 'question'
  if (q) {
    form.value = {
      id: q.id,
      type: q.type,
      difficulty: q.difficulty,
      question_text: q.question_text,
      correct_answer: q.correct_answer,
      options: q.options ?? { a: '', b: '', c: '', d: '' },
      explanation: q.explanation ?? '',
    }
  } else {
    form.value = emptyForm()
  }
  modal.value = true
}

async function fetchQuestions() {
  loading.value = true
  const { data } = await api.get('/questions', {
    params: {
      type: filterType.value || undefined,
      difficulty: filterDiff.value || undefined,
      page: page.value,
    },
  })
  questions.value = data.data ?? []
  lastPage.value  = data.last_page ?? 1
  total.value     = data.total ?? 0
  loading.value   = false
}

function goPage(p: number) {
  page.value = p
  fetchQuestions()
}

// Ganti filter → kembali ke halaman 1 lalu muat ulang
watch([filterType, filterDiff], () => {
  page.value = 1
  fetchQuestions()
})

async function saveQuestion() {
  saving.value  = true
  formError.value = null
  try {
    const payload: any = { ...form.value }
    if (form.value.type !== 'multiple_choice') delete payload.options
    const isNew = !form.value.id
    if (form.value.id) {
      await api.put(`/questions/${form.value.id}`, payload)
    } else {
      await api.post('/questions', payload)
    }
    modal.value = false
    if (isNew) page.value = 1 // soal baru tampil di halaman pertama (urutan terbaru)
    fetchQuestions()
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Gagal menyimpan soal.'
  } finally {
    saving.value = false
  }
}

async function deleteQuestion(id: number) {
  if (!confirm('Hapus soal ini?')) return
  await api.delete(`/questions/${id}`)
  // jika item terakhir di halaman ini dihapus, mundur satu halaman
  if (questions.value.length === 1 && page.value > 1) page.value--
  fetchQuestions()
}

const typeLabel = (t: string) => ({ sunda_to_latin: 'Sunda→Latin', latin_to_sunda: 'Latin→Sunda', multiple_choice: 'Pilihan Ganda' }[t] ?? t)
const diffBadge = (d: string) => ({ mudah: 'badge-green', sedang: 'badge-yellow', sulit: 'badge-red' }[d] ?? '')

onMounted(fetchQuestions)
</script>
