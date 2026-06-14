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
            ->where('created_by', auth()->id())
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

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'total_questions'   => 'required|integer|min:1|max:100',
            'duration_minutes'  => 'required|integer|min:1|max:180',
            'difficulty'        => 'required|in:mudah,sedang,sulit,campuran',
            'shuffle_questions' => 'boolean',
            'shuffle_options'   => 'boolean',
            'is_active'         => 'boolean',
        ]);

        // Validasi: cukup soal tersedia untuk memenuhi permintaan
        $availableCount = Question::active()
            ->when($validated['difficulty'] !== 'campuran', fn($q) => $q->where('difficulty', $validated['difficulty']))
            ->count();

        if ($availableCount < $validated['total_questions']) {
            return response()->json([
                'message' => "Tidak cukup soal. Tersedia: $availableCount soal, diminta: {$validated['total_questions']} soal.",
            ], 422);
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

        $setting = QuizSetting::where('created_by', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'total_questions'   => 'required|integer|min:1|max:100',
            'duration_minutes'  => 'required|integer|min:1|max:180',
            'difficulty'        => 'required|in:mudah,sedang,sulit,campuran',
            'shuffle_questions' => 'boolean',
            'shuffle_options'   => 'boolean',
            'is_active'         => 'boolean',
        ]);

        $setting->update($validated);

        return response()->json(['message' => 'Pengaturan quiz diperbarui.', 'data' => $setting->fresh()]);
    }

    /**
     * DELETE /api/quiz/settings/{id}
     */
    public function destroySettings(int $id): JsonResponse
    {
        $this->authorizeGuru();

        QuizSetting::where('created_by', auth()->id())->findOrFail($id)->delete();

        return response()->json(['message' => 'Pengaturan quiz dihapus.']);
    }

    // ═══════════════════════ MENGERJAKAN QUIZ (SISWA) ═══════════════════════

    /**
     * GET /api/quiz/available
     * Daftar quiz yang tersedia untuk siswa.
     */
    public function available(): JsonResponse
    {
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

        // Cek apakah ada attempt yang sedang berjalan
        $existing = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_setting_id', $setting->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda memiliki sesi quiz yang belum selesai.',
                'attempt_id' => $existing->id,
            ]);
        }

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

        $setting = $attempt->quizSetting;

        DB::transaction(function () use ($attempt, $request, $setting) {
            $correct = 0;
            $wrong   = 0;

            foreach ($request->answers as $answerData) {
                $question   = Question::findOrFail($answerData['question_id']);
                $userAnswer = trim($answerData['answer']);
                $isCorrect  = mb_strtolower($userAnswer) === mb_strtolower($question->correct_answer);

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
                'time_spent_seconds' => now()->diffInSeconds($attempt->started_at),
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
            ->latest()
            ->paginate(10);

        return response()->json($attempts);
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
                    'correct_answer' => $a->question->correct_answer,
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

        $settingIds = QuizSetting::where('created_by', auth()->id())->pluck('id');

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

        $settingIds = QuizSetting::where('created_by', auth()->id())->pluck('id');

        $results = QuizAttempt::with(['user', 'quizSetting'])
            ->whereIn('quiz_setting_id', $settingIds)
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return response()->json($results);
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
