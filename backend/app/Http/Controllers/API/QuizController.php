<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizSetting;
use App\Models\User;
use App\Services\QuizRandomizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function __construct(private QuizRandomizerService $randomizer) {}

    // ═══════════════════════ PENGATURAN QUIZ (GURU) ═══════════════════════

    /**
     * GET /api/quiz/settings
     */
    public function indexSettings(): JsonResponse
    {
        $this->authorizeGuru();

        $settings = QuizSetting::with('creator')
            // Guru tanpa hak "lihat semua" hanya melihat quiz buatannya sendiri
            ->when(!auth()->user()->canViewAllData(), fn($q) => $q->where('created_by', auth()->id()))
            ->latest()
            ->paginate(10);

        return response()->json($settings);
    }

    /**
     * POST /api/quiz/settings
     * Guru membuat pengaturan quiz.
     */
    public function storeSettings(Request $request): JsonResponse
    {
        $this->authorizeGuru();

        $validated = $this->validateSettingsPayload($request);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $setting = QuizSetting::create([...$validated, 'created_by' => auth()->id()]);

        return response()->json([
            'message' => 'Pengaturan quiz berhasil dibuat.',
            'data'    => $setting,
        ], 201);
    }

    /**
     * PUT /api/quiz/settings/{id}
     */
    public function updateSettings(Request $request, int $id): JsonResponse
    {
        $this->authorizeGuru();

        $setting = QuizSetting::when(
            !auth()->user()->canViewAllData(),
            fn($q) => $q->where('created_by', auth()->id())
        )->findOrFail($id);

        $validated = $this->validateSettingsPayload($request);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $setting->update($validated);

        return response()->json(['message' => 'Pengaturan quiz diperbarui.', 'data' => $setting->fresh()]);
    }

    /**
     * Validasi & normalisasi payload pengaturan quiz.
     * Mendukung dua mode pemilihan soal: 'random' dan 'manual'.
     *
     * @return array|JsonResponse Data tervalidasi, atau JsonResponse 422 jika gagal.
     */
    private function validateSettingsPayload(Request $request): array|JsonResponse
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'duration_minutes'  => 'required|integer|min:1|max:180',
            'difficulty'        => 'nullable|in:mudah,sedang,sulit,campuran',
            'selection_mode'    => 'required|in:random,manual',
            'total_questions'   => 'required_if:selection_mode,random|nullable|integer|min:1',
            'question_ids'      => 'required|array|min:1',
            'question_ids.*'    => 'integer|exists:questions,id',
            'shuffle_questions' => 'boolean',
            'shuffle_options'   => 'boolean',
            'is_active'         => 'boolean',
        ]);

        if ($validated['selection_mode'] === 'manual') {
            // Jumlah soal mengikuti banyaknya soal yang dipilih guru
            $validated['total_questions'] = count($validated['question_ids']);
        } else {
            // Mode acak: validasi total_questions tidak melebihi pool yang dipilih
            $poolCount = count($validated['question_ids']);
            if ($validated['total_questions'] > $poolCount) {
                return response()->json([
                    'message' => "Jumlah soal per siswa ({$validated['total_questions']}) tidak boleh melebihi soal yang dipilih ($poolCount).",
                ], 422);
            }
        }

        return $validated;
    }

    /**
     * DELETE /api/quiz/settings/{id}
     */
    public function destroySettings(int $id): JsonResponse
    {
        $this->authorizeGuru();

        QuizSetting::when(
            !auth()->user()->canViewAllData(),
            fn($q) => $q->where('created_by', auth()->id())
        )->findOrFail($id)->delete();

        return response()->json(['message' => 'Pengaturan quiz dihapus.']);
    }

    // ═══════════════════════ MENGERJAKAN QUIZ (SISWA) ═══════════════════════

    /**
     * GET /api/quiz/available
     * Daftar quiz yang tersedia untuk siswa.
     */
    public function available(): JsonResponse
    {
        // ID quiz yang sudah pernah diselesaikan siswa ini
        $completedIds = QuizAttempt::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->pluck('quiz_setting_id')
            ->unique();

        $settings = QuizSetting::active()
            ->with('creator')
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'title'           => $s->title,
                'description'     => $s->description,
                'total_questions' => $s->total_questions,
                'duration_minutes' => $s->duration_minutes,
                'difficulty'      => $s->difficulty,
                'creator'         => $s->creator->name,
                'is_completed'    => $completedIds->contains($s->id),
            ]);

        return response()->json(['data' => $settings]);
    }

    /**
     * POST /api/quiz/start
     * Siswa memulai quiz → Fisher-Yates Shuffle diterapkan di sini.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_setting_id' => 'required|exists:quiz_settings,id',
        ]);

        $setting = QuizSetting::active()->findOrFail($request->quiz_setting_id);

        // Bersihkan sesi yang belum selesai agar selalu mulai dengan set soal baru
        QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_setting_id', $setting->id)
            ->where('status', 'in_progress')
            ->delete();

        // Gunakan Fisher-Yates Shuffle untuk mengacak soal
        $questionIds = $this->randomizer->getRandomizedQuestions($setting);

        $attempt = QuizAttempt::create([
            'user_id'         => auth()->id(),
            'quiz_setting_id' => $setting->id,
            'question_order'  => $questionIds,
            'total_questions' => count($questionIds),
            'started_at'      => now(),
            'status'          => 'in_progress',
        ]);

        // Ambil soal pertama
        $questions = $this->loadQuestionsForAttempt($attempt, $setting);

        return response()->json([
            'message'         => 'Quiz dimulai.',
            'attempt_id'      => $attempt->id,
            'total_questions' => $attempt->total_questions,
            'duration_minutes' => $setting->duration_minutes,
            'questions'       => $questions,
        ]);
    }

    /**
     * POST /api/quiz/submit
     * Siswa mengumpulkan jawaban quiz.
     */
    public function submit(Request $request): JsonResponse
    {
        $request->validate([
            'attempt_id' => 'required|exists:quiz_attempts,id',
            'answers'    => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer'      => 'required|string',
            'answers.*.time_spent'  => 'nullable|integer',
        ]);

        $attempt = QuizAttempt::where('user_id', auth()->id())
            ->where('id', $request->attempt_id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        DB::transaction(function () use ($attempt, $request) {
            $correct = 0;
            $wrong   = 0;

            foreach ($request->answers as $answerData) {
                $question   = Question::findOrFail($answerData['question_id']);
                $userAnswer = trim($answerData['answer']);

                // Pilihan ganda: correct_answer berupa kunci (a-d). Bandingkan
                // berdasarkan NILAI opsi agar tetap benar walau opsi diacak.
                $correctAnswer = $question->type === 'multiple_choice'
                    ? ($question->options[$question->correct_answer] ?? $question->correct_answer)
                    : $question->correct_answer;

                $isCorrect = mb_strtolower($userAnswer) === mb_strtolower($correctAnswer);

                QuizAnswer::create([
                    'quiz_attempt_id'   => $attempt->id,
                    'question_id'       => $question->id,
                    'user_answer'       => $userAnswer,
                    'is_correct'        => $isCorrect,
                    'time_spent_seconds' => $answerData['time_spent'] ?? null,
                ]);

                $isCorrect ? $correct++ : $wrong++;
            }

            $score = $attempt->total_questions > 0
                ? round(($correct / $attempt->total_questions) * 100, 2)
                : 0;

            $attempt->update([
                'correct_answers'   => $correct,
                'wrong_answers'     => $wrong,
                'score'             => $score,
                'finished_at'       => now(),
                'time_spent_seconds' => (int) abs($attempt->started_at->diffInSeconds(now())),
                'status'            => 'completed',
            ]);
        });

        $attempt->refresh();

        return response()->json([
            'message'          => 'Quiz selesai!',
            'score'            => $attempt->score,
            'correct_answers'  => $attempt->correct_answers,
            'wrong_answers'    => $attempt->wrong_answers,
            'total_questions'  => $attempt->total_questions,
            'time_spent'       => $attempt->time_spent_seconds,
            'attempt_id'       => $attempt->id,
        ]);
    }

    /**
     * GET /api/quiz/history
     * Riwayat quiz siswa.
     */
    public function history(Request $request): JsonResponse
    {
        $attempts = QuizAttempt::with('quizSetting')
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereHas('quizSetting')
            ->latest()
            ->paginate(10);

        return response()->json($attempts);
    }

    /**
     * GET /api/quiz/results
     * Hasil quiz SEMUA siswa — dapat diakses oleh siswa.
     * Baris milik siswa yang sedang login ditandai dengan is_mine = true.
     */
    public function allResults(): JsonResponse
    {
        $attempts = QuizAttempt::with(['user', 'quizSetting'])
            ->where('status', 'completed')
            ->whereHas('quizSetting')
            ->latest('finished_at')
            ->get();

        $results = $attempts->map(fn($a) => [
            'id'                 => $a->id,
            'quiz_id'            => $a->quiz_setting_id,
            'user_name'          => $a->user?->name,
            'user_kelas'         => $a->user?->kelas,
            'quiz_title'         => $a->quizSetting?->title,
            'score'              => $a->score,
            'correct_answers'    => $a->correct_answers,
            'wrong_answers'      => $a->wrong_answers,
            'time_spent_seconds' => $a->time_spent_seconds,
            'finished_at'        => $a->finished_at,
            'is_mine'            => $a->user_id === auth()->id(),
        ]);

        return response()->json([
            'stats' => [
                'total_attempts' => $attempts->count(),
                'average_score'  => round($attempts->avg('score') ?? 0, 2),
                'highest_score'  => $attempts->max('score') ?? 0,
                'lowest_score'   => $attempts->min('score') ?? 0,
            ],
            'data' => $results,
        ]);
    }

    /**
     * GET /api/quiz/attempts/{id}/review
     * Detail review jawaban setelah quiz selesai.
     */
    public function review(int $id): JsonResponse
    {
        $attempt = QuizAttempt::with(['answers.question', 'quizSetting'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'attempt'  => $attempt,
                'score'    => $attempt->score,
                'answers'  => $attempt->answers->map(fn($a) => [
                    'question'      => $a->question->question_text,
                    'user_answer'   => $a->user_answer,
                    // Pilihan ganda: tampilkan NILAI opsi yang benar, bukan kunci (a-d)
                    'correct_answer' => $a->question->type === 'multiple_choice'
                        ? ($a->question->options[$a->question->correct_answer] ?? $a->question->correct_answer)
                        : $a->question->correct_answer,
                    'is_correct'    => $a->is_correct,
                    'explanation'   => $a->question->explanation,
                ]),
            ],
        ]);
    }

    // ═══════════════════════ STATISTIK GURU ═══════════════════════

    /**
     * GET /api/quiz/teacher/stats
     * Statistik nilai siswa untuk dashboard guru.
     */
    public function teacherStats(): JsonResponse
    {
        $this->authorizeGuru();

        $settingIds = QuizSetting::query()
            ->when(!auth()->user()->canViewAllData(), fn($q) => $q->where('created_by', auth()->id()))
            ->pluck('id');

        $attempts = QuizAttempt::whereIn('quiz_setting_id', $settingIds)
            ->where('status', 'completed');

        return response()->json([
            'data' => [
                'total_attempts' => $attempts->count(),
                'average_score'  => round($attempts->avg('score'), 2),
                'highest_score'  => $attempts->max('score'),
                'lowest_score'   => $attempts->min('score'),
                'score_distribution' => $attempts->get()->groupBy(fn($a) => $this->getScoreRange($a->score)),
            ],
        ]);
    }

    /**
     * GET /api/quiz/teacher/students
     * Daftar nilai siswa per quiz.
     */
    public function studentResults(Request $request): JsonResponse
    {
        $this->authorizeGuru();

        $settingIds = QuizSetting::query()
            ->when(!auth()->user()->canViewAllData(), fn($q) => $q->where('created_by', auth()->id()))
            ->pluck('id');

        $attempts = QuizAttempt::with(['user', 'quizSetting'])
            ->whereIn('quiz_setting_id', $settingIds)
            ->where('status', 'completed')
            ->latest('finished_at')
            ->get();

        $results = $attempts->map(fn($a) => [
            'id'                 => $a->id,
            'quiz_id'            => $a->quiz_setting_id,
            'user_name'          => $a->user?->name,
            'user_kelas'         => $a->user?->kelas,
            'quiz_title'         => $a->quizSetting?->title,
            'score'              => $a->score,
            'correct_answers'    => $a->correct_answers,
            'wrong_answers'      => $a->wrong_answers,
            'time_spent_seconds' => $a->time_spent_seconds,
            'finished_at'        => $a->finished_at,
        ]);

        return response()->json(['data' => $results]);
    }

    // ─────────────────────── Helpers ───────────────────────

    private function loadQuestionsForAttempt(QuizAttempt $attempt, QuizSetting $setting): array
    {
        return Question::whereIn('id', $attempt->question_order)
            ->get()
            ->sortBy(fn($q) => array_search($q->id, $attempt->question_order))
            ->values()
            ->map(function ($q) use ($setting) {
                $data = [
                    'id'            => $q->id,
                    'type'          => $q->type,
                    'difficulty'    => $q->difficulty,
                    'question_text' => $q->question_text,
                ];

                if ($q->type === 'multiple_choice' && $q->options) {
                    $options = $q->options;
                    if ($setting->shuffle_options) {
                        $shuffled = app(QuizRandomizerService::class)->shuffleOptions($options, $q->correct_answer);
                        $data['options'] = $shuffled['options'];
                    } else {
                        $data['options'] = $options;
                    }
                }

                return $data;
            })
            ->toArray();
    }

    private function getScoreRange(float $score): string
    {
        return match (true) {
            $score >= 90 => '90-100',
            $score >= 80 => '80-89',
            $score >= 70 => '70-79',
            $score >= 60 => '60-69',
            default      => '<60',
        };
    }

    private function authorizeGuru(): void
    {
        abort_unless(
            auth()->user()->isGuru() || auth()->user()->isAdmin(),
            403,
            'Hanya guru atau admin yang dapat mengakses fitur ini.'
        );
    }
}
