<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Http\RedirectResponse;

class ReminderController extends Controller
{
    public function __construct(private readonly ReminderService $reminderService) {}

    public function dismiss(Reminder $reminder): RedirectResponse
    {
        $this->reminderService->dismiss($reminder);

        return back();
    }

    public function dismissSemua(): RedirectResponse
    {
        $this->reminderService->dismissSemua();

        return back()->with('success', 'Semua pengingat ditandai sudah dibaca.');
    }

    /**
     * Jalankan pengecekan sekarang tanpa menunggu scheduler harian.
     */
    public function sinkronkan(): RedirectResponse
    {
        $hasil = $this->reminderService->sinkronkan();

        return back()->with('success', "Pengecekan selesai: {$hasil['dibuat']} pengingat baru, {$hasil['dihapus']} dihapus karena sudah tidak relevan.");
    }
}
