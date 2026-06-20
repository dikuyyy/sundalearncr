<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * GET /api/questions
     * Bank soal (hanya guru dan admin).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeGuru();

        $query = Question::with('creator')
            // Guru tanpa hak "lihat semua" hanya melihat soal buatannya sendiri
            ->when(!auth()->user()->canViewAllData(), fn($q) => $q->where('created_by', auth()->id()))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->difficulty, fn($q, $d) => $q->where('difficulty', $d))
            ->when($request->search, fn($q, $s) => $q->where('question_text', 'like', "%$s%"))
            ->latest();

        // ?all=1 → kembalikan semua soal tanpa paginasi (mis. untuk pemilihan manual quiz)
        if ($request->boolean('all')) {
            return response()->json(['data' => $query->get()]);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * POST /api/questions
     * Guru: tambah soal ke bank soal.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeGuru();

        $validated = $request->validate([
            'type'           => 'required|in:sunda_to_latin,latin_to_sunda,multiple_choice',
            'difficulty'     => 'required|in:mudah,sedang,sulit',
            'question_text'  => 'required|string',
            'correct_answer' => 'required|string',
            'options'        => 'required_if:type,multiple_choice|array|size:4',
            'options.a'      => 'required_if:type,multiple_choice|string',
            'options.b'      => 'required_if:type,multiple_choice|string',
            'options.c'      => 'required_if:type,multiple_choice|string',
            'options.d'      => 'required_if:type,multiple_choice|string',
            'explanation'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $question = Question::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Soal berhasil ditambahkan ke bank soal.',
            'data'    => $question,
        ], 201);
    }

    /**
     * GET /api/questions/{id}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorizeGuru();

        return response()->json(['data' => Question::with('creator')->findOrFail($id)]);
    }

    /**
     * PUT /api/questions/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizeGuru();

        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'type'           => 'required|in:sunda_to_latin,latin_to_sunda,multiple_choice',
            'difficulty'     => 'required|in:mudah,sedang,sulit',
            'question_text'  => 'required|string',
            'correct_answer' => 'required|string',
            'options'        => 'nullable|array',
            'explanation'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $question->update($validated);

        return response()->json([
            'message' => 'Soal berhasil diperbarui.',
            'data'    => $question->fresh(),
        ]);
    }

    /**
     * DELETE /api/questions/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorizeGuru();

        Question::findOrFail($id)->delete();

        return response()->json(['message' => 'Soal berhasil dihapus dari bank soal.']);
    }

    /**
     * GET /api/questions/stats
     * Statistik bank soal untuk dashboard guru.
     */
    public function stats(): JsonResponse
    {
        $this->authorizeGuru();

        // Guru tanpa hak "lihat semua" hanya menghitung soal buatannya sendiri
        $scope = fn() => Question::query()
            ->when(!auth()->user()->canViewAllData(), fn($q) => $q->where('created_by', auth()->id()));

        return response()->json([
            'data' => [
                'total'      => $scope()->count(),
                'by_type'    => $scope()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
                'by_difficulty' => $scope()->selectRaw('difficulty, count(*) as total')->groupBy('difficulty')->pluck('total', 'difficulty'),
                'active'     => $scope()->where('is_active', true)->count(),
            ],
        ]);
    }

    private function authorizeGuru(): void
    {
        abort_unless(
            auth()->user()->isGuru() || auth()->user()->isAdmin(),
            403,
            'Hanya guru atau admin yang dapat mengakses bank soal.'
        );
    }
}
