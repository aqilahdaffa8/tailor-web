<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;

class ReminderController extends Controller
{
    public function __construct(private readonly ReminderService $reminderService) {}

    /**
     * GET /api/reminders
     * Daftar reminder aktif (belum di-dismiss), terbaru di atas.
     */
    public function index(): JsonResponse
    {
        $daftar = $this->reminderService->daftarAktif();

        return response()->json([
            'success' => true,
            'data' => $daftar,
            'meta' => ['jumlah_belum_dibaca' => $daftar->count()],
        ]);
    }

    /**
     * PUT /api/reminders/{reminder}/dismiss
     */
    public function dismiss(Reminder $reminder): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pengingat ditandai sudah dibaca.',
            'data' => $this->reminderService->dismiss($reminder),
        ]);
    }
}
