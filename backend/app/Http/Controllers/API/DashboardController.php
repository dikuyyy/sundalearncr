<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizSetting;
use App\Models\StudentProgress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Dashboard dinamis berdasarkan role pengguna.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return match ($user->role->name) {
            'admin'  => $this->adminDashboard(),
            'guru'   => $this->guruDashboard($user),
            'siswa'  => $this->siswaDashboard($user),
            default  => response()->json(['message' => 'Role tidak dikenal.'], 403),
        };
    }

    private function adminDashboard(): JsonResponse
    {
        return response()->json([
            'role' => 'admin',
            'stats' => [
                'total_guru'   => User::whereHas('role', fn($q) => $q->where('name', 'guru'))->count(),
                'total_siswa'  => User::whereHas('role', fn($q) => $q->where('name', 'siswa'))->count(),
                'total_materi' => Material::count(),
                'total_soal'   => Question::count(),
                'quiz_selesai' => QuizAttempt::where('status', 'completed')->count(),
            ],
        ]);
    }

    private function guruDashboard(User $user): JsonResponse
    {
        $settingIds = QuizSetting::where('created_by', $user->id)->pluck('id');

        $attempts = QuizAttempt::whereIn('quiz_setting_id', $settingIds)
            ->where('status', 'completed');

        // Grafik hasil quiz: rata-rata skor per quiz
        $quizGraph = QuizSetting::where('created_by', $user->id)
            ->withCount(['attempts as completed_count' => fn($q) => $q->where('status', 'completed')])
            ->withAvg(['attempts as avg_score' => fn($q) => $q->where('status', 'completed')], 'score')
            ->get()
            ->map(fn($s) => [
                'quiz_title'  => $s->title,
                'avg_score'   => round($s->avg_score ?? 0, 1),
                'total_taken' => $s->completed_count,
            ]);

        return response()->json([
            'role' => 'guru',
            'stats' => [
                'total_soal'      => Question::where('created_by', $user->id)->count(),
                'total_quiz'      => QuizSetting::where('created_by', $user->id)->count(),
                'total_percobaan' => $attempts->count(),
                'rata_nilai'      => round($attempts->avg('score') ?? 0, 1),
                'nilai_tertinggi' => $attempts->max('score') ?? 0,
                'nilai_terendah'  => $attempts->min('score') ?? 0,
            ],
            'quiz_graph' => $quizGraph,
            'recent_results' => QuizAttempt::with(['user', 'quizSetting'])
                ->whereIn('quiz_setting_id', $settingIds)
                ->where('status', 'completed')
                ->latest('finished_at')
                ->take(10)
                ->get()
                ->map(fn($a) => [
                    'siswa'     => $a->user->name,
                    'quiz'      => $a->quizSetting->title,
                    'skor'      => $a->score,
                    'benar'     => $a->correct_answers,
                    'salah'     => $a->wrong_answers,
                    'selesai'   => $a->finished_at?->format('d/m/Y H:i'),
                ]),
        ]);
    }

    private function siswaDashboard(User $user): JsonResponse
    {
        $attempts = QuizAttempt::where('user_id', $user->id)
            ->where('status', 'completed');

        $lastAttempt = QuizAttempt::with('quizSetting')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('finished_at')
            ->first();

        $totalMaterials = Material::published()->count();
        $completedMaterials = StudentProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        return response()->json([
            'role' => 'siswa',
            'stats' => [
                'nilai_terakhir'   => $lastAttempt?->score ?? 0,
                'quiz_terakhir'    => $lastAttempt?->quizSetting?->title,
                'rata_nilai'       => round($attempts->avg('score') ?? 0, 1),
                'total_quiz'       => $attempts->count(),
                'materi_selesai'   => $completedMaterials,
                'total_materi'     => $totalMaterials,
                'progress_persen'  => $totalMaterials > 0
                    ? round(($completedMaterials / $totalMaterials) * 100, 0)
                    : 0,
            ],
            'recent_attempts' => QuizAttempt::with('quizSetting')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->latest('finished_at')
                ->take(5)
                ->get()
                ->map(fn($a) => [
                    'quiz'    => $a->quizSetting->title,
                    'skor'    => $a->score,
                    'benar'   => $a->correct_answers,
                    'salah'   => $a->wrong_answers,
                    'tanggal' => $a->finished_at?->format('d/m/Y H:i'),
                ]),
        ]);
    }
}
