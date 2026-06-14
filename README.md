# SundaLearn - Aplikasi Pembelajaran Aksara Sunda

Aplikasi web pembelajaran aksara Sunda berbasis Laravel 12 + Vue 3 untuk kebutuhan skripsi.

---

## Daftar Isi

- [Teknologi yang Digunakan](#teknologi)
- [Fitur Utama](#fitur)
- [Struktur Proyek](#struktur)
- [Instalasi dan Konfigurasi](#instalasi)
- [API Reference](#api)
- [Algoritma Fisher-Yates Shuffle](#fisher-yates)
- [Algoritma Transliterasi](#transliterasi)
- [Diagram Sistem](#diagram)
- [Akun Demo](#demo)

---

## Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3 + Composition API |
| Database | MySQL 8.0+ |
| UI Framework | Tailwind CSS 3 |
| Authentication | Laravel Sanctum (Token-based) |
| State Management | Pinia |
| Build Tool | Vite 5 |
| Font Aksara | Noto Sans Sundanese (Google Fonts) |

---

## Fitur Utama

### Modul 1 - Materi Pembelajaran
- Huruf Dasar aksara Sunda (dengan variasi vokal)
- Rarangken (tanda diakritik)
- Angka Sunda
- Contoh kata sehari-hari

### Modul 2 - Transliterasi
- Latin → Aksara Sunda (Rule-Based)
- Aksara Sunda → Latin
- Virtual keyboard aksara Sunda

### Modul 3 - Virtual Keyboard
- Huruf dasar, vokal mandiri, rarangken, angka Sunda
- Dapat digunakan untuk input soal (guru) dan transliterasi

### Modul 4 - Quiz Interaktif
- Tipe 1: Aksara Sunda → Latin (teks bebas)
- Tipe 2: Latin → Aksara Sunda (teks bebas)
- Tipe 3: Pilihan ganda (4 opsi)
- Pengacakan soal: **Fisher-Yates Shuffle**
- Timer countdown
- Penilaian otomatis: `Nilai = (Benar / Total) × 100`

### Modul 5 - Dashboard
- Admin: statistik sistem, manajemen pengguna
- Guru: hasil quiz siswa, grafik nilai, bank soal
- Siswa: progress belajar, riwayat nilai

---

## Struktur Proyek

```
aplikasi-pembelajaran-bahasa-sunda/
├── backend/                        # Laravel 12
│   ├── app/
│   │   ├── Http/Controllers/API/   # API Controllers
│   │   │   ├── AuthController.php
│   │   │   ├── AdminController.php
│   │   │   ├── MaterialController.php
│   │   │   ├── QuestionController.php
│   │   │   ├── QuizController.php
│   │   │   ├── TransliterationController.php
│   │   │   └── DashboardController.php
│   │   ├── Models/                 # Eloquent Models
│   │   │   ├── User.php
│   │   │   ├── Role.php
│   │   │   ├── Material.php
│   │   │   ├── MaterialItem.php
│   │   │   ├── Question.php
│   │   │   ├── QuizSetting.php
│   │   │   ├── QuizAttempt.php
│   │   │   ├── QuizAnswer.php
│   │   │   ├── TransliterationHistory.php
│   │   │   └── StudentProgress.php
│   │   └── Services/
│   │       ├── TransliterationService.php   # Rule-based transliteration
│   │       └── QuizRandomizerService.php    # Fisher-Yates Shuffle
│   ├── database/
│   │   ├── migrations/             # 7 migration files
│   │   ├── seeders/                # Data awal
│   │   └── factories/              # Factory untuk testing
│   ├── routes/api.php              # REST API routes
│   └── tests/
│       ├── Unit/
│       │   ├── TransliterationServiceTest.php
│       │   └── QuizRandomizerServiceTest.php
│       └── Feature/
│           ├── AuthTest.php
│           ├── TransliterationTest.php
│           └── QuizTest.php
│
└── frontend/                       # Vue 3
    └── src/
        ├── api/axios.ts            # Axios instance
        ├── stores/                 # Pinia stores
        │   ├── auth.ts
        │   ├── material.ts
        │   ├── quiz.ts
        │   └── transliteration.ts
        ├── router/index.ts         # Vue Router (role-guard)
        ├── layouts/AppLayout.vue   # Layout utama dengan sidebar
        ├── components/
        │   ├── common/             # StatCard, NavLink, NavDivider
        │   └── keyboard/           # SundaKeyboard.vue, KeyButton.vue
        └── pages/
            ├── auth/LoginPage.vue
            ├── DashboardPage.vue   # Role-aware dashboard
            ├── student/
            │   ├── MateriListPage.vue
            │   ├── MateriDetailPage.vue
            │   ├── TransliterasiPage.vue
            │   ├── QuizListPage.vue
            │   ├── QuizPlayPage.vue    # Timer + Fisher-Yates
            │   ├── QuizHistoryPage.vue
            │   └── QuizResultPage.vue
            ├── teacher/
            │   ├── BankSoalPage.vue
            │   ├── QuizSettingsPage.vue
            │   ├── HasilSiswaPage.vue
            │   └── MateriManagePage.vue
            └── admin/
                ├── TeacherManagePage.vue
                └── StudentManagePage.vue
```

---

## Instalasi dan Konfigurasi

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Backend (Laravel)

```bash
# 1. Masuk ke direktori backend
cd backend

# 2. Install dependensi PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Konfigurasi database di .env
# DB_DATABASE=sundalearncr
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 6. Jalankan migrasi dan seeder
php artisan migrate --seed

# 7. Jalankan server development
php artisan serve --port=8000
```

### Frontend (Vue 3)

```bash
# 1. Masuk ke direktori frontend
cd frontend

# 2. Install dependensi Node
npm install

# 3. Buat file .env
echo "VITE_API_URL=http://localhost:8000/api" > .env

# 4. Jalankan development server
npm run dev
```

Akses aplikasi di: **http://localhost:5173**

---

## API Reference

### Authentication
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/auth/login` | Login, mendapatkan token |
| POST | `/api/auth/logout` | Logout, revoke token |
| GET | `/api/user` | Profil pengguna yang login |

### Materi
| Method | Endpoint | Role |
|--------|----------|------|
| GET | `/api/materials` | Semua |
| GET | `/api/materials/{id}` | Semua |
| POST | `/api/materials` | Guru/Admin |
| PUT | `/api/materials/{id}` | Guru/Admin |
| DELETE | `/api/materials/{id}` | Guru/Admin |

### Transliterasi
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/transliterate` | Transliterasi dua arah |
| GET | `/api/transliterate/mappings` | Semua mapping aksara |
| GET | `/api/transliterate/huruf-dasar` | Data huruf dasar |

### Quiz
| Method | Endpoint | Role |
|--------|----------|------|
| GET | `/api/quiz/available` | Siswa |
| POST | `/api/quiz/start` | Siswa |
| POST | `/api/quiz/submit` | Siswa |
| GET | `/api/quiz/history` | Siswa |
| POST | `/api/quiz/settings` | Guru |
| GET | `/api/quiz/teacher/stats` | Guru |
| GET | `/api/quiz/teacher/students` | Guru |

---

## Algoritma Fisher-Yates Shuffle

Digunakan untuk mengacak urutan soal quiz agar setiap siswa mendapatkan urutan berbeda.

### Pseudocode
```
procedure FisherYatesShuffle(A[0..n-1]):
  for i from n-1 downto 1 do:
    j ← random integer such that 0 ≤ j ≤ i
    swap A[i] with A[j]
```

### Implementasi PHP
```php
// app/Services/QuizRandomizerService.php
public function fisherYatesShuffle(array $array): array
{
    $n = count($array);
    for ($i = $n - 1; $i > 0; $i--) {
        $j = random_int(0, $i);              // j acak: 0 ≤ j ≤ i
        [$array[$i], $array[$j]] = [$array[$j], $array[$i]]; // tukar
    }
    return $array;
}
```

### Properti
- **Time Complexity**: O(n)
- **Space Complexity**: O(1) — in-place
- **Unbiased**: Setiap permutasi memiliki probabilitas 1/n!

---

## Algoritma Transliterasi

### Latin → Aksara Sunda

```
1. Lowercase input
2. Iterasi karakter:
   a. Digit → angka Sunda
   b. Spasi/tanda baca → pertahankan
   c. Digraf (ng, ny) → konsonan Sunda
   d. Vokal awal kata → vokal mandiri
   e. Konsonan:
      - Diikuti 'a' → hanya konsonan (vokal inheren)
      - Diikuti vokal lain → konsonan + rarangken
      - Diikuti konsonan/akhir → konsonan + pamaéh (ᮺ)
```

### Aksara Sunda → Latin

```
1. Iterasi karakter Unicode:
   a. Vokal mandiri → Latin
   b. Konsonan:
      - Diikuti pamaéh → konsonan saja
      - Diikuti rarangken → konsonan + vokal
      - Tidak ada → konsonan + 'a' (vokal inheren)
   c. Angka Sunda → digit Latin
   d. Lainnya → pertahankan
```

---

## Diagram Sistem

### ERD (Entity Relationship Diagram)

```
roles (id, name, display_name)
  └─► users (id, role_id, name, email, password, nisn, nip, ...)
        ├─► materials (id, created_by, title, category, ...)
        │     └─► material_items (id, material_id, sunda_script, latin_name, ...)
        ├─► questions (id, created_by, type, difficulty, correct_answer, options, ...)
        ├─► quiz_settings (id, created_by, total_questions, duration_minutes, ...)
        │     └─► quiz_attempts (id, user_id, quiz_setting_id, question_order, score, ...)
        │           └─► quiz_answers (id, quiz_attempt_id, question_id, user_answer, ...)
        ├─► transliteration_history (id, user_id, direction, input_text, output_text)
        └─► student_progress (id, user_id, material_id, is_completed, progress_percentage)
```

### Use Case

**Admin**: Login → Kelola Guru → Kelola Siswa → Lihat Statistik

**Guru**: Login → CRUD Bank Soal → CRUD Materi → Atur Quiz → Lihat Hasil Siswa

**Siswa**: Login → Belajar Materi → Transliterasi → Ikuti Quiz → Lihat Riwayat

---

## Akun Demo

Setelah menjalankan `php artisan migrate --seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sundalearncr.local | password123 |
| Guru | ahmad@sundalearncr.local | password123 |
| Siswa | budi@sundalearncr.local | password123 |

---

## Lisensi

Proyek ini dibuat untuk kebutuhan **skripsi** dengan tujuan pendidikan.
Aksara Sunda menggunakan Unicode Range U+1B80–U+1BBF (Sundanese Script).
