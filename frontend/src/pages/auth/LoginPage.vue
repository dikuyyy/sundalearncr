<template>
  <div class="min-h-screen bg-gradient-to-br from-sunda-600 to-sunda-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-sunda-700 px-8 py-8 text-center">
          <div class="w-16 h-16 bg-white rounded-2xl mx-auto flex items-center justify-center mb-4 shadow-lg">
            <span class="font-sunda text-sunda-700 text-3xl">ᮞ</span>
          </div>
          <h1 class="text-2xl font-bold text-white">SundaLearn</h1>
          <p class="text-sunda-200 text-sm mt-1">Aplikasi Pembelajaran Aksara Sunda</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="px-8 py-8 space-y-5">
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

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                required
                autocomplete="current-password"
                class="input-field pr-10"
                placeholder="••••••••"
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

          <!-- Error Alert -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ error }}
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full py-3 text-base">
            <span v-if="loading">⏳ Memproses...</span>
            <span v-else>Masuk ke SundaLearn</span>
          </button>

          <!-- Demo Accounts -->
          <div class="border-t pt-5">
            <p class="text-xs text-gray-500 mb-3 text-center">Akun Demo:</p>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="demo in demoAccounts"
                :key="demo.role"
                type="button"
                @click="fillDemo(demo)"
                class="text-xs bg-gray-50 hover:bg-sunda-50 hover:text-sunda-700 border border-gray-200 rounded-lg py-2 px-2 transition-colors"
              >
                {{ demo.label }}
              </button>
            </div>
          </div>
        </form>
      </div>

      <p class="text-center text-sunda-200 text-sm mt-6">
        ᮊᮥᮙᮕᮥᮜᮔᮺ ᮘᮞ ᮞᮥᮔ · Kumupu na Basa Sunda
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const form     = ref({ email: '', password: '' })
const loading  = ref(false)
const error    = ref<string | null>(null)
const showPass = ref(false)

const demoAccounts = [
  { role: 'Admin',   label: '🔧 Admin',  email: 'admin@sundalearncr.local',  password: 'password123' },
  { role: 'Guru',    label: '👨‍🏫 Guru',   email: 'ahmad@sundalearncr.local',  password: 'password123' },
  { role: 'Siswa',   label: '👨‍🎓 Siswa',  email: 'budi@sundalearncr.local',   password: 'password123' },
]

function fillDemo(account: typeof demoAccounts[0]) {
  form.value.email    = account.email
  form.value.password = account.password
}

async function handleLogin() {
  loading.value = true
  error.value   = null
  try {
    await auth.login(form.value.email, form.value.password)
    const redirect = route.query.redirect as string || '/dashboard'
    router.push(redirect)
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Login gagal. Periksa email dan password Anda.'
  } finally {
    loading.value = false
  }
}
</script>
