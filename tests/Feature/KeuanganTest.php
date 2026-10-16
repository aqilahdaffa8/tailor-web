<?php

namespace Tests\Feature;

use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Services\DashboardService;
use App\Services\KeuanganService;

/**
 * Test modul Keuangan.
 *
 * Seluruh data test memakai periode Maret 2001 agar total yang di-assert tidak bercampur dengan
 * data lain di database. Pengeluaran test ditandai awalan PREFIX pada catatan dan dibersihkan di tearDown().
 */
class KeuanganTest extends PesananTestCase
{
    private const DARI = '2001-03-01';

    private const SAMPAI = '2001-03-31';

    protected function bersihkanDataTest(): void
    {
        parent::bersihkanDataTest();

        Pengeluaran::where('catatan', 'like', self::PREFIX.'%')->delete();
        KategoriPengeluaran::where('nama_kategori', 'like', self::PREFIX.'%')->delete();
    }

    protected function pesananLimaRatusRibu(): Pesanan
    {
        return $this->buatPesananDenganItem(null, [], ['harga' => 500000]);
    }

    protected function catatPembayaran(Pesanan $pesanan, int $jumlah, string $tanggal, string $metode = 'tunai'): void
    {
        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", [
                'jumlah' => $jumlah,
                'metode' => $metode,
                'tanggal_bayar' => $tanggal,
            ])
            ->assertSessionHasNoErrors();
    }

    protected function buatKategori(string $nama): KategoriPengeluaran
    {
        return KategoriPengeluaran::create(['nama_kategori' => self::PREFIX.' '.$nama]);
    }

    protected function buatPengeluaran(int $nominal, string $tanggal, ?KategoriPengeluaran $kategori = null, string $catatan = 'uji'): Pengeluaran
    {
        return Pengeluaran::factory()->create([
            'kategori_pengeluaran_id' => $kategori?->id,
            'nominal' => $nominal,
            'tanggal' => $tanggal,
            'catatan' => self::PREFIX.' '.$catatan,
        ]);
    }

    /**
     * Data standar: 150.000 pendapatan dan 65.000 pengeluaran di dalam periode,
     * ditambah data di luar periode yang tidak boleh ikut terhitung.
     *
     * @return array{pesanan: Pesanan, kain: KategoriPengeluaran}
     */
    protected function siapkanDataPeriode(): array
    {
        $pesanan = $this->pesananLimaRatusRibu();
        $this->catatPembayaran($pesanan, 100000, '2001-03-05', 'tunai');
        $this->catatPembayaran($pesanan, 50000, '2001-03-31', 'transfer');   // batas akhir: ikut terhitung
        $this->catatPembayaran($pesanan, 20000, '2001-04-01');                // di luar periode
        $this->catatPembayaran($pesanan, 30000, '2001-02-28');                // di luar periode

        $kain = $this->buatKategori('Kain');
        $this->buatPengeluaran(25000, '2001-03-01', $kain, 'awal');           // batas awal: ikut terhitung
        $this->buatPengeluaran(40000, '2001-03-31', null, 'akhir');
        $this->buatPengeluaran(99000, '2001-04-01', $kain, 'luar');           // di luar periode

        return ['pesanan' => $pesanan, 'kain' => $kain];
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_route_keuangan_memerlukan_login(): void
    {
        $this->get('/keuangan')->assertRedirect('/login');
        $this->post('/keuangan/pengeluaran', [])->assertRedirect('/login');
        $this->put('/keuangan/pengeluaran/1', [])->assertRedirect('/login');
        $this->delete('/keuangan/pengeluaran/1')->assertRedirect('/login');

        $this->getJson('/api/keuangan/ringkasan')->assertStatus(401);
        $this->getJson('/api/keuangan/pendapatan')->assertStatus(401);
        $this->getJson('/api/pengeluaran')->assertStatus(401);
        $this->postJson('/api/pengeluaran', [])->assertStatus(401);
        $this->putJson('/api/pengeluaran/1', [])->assertStatus(401);
        $this->deleteJson('/api/pengeluaran/1')->assertStatus(401);
    }

    // ------------------------------------------------------------------
    // Service: periode & ringkasan
    // ------------------------------------------------------------------

    public function test_periode_default_adalah_bulan_berjalan(): void
    {
        $periode = app(KeuanganService::class)->tentukanPeriode();

        $this->assertSame(now()->startOfMonth()->toDateString(), $periode['awal']->toDateString());
        $this->assertSame(now()->endOfMonth()->toDateString(), $periode['akhir']->toDateString());
    }

    public function test_periode_terbalik_dikoreksi_agar_tidak_negatif(): void
    {
        $periode = app(KeuanganService::class)->tentukanPeriode('2001-03-20', '2001-03-10');

        $this->assertSame('2001-03-20', $periode['awal']->toDateString());
        $this->assertSame('2001-03-20', $periode['akhir']->toDateString());
    }

    public function test_ringkasan_menghitung_pendapatan_pengeluaran_dan_selisih(): void
    {
        $this->siapkanDataPeriode();

        $service = app(KeuanganService::class);
        ['awal' => $awal, 'akhir' => $akhir] = $service->tentukanPeriode(self::DARI, self::SAMPAI);
        $ringkasan = $service->ringkasan($awal, $akhir);

        $this->assertEquals(150000, $ringkasan['pendapatan']);
        $this->assertEquals(65000, $ringkasan['pengeluaran']);
        $this->assertEquals(85000, $ringkasan['selisih']);
        $this->assertSame(2, $ringkasan['jumlah_pembayaran']);
        $this->assertSame(2, $ringkasan['jumlah_pengeluaran']);
    }

    public function test_selisih_negatif_saat_pengeluaran_melebihi_pendapatan(): void
    {
        $this->buatPengeluaran(75000, '2001-05-10');

        $service = app(KeuanganService::class);
        ['awal' => $awal, 'akhir' => $akhir] = $service->tentukanPeriode('2001-05-01', '2001-05-31');
        $ringkasan = $service->ringkasan($awal, $akhir);

        $this->assertEquals(0, $ringkasan['pendapatan']);
        $this->assertEquals(-75000, $ringkasan['selisih']);
    }

    public function test_ringkasan_memecah_pendapatan_per_metode_dan_pengeluaran_per_kategori(): void
    {
        $data = $this->siapkanDataPeriode();

        $service = app(KeuanganService::class);
        ['awal' => $awal, 'akhir' => $akhir] = $service->tentukanPeriode(self::DARI, self::SAMPAI);
        $ringkasan = $service->ringkasan($awal, $akhir);

        $perMetode = collect($ringkasan['pendapatan_per_metode'])->keyBy('metode');
        $this->assertEquals(100000, $perMetode['tunai']['total']);
        $this->assertEquals(50000, $perMetode['transfer']['total']);

        $perKategori = collect($ringkasan['pengeluaran_per_kategori'])->keyBy('kategori');
        $this->assertEquals(40000, $perKategori['Tanpa kategori']['total']);
        $this->assertEquals(25000, $perKategori[$data['kain']->nama_kategori]['total']);
    }

    public function test_pendapatan_dashboard_dan_keuangan_memakai_sumber_yang_sama(): void
    {
        $service = app(KeuanganService::class);
        $total = $service->totalPeriode(now()->startOfMonth(), now()->endOfMonth());

        $dashboard = app(DashboardService::class)->ringkasan()['keuangan_bulan_ini'];

        $this->assertEquals($total['pendapatan'], $dashboard['pendapatan']);
        $this->assertEquals($total['pengeluaran'], $dashboard['pengeluaran']);
        $this->assertEquals($total['selisih'], $dashboard['selisih']);
    }

    // ------------------------------------------------------------------
    // Web
    // ------------------------------------------------------------------

    public function test_web_halaman_ringkasan_menampilkan_total_periode(): void
    {
        $this->siapkanDataPeriode();

        $this->actingAs($this->user)
            ->get('/keuangan?dari='.self::DARI.'&sampai='.self::SAMPAI)
            ->assertOk()
            ->assertSee('Rp 150.000', false)
            ->assertSee('Rp 65.000', false)
            ->assertSee('Rp 85.000', false);
    }

    public function test_web_tab_pendapatan_menampilkan_pembayaran_otomatis(): void
    {
        $data = $this->siapkanDataPeriode();

        $this->actingAs($this->user)
            ->get('/keuangan?tab=pendapatan&dari='.self::DARI.'&sampai='.self::SAMPAI)
            ->assertOk()
            ->assertSee($data['pesanan']->nomor_pesanan)
            ->assertSee('Rp 100.000', false)
            ->assertSee('Rp 50.000', false)
            ->assertDontSee('Rp 20.000', false);
    }

    public function test_web_tab_pengeluaran_menampilkan_daftar_dan_filter_kategori(): void
    {
        $data = $this->siapkanDataPeriode();

        $this->actingAs($this->user)
            ->get('/keuangan?tab=pengeluaran&dari='.self::DARI.'&sampai='.self::SAMPAI)
            ->assertOk()
            ->assertSee(self::PREFIX.' awal')
            ->assertSee(self::PREFIX.' akhir');

        $this->actingAs($this->user)
            ->get('/keuangan?tab=pengeluaran&dari='.self::DARI.'&sampai='.self::SAMPAI.'&kategori_id='.$data['kain']->id)
            ->assertOk()
            ->assertSee(self::PREFIX.' awal')
            ->assertDontSee(self::PREFIX.' akhir');
    }

    public function test_web_filter_periode_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->user)
            ->get('/keuangan?dari=2001-03-20&sampai=2001-03-10')
            ->assertSessionHasErrors('sampai');

        $this->actingAs($this->user)
            ->get('/keuangan?tab=lainnya')
            ->assertSessionHasErrors('tab');
    }

    public function test_web_crud_pengeluaran(): void
    {
        $kategori = $this->buatKategori('Listrik');

        $this->actingAs($this->user)
            ->post('/keuangan/pengeluaran', [
                'kategori_pengeluaran_id' => $kategori->id,
                'nominal' => 125000,
                'tanggal' => '2001-03-15',
                'catatan' => self::PREFIX.' tagihan',
            ])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        $pengeluaran = Pengeluaran::where('catatan', self::PREFIX.' tagihan')->firstOrFail();
        $this->assertEquals(125000, $pengeluaran->nominal);
        $this->assertSame($kategori->id, $pengeluaran->kategori_pengeluaran_id);

        $this->actingAs($this->user)
            ->put("/keuangan/pengeluaran/{$pengeluaran->id}", [
                'kategori_pengeluaran_id' => null,
                'nominal' => 130000.50,
                'tanggal' => '2001-03-16',
                'catatan' => self::PREFIX.' tagihan revisi',
            ])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        $pengeluaran->refresh();
        $this->assertEquals(130000.50, $pengeluaran->nominal);
        $this->assertNull($pengeluaran->kategori_pengeluaran_id);
        $this->assertSame('2001-03-16', $pengeluaran->tanggal->toDateString());

        $this->actingAs($this->user)
            ->delete("/keuangan/pengeluaran/{$pengeluaran->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pengeluaran', ['id' => $pengeluaran->id]);
    }

    public function test_web_validasi_pengeluaran(): void
    {
        $this->actingAs($this->user)
            ->post('/keuangan/pengeluaran', [
                'kategori_pengeluaran_id' => 999999999,
                'nominal' => 0,
                'tanggal' => now()->addDays(3)->toDateString(),
            ])
            ->assertSessionHasErrors(['kategori_pengeluaran_id', 'nominal', 'tanggal']);

        $this->actingAs($this->user)
            ->post('/keuangan/pengeluaran', ['catatan' => self::PREFIX.' kosong'])
            ->assertSessionHasErrors(['nominal', 'tanggal']);

        $this->assertDatabaseMissing('pengeluaran', ['catatan' => self::PREFIX.' kosong']);
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_ringkasan_keuangan(): void
    {
        $this->siapkanDataPeriode();

        $response = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/keuangan/ringkasan?dari='.self::DARI.'&sampai='.self::SAMPAI)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.periode.dari', self::DARI)
            ->assertJsonPath('data.periode.sampai', self::SAMPAI)
            ->assertJsonStructure(['data' => [
                'periode', 'pendapatan', 'pengeluaran', 'selisih',
                'jumlah_pembayaran', 'jumlah_pengeluaran',
                'pendapatan_per_metode', 'pengeluaran_per_kategori',
            ]]);

        $this->assertEquals(150000, $response->json('data.pendapatan'));
        $this->assertEquals(65000, $response->json('data.pengeluaran'));
        $this->assertEquals(85000, $response->json('data.selisih'));
    }

    public function test_api_ringkasan_menolak_periode_terbalik(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/keuangan/ringkasan?dari=2001-03-20&sampai=2001-03-10')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sampai');
    }

    public function test_api_pendapatan_berisi_pembayaran_pada_periode(): void
    {
        $data = $this->siapkanDataPeriode();

        $response = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/keuangan/pendapatan?dari='.self::DARI.'&sampai='.self::SAMPAI)
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['id', 'tanggal_bayar', 'jumlah', 'metode', 'catatan', 'pesanan' => ['id', 'nomor_pesanan', 'pelanggan']]]]);

        $this->assertSame($data['pesanan']->nomor_pesanan, $response->json('data.0.pesanan.nomor_pesanan'));
        $this->assertEqualsCanonicalizing([100000, 50000], array_map('intval', array_column($response->json('data'), 'jumlah')));
    }

    public function test_api_crud_pengeluaran(): void
    {
        $headers = $this->apiHeaders();
        $kategori = $this->buatKategori('Sewa');

        $id = $this->withHeaders($headers)
            ->postJson('/api/pengeluaran', [
                'kategori_pengeluaran_id' => $kategori->id,
                'nominal' => 500000,
                'tanggal' => '2001-03-10',
                'catatan' => self::PREFIX.' sewa',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kategori.id', $kategori->id)
            ->json('data.id');

        $this->withHeaders($headers)
            ->getJson('/api/pengeluaran?dari='.self::DARI.'&sampai='.self::SAMPAI.'&kategori_id='.$kategori->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $id);

        $this->withHeaders($headers)
            ->putJson("/api/pengeluaran/{$id}", [
                'nominal' => 450000,
                'tanggal' => '2001-03-11',
                'catatan' => self::PREFIX.' sewa revisi',
            ])
            ->assertOk()
            ->assertJsonPath('data.catatan', self::PREFIX.' sewa revisi');

        $this->assertEquals(450000, Pengeluaran::findOrFail($id)->nominal);

        $this->withHeaders($headers)
            ->deleteJson("/api/pengeluaran/{$id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('pengeluaran', ['id' => $id]);
    }

    public function test_api_pengeluaran_validasi_dan_tidak_ditemukan(): void
    {
        $headers = $this->apiHeaders();

        $this->withHeaders($headers)
            ->postJson('/api/pengeluaran', ['nominal' => -5, 'tanggal' => 'bukan-tanggal'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nominal', 'tanggal']);

        $this->withHeaders($headers)->putJson('/api/pengeluaran/999999999', [
            'nominal' => 1000,
            'tanggal' => '2001-03-10',
        ])->assertNotFound();

        $this->withHeaders($headers)->deleteJson('/api/pengeluaran/999999999')->assertNotFound();
    }

    public function test_kategori_dihapus_membuat_pengeluaran_tanpa_kategori(): void
    {
        $kategori = $this->buatKategori('Sementara');
        $pengeluaran = $this->buatPengeluaran(10000, '2001-03-10', $kategori);

        $kategori->delete();

        $this->assertNull($pengeluaran->fresh()->kategori_pengeluaran_id);
    }
}
