<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SinkronkanReminder extends Command
{
    protected $signature = 'reminder:sinkronkan';

    protected $description = 'Deteksi deadline, keterlambatan, pembayaran, pesanan siap diambil, dan stok menipis lalu perbarui reminder';

    public function handle(ReminderService $reminderService): int
    {
        $hasil = $reminderService->sinkronkan();

        $this->info("Reminder disinkronkan: {$hasil['aktif']} aktif, {$hasil['dibuat']} baru, {$hasil['diperbarui']} diperbarui, {$hasil['dihapus']} dihapus.");

        return self::SUCCESS;
    }
}
