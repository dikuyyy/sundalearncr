<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Kelola Guru</h1>
        <p class="text-gray-500 mt-1">Tambah dan kelola akun guru SundaLearn.</p>
      </div>
      <button @click="openModal()" class="btn-primary">+ Tambah Guru</button>
    </div>

    <!-- Search -->
    <div class="mb-4">
      <input v-model="search" class="input-field max-w-xs" placeholder="🔍 Cari nama atau email..." />
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Nama</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Email</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">NIP</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Akses Data</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Status</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="t in teachers" :key="t.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">{{ t.name }}</td>
              <td class="px-4 py-3 text-gray-600">{{ t.email }}</td>
              <td class="px-4 py-3 text-gray-500">{{ t.nip || '-' }}</td>
              <td class="px-4 py-3">
                <span class="badge text-xs" :class="t.can_view_all ? 'badge-blue' : 'badge-yellow'">
                  {{ t.can_view_all ? '🌐 Semua Data' : '🔒 Data Sendiri' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="badge text-xs" :class="t.is_active ? 'badge-green' : 'badge-red'">
                  {{ t.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button @click="openModal(t)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                  <button @click="remove(t.id)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :current-page="page" :last-page="lastPage" :total="total" @change="goPage" />

    <!-- Modal -->
    <div v-if="modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="font-bold text-gray-900">{{ form.id ? 'Edit Guru' : 'Tambah Guru Baru' }}</h3>
          <button @click="modal = false" class="text-gray-400 text-xl">✕</button>
        </div>
        <form @submit.prevent="save" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input v-model="form.name" required class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" required class="input-field" />
          </div>
          <div v-if="!form.id">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input v-model="form.password" type="password" required class="input-field" placeholder="Min. 8 karakter" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
              <input v-model="form.nip" class="input-field" placeholder="Opsional" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
              <input v-model="form.phone" class="input-field" placeholder="Opsional" />
            </div>
          </div>
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            <label class="flex items-start gap-2 cursor-pointer">
              <input v-model="form.can_view_all" type="checkbox" class="w-4 h-4 rounded text-sunda-600 mt-0.5" />
              <span class="text-sm text-gray-700">
                <span class="font-medium">Akses semua data</span>
                <span class="block text-xs text-gray-500 mt-0.5">
                  Jika dicentang, guru bisa melihat <strong>semua</strong> bank soal, quiz, dan hasil siswa.
                  Jika tidak, guru hanya melihat data dari quiz/soal yang ia buat sendiri.
                </span>
              </span>
            </label>
          </div>
          <div v-if="form.id">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded text-sunda-600" />
              <span class="text-sm text-gray-700">Akun Aktif</span>
            </label>
          </div>
          <div v-if="formError" class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded text-sm">{{ formError }}</div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="modal = false" class="btn-secondary flex-1">Batal</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Menyimpan...' : (form.id ? 'Perbarui' : 'Tambahkan') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import api from '@/api/axios'
import Pagination from '@/components/common/Pagination.vue'

const teachers  = ref<any[]>([])
const modal     = ref(false)
const saving    = ref(false)
const formError = ref<string | null>(null)
const search    = ref('')
const page      = ref(1)
const lastPage  = ref(1)
const total     = ref(0)

const emptyForm = () => ({
  id: null as number | null,
  name: '', email: '', password: '', nip: '', phone: '', is_active: true,
  can_view_all: false,
})
const form = ref(emptyForm())

async function fetchTeachers() {
  const { data } = await api.get('/admin/teachers', {
    params: { search: search.value || undefined, page: page.value },
  })
  teachers.value = data.data ?? []
  lastPage.value = data.last_page ?? 1
  total.value    = data.total ?? 0
}

function goPage(p: number) {
  page.value = p
  fetchTeachers()
}

// Pencarian: kembali ke halaman 1 lalu muat ulang (debounce 300ms)
let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(search, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchTeachers()
  }, 300)
})

function openModal(t?: any) {
  formError.value = null
  form.value = t ? { ...t, password: '' } : emptyForm()
  modal.value = true
}

async function save() {
  saving.value    = true
  formError.value = null
  try {
    const payload = { ...form.value }
    if (!payload.password) delete (payload as any).password
    const isNew = !form.value.id
    if (form.value.id) {
      await api.put(`/admin/teachers/${form.value.id}`, payload)
    } else {
      await api.post('/admin/teachers', payload)
    }
    modal.value = false
    if (isNew) page.value = 1 // guru baru tampil di halaman pertama (urutan terbaru)
    fetchTeachers()
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Gagal menyimpan data guru.'
  } finally {
    saving.value = false
  }
}

async function remove(id: number) {
  if (!confirm('Hapus guru ini?')) return
  await api.delete(`/admin/teachers/${id}`)
  // jika item terakhir di halaman ini dihapus, mundur satu halaman
  if (teachers.value.length === 1 && page.value > 1) page.value--
  fetchTeachers()
}

onMounted(fetchTeachers)
</script>
