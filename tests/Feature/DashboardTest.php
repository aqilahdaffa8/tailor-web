<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Services\DashboardService;

class DashboardTest extends PesananTestCase
{
    protected function service(): DashboardService
    {
        return app(DashboardService::class);
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_dashboard_memerlukan_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        foreach (['summary', 'pesanan-aktif', 'pesanan-mendekati-deadline', 'pesanan-terlambat', 'stok-menipis'] as $endpoint) {
            $this->getJson("/api/dashboard/{$endpoint}")->assertStatus(401);
        }
    }

    // ------------------------------------------------------------------
    // DashboardService: metric ringkasan
    // ------------------------------------------------------------------

    public function test_ringkasan_menghitung_pesanan_aktif_mendekati_dan_terlambat(): void
    {
        $sebelum = $this->service()->ringkasan();
        $pelanggan = $this->buatPelanggan();

        $this->buatPesananDenganItem($pelanggan, ['deadline' => now()->addDays(30)]);                       // aktif biasa
        $this->buatPesananDenganItem($pelanggan, ['deadline' => now()->addDays(2)]);                        // aktif H-2
        $this->buatPesananDenganItem($pelanggan, ['tanggal_pesan' => now()->subDays(12), 'deadline' => now()->subDays(2)]); // aktif terlambat
        $this->buatPesananDenganItem($pelanggan, ['status' => 'selesai', 'deadline' => now()->addDays(1)]); // tidak dihitung
        $this->buatPesananDenganItem($pelanggan, ['status' => 'dibatalkan', 'deadline' => now()->subDays(5)]); // tidak dihitung

        $sesudah = $this->service()->ringkasan();

        $this->assertSame($sebelum['pesanan_aktif'] + 3, $sesudah['pesanan_aktif']);
        $this->assertSame($sebelum['pesanan_mendekati_deadline'] + 1, $sesudah['pesanan_mendekati_deadline']);
        $this->assertSame($sebelum['pesanan_terlambat'] + 1, $sesudah['pesanan_terlambat']);
    }

    public function test_ringkasan_menghitung_pembayaran_belum_lunas_dengan_stored_function(): void
    {
        $sebelum = $this->service()->ringkasan()['pembayaran_belum_lunas'];
        $pelanggan = $this->buatPelanggan();

        // Sisa 150.000 -> dihitung.
        $a = $this->buatPesananDenganItem($pelanggan, [], ['harga' => 200000]);
        Pembayaran::create(['pesanan_id' => $a->id, 'jumlah' => 50000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        // Lunas -> tidak dihitung.
        $b = $this->buatPesananDenganItem($pelanggan, [], ['harga' => 100000]);
        Pembayaran::create(['pesanan_id' => $b->id, 'jumlah' => 100000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        // Dibatalkan dan belum bayar -> tidak dihitung.
        $this->buatPesananDenganItem($pelanggan, ['status' => 'dibatalkan'], ['harga' => 300000]);

        $sesudah = $this->service()->ringkasan()['pembayaran_belum_lunas'];

        $this->assertSame($sebelum['jumlah_pesanan'] + 1, $sesudah['jumlah_pesanan']);
        $this->assertEquals($sebelum['total_sisa'] + 150000, $sesudah['total_sisa']);
    }

    public function test_ringkasan_keuangan_hanya_menghitung_bulan_berjalan(): void
    {
        $sebelum = $this->service()->ringkasan()['keuangan_bulan_ini'];
        $pesanan = $this->buatPesananDenganItem(null, [], ['harga' => 500000]);

        $awalBulan = now()->startOfMonth();
        $bulanLalu = $awalBulan->copy()->subDay();

        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 75000, 'metode' => 'tunai', 'tanggal_bayar' => $awalBulan->copy()->addHour()]);
        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 999000, 'metode' => 'tunai', 'tanggal_bayar' => $bulanLalu]);

        Pengeluaran::factory()->create(['nominal' => 20000, 'tanggal' => now()->toDateString()]);
        Pengeluaran::factory()->create(['nominal' => 888000, 'tanggal' => $bulanLalu->toDateString()]);

        $sesudah = $this->service()->ringkasan()['keuangan_bulan_ini'];

        $this->assertEquals($sebelum['pendapatan'] + 75000, $sesudah['pendapatan']);
        $this->assertEquals($sebelum['pengeluaran'] + 20000, $sesudah['pengeluaran']);
        $this->assertEquals($sesudah['pendapatan'] - $sesudah['pengeluaran'], $sesudah['selisih']);
    }

    public function test_ringkasan_menghitung_stok_menipis(): void
    {
        $sebelum = $this->service()->ringkasan()['stok_menipis'];

        $this->buatBahan(0.5, 'Menipis');   // stok minimum 1 -> menipis
        $this->buatBahan(50, 'Aman');

        $this->assertSame($sebelum + 1, $this->service()->ringkasan()['stok_menipis']);
    }

    public function test_periode_ringkasan_memakai_bulan_berjalan(): void
    {
        $periode = $this->service()->ringkasan()['periode'];

        $this->assertSame(now()->startOfMonth()->toDateString(), $periode['awal']);
        $this->assertSame(now()->endOfMonth()->toDateString(), $periode['akhir']);
        $this->assertSame(now()->locale('id')->translatedFormat('F Y'), $periode['label']);
    }

    // ------------------------------------------------------------------
    // Halaman web
    // ------------------------------------------------------------------

    public function test_halaman_dashboard_menampilkan_widget_dengan_data_nyata(): void
    {
        $pelanggan = $this->buatPelanggan();
        $terlambat = $this->buatPesananDenganItem($pelanggan, [
            'nomor_pesanan' => 'ZZT-DASH-TELAT',
            'tanggal_pesan' => now()->subYears(3)->subDays(10),
            'deadline' => now()->subYears(3),
        ]);
        $mendekati = $this->buatPesananDenganItem($pelanggan, [
            'nomor_pesanan' => 'ZZT-DASH-DEKAT',
            'deadline' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Ruang Kerja Atelier')
            ->assertSee('Buat Pesanan Baru')
            ->assertSee('Pesanan Aktif')
            ->assertSee('Mendekati Deadline')
            ->assertSee('Pesanan Terlambat')
            ->assertSee('Belum Lunas')
            ->assertSee('Pendapatan vs Pengeluaran')
            ->assertSee('Stok Bahan Menipis')
            ->assertSee($this->service()->ringkasan()['periode']['label'])
            ->assertSee($terlambat->nomor_pesanan)
            ->assertSee($mendekati->nomor_pesanan)
            ->assertSee($pelanggan->nama);
    }

    public function test_dashboard_menyediakan_shortcut_ke_modul_terkait(): void
    {
        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertSee(route('pesanan.create'))
            ->assertSee(route('pelanggan.create'))
            ->assertSee(route('pesanan.index', ['status' => 'aktif']))
            ->assertSee(route('pesanan.index', ['deadline' => 'terlambat']))
            ->assertSee(route('pesanan.index', ['deadline' => 'mendekati']))
            ->assertSee(route('pesanan.index', ['pembayaran' => 'belum_lunas']))
            ->assertSee(route('inventory.index', ['tab' => 'bahan', 'menipis' => 1]));
    }

    public function test_permintaan_ajax_hanya_mengembalikan_potongan_widget(): void
    {
        $this->actingAs($this->user)
            ->get('/dashboard', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(200)
            ->assertSee('Pesanan Aktif')
            ->assertSee('Stok Bahan Menipis')
            ->assertDontSee('Ruang Kerja Atelier')
            ->assertDontSee('<html', false);
    }

    public function test_widget_menampilkan_keadaan_kosong_bila_tidak_ada_data(): void
    {
        $html = view('dashboard._widget', [
            'ringkasan' => [
                'periode' => ['label' => 'Oktober 2026', 'awal' => '2026-10-01', 'akhir' => '2026-10-31'],
                'pesanan_aktif' => 0,
                'pesanan_mendekati_deadline' => 0,
                'pesanan_terlambat' => 0,
                'pembayaran_belum_lunas' => ['jumlah_pesanan' => 0, 'total_sisa' => 0.0],
                'keuangan_bulan_ini' => ['pendapatan' => 0.0, 'pengeluaran' => 0.0, 'selisih' => 0.0],
                'stok_menipis' => 0,
            ],
            'pesananTerlambat' => collect(),
            'pesananMendekati' => collect(),
            'stokMenipis' => collect(),
        ])->render();

        $this->assertStringContainsString('Tidak ada pesanan yang terlambat.', $html);
        $this->assertStringContainsString('Tidak ada deadline dalam 3 hari ke depan.', $html);
        $this->assertStringContainsString('Semua stok bahan masih aman.', $html);
        $this->assertStringContainsString('Rp 0', $html);
    }

    // ------------------------------------------------------------------
    // Filter "belum lunas" pada daftar pesanan (tujuan shortcut dashboard)
    // ------------------------------------------------------------------

    public function test_filter_pembayaran_belum_lunas_di_daftar_pesanan(): void
    {
        $pelanggan = $this->buatPelanggan();
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-BAYAR-BELUM'], ['harga' => 100000]);
        $lunas = $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-BAYAR-LUNAS'], ['harga' => 100000]);
        Pembayaran::create(['pesanan_id' => $lunas->id, 'jumlah' => 100000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-BAYAR-BATAL', 'status' => 'dibatalkan'], ['harga' => 100000]);

        $this->actingAs($this->user)
            ->get("/pesanan?pelanggan_id={$pelanggan->id}&pembayaran=belum_lunas")
            ->assertSee('ZZT-BAYAR-BELUM')
            ->assertDontSee('ZZT-BAYAR-LUNAS')
            ->assertDontSee('ZZT-BAYAR-BATAL');

        $this->withHeaders($this->apiHeaders())
            ->getJson("/api/pesanan?pelanggan_id={$pelanggan->id}&pembayaran=belum_lunas")
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.nomor_pesanan', 'ZZT-BAYAR-BELUM');
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_summary_mengembalikan_struktur_lengkap(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/dashboard/summary')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periode' => ['label', 'awal', 'akhir'],
                    'pesanan_aktif',
                    'pesanan_mendekati_deadline',
                    'pesanan_terlambat',
                    'pembayaran_belum_lunas' => ['jumlah_pesanan', 'total_sisa'],
                    'keuangan_bulan_ini' => ['pendapatan', 'pengeluaran', 'selisih'],
                    'stok_menipis',
                ],
            ]);
    }

    public function test_api_daftar_pesanan_aktif_terlambat_dan_mendekati(): void
    {
        $headers = $this->apiHeaders();
        $pelanggan = $this->buatPelanggan();
        $terlambat = $this->buatPesananDenganItem($pelanggan, [
            'nomor_pesanan' => 'ZZT-API-TELAT',
            'tanggal_pesan' => now()->subYears(3)->subDays(10),
            'deadline' => now()->subYears(3),
        ]);
        $mendekati = $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-API-DEKAT', 'deadline' => now()]);
        $selesai = $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-API-SELESAI', 'status' => 'selesai']);

        $aktif = collect($this->withHeaders($headers)->getJson('/api/dashboard/pesanan-aktif?limit=100')
            ->assertStatus(200)->assertJsonPath('success', true)->json('data'))->pluck('nomor_pesanan');
        $this->assertTrue($aktif->contains($terlambat->nomor_pesanan));
        $this->assertTrue($aktif->contains($mendekati->nomor_pesanan));
        $this->assertFalse($aktif->contains($selesai->nomor_pesanan));

        $lambat = collect($this->withHeaders($headers)->getJson('/api/dashboard/pesanan-terlambat?limit=100')
            ->assertStatus(200)->json('data'))->pluck('nomor_pesanan');
        $this->assertTrue($lambat->contains($terlambat->nomor_pesanan));
        $this->assertFalse($lambat->contains($mendekati->nomor_pesanan));

        $dekat = collect($this->withHeaders($headers)->getJson('/api/dashboard/pesanan-mendekati-deadline?limit=100')
            ->assertStatus(200)->json('data'))->pluck('nomor_pesanan');
        $this->assertTrue($dekat->contains($mendekati->nomor_pesanan));
        $this->assertFalse($dekat->contains($terlambat->nomor_pesanan));
    }

    public function test_api_pesanan_dashboard_menyertakan_pelanggan_dan_status_bayar(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, [
            'nomor_pesanan' => 'ZZT-API-FIELD',
            'tanggal_pesan' => now()->subYears(3)->subDays(10),
            'deadline' => now()->subYears(3),
        ], ['harga' => 100000]);

        $baris = collect($this->withHeaders($this->apiHeaders())->getJson('/api/dashboard/pesanan-terlambat?limit=100')->json('data'))
            ->firstWhere('nomor_pesanan', 'ZZT-API-FIELD');

        $this->assertNotNull($baris);
        $this->assertSame($pesanan->pelanggan->nama, $baris['pelanggan']['nama']);
        $this->assertSame('Belum Bayar', $baris['status_bayar']);
        $this->assertEquals(100000.0, (float) $baris['sisa_tagihan']);
    }

    public function test_api_stok_menipis_menyertakan_bahan_yang_menipis_saja(): void
    {
        $menipis = $this->buatBahan(0, 'API Menipis');
        $aman = $this->buatBahan(80, 'API Aman');

        $nama = collect($this->withHeaders($this->apiHeaders())->getJson('/api/dashboard/stok-menipis?limit=100')
            ->assertStatus(200)->assertJsonPath('success', true)->json('data'))->pluck('nama_bahan');

        $this->assertTrue($nama->contains($menipis->nama_bahan));
        $this->assertFalse($nama->contains($aman->nama_bahan));
    }

    public function test_api_limit_dibatasi_antara_1_dan_100(): void
    {
        $headers = $this->apiHeaders();
        $pelanggan = $this->buatPelanggan();
        $this->buatPesananDenganItem($pelanggan);
        $this->buatPesananDenganItem($pelanggan);

        $this->assertCount(1, $this->withHeaders($headers)->getJson('/api/dashboard/pesanan-aktif?limit=1')->json('data'));
        $this->assertCount(1, $this->withHeaders($headers)->getJson('/api/dashboard/pesanan-aktif?limit=0')->json('data'));
        $this->assertLessThanOrEqual(100, count($this->withHeaders($headers)->getJson('/api/dashboard/pesanan-aktif?limit=99999')->json('data')));
    }

    public function test_pengeluaran_factory_dan_scope_pesanan_berfungsi(): void
    {
        $pengeluaran = Pengeluaran::factory()->create(['nominal' => 12345, 'tanggal' => now()->toDateString()]);
        $this->assertDatabaseHas('pengeluaran', ['id' => $pengeluaran->id]);

        $pelanggan = $this->buatPelanggan();
        $terlambat = Pesanan::factory()->terlambat()->create(['pelanggan_id' => $pelanggan->id]);
        $dekat = Pesanan::factory()->mendekatiDeadline()->create(['pelanggan_id' => $pelanggan->id]);

        $this->assertTrue(Pesanan::terlambat()->whereKey($terlambat->id)->exists());
        $this->assertFalse(Pesanan::terlambat()->whereKey($dekat->id)->exists());
        $this->assertTrue(Pesanan::mendekatiDeadline()->whereKey($dekat->id)->exists());
        $this->assertTrue(Pesanan::aktif()->whereKey($dekat->id)->exists());
    }
}
