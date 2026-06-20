<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\QuestionController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\TransliterationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SundaLearn REST API Routes
|--------------------------------------------------------------------------
|
| Prefix  : /api
| Versi   : v1
| Auth    : Laravel Sanctum (token-based)
|
| Struktur hak akses:
|   - auth:sanctum       → semua pengguna yang sudah login
|   - role=admin         → hanya admin
|   - role=guru|admin    → guru dan admin
|   - role=siswa         → hanya siswa
|
*/

// ─────────────────── AUTH (Public) ───────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ─────────────────── Transliterasi (Semi-public) ───────────────────
// Dapat diakses tanpa login, tapi riwayat hanya tersimpan jika login
Route::post('/transliterate', [TransliterationController::class, 'transliterate']);
Route::get('/transliterate/mappings', [TransliterationController::class, 'getMappings']);
Route::get('/transliterate/huruf-dasar', [TransliterationController::class, 'getHurufDasar']);
Route::get('/transliterate/rarangken', [TransliterationController::class, 'getRarangken']);
Route::get('/transliterate/angka', [TransliterationController::class, 'getAngkaSunda']);

// ─────────────────── AUTHENTICATED ROUTES ───────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'changePassword']);

    // Dashboard (role-aware)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ─── Materi ───
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::get('/materials/{id}', [MaterialController::class, 'show']);
    Route::put('/materials/{id}/complete', [MaterialController::class, 'markComplete']);

    // Guru/Admin: CRUD Materi
    Route::post('/materials', [MaterialController::class, 'store']);
    Route::put('/materials/{id}', [MaterialController::class, 'update']);
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);
    Route::post('/materials/{id}/items', [MaterialController::class, 'storeItem']);
    Route::put('/materials/{materialId}/items/{itemId}', [MaterialController::class, 'updateItem']);
    Route::delete('/materials/{materialId}/items/{itemId}', [MaterialController::class, 'destroyItem']);

    // ─── Bank Soal (Guru/Admin) ───
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::get('/questions/stats', [QuestionController::class, 'stats']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

    // ─── Quiz ───
    // Guru: pengaturan quiz
    Route::get('/quiz/settings', [QuizController::class, 'indexSettings']);
    Route::post('/quiz/settings', [QuizController::class, 'storeSettings']);
    Route::put('/quiz/settings/{id}', [QuizController::class, 'updateSettings']);
    Route::delete('/quiz/settings/{id}', [QuizController::class, 'destroySettings']);

    // Guru: lihat hasil siswa
    Route::get('/quiz/teacher/stats', [QuizController::class, 'teacherStats']);
    Route::get('/quiz/teacher/students', [QuizController::class, 'studentResults']);

    // Siswa: mengerjakan quiz
    Route::get('/quiz/available', [QuizController::class, 'available']);
    Route::post('/quiz/start', [QuizController::class, 'start']);
    Route::post('/quiz/submit', [QuizController::class, 'submit']);
    Route::get('/quiz/history', [QuizController::class, 'history']);
    Route::get('/quiz/results', [QuizController::class, 'allResults']);
    Route::get('/quiz/attempts/{id}/review', [QuizController::class, 'review']);

    // ─── Riwayat Transliterasi ───
    Route::get('/transliterate/history', [TransliterationController::class, 'history']);

    // ─── Admin: kelola pengguna ───
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        Route::get('/teachers', [AdminController::class, 'indexTeachers']);
        Route::post('/teachers', [AdminController::class, 'storeTeacher']);
        Route::put('/teachers/{id}', [AdminController::class, 'updateTeacher']);
        Route::delete('/teachers/{id}', [AdminController::class, 'destroyTeacher']);

        Route::get('/students', [AdminController::class, 'indexStudents']);
        Route::post('/students', [AdminController::class, 'storeStudent']);
        Route::put('/students/{id}', [AdminController::class, 'updateStudent']);
        Route::delete('/students/{id}', [AdminController::class, 'destroyStudent']);
    });
});
