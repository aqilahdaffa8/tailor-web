<?php

namespace App\Providers;

use App\Services\ReminderService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Data bell reminder di navbar layout utama.
        View::composer('layouts.app', function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $reminderService = app(ReminderService::class);

            $view->with([
                'reminderJumlah' => $reminderService->jumlahBelumDibaca(),
                'reminderDaftar' => $reminderService->daftarAktif(8),
            ]);
        });
    }
}
