import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'

export interface User {
  id: number
  name: string
  email: string
  username: string | null
  role: 'admin' | 'guru' | 'siswa'
  role_display: string
  nisn: string | null
  kelas: string | null
  nip: string | null
  phone: string | null
  is_active: boolean
}

export const useAuthStore = defineStore('auth', () => {
  const user  = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isGuru     = computed(() => user.value?.role === 'guru')
  const isSiswa    = computed(() => user.value?.role === 'siswa')

  async function register(payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
    nisn?: string
    kelas?: string
    phone?: string
    gender?: string
  }) {
    const { data } = await api.post('/auth/register', payload)
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('auth_token', data.token)
    return data
  }

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('auth_token', data.token)
    return data
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      token.value = null
      user.value  = null
      localStorage.removeItem('auth_token')
    }
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/user')
      user.value = data.user
    } catch {
      token.value = null
      user.value  = null
      localStorage.removeItem('auth_token')
    }
  }

  async function updateProfile(payload: Partial<User>) {
    const { data } = await api.put('/profile', payload)
    user.value = data.user
    return data
  }

  async function changePassword(currentPassword: string, password: string, passwordConfirmation: string) {
    return api.put('/profile/password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    })
  }

  return {
    user, token,
    isLoggedIn, isAdmin, isGuru, isSiswa,
    register, login, logout, fetchUser, updateProfile, changePassword,
  }
})
