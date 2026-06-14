<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    /**
     * GET /api/admin/dashboard
     * Statistik sistem untuk admin.
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'total_guru'    => User::whereHas('role', fn($q) => $q->where('name', 'guru'))->count(),
                'total_siswa'   => User::whereHas('role', fn($q) => $q->where('name', 'siswa'))->count(),
                'total_materi'  => Material::count(),
                'total_soal'    => Question::count(),
                'total_quiz'    => QuizAttempt::where('status', 'completed')->count(),
            ],
            'recent_users' => User::with('role')
                ->latest()
                ->take(10)
                ->get()
                ->map(fn($u) => [
                    'id'   => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role->display_name,
                    'created_at' => $u->created_at->format('d/m/Y'),
                ]),
        ]);
    }

    // ───────────────────────── KELOLA GURU ─────────────────────────

    /**
     * GET /api/admin/teachers
     */
    public function indexTeachers(Request $request): JsonResponse
    {
        $teachers = User::with('role')
            ->whereHas('role', fn($q) => $q->where('name', 'guru'))
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%");
            }))
            ->paginate(15);

        return response()->json($teachers);
    }

    /**
     * POST /api/admin/teachers
     */
    public function storeTeacher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'nip'      => 'nullable|string|max:50',
            'phone'    => 'nullable|string|max:20',
            'gender'   => 'nullable|in:L,P',
        ]);

        $guruRole = Role::where('name', 'guru')->firstOrFail();
        $teacher = User::create([...$validated, 'role_id' => $guruRole->id]);

        return response()->json([
            'message' => 'Guru berhasil ditambahkan.',
            'user'    => $teacher,
        ], 201);
    }

    /**
     * PUT /api/admin/teachers/{id}
     */
    public function updateTeacher(Request $request, int $id): JsonResponse
    {
        $teacher = User::whereHas('role', fn($q) => $q->where('name', 'guru'))->findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'nip'       => 'nullable|string|max:50',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:L,P',
            'is_active' => 'boolean',
        ]);

        $teacher->update($validated);

        return response()->json(['message' => 'Data guru berhasil diperbarui.', 'user' => $teacher]);
    }

    /**
     * DELETE /api/admin/teachers/{id}
     */
    public function destroyTeacher(int $id): JsonResponse
    {
        $teacher = User::whereHas('role', fn($q) => $q->where('name', 'guru'))->findOrFail($id);
        $teacher->delete();

        return response()->json(['message' => 'Guru berhasil dihapus.']);
    }

    // ───────────────────────── KELOLA SISWA ─────────────────────────

    /**
     * GET /api/admin/students
     */
    public function indexStudents(Request $request): JsonResponse
    {
        $students = User::with('role')
            ->whereHas('role', fn($q) => $q->where('name', 'siswa'))
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")->orWhere('nisn', 'like', "%$s%");
            }))
            ->paginate(15);

        return response()->json($students);
    }

    /**
     * POST /api/admin/students
     */
    public function storeStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'nisn'     => 'nullable|string|max:20',
            'phone'    => 'nullable|string|max:20',
            'gender'   => 'nullable|in:L,P',
        ]);

        $siswaRole = Role::where('name', 'siswa')->firstOrFail();
        $student = User::create([...$validated, 'role_id' => $siswaRole->id]);

        return response()->json([
            'message' => 'Siswa berhasil ditambahkan.',
            'user'    => $student,
        ], 201);
    }

    /**
     * PUT /api/admin/students/{id}
     */
    public function updateStudent(Request $request, int $id): JsonResponse
    {
        $student = User::whereHas('role', fn($q) => $q->where('name', 'siswa'))->findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'nisn'      => 'nullable|string|max:20',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:L,P',
            'is_active' => 'boolean',
        ]);

        $student->update($validated);

        return response()->json(['message' => 'Data siswa berhasil diperbarui.', 'user' => $student]);
    }

    /**
     * DELETE /api/admin/students/{id}
     */
    public function destroyStudent(int $id): JsonResponse
    {
        $student = User::whereHas('role', fn($q) => $q->where('name', 'siswa'))->findOrFail($id);
        $student->delete();

        return response()->json(['message' => 'Siswa berhasil dihapus.']);
    }
}
