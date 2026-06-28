<template>
  <div class="min-h-screen bg-gradient-to-br from-sunda-600 to-sunda-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-sunda-700 px-8 py-6 text-center">
          <div class="w-14 h-14 bg-white rounded-2xl mx-auto flex items-center justify-center mb-3 shadow-lg">
            <span class="font-sunda text-sunda-700 text-2xl">ᮞ</span>
          </div>
          <h1 class="text-xl font-bold text-white">Daftar Akun Siswa</h1>
          <p class="text-sunda-200 text-sm mt-0.5">SundaLearn — Pembelajaran Aksara Sunda</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="px-8 py-6 space-y-4">
          <!-- Nama -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              autocomplete="name"
              class="input-field"
              placeholder="Nama lengkap Anda"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="input-field"
              placeholder="contoh@email.com"
            />
          </div>

          <!-- NISN (opsional) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              NISN <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input
              v-model="form.nisn"
              type="text"
              class="input-field"
              placeholder="Nomor Induk Siswa Nasional"
              maxlength="20"
            />
          </div>

          <!-- Kelas (opsional) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Kelas <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input
              v-model="form.kelas"
              type="text"
              class="input-field"
              placeholder="Contoh: X IPA 1, XI IPS 2"
              maxlength="50"
            />
          </div>

          <!-- Jenis Kelamin -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Jenis Kelamin <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <select v-model="form.gender" class="input-field">
              <option value="">-- Pilih --</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                required
                autocomplete="new-password"
                class="input-field pr-10"
                placeholder="Minimal 8 karakter"
              />
              <button
                type="button"
                @click="showPass = !showPass"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                {{ showPass ? '🙈' : '👁️' }}
              </button>
            </div>
          </div>

          <!-- Konfirmasi Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showPassConfirm ? 'text' : 'password'"
                required
                autocomplete="new-password"
                class="input-field pr-10"
                placeholder="Ulangi password"
              />
              <button
                type="button"
                @click="showPassConfirm = !showPassConfirm"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                {{ showPassConfirm ? '🙈' : '👁️' }}
              </button>
            </div>
            <p v-if="passwordMismatch" class="text-xs text-red-500 mt-1">Password tidak cocok.</p>
          </div>

          <!-- Error -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ error }}
          </div>

          <button type="submit" :disabled="loading || passwordMismatch" class="btn-primary w-full py-3 text-base">
            <span v-if="loading">⏳ Mendaftarkan...</span>
            <span v-else>Daftar Sekarang</span>
          </button>

          <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <RouterLink to="/login" class="text-sunda-600 hover:text-sunda-800 font-medium">Masuk di sini</RouterLink>
          </p>
        </form>
      </div>

      <p class="text-center text-sunda-200 text-sm mt-6">
        ᮊᮥᮙᮕᮥᮜᮔᮺ ᮘᮞ ᮞᮥᮔ · Kumupu na Basa Sunda
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  nisn: '',
  kelas: '',
  gender: '',
  password: '',
  password_confirmation: '',
})

const loading          = ref(false)
const error            = ref<string | null>(null)
const showPass         = ref(false)
const showPassConfirm  = ref(false)

const passwordMismatch = computed(() =>
  !!form.value.password_confirmation &&
  form.value.password !== form.value.password_confirmation
)

async function handleRegister() {
  if (passwordMismatch.value) return
  loading.value = true
  error.value   = null
  try {
    await auth.register({
      name:                  form.value.name,
      email:                 form.value.email,
      password:              form.value.password,
      password_confirmation: form.value.password_confirmation,
      nisn:                  form.value.nisn  || undefined,
      kelas:                 form.value.kelas || undefined,
      gender:                form.value.gender || undefined,
    })
    router.push('/dashboard')
  } catch (e: any) {
    const msg = e.response?.data?.message
    const errors = e.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = msg ?? 'Registrasi gagal. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>
