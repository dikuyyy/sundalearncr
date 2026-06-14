<template>
  <div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">Kelola Materi</h1>
        <p class="text-gray-500 mt-1">Tambah, edit, dan hapus materi pembelajaran aksara Sunda.</p>
      </div>
      <button @click="openModal()" class="btn-primary">+ Tambah Materi</button>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Judul</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Kategori</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Item</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Status</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="m in store.materials" :key="m.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-800">{{ m.title }}</td>
              <td class="px-4 py-3">
                <span class="badge badge-blue text-xs">{{ m.category }}</span>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ m.items_count }}</td>
              <td class="px-4 py-3">
                <span class="badge text-xs" :class="m.is_published ? 'badge-green' : 'badge-yellow'">
                  {{ m.is_published ? 'Terbit' : 'Draft' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button @click="openModal(m)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                  <button @click="store.remove(m.id)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h3 class="font-bold text-gray-900">{{ form.id ? 'Edit Materi' : 'Tambah Materi' }}</h3>
          <button @click="modal = false" class="text-gray-400 text-xl">✕</button>
        </div>
        <form @submit.prevent="save" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
            <input v-model="form.title" required class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea v-model="form.description" class="input-field min-h-[80px]" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select v-model="form.category" class="input-field">
              <option value="huruf_dasar">Huruf Dasar</option>
              <option value="rarangken">Rarangken</option>
              <option value="angka">Angka Sunda</option>
              <option value="contoh_kata">Contoh Kata</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
              <input v-model.number="form.order" type="number" min="0" class="input-field" />
            </div>
            <div class="flex items-end pb-1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_published" type="checkbox" class="w-4 h-4 rounded text-sunda-600" />
                <span class="text-sm text-gray-700">Terbitkan</span>
              </label>
            </div>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="modal = false" class="btn-secondary flex-1">Batal</button>
            <button type="submit" class="btn-primary flex-1">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useMaterialStore } from '@/stores/material'

const store = useMaterialStore()
const modal = ref(false)

const emptyForm = () => ({
  id: null as number | null,
  title: '',
  description: '',
  category: 'huruf_dasar',
  order: 0,
  is_published: true,
})

const form = ref(emptyForm())

function openModal(m?: any) {
  form.value = m ? { ...m } : emptyForm()
  modal.value = true
}

async function save() {
  if (form.value.id) {
    await store.update(form.value.id, form.value)
  } else {
    await store.create(form.value)
  }
  modal.value = false
}

onMounted(() => store.fetchAll())
</script>
