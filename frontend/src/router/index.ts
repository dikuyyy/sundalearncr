import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // ─── Auth ───
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { guest: true },
    },

    // ─── Main Layout ───
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/dashboard',
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('@/pages/DashboardPage.vue'),
          meta: { title: 'Dashboard' },
        },

        // ─── Materi ───
        {
          path: 'materi',
          name: 'materi.index',
          component: () => import('@/pages/student/MateriListPage.vue'),
          meta: { title: 'Materi Pembelajaran' },
        },
        {
          path: 'materi/:id',
          name: 'materi.show',
          component: () => import('@/pages/student/MateriDetailPage.vue'),
          meta: { title: 'Detail Materi' },
        },

        // ─── Transliterasi ───
        {
          path: 'transliterasi',
          name: 'transliterasi',
          component: () => import('@/pages/student/TransliterasiPage.vue'),
          meta: { title: 'Transliterasi Aksara Sunda' },
        },

        // ─── Quiz (Siswa) ───
        {
          path: 'quiz',
          name: 'quiz.list',
          component: () => import('@/pages/student/QuizListPage.vue'),
          meta: { title: 'Daftar Quiz', role: 'siswa' },
        },
        {
          path: 'quiz/:id/mulai',
          name: 'quiz.start',
          component: () => import('@/pages/student/QuizPlayPage.vue'),
          meta: { title: 'Kerjakan Quiz', role: 'siswa' },
        },
        {
          path: 'quiz/riwayat',
          name: 'quiz.history',
          component: () => import('@/pages/student/QuizHistoryPage.vue'),
          meta: { title: 'Hasil Quiz', role: 'siswa' },
        },
        {
          path: 'quiz/hasil/:id',
          name: 'quiz.result',
          component: () => import('@/pages/student/QuizResultPage.vue'),
          meta: { title: 'Hasil Quiz' },
        },

        // ─── Guru: Kelola Soal (gabungan Bank Soal + Pengaturan Quiz) ───
        {
          path: 'kelola-soal',
          name: 'kelola.soal',
          component: () => import('@/pages/teacher/KelolaSoalPage.vue'),
          meta: { title: 'Kelola Soal', role: 'guru' },
        },
        {
          path: 'hasil-siswa',
          name: 'teacher.results',
          component: () => import('@/pages/teacher/HasilSiswaPage.vue'),
          meta: { title: 'Hasil Quiz Siswa', role: 'guru' },
        },

        // ─── Guru: Kelola Materi ───
        {
          path: 'kelola-materi',
          name: 'materi.manage',
          component: () => import('@/pages/teacher/MateriManagePage.vue'),
          meta: { title: 'Kelola Materi', role: 'guru' },
        },

        // ─── Admin ───
        {
          path: 'admin/guru',
          name: 'admin.teachers',
          component: () => import('@/pages/admin/TeacherManagePage.vue'),
          meta: { title: 'Kelola Guru', role: 'admin' },
        },
        {
          path: 'admin/siswa',
          name: 'admin.students',
          component: () => import('@/pages/admin/StudentManagePage.vue'),
          meta: { title: 'Kelola Siswa', role: 'admin' },
        },

        // ─── Profil ───
        {
          path: 'profil',
          name: 'profil',
          component: () => import('@/pages/ProfilePage.vue'),
          meta: { title: 'Profil Saya' },
        },
      ],
    },

    // Catch-all
    {
      path: '/:pathMatch(.*)*',
      redirect: '/dashboard',
    },
  ],
})

// Navigation guards
router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore()

  // Saat page refresh: token ada di localStorage tapi user belum di-load → fetch dulu
  if (auth.token && !auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guest && auth.isLoggedIn) {
    return next({ name: 'dashboard' })
  }

  // Role-based guard
  if (to.meta.role && auth.user?.role !== to.meta.role && auth.user?.role !== 'admin') {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
