<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * Siswa mendaftar sendiri.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'confirmed', Password::min(8)],
            'nisn'                  => 'nullable|string|max:20',
            'kelas'                 => 'nullable|string|max:50',
            'phone'                 => 'nullable|string|max:20',
            'gender'                => 'nullable|in:L,P',
        ]);

        $siswaRole = Role::where('name', 'siswa')->firstOrFail();

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'nisn'     => $request->nisn,
            'kelas'    => $request->kelas,
            'phone'    => $request->phone,
            'gender'   => $request->gender,
            'role_id'  => $siswaRole->id,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil. Selamat datang di SundaLearn!',
            'token'   => $token,
            'user'    => $this->formatUser($user->load('role')),
        ], 201);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()->load('role')),
        ]);
    }

    /**
     * PUT /api/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gender'  => 'nullable|in:L,P',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $this->formatUser($user->fresh('role')),
        ]);
    }

    /**
     * PUT /api/profile/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'username' => $user->username,
            'role'     => $user->role->name ?? null,
            'role_display' => $user->role->display_name ?? null,
            'nisn'     => $user->nisn,
            'kelas'    => $user->kelas,
            'nip'      => $user->nip,
            'phone'    => $user->phone,
            'address'  => $user->address,
            'gender'   => $user->gender,
            'is_active' => $user->is_active,
            'can_view_all' => $user->can_view_all,
        ];
    }
}
