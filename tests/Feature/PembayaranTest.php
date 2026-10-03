<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Pesanan;

class PembayaranTest extends PesananTestCase
{
    /**
     * Pesanan dengan satu item bernilai Rp 200.000.
     */
    protected function pesananDuaRatusRibu(array $pesananOverride = []): Pesanan
    {
        return $this->buatPesananDenganItem(null, $pesananOverride, ['harga' => 200000]);
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function dataBayar(array $override = []): array
    {
        return array_merge(['jumlah' => 50000, 'metode' => 'tunai', 'catatan' => 'DP'], $override);
    }

    // ------------------------------------------------------------------
    // Akses
    // ------------------------------------------------------------------

    public function test_route_pembayaran_memerlukan_login(): void
    {
        $this->post('/pesanan/1/pembayaran', [])->assertRedirect('/login');
        $this->delete('/pembayaran/1')->assertRedirect('/login');
        $this->get('/pesanan/1/invoice')->assertRedirect('/login');
        $this->get('/pesanan/1/invoice/pdf')->assertRedirect('/login');

        $this->getJson('/api/pesanan/1/pembayaran')->assertStatus(401);
        $this->postJson('/api/pesanan/1/pembayaran', [])->assertStatus(401);
        $this->getJson('/api/pesanan/1/invoice')->assertStatus(401);
        $this->getJson('/api/pesanan/1/invoice/pdf')->assertStatus(401);
        $this->deleteJson('/api/pembayaran/1')->assertStatus(401);
    }

    // ------------------------------------------------------------------
    // Web: pembayaran bertahap & status otomatis
    // ------------------------------------------------------------------

    public function test_web_pembayaran_bertahap_mengubah_status_otomatis(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        $this->assertSame('Belum Bayar', $pesanan->status_pembayaran);

        // 1. DP
        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 50000]))
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');
        $pesanan->refresh();
        $this->assertSame('DP', $pesanan->status_pembayaran);
        $this->assertEquals(150000.0, $pesanan->sisa_pembayaran);

        // 2. Cicilan
        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 100000, 'metode' => 'transfer', 'catatan' => 'Cicilan']))
            ->assertSessionHas('success');
        $this->assertSame('DP', $pesanan->fresh()->status_pembayaran);
        $this->assertEquals(50000.0, $pesanan->fresh()->sisa_pembayaran);

        // 3. Pelunasan
        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 50000, 'metode' => 'qris', 'catatan' => 'Pelunasan']))
            ->assertSessionHas('success');
        $this->assertSame('Lunas', $pesanan->fresh()->status_pembayaran);
        $this->assertEquals(0.0, $pesanan->fresh()->sisa_pembayaran);

        $this->assertSame(3, $pesanan->pembayaran()->count());
        $this->assertEquals(200000.0, $pesanan->fresh()->total_dibayar);
    }

    public function test_web_tanggal_bayar_default_hari_ini_dan_dapat_diatur(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();

        $this->actingAs($this->user)->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 10000]));
        $this->actingAs($this->user)->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar([
            'jumlah' => 20000,
            'tanggal_bayar' => now()->subDays(3)->toDateString(),
        ]));

        $default = $pesanan->pembayaran()->where('jumlah', 10000)->firstOrFail();
        $diatur = $pesanan->pembayaran()->where('jumlah', 20000)->firstOrFail();
        $this->assertSame(now()->toDateString(), $default->tanggal_bayar->toDateString());
        $this->assertSame(now()->subDays(3)->toDateString(), $diatur->tanggal_bayar->toDateString());
    }

    public function test_web_pembayaran_melebihi_sisa_ditolak(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 200001]))
            ->assertSessionHasErrors(['jumlah']);

        $this->assertSame(0, $pesanan->pembayaran()->count());
    }

    public function test_web_pembayaran_pada_pesanan_lunas_ditolak(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        $this->actingAs($this->user)->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 200000]));
        $this->assertSame('Lunas', $pesanan->fresh()->status_pembayaran);

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 1000]))
            ->assertSessionHasErrors(['jumlah']);

        $this->assertSame(1, $pesanan->pembayaran()->count());
    }

    public function test_web_pembayaran_pada_pesanan_dibatalkan_ditolak(): void
    {
        $pesanan = $this->pesananDuaRatusRibu(['status' => 'dibatalkan']);

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar())
            ->assertSessionHasErrors(['pesanan']);

        $this->assertSame(0, $pesanan->pembayaran()->count());
    }

    public function test_web_pembayaran_gagal_validasi(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", [])
            ->assertSessionHasErrors(['jumlah', 'metode']);

        foreach ([0, -5000, 'abc'] as $jumlah) {
            $this->actingAs($this->user)
                ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => $jumlah]))
                ->assertSessionHasErrors(['jumlah']);
        }

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['metode' => 'kartu_kredit']))
            ->assertSessionHasErrors(['metode']);

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['tanggal_bayar' => now()->addDays(2)->toDateString()]))
            ->assertSessionHasErrors(['tanggal_bayar']);

        $this->assertSame(0, $pesanan->pembayaran()->count());
    }

    public function test_web_batalkan_pembayaran_mengembalikan_sisa_dan_status(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        $bayar = Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'jumlah' => 200000,
            'metode' => 'tunai',
            'tanggal_bayar' => now(),
        ]);
        $this->assertSame('Lunas', $pesanan->fresh()->status_pembayaran);

        $this->actingAs($this->user)
            ->delete("/pembayaran/{$bayar->id}")
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pembayaran', ['id' => $bayar->id]);
        $this->assertSame('Belum Bayar', $pesanan->fresh()->status_pembayaran);
        $this->assertEquals(200000.0, $pesanan->fresh()->sisa_pembayaran);
    }

    // ------------------------------------------------------------------
    // Web: tampilan detail & invoice
    // ------------------------------------------------------------------

    public function test_detail_pesanan_menampilkan_riwayat_dan_aksi_pembayaran(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 75000, 'metode' => 'transfer', 'tanggal_bayar' => now(), 'catatan' => 'DP awal uji']);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertStatus(200)
            ->assertSee('Input Pembayaran')
            ->assertSee('Cetak Invoice')
            ->assertSee('Rp 75.000')
            ->assertSee('Transfer')
            ->assertSee('DP awal uji')
            ->assertSee('Sisa')
            ->assertSee('Rp 125.000');
    }

    public function test_detail_pesanan_selesai_dengan_sisa_menampilkan_banner_pelunasan(): void
    {
        $pesanan = $this->pesananDuaRatusRibu(['status' => 'selesai']);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertSee('Pesanan sudah selesai dijahit')
            ->assertSee('Lunasi Sisa Sekarang');

        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 200000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertSee('Pesanan lunas dan siap diambil')
            ->assertDontSee('Lunasi Sisa Sekarang')
            ->assertDontSee('Input Pembayaran');
    }

    public function test_halaman_invoice_menampilkan_data_lengkap(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 80000, 'metode' => 'qris', 'tanggal_bayar' => now()]);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}/invoice")
            ->assertStatus(200)
            ->assertSee('INVOICE')
            ->assertSee($pesanan->nomor_pesanan)
            ->assertSee($pesanan->pelanggan->nama)
            ->assertSee('Rp 200.000')
            ->assertSee('Rp 80.000')
            ->assertSee('Rp 120.000')
            ->assertSee('QRIS')
            ->assertSee('Unduh PDF');
    }

    // ------------------------------------------------------------------
    // Alur: Selesai -> Cek Sisa -> Bayar -> Diambil
    // ------------------------------------------------------------------

    public function test_tahap_diambil_ditolak_selama_belum_lunas_dan_lolos_setelah_bayar(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        $item = $pesanan->itemPesanan()->first();
        $headers = $this->apiHeaders();

        // Selesaikan semua tahapan kecuali yang terakhir ("Diambil Pelanggan").
        $semua = $item->progress()->get();
        foreach ($semua->slice(0, -1) as $tahap) {
            $tahap->update(['is_completed' => true, 'completed_at' => now()]);
        }
        $this->assertStringContainsStringIgnoringCase('diambil', $semua->last()->nama_tahapan);

        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 50000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        $this->withHeaders($headers)
            ->putJson("/api/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['aksi']);
        $this->assertFalse($semua->last()->fresh()->is_completed);

        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 150000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        $this->withHeaders($headers)
            ->putJson("/api/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
            ->assertStatus(200)
            ->assertJsonPath('data.status_pesanan', 'diambil');
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_catat_pembayaran_dan_riwayat(): void
    {
        $headers = $this->apiHeaders();
        $pesanan = $this->pesananDuaRatusRibu();

        $catat = $this->withHeaders($headers)
            ->postJson("/api/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 60000, 'metode' => 'transfer']))
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pembayaran.metode', 'transfer')
            ->assertJsonPath('data.ringkasan.status', 'DP');
        $this->assertEquals(140000.0, (float) $catat->json('data.ringkasan.sisa'));
        $this->assertEquals(200000.0, (float) $catat->json('data.ringkasan.total_tagihan'));

        $this->withHeaders($headers)
            ->postJson("/api/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 140000]))
            ->assertStatus(201)
            ->assertJsonPath('data.ringkasan.status', 'Lunas');

        $riwayat = $this->withHeaders($headers)
            ->getJson("/api/pesanan/{$pesanan->id}/pembayaran")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ringkasan.status', 'Lunas');
        $this->assertCount(2, $riwayat->json('data.riwayat'));
        $this->assertEquals(0.0, (float) $riwayat->json('data.ringkasan.sisa'));
    }

    public function test_api_pembayaran_gagal_validasi(): void
    {
        $headers = $this->apiHeaders();
        $pesanan = $this->pesananDuaRatusRibu();

        $this->withHeaders($headers)
            ->postJson("/api/pesanan/{$pesanan->id}/pembayaran", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jumlah', 'metode']);

        $this->withHeaders($headers)
            ->postJson("/api/pesanan/{$pesanan->id}/pembayaran", $this->dataBayar(['jumlah' => 999999999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jumlah']);

        $this->withHeaders($headers)
            ->postJson('/api/pesanan/999999999/pembayaran', $this->dataBayar())
            ->assertStatus(404);
    }

    public function test_api_batalkan_pembayaran(): void
    {
        $headers = $this->apiHeaders();
        $pesanan = $this->pesananDuaRatusRibu();
        $bayar = Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 100000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        $respon = $this->withHeaders($headers)
            ->deleteJson("/api/pembayaran/{$bayar->id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ringkasan.status', 'Belum Bayar');
        $this->assertEquals(200000.0, (float) $respon->json('data.ringkasan.sisa'));
        $this->assertDatabaseMissing('pembayaran', ['id' => $bayar->id]);
    }

    public function test_api_invoice_mengembalikan_data_lengkap(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 50000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        $respon = $this->withHeaders($this->apiHeaders())
            ->getJson("/api/pesanan/{$pesanan->id}/invoice")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pesanan.nomor_pesanan', $pesanan->nomor_pesanan)
            ->assertJsonPath('data.pesanan.pelanggan.id', $pesanan->pelanggan_id)
            ->assertJsonPath('data.ringkasan.status', 'DP');

        $this->assertArrayHasKey('nama_usaha', $respon->json('data.usaha'));
        $this->assertCount(1, $respon->json('data.pesanan.item_pesanan'));
        $this->assertCount(1, $respon->json('data.pesanan.pembayaran'));
    }

    // ------------------------------------------------------------------
    // Invoice PDF
    // ------------------------------------------------------------------

    public function test_web_unduh_invoice_pdf(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();
        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 50000, 'metode' => 'tunai', 'tanggal_bayar' => now()]);

        $respon = $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}/invoice/pdf")
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $respon->getContent());
        $this->assertStringContainsString(
            "Invoice-{$pesanan->nomor_pesanan}.pdf",
            (string) $respon->headers->get('content-disposition')
        );
    }

    public function test_halaman_invoice_menyediakan_tombol_unduh_pdf(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}/invoice")
            ->assertSee('Unduh PDF')
            ->assertSee(route('pesanan.invoice.pdf', $pesanan), false);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertSee('Unduh PDF');
    }

    public function test_api_unduh_invoice_pdf(): void
    {
        $pesanan = $this->pesananDuaRatusRibu();

        $respon = $this->withHeaders($this->apiHeaders())
            ->get("/api/pesanan/{$pesanan->id}/invoice/pdf")
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $respon->getContent());

        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/pesanan/999999999/invoice/pdf')
            ->assertStatus(404);
    }
}
