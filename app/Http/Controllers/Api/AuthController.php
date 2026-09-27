<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Login untuk aplikasi Mobile (mengembalikan token Sanctum)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->authenticate(
            $request->validated('email'),
            $request->validated('password')
        );

        $deviceName = $request->validated('device_name') ?? 'mobile-app';
        $token = $this->authService->createApiToken($user, $deviceName);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Logout untuk aplikasi Mobile (revoke token aktif)
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeCurrentToken($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token telah dicabut.',
        ]);
    }

    /**
     * Mengambil profil pengguna yang sedang login
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
