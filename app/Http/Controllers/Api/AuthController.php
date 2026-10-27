<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        return $this->success('Registrasi berhasil', [
            'user' => $this->profil($user),
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            /*
             * Pesan sengaja dibuat umum: jangan beri tahu apakah email
             * tidak terdaftar atau password salah, agar tidak membantu
             * penyerang menebak akun yang ada.
             */
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        return $this->success('Login berhasil', [
            'user' => $this->profil($user),
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            'Profil pengguna berhasil diambil',
            $this->profil($request->user())
        );
    }

    public function logout(Request $request): JsonResponse
    {
        // Hanya token yang sedang dipakai yang dicabut, sesi lain tetap hidup.
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logout berhasil', null);
    }

    /**
     * Bentuk data user yang aman dikirim ke klien.
     * password dan remember_token tidak pernah ikut.
     */
    private function profil(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dibuat_pada' => $user->created_at?->toIso8601String(),
        ];
    }
}
