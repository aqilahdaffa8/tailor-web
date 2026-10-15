<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengecekan harian reminder (butuh cron: * * * * * php artisan schedule:run)
Schedule::command('reminder:sinkronkan')->dailyAt('07:00')->withoutOverlapping();
