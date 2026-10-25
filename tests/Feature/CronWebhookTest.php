<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CronWebhookTest extends TestCase
{
    public function test_cron_webhook_ditolak_jika_env_cron_token_kosong(): void
    {
        config(['services.cron_token' => null]);

        $response = $this->postJson('/api/internal/cron/reminder', [], [
            'X-Cron-Token' => 'some-token',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Cron token belum dikonfigurasi di server.',
        ]);
    }

    public function test_cron_webhook_ditolak_jika_token_salah(): void
    {
        config(['services.cron_token' => 'secret-cron-token-123']);

        $response = $this->postJson('/api/internal/cron/reminder', [], [
            'X-Cron-Token' => 'token-yang-salah',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Token cron tidak valid.',
        ]);
    }

    public function test_cron_webhook_berhasil_dieksekusi_jika_token_valid(): void
    {
        config(['services.cron_token' => 'secret-cron-token-123']);

        // Fake artisan call agar tidak perlu menyentuh database
        Artisan::shouldReceive('call')
            ->once()
            ->with('reminder:sinkronkan')
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Reminder sinkronisasi selesai.');

        $response = $this->postJson('/api/internal/cron/reminder', [], [
            'X-Cron-Token' => 'secret-cron-token-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Reminder berhasil disinkronkan.',
            'output' => 'Reminder sinkronisasi selesai.',
        ]);
    }
}
