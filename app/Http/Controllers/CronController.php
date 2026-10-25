<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    /**
     * Endpoint webhook scheduler untuk cron eksternal (mis. cron-job.org).
     * Memanggil artisan command 'reminder:sinkronkan' jika token valid.
     */
    public function reminder(Request $request): JsonResponse
    {
        $expectedToken = config('services.cron.token', env('CRON_TOKEN'));

        // Jika env kosong atau null, selalu tolak demi keamanan
        if (empty($expectedToken) || ! is_string($expectedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Cron token belum dikonfigurasi di server.',
            ], 403);
        }

        $providedToken = (string) $request->header('X-Cron-Token', '');

        if (! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Token cron tidak valid.',
            ], 401);
        }

        Artisan::call('reminder:sinkronkan');

        return response()->json([
            'success' => true,
            'message' => 'Reminder berhasil disinkronkan.',
            'output' => trim(Artisan::output()),
        ]);
    }
}
