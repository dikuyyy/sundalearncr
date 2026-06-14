<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Kelola Siswa</h1>
        <p class="text-gray-500 mt-1">Tambah dan kelola akun siswa SundaLearn.</p>
      </div>
      <button @click="openModal()" class="btn-primary">+ Tambah Siswa</button>
    </div>

    <div class="mb-4">
      <input v-model="search" class="input-field max-w-xs" placeholder="🔍 Cari nama atau NISN..." />
    </div>

    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Nama</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Email</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">NISN</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Status</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="s in students" :key="s.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">{{ s.name }}</td>
              <td class="px-4 py-3 text-gray-600">{{ s.email }}</td>
              <td class="px-4 py-3 text-gray-500">{{ s.nisn || '-' }}</td>
              <td class="px-4 py-3">
                <span class="badge text-xs" :class="s.is_active ? 'badge-green' : 'badge-red'">
                  {{ s.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button @click="openModal(s)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                  <button @click="remove(s.id)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="font-bold text-gray-900">{{ form.id ? 'Edit Siswa' : 'Tambah Siswa Baru' }}</h3>
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
            <input v-model="form.password" type="password" required class="input-field" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
              <input v-model="form.nisn" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
              <input v-model="form.phone" class="input-field" />
            </div>
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
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const students  = ref<any[]>([])
const modal     = ref(false)
const saving    = ref(false)
const formError = ref<string | null>(null)
const search    = ref('')

const emptyForm = () => ({
  id: null as number | null,
  name: '', email: '', password: '', nisn: '', phone: '', is_active: true,
})
const form = ref(emptyForm())

async function fetchStudents() {
  const { data } = await api.get('/admin/students', { params: { search: search.value || undefined } })
  students.value = data.data ?? []
}

function openModal(s?: any) {
  formError.value = null
  form.value = s ? { ...s, password: '' } : emptyForm()
  modal.value = true
}

async function save() {
  saving.value    = true
  formError.value = null
  try {
    const payload = { ...form.value }
    if (!payload.password) delete (payload as any).password
    if (form.value.id) {
      await api.put(`/admin/students/${form.value.id}`, payload)
    } else {
      await api.post('/admin/students', payload)
    }
    modal.value = false
    fetchStudents()
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Gagal menyimpan data siswa.'
  } finally {
    saving.value = false
  }
}

async function remove(id: number) {
  if (!confirm('Hapus siswa ini?')) return
  await api.delete(`/admin/students/${id}`)
  fetchStudents()
}

onMounted(fetchStudents)
</script>
