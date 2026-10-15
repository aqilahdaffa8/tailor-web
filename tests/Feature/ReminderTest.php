<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Services\ReminderService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

/**
 * Test Reminder. Pengecekan berjalan atas seluruh data di database, jadi setiap assert
 * dibatasi pada pesanan/bahan milik test (lewat referensi_id), bukan pada total reminder.
 * Semua operasi memakai factory (tanpa Stored Procedure) sehingga ikut di-rollback oleh DatabaseTransactions.
 */
class ReminderTest extends PesananTestCase
{
    protected function bersihkanDataTest(): void
    {
        parent::bersihkanDataTest();

        Reminder::where('judul', 'like', self::PREFIX.'%')->delete();
    }

    protected function sinkronkan(): array
    {
        return app(ReminderService::class)->sinkronkan();
    }

    protected function reminderUntuk(string $tipe, int $referensiId, string $referensiTipe = 'pesanan'): ?Reminder
    {
        return Reminder::where('tipe', $tipe)
            ->where('referensi_tipe', $referensiTipe)
            ->where('referensi_id', $referensiId)
            ->first();
    }

    protected function jumlahReminderUntuk(string $tipe, int $referensiId, string $referensiTipe = 'pesanan'): int
    {
        return Reminder::where('tipe', $tipe)
            ->where('referensi_tipe', $referensiTipe)
            ->where('referensi_id', $referensiId)
            ->count();
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_route_reminder_memerlukan_login(): void
    {
        $this->put('/reminders/1/dismiss')->assertRedirect('/login');
        $this->post('/reminders/dismiss-semua')->assertRedirect('/login');
        $this->post('/reminders/sinkronkan')->assertRedirect('/login');

        $this->getJson('/api/reminders')->assertStatus(401);
        $this->putJson('/api/reminders/1/dismiss')->assertStatus(401);
    }

    // ------------------------------------------------------------------
    // Deteksi
    // ------------------------------------------------------------------

    public function test_pesanan_mendekati_deadline_membuat_reminder(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->addDays(2)->toDateString()]);

        $this->sinkronkan();

        $reminder = $this->reminderUntuk('deadline', $pesanan->id);
        $this->assertNotNull($reminder);
        $this->assertFalse($reminder->is_read);
        $this->assertStringContainsString($pesanan->nomor_pesanan, $reminder->judul);
        $this->assertNull($this->reminderUntuk('terlambat', $pesanan->id));
    }

    public function test_pesanan_terlambat_membuat_reminder(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->subDays(2)->toDateString()]);

        $this->sinkronkan();

        $this->assertNotNull($this->reminderUntuk('terlambat', $pesanan->id));
        $this->assertNull($this->reminderUntuk('deadline', $pesanan->id));
    }

    public function test_pesanan_aktif_dengan_deadline_jauh_tidak_membuat_reminder(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->addDays(14)->toDateString()]);

        $this->sinkronkan();

        foreach (ReminderService::TIPE as $tipe) {
            $this->assertNull($this->reminderUntuk($tipe, $pesanan->id), "Tidak boleh ada reminder {$tipe}.");
        }
    }

    public function test_pesanan_dibatalkan_tidak_membuat_reminder(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, [
            'deadline' => now()->subDays(2)->toDateString(),
            'status' => 'dibatalkan',
        ]);

        $this->sinkronkan();

        foreach (ReminderService::TIPE as $tipe) {
            $this->assertNull($this->reminderUntuk($tipe, $pesanan->id));
        }
    }

    public function test_pesanan_selesai_belum_lunas_membuat_reminder_pembayaran_dan_siap_diambil(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['status' => 'selesai'], ['harga' => 200000]);

        $this->sinkronkan();

        $pembayaran = $this->reminderUntuk('pembayaran', $pesanan->id);
        $this->assertNotNull($pembayaran);
        $this->assertStringContainsString('200.000', $pembayaran->pesan);
        $this->assertNotNull($this->reminderUntuk('siap_diambil', $pesanan->id));
    }

    public function test_pesanan_selesai_dan_lunas_hanya_membuat_reminder_siap_diambil(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['status' => 'selesai'], ['harga' => 200000]);
        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", [
                'jumlah' => 200000,
                'metode' => 'tunai',
                'tanggal_bayar' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->sinkronkan();

        $this->assertNull($this->reminderUntuk('pembayaran', $pesanan->id));
        $this->assertNotNull($this->reminderUntuk('siap_diambil', $pesanan->id));
    }

    public function test_pesanan_aktif_belum_lunas_tidak_membuat_reminder_pembayaran(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['status' => 'aktif'], ['harga' => 200000]);

        $this->sinkronkan();

        $this->assertNull($this->reminderUntuk('pembayaran', $pesanan->id));
    }

    public function test_stok_menipis_membuat_reminder_dan_stok_cukup_tidak(): void
    {
        $menipis = $this->buatBahan(0.5, 'Benang Menipis');
        $cukup = $this->buatBahan(10, 'Benang Cukup');

        $this->sinkronkan();

        $reminder = $this->reminderUntuk('stok', $menipis->id, 'bahan');
        $this->assertNotNull($reminder);
        $this->assertStringContainsString($menipis->nama_bahan, $reminder->pesan);
        $this->assertNull($this->reminderUntuk('stok', $cukup->id, 'bahan'));
    }

    // ------------------------------------------------------------------
    // Sinkronisasi
    // ------------------------------------------------------------------

    public function test_sinkronisasi_idempotent_tidak_membuat_duplikat(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->subDay()->toDateString()]);

        $this->sinkronkan();
        $kedua = $this->sinkronkan();

        $this->assertSame(1, $this->jumlahReminderUntuk('terlambat', $pesanan->id));
        $this->assertSame(0, $kedua['dibuat']);
    }

    public function test_reminder_yang_sudah_dismiss_tidak_dibuat_ulang(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->subDay()->toDateString()]);
        $this->sinkronkan();

        $reminder = $this->reminderUntuk('terlambat', $pesanan->id);
        app(ReminderService::class)->dismiss($reminder);

        $this->sinkronkan();

        $this->assertSame(1, $this->jumlahReminderUntuk('terlambat', $pesanan->id));
        $this->assertTrue($this->reminderUntuk('terlambat', $pesanan->id)->is_read);
    }

    public function test_reminder_dihapus_saat_kondisi_tidak_berlaku_lagi(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->subDay()->toDateString()]);
        $bahan = $this->buatBahan(0.5, 'Kancing');
        $this->sinkronkan();

        $this->assertNotNull($this->reminderUntuk('terlambat', $pesanan->id));
        $this->assertNotNull($this->reminderUntuk('stok', $bahan->id, 'bahan'));

        $pesanan->update(['status' => 'dibatalkan']);
        $bahan->update(['stok' => 20]);
        $hasil = $this->sinkronkan();

        $this->assertNull($this->reminderUntuk('terlambat', $pesanan->id));
        $this->assertNull($this->reminderUntuk('stok', $bahan->id, 'bahan'));
        $this->assertGreaterThanOrEqual(2, $hasil['dihapus']);
    }

    public function test_reminder_berpindah_dari_deadline_ke_terlambat(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->addDay()->toDateString()]);
        $this->sinkronkan();
        $this->assertNotNull($this->reminderUntuk('deadline', $pesanan->id));

        $pesanan->update(['deadline' => now()->subDay()->toDateString()]);
        $this->sinkronkan();

        $this->assertNull($this->reminderUntuk('deadline', $pesanan->id));
        $this->assertNotNull($this->reminderUntuk('terlambat', $pesanan->id));
    }

    public function test_pesan_diperbarui_tanpa_mengubah_status_dibaca(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['status' => 'selesai'], ['harga' => 200000]);
        $this->sinkronkan();

        $awal = $this->reminderUntuk('pembayaran', $pesanan->id);
        app(ReminderService::class)->dismiss($awal);

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", [
                'jumlah' => 50000,
                'metode' => 'tunai',
                'tanggal_bayar' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $hasil = $this->sinkronkan();

        $akhir = $this->reminderUntuk('pembayaran', $pesanan->id);
        $this->assertSame($awal->id, $akhir->id);
        $this->assertTrue($akhir->is_read);
        $this->assertStringContainsString('150.000', $akhir->pesan);
        $this->assertGreaterThanOrEqual(1, $hasil['diperbarui']);
    }

    // ------------------------------------------------------------------
    // Web
    // ------------------------------------------------------------------

    public function test_web_dismiss_menandai_reminder_dibaca(): void
    {
        $reminder = Reminder::factory()->create(['judul' => self::PREFIX.' dismiss']);

        $this->actingAs($this->user)
            ->put("/reminders/{$reminder->id}/dismiss")
            ->assertRedirect();

        $this->assertTrue($reminder->fresh()->is_read);
    }

    public function test_web_dismiss_semua(): void
    {
        $satu = Reminder::factory()->create(['judul' => self::PREFIX.' satu']);
        $dua = Reminder::factory()->create(['judul' => self::PREFIX.' dua']);

        $this->actingAs($this->user)
            ->post('/reminders/dismiss-semua')
            ->assertSessionHas('success');

        $this->assertTrue($satu->fresh()->is_read);
        $this->assertTrue($dua->fresh()->is_read);
        $this->assertSame(0, Reminder::belumDibaca()->count());
    }

    public function test_web_periksa_sekarang_menjalankan_sinkronisasi(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->subDay()->toDateString()]);

        $this->actingAs($this->user)
            ->post('/reminders/sinkronkan')
            ->assertSessionHas('success');

        $this->assertNotNull($this->reminderUntuk('terlambat', $pesanan->id));
    }

    public function test_navbar_menampilkan_reminder_belum_dibaca_dan_tautannya(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        Reminder::factory()->create([
            'judul' => self::PREFIX.' bell',
            'tipe' => 'terlambat',
            'referensi_tipe' => 'pesanan',
            'referensi_id' => $pesanan->id,
        ]);

        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(self::PREFIX.' bell')
            ->assertSee(route('pesanan.show', $pesanan->id), false);
    }

    public function test_navbar_tidak_menampilkan_reminder_yang_sudah_dibaca(): void
    {
        Reminder::factory()->dibaca()->create(['judul' => self::PREFIX.' sudah-dibaca']);

        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee(self::PREFIX.' sudah-dibaca');
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_daftar_reminder_hanya_yang_aktif(): void
    {
        $aktif = Reminder::factory()->create(['judul' => self::PREFIX.' aktif']);
        $dibaca = Reminder::factory()->dibaca()->create(['judul' => self::PREFIX.' dibaca']);

        $response = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/reminders')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['jumlah_belum_dibaca']]);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($aktif->id));
        $this->assertFalse($ids->contains($dibaca->id));
        $this->assertSame(count($response->json('data')), $response->json('meta.jumlah_belum_dibaca'));
    }

    public function test_api_dismiss_reminder(): void
    {
        $reminder = Reminder::factory()->create(['judul' => self::PREFIX.' api']);

        $this->withHeaders($this->apiHeaders())
            ->putJson("/api/reminders/{$reminder->id}/dismiss")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertTrue($reminder->fresh()->is_read);

        $this->withHeaders($this->apiHeaders())
            ->putJson('/api/reminders/999999999/dismiss')
            ->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Command, scheduler, tautan
    // ------------------------------------------------------------------

    public function test_command_sinkronkan_berjalan(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['deadline' => now()->subDay()->toDateString()]);

        $this->artisan('reminder:sinkronkan')->assertSuccessful();

        $this->assertNotNull($this->reminderUntuk('terlambat', $pesanan->id));
    }

    public function test_command_terdaftar_di_scheduler_harian(): void
    {
        Artisan::all(); // memuat routes/console.php agar jadwal terdaftar

        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains((string) $event->command, 'reminder:sinkronkan'));

        $this->assertNotNull($event, 'reminder:sinkronkan belum dijadwalkan.');
        $this->assertSame('0 7 * * *', $event->expression);
    }

    public function test_tautan_reminder(): void
    {
        $pesanan = new Reminder(['referensi_tipe' => 'pesanan', 'referensi_id' => 5]);
        $bahan = new Reminder(['referensi_tipe' => 'bahan', 'referensi_id' => 3]);
        $tanpa = new Reminder(['referensi_tipe' => null, 'referensi_id' => null]);

        $this->assertSame(route('pesanan.show', 5), $pesanan->tautan);
        $this->assertSame(route('inventory.index', ['tab' => 'bahan']), $bahan->tautan);
        $this->assertNull($tanpa->tautan);
    }
}
