<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-sunda-800 text-white transform transition-transform duration-300 flex flex-col"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
      <!-- Logo -->
      <div class="flex items-center gap-3 px-6 py-5 border-b border-sunda-700">
        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
          <span class="font-sunda text-sunda-700 font-bold text-lg">ᮞ</span>
        </div>
        <div>
          <h1 class="font-bold text-lg leading-tight">SundaLearn</h1>
          <p class="text-sunda-300 text-xs">Aksara Sunda</p>
        </div>
      </div>

      <!-- User Info -->
      <div class="px-6 py-4 border-b border-sunda-700">
        <p class="text-sm font-semibold truncate">{{ auth.user?.name }}</p>
        <span class="badge bg-sunda-600 text-sunda-100 text-xs mt-1">
          {{ auth.user?.role_display }}
        </span>
      </div>

      <!-- Navigation -->
      <nav class="px-3 py-4 flex-1 overflow-y-auto sidebar-scroll">
        <NavLink to="/dashboard" icon="🏠" label="Dashboard" />

        <template v-if="auth.isSiswa || auth.isAdmin">
          <NavDivider label="Pembelajaran" />
          <NavLink to="/materi" icon="📚" label="Materi" />
          <NavLink to="/transliterasi" icon="🔄" label="Transliterasi" />
          <NavLink to="/quiz" icon="✏️" label="Quiz" />
          <NavLink to="/quiz/riwayat" icon="📊" label="Hasil Quiz" />
        </template>

        <template v-if="auth.isGuru || auth.isAdmin">
          <NavDivider label="Manajemen" />
          <!-- Sementara disembunyikan: <NavLink to="/kelola-materi" icon="📝" label="Kelola Materi" /> -->
          <NavLink to="/kelola-soal" icon="🗄️" label="Kelola Soal" />
          <NavLink to="/hasil-siswa" icon="📈" label="Hasil Siswa" />
        </template>

        <template v-if="auth.isAdmin">
          <NavDivider label="Administrator" />
          <NavLink to="/admin/guru" icon="👨‍🏫" label="Kelola Guru" />
          <NavLink to="/admin/siswa" icon="👨‍🎓" label="Kelola Siswa" />
        </template>

        <NavDivider label="Akun" />
        <NavLink to="/profil" icon="👤" label="Profil Saya" />
        <button @click="handleLogout"
          class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sunda-200 hover:bg-sunda-700 hover:text-white transition-colors text-sm mt-1">
          <span>🚪</span>
          <span>Keluar</span>
        </button>
      </nav>
    </aside>

    <!-- Overlay untuk mobile -->
    <div v-if="sidebarOpen" class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="sidebarOpen = false" />

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
      <!-- Top Bar -->
      <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4 sticky top-0 z-30">
        <button class="lg:hidden text-gray-500 hover:text-gray-700" @click="sidebarOpen = !sidebarOpen">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <h2 class="font-semibold text-gray-800 flex-1">{{ currentTitle }}</h2>

        <div class="text-right hidden sm:block">
          <p class="text-sm font-medium text-gray-800">{{ auth.user?.name }}</p>
          <p class="text-xs text-gray-500">{{ auth.user?.role_display }}</p>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <RouterView />
      </main>

      <footer class="text-center py-4 text-xs text-gray-400 border-t">
        SundaLearn © 2026 · Aplikasi Pembelajaran Aksara Sunda
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NavLink from '@/components/common/NavLink.vue'
import NavDivider from '@/components/common/NavDivider.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

const currentTitle = computed(() => route.meta.title as string ?? 'SundaLearn')

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
