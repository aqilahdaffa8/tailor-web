<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $wantsJson = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($wantsJson);

        // Format error API seragam: { success: false, message, errors? }
        // (respons sukses memakai { success: true, data?, message?, meta? }).
        $exceptions->render(function (ValidationException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Belum login atau token tidak valid.',
            ], 401);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request)) {
                return null;
            }

            $status = $e->getStatusCode();
            $pesan = match ($status) {
                403 => 'Anda tidak memiliki akses untuk tindakan ini.',
                405 => 'Metode HTTP tidak diizinkan untuk endpoint ini.',
                419 => 'Sesi telah berakhir. Muat ulang halaman lalu coba lagi.',
                429 => 'Terlalu banyak permintaan. Coba lagi beberapa saat lagi.',
                default => $e->getMessage() !== '' ? $e->getMessage() : 'Terjadi kesalahan.',
            };

            return response()->json(['success' => false, 'message' => $pesan], $status, $e->getHeaders());
        });

        // Kesalahan tak terduga: sembunyikan detail di produksi (saat APP_DEBUG=true tetap tampil untuk debugging).
        $exceptions->render(function (Throwable $e, Request $request) use ($wantsJson) {
            if (! $wantsJson($request) || config('app.debug')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        });
    })->create();
