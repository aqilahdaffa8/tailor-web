<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Verifikasi kredensial pengguna
     *
     * @throws ValidationException
     */
    public function authenticate(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang dimasukkan tidak cocok dengan catatan kami.'],
            ]);
        }

        return $user;
    }

    /**
     * Menerbitkan token Sanctum untuk akses Mobile API
     */
    public function createApiToken(User $user, string $deviceName = 'mobile-device'): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Mencabut token aktif pengguna (Logout API)
     */
    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
