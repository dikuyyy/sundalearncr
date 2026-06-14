# Panduan Aplikasi SundaLearn

> Dokumen ini menjelaskan cara kerja aplikasi, struktur file utama, dan fungsi setiap menu — ditulis agar mudah dipahami oleh siapapun tanpa latar belakang teknis.

---

## Daftar Isi

1. [Apa itu SundaLearn?](#1-apa-itu-sundalearncr)
2. [Siapa Penggunanya?](#2-siapa-penggunanya)
3. [Alur Penggunaan Aplikasi](#3-alur-penggunaan-aplikasi)
4. [Penjelasan Setiap Menu](#4-penjelasan-setiap-menu)
5. [Struktur File Utama](#5-struktur-file-utama)
6. [Cara Kerja di Balik Layar](#6-cara-kerja-di-balik-layar)

---

## 1. Apa itu SundaLearn?

**SundaLearn** adalah aplikasi web untuk belajar **Aksara Sunda** — huruf tradisional masyarakat Sunda yang sudah diakui UNESCO sebagai warisan budaya. Aplikasi ini membantu siswa mengenal, membaca, dan menulis aksara Sunda melalui materi pembelajaran, latihan transliterasi, keyboard virtual, dan kuis interaktif.

---

## 2. Siapa Penggunanya?

Aplikasi ini memiliki **3 jenis pengguna** dengan hak akses berbeda:

| Peran     | Deskripsi        | Akses                                           |
| --------- | ---------------- | ----------------------------------------------- |
| **Admin** | Pengelola sistem | Semua fitur + kelola akun guru & siswa          |
| **Guru**  | Pengajar         | Buat materi, soal, atur kuis, lihat hasil siswa |
| **Siswa** | Pelajar          | Belajar materi, transliterasi, ikut kuis        |

---

## 3. Alur Penggunaan Aplikasi

### Alur Umum (semua pengguna)

```
Buka Aplikasi
     ↓
Halaman Login
     ↓
Masukkan Email + Password
     ↓
Masuk ke Dashboard (tampilan berbeda sesuai peran)
     ↓
Pilih menu di sidebar kiri
     ↓
Gunakan fitur
     ↓
Klik "Keluar" untuk logout
```

### Alur Siswa Belajar

```
Login sebagai Siswa
     ↓
Dashboard → lihat progress belajar & nilai terakhir
     ↓
Menu "Materi" → pilih kategori → baca materi aksara Sunda
     ↓
Menu "Transliterasi" → ketik teks Latin → lihat hasil aksara Sunda
     ↓
Menu "Quiz" → pilih kuis → kerjakan soal (ada timer)
     ↓
Lihat hasil nilai setelah selesai
```

### Alur Guru Membuat Soal

```
Login sebagai Guru
     ↓
Menu "Bank Soal" → klik "Tambah Soal"
     ↓
Pilih tipe soal:
  - Sunda → Latin (baca aksara, jawab huruf biasa)
  - Latin → Sunda (ketik, jawab pakai keyboard virtual aksara)
  - Pilihan Ganda (4 opsi A-B-C-D)
     ↓
Isi pertanyaan & jawaban → Simpan
     ↓
Menu "Pengaturan Quiz" → buat kuis baru → pilih soal-soal dari bank
     ↓
Siswa bisa mulai mengerjakan kuis tersebut
```

### Alur Admin Kelola Pengguna

```
Login sebagai Admin
     ↓
Menu "Kelola Guru" → tambah/edit/nonaktifkan akun guru
     ↓
Menu "Kelola Siswa" → tambah/edit/nonaktifkan akun siswa
     ↓
(Admin juga bisa mengakses semua menu guru dan siswa)
```

---

## 4. Penjelasan Setiap Menu

### Menu Siswa

#### Dashboard (Beranda)

Halaman pertama setelah login. Menampilkan:

- Nilai kuis terakhir yang dikerjakan
- Rata-rata nilai dari semua kuis
- Berapa materi yang sudah selesai dipelajari
- Progress belajar dalam bentuk bar persentase
- Riwayat kuis terbaru

#### Materi Pembelajaran

Berisi kumpulan materi belajar aksara Sunda yang dibuat oleh guru. Materi dikelompokkan dalam kategori seperti huruf dasar, vokal, rarangken (diakritik), dan angka. Setiap materi bisa ditandai "selesai" setelah dipelajari.

#### Transliterasi

Alat konversi teks dua arah:

- **Latin → Sunda**: Ketik teks biasa (misal: "halo") → muncul aksara Sunda (ᮠᮜᮧ)
- **Sunda → Latin**: Ketik/pilih aksara Sunda → muncul bacaan Latin

Di halaman ini juga tersedia **Keyboard Virtual Aksara Sunda** yang memudahkan pengetikan karakter aksara tanpa perlu install font khusus di keyboard perangkat.

#### Quiz

Daftar kuis yang tersedia untuk dikerjakan. Setiap kuis:

- Memiliki batas waktu (timer hitung mundur)
- Soal diacak secara otomatis (algoritma Fisher-Yates Shuffle)
- Ada 3 tipe soal: baca aksara, tulis aksara, pilihan ganda

#### Riwayat Quiz

Rekap semua kuis yang pernah dikerjakan beserta nilai, tanggal, dan detail jawaban benar/salah.

---

### Menu Guru

#### Kelola Materi

Guru bisa menambah, mengedit, atau menghapus materi pembelajaran. Setiap materi terdiri dari:

- Judul dan kategori
- Deskripsi singkat
- Konten detail (bisa berisi aksara Sunda + penjelasannya)

#### Bank Soal

Kumpulan semua soal yang dibuat guru. Tersedia 3 tipe soal:

| Tipe              | Cara Mengerjakan                                                             |
| ----------------- | ---------------------------------------------------------------------------- |
| **Sunda → Latin** | Siswa melihat aksara Sunda, mengetik jawabannya dalam huruf Latin            |
| **Latin → Sunda** | Siswa melihat kata Latin, mengetik aksara Sunda menggunakan keyboard virtual |
| **Pilihan Ganda** | Siswa memilih satu dari 4 opsi jawaban (A/B/C/D)                             |

#### Pengaturan Quiz

Guru membuat konfigurasi kuis: nama kuis, jumlah soal yang diambil dari bank soal, durasi waktu, dan tingkat kesulitan. Soal akan diacak setiap kali siswa memulai kuis.

#### Hasil Siswa

Laporan nilai semua siswa per kuis. Guru bisa melihat siapa yang sudah mengerjakan, nilai berapa, dan berapa soal yang benar/salah.

---

### Menu Admin

#### Kelola Guru

Menambah akun guru baru, mengubah data, atau menonaktifkan akun. Guru yang dinonaktifkan tidak bisa login.

#### Kelola Siswa

Sama seperti kelola guru, namun untuk akun siswa. Data siswa mencakup nama, email, NISN (Nomor Induk Siswa Nasional), dan status aktif.

---

### Menu Bersama (semua peran)

#### Profil Saya

Setiap pengguna bisa mengubah nama, nomor telepon, dan mengganti password melalui halaman ini.

---

## 5. Struktur File Utama

### Gambaran Besar

```
aplikasi-pembelajaran-bahasa-sunda/
├── backend/          ← Server (Laravel PHP) — logika & database
└── frontend/         ← Tampilan (Vue.js) — yang dilihat pengguna
```

### Backend (Folder `backend/`)

```
backend/
├── app/
│   ├── Http/Controllers/API/    ← Pengendali permintaan dari frontend
│   │   ├── AuthController.php       → Login, logout, profil
│   │   ├── MaterialController.php   → CRUD materi pembelajaran
│   │   ├── QuestionController.php   → CRUD bank soal
│   │   ├── QuizController.php       → Alur kuis (mulai, submit, riwayat)
│   │   ├── TransliterationController.php → Konversi aksara
│   │   ├── DashboardController.php  → Data statistik dashboard
│   │   └── AdminController.php      → Kelola pengguna oleh admin
│   │
│   ├── Models/                  ← Representasi tabel database
│   │   ├── User.php                 → Data pengguna
│   │   ├── Role.php                 → Peran (admin/guru/siswa)
│   │   ├── Material.php             → Materi pembelajaran
│   │   ├── Question.php             → Soal kuis
│   │   ├── QuizSetting.php          → Konfigurasi kuis
│   │   ├── QuizAttempt.php          → Riwayat percobaan kuis siswa
│   │   └── QuizAnswer.php           → Jawaban per soal
│   │
│   └── Services/                ← Algoritma & logika bisnis khusus
│       ├── TransliterationService.php   → Algoritma konversi aksara
│       └── QuizRandomizerService.php    → Algoritma acak soal (Fisher-Yates)
│
├── database/
│   ├── migrations/              ← Script pembuat tabel database
│   └── seeders/                 ← Data awal (akun demo, contoh soal)
│
├── routes/
│   └── api.php                  ← Daftar semua alamat API endpoint
│
└── .env                         ← Konfigurasi rahasia (password DB, dll)
```

### Frontend (Folder `frontend/src/`)

```
frontend/src/
├── pages/                       ← Halaman-halaman aplikasi
│   ├── auth/
│   │   └── LoginPage.vue            → Halaman login
│   ├── student/
│   │   ├── MateriListPage.vue        → Daftar materi
│   │   ├── MateriDetailPage.vue      → Isi materi
│   │   ├── TransliterasiPage.vue     → Alat transliterasi
│   │   ├── QuizListPage.vue          → Daftar kuis
│   │   ├── QuizPlayPage.vue          → Halaman mengerjakan kuis
│   │   ├── QuizResultPage.vue        → Hasil kuis
│   │   └── QuizHistoryPage.vue       → Riwayat kuis
│   ├── teacher/
│   │   ├── BankSoalPage.vue          → Kelola soal
│   │   ├── MateriManagePage.vue      → Kelola materi
│   │   ├── QuizSettingsPage.vue      → Atur kuis
│   │   └── HasilSiswaPage.vue        → Nilai siswa
│   ├── admin/
│   │   ├── TeacherManagePage.vue     → Kelola akun guru
│   │   └── StudentManagePage.vue     → Kelola akun siswa
│   ├── DashboardPage.vue             → Beranda (berbeda per peran)
│   └── ProfilePage.vue               → Halaman profil
│
├── components/                  ← Bagian UI yang dipakai ulang
│   ├── keyboard/
│   │   └── SundaKeyboard.vue         → Keyboard virtual aksara Sunda
│   └── common/
│       ├── NavLink.vue               → Tombol menu di sidebar
│       └── StatCard.vue              → Kartu statistik di dashboard
│
├── layouts/
│   └── AppLayout.vue            ← Kerangka halaman (sidebar + header)
│
├── stores/                      ← Penyimpanan data sementara di browser
│   ├── auth.ts                      → Status login & data pengguna aktif
│   ├── material.ts                  → Data materi
│   ├── quiz.ts                      → Data kuis
│   └── transliteration.ts           → Data transliterasi
│
├── router/
│   └── index.ts                 ← Pengatur halaman mana yang bisa diakses siapa
│
└── api/
    └── axios.ts                 ← Pengatur komunikasi dengan server backend
```

---

## 6. Cara Kerja di Balik Layar

### Bagaimana Login Bekerja?

```
1. Pengguna ketik email + password → klik "Masuk"
2. Frontend kirim data ke server: POST /api/auth/login
3. Server periksa email & password di database
4. Jika benar → server kirim "token" (kode unik) ke browser
5. Token disimpan di browser (localStorage)
6. Setiap permintaan berikutnya menyertakan token ini
   agar server tahu siapa yang meminta data
7. Saat logout → token dihapus dari browser & server
```

### Bagaimana Soal Kuis Diacak? (Fisher-Yates Shuffle)

Algoritma ini memastikan soal tampil dalam urutan berbeda setiap kali kuis dimulai, tanpa soal yang sama muncul dua kali:

```
Contoh: bank soal = [Soal A, Soal B, Soal C, Soal D, Soal E]

Langkah 1: Pilih acak dari posisi 0–4 → tukar dengan posisi terakhir (4)
           → [Soal A, Soal C, Soal D, Soal B, Soal E]  (misal: Soal B tukar ke posisi 4)

Langkah 2: Pilih acak dari posisi 0–3 → tukar dengan posisi 3
           → [Soal A, Soal D, Soal C, Soal B, Soal E]

...dst hingga semua posisi terproses

Hasil: urutan baru yang benar-benar acak & tidak ada duplikat
```

### Bagaimana Transliterasi Bekerja? (Rule-Based)

Sistem tidak menggunakan AI — melainkan tabel pemetaan karakter:

```
Latin  →  Aksara Sunda

"k"    →  ᮊ
"a"    →  (vokal bawaan huruf konsonan)
"ba"   →  ᮘ
"ng"   →  ᮍ  (dua huruf Latin = satu aksara)
"ny"   →  ᮑ  (dua huruf Latin = satu aksara)

Contoh: "kuda" → ᮊᮥᮓ
  k  → ᮊ  (konsonan ka)
  u  → ᮥ  (rarangken/diakritik vokal u)
  d  → ᮓ  (konsonan da)
  a  → (vokal 'a' sudah melekat pada konsonan da)
```

### Hak Akses Menu (Role Guard)

Setiap halaman dilindungi. Saat pengguna mencoba membuka halaman tertentu:

```
Apakah sudah login?
  → Tidak → arahkan ke halaman Login
  → Ya → periksa peran pengguna

Apakah perannya sesuai?
  → Siswa buka /bank-soal → ditolak → arahkan ke Dashboard
  → Guru buka /bank-soal  → diizinkan → halaman tampil
  → Admin buka apapun     → diizinkan → halaman tampil
```

---

_Dibuat untuk keperluan dokumentasi skripsi — SundaLearn, Aplikasi Pembelajaran Aksara Sunda._
