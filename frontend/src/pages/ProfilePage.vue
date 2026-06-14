<template>
  <div class="max-w-2xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
    </div>

    <!-- Avatar -->
    <div class="card mb-6 text-center">
      <div class="w-20 h-20 bg-sunda-100 rounded-full mx-auto flex items-center justify-center text-4xl mb-3">
        {{ auth.user?.role === 'guru' ? '👨‍🏫' : auth.user?.role === 'admin' ? '🔧' : '👨‍🎓' }}
      </div>
      <h2 class="text-xl font-bold text-gray-900">{{ auth.user?.name }}</h2>
      <span class="badge badge-green mt-2">{{ auth.user?.role_display }}</span>
    </div>

    <!-- Profile Form -->
    <div class="card mb-6">
      <h3 class="font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
      <form @submit.prevent="saveProfile" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
          <input v-model="profileForm.name" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input :value="auth.user?.email" disabled class="input-field bg-gray-50 text-gray-500" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
            <input v-model="profileForm.phone" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
            <select v-model="profileForm.gender" class="input-field">
              <option value="">Pilih...</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
          <textarea v-model="profileForm.address" class="input-field min-h-[80px]" />
        </div>
        <div v-if="profileSuccess" class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm">
          ✅ Profil berhasil diperbarui.
        </div>
        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </form>
    </div>

    <!-- Change Password -->
    <div class="card">
      <h3 class="font-semibold text-gray-800 mb-4">Ubah Password</h3>
      <form @submit.prevent="savePassword" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
          <input v-model="passForm.current" type="password" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
          <input v-model="passForm.new" type="password" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
          <input v-model="passForm.confirm" type="password" required class="input-field" />
        </div>
        <div v-if="passError" class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded text-sm">{{ passError }}</div>
        <div v-if="passSuccess" class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm">✅ Password berhasil diubah.</div>
        <button type="submit" :disabled="changingPass" class="btn-primary">
          {{ changingPass ? 'Memproses...' : 'Ubah Password' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const profileForm = ref({
  name: auth.user?.name ?? '',
  phone: auth.user?.phone ?? '',
  gender: auth.user?.gender ?? '',
  address: '',
})
const passForm   = ref({ current: '', new: '', confirm: '' })
const saving     = ref(false)
const changingPass = ref(false)
const profileSuccess = ref(false)
const passSuccess = ref(false)
const passError   = ref<string | null>(null)

async function saveProfile() {
  saving.value = true
  await auth.updateProfile(profileForm.value)
  profileSuccess.value = true
  saving.value = false
  setTimeout(() => (profileSuccess.value = false), 3000)
}

async function savePassword() {
  if (passForm.value.new !== passForm.value.confirm) {
    passError.value = 'Konfirmasi password tidak cocok.'
    return
  }
  changingPass.value = true
  passError.value = null
  try {
    await auth.changePassword(passForm.value.current, passForm.value.new, passForm.value.confirm)
    passSuccess.value = true
    passForm.value = { current: '', new: '', confirm: '' }
    setTimeout(() => (passSuccess.value = false), 3000)
  } catch (e: any) {
    passError.value = e.response?.data?.message ?? 'Gagal mengubah password.'
  } finally {
    changingPass.value = false
  }
}
</script>
