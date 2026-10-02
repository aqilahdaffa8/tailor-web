<?php

namespace Tests\Feature;

use App\Models\ItemPesanan;
use App\Models\Pembayaran;
use App\Models\Pengukuran;
use App\Models\Pesanan;
use App\Models\ProgressItem;

class PesananTest extends PesananTestCase
{
    // ------------------------------------------------------------------
    // Akses & daftar
    // ------------------------------------------------------------------

    public function test_halaman_pesanan_memerlukan_login(): void
    {
        $this->get('/pesanan')->assertRedirect('/login');
        $this->get('/pesanan/create')->assertRedirect('/login');
        $this->post('/pesanan', [])->assertRedirect('/login');
    }

    public function test_halaman_daftar_pesanan_dapat_diakses(): void
    {
        $pesanan = $this->buatPesananDenganItem();

        $this->actingAs($this->user)
            ->get('/pesanan?pelanggan_id='.$pesanan->pelanggan_id)
            ->assertStatus(200)
            ->assertSee('Pesanan Jahit')
            ->assertSee('Buat Pesanan Baru')
            ->assertSee($pesanan->nomor_pesanan)
            ->assertSee($pesanan->pelanggan->nama);
    }

    public function test_filter_status_dan_pencarian_daftar_pesanan(): void
    {
        $pelanggan = $this->buatPelanggan();
        $aktif = $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-AKTIF-001']);
        $selesai = $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-SELESAI-001', 'status' => 'selesai']);

        $this->actingAs($this->user)
            ->get("/pesanan?pelanggan_id={$pelanggan->id}&status=selesai")
            ->assertSee('ZZT-SELESAI-001')
            ->assertDontSee('ZZT-AKTIF-001');

        $this->actingAs($this->user)
            ->get('/pesanan?search=ZZT-AKTIF-001')
            ->assertSee('ZZT-AKTIF-001')
            ->assertDontSee('ZZT-SELESAI-001');

        $this->actingAs($this->user)
            ->get('/pesanan?search='.urlencode($pelanggan->nama))
            ->assertSee('ZZT-AKTIF-001')
            ->assertSee('ZZT-SELESAI-001');
    }

    public function test_filter_deadline_terlambat_dan_mendekati(): void
    {
        $pelanggan = $this->buatPelanggan();
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-TELAT-001', 'tanggal_pesan' => now()->subDays(10), 'deadline' => now()->subDays(2)]);
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-DEKAT-001', 'deadline' => now()->addDays(2)]);
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-JAUH-001', 'deadline' => now()->addDays(30)]);

        $this->actingAs($this->user)
            ->get("/pesanan?pelanggan_id={$pelanggan->id}&deadline=terlambat")
            ->assertSee('ZZT-TELAT-001')
            ->assertSee('Terlambat 2 hari')
            ->assertDontSee('ZZT-DEKAT-001')
            ->assertDontSee('ZZT-JAUH-001');

        $this->actingAs($this->user)
            ->get("/pesanan?pelanggan_id={$pelanggan->id}&deadline=mendekati")
            ->assertSee('ZZT-DEKAT-001')
            ->assertDontSee('ZZT-TELAT-001')
            ->assertDontSee('ZZT-JAUH-001');
    }

    public function test_daftar_pesanan_menampilkan_sisa_tagihan_dari_stored_function(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, [], ['harga' => 200000]);
        Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'jumlah' => 50000,
            'metode' => 'tunai',
            'tanggal_bayar' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/pesanan?pelanggan_id='.$pesanan->pelanggan_id)
            ->assertSee('Rp 200.000')
            ->assertSee('Sisa Rp 150.000')
            ->assertSee('DP');
    }

    // ------------------------------------------------------------------
    // Web: buat pesanan
    // ------------------------------------------------------------------

    public function test_halaman_buat_pesanan_dapat_diakses(): void
    {
        $pelanggan = $this->buatPelanggan();

        $this->actingAs($this->user)
            ->get('/pesanan/create?pelanggan_id='.$pelanggan->id)
            ->assertStatus(200)
            ->assertSee('Buat Pesanan Baru')
            ->assertSee('Simpan Pesanan')
            ->assertSee($pelanggan->nama);
    }

    public function test_web_buat_pesanan_membuat_item_dan_progress_dari_template(): void
    {
        $pelanggan = $this->buatPelanggan();
        $jasa = $this->jasa();

        $response = $this->actingAs($this->user)
            ->post('/pesanan', $this->dataPesanan($pelanggan));

        $pesanan = Pesanan::where('pelanggan_id', $pelanggan->id)->firstOrFail();
        $response->assertRedirect(route('pesanan.show', $pesanan))->assertSessionHas('success');

        $this->assertMatchesRegularExpression('/^[A-Z0-9]+-\d{8}-\d{4}$/', $pesanan->nomor_pesanan);
        $this->assertSame('aktif', $pesanan->status);
        $this->assertSame('Pesanan uji coba', $pesanan->catatan);

        $item = $pesanan->itemPesanan()->firstOrFail();
        $this->assertSame($jasa->id, $item->jenis_jasa_id);
        $this->assertEquals(150000.0, (float) $item->harga);
        $this->assertSame('menunggu', $item->status_item);
        $this->assertSame($this->jumlahTahapan($jasa), $item->progress()->count());
        $this->assertSame(0, $item->progress()->where('is_completed', true)->count());
    }

    public function test_web_buat_pesanan_banyak_item_dengan_dp(): void
    {
        $pelanggan = $this->buatPelanggan();
        $jasa = $this->jasa();

        $payload = $this->dataPesanan($pelanggan, [
            'dp' => 100000,
            'metode_dp' => 'transfer',
            'items' => [
                ['jenis_jasa_id' => $jasa->id, 'harga' => 150000, 'detail_pekerjaan' => 'Kemeja', 'catatan' => 'Kancing emas'],
                ['jenis_jasa_id' => $jasa->id, 'harga' => 80000, 'detail_pekerjaan' => 'Celana', 'deadline' => now()->addDays(10)->toDateString()],
            ],
        ]);

        $this->actingAs($this->user)->post('/pesanan', $payload)->assertSessionHas('success');

        $pesanan = Pesanan::where('pelanggan_id', $pelanggan->id)->firstOrFail();
        $this->assertSame(2, $pesanan->itemPesanan()->count());
        $this->assertEquals(230000.0, $pesanan->total_harga);

        $pertama = $pesanan->itemPesanan()->orderBy('id')->first();
        $kedua = $pesanan->itemPesanan()->orderBy('id')->skip(1)->first();
        $this->assertSame('Kancing emas', $pertama->catatan);
        $this->assertSame(now()->addDays(10)->toDateString(), $kedua->deadline->toDateString());
        $this->assertSame($this->jumlahTahapan($jasa), $kedua->progress()->count());

        $pembayaran = $pesanan->pembayaran()->firstOrFail();
        $this->assertEquals(100000.0, (float) $pembayaran->jumlah);
        $this->assertSame('transfer', $pembayaran->metode);
        $this->assertEquals(130000.0, $pesanan->sisa_pembayaran);
        $this->assertSame('DP', $pesanan->status_pembayaran);
    }

    public function test_web_nomor_pesanan_bertambah_untuk_pesanan_di_hari_yang_sama(): void
    {
        $pelanggan = $this->buatPelanggan();

        $this->actingAs($this->user)->post('/pesanan', $this->dataPesanan($pelanggan))->assertSessionHas('success');
        $this->actingAs($this->user)->post('/pesanan', $this->dataPesanan($pelanggan))->assertSessionHas('success');

        $nomor = Pesanan::where('pelanggan_id', $pelanggan->id)->orderBy('id')->pluck('nomor_pesanan');
        $this->assertCount(2, $nomor);
        $this->assertNotSame($nomor[0], $nomor[1]);
        $this->assertSame(
            ((int) substr($nomor[0], -4)) + 1,
            (int) substr($nomor[1], -4)
        );
    }

    public function test_web_buat_pesanan_menolak_dp_melebihi_total(): void
    {
        $pelanggan = $this->buatPelanggan();

        $this->actingAs($this->user)
            ->post('/pesanan', $this->dataPesanan($pelanggan, ['dp' => 999999999]))
            ->assertSessionHasErrors(['dp']);

        $this->assertSame(0, Pesanan::where('pelanggan_id', $pelanggan->id)->count());
    }

    public function test_web_buat_pesanan_gagal_validasi(): void
    {
        $this->actingAs($this->user)
            ->post('/pesanan', [])
            ->assertSessionHasErrors(['pelanggan_id', 'tanggal_pesan', 'deadline', 'items']);

        $pelanggan = $this->buatPelanggan();

        $this->actingAs($this->user)
            ->post('/pesanan', $this->dataPesanan($pelanggan, [
                'tanggal_pesan' => '2026-10-10',
                'deadline' => '2026-10-01',
                'items' => [['jenis_jasa_id' => $this->jasa()->id]],
            ]))
            ->assertSessionHasErrors(['deadline', 'items.0.harga']);
    }

    public function test_web_buat_pesanan_menolak_pengukuran_milik_pelanggan_lain(): void
    {
        $pelanggan = $this->buatPelanggan();
        $lain = $this->buatPelanggan();
        $ukuranLain = Pengukuran::factory()->create(['pelanggan_id' => $lain->id]);
        $ukuranSendiri = Pengukuran::factory()->create(['pelanggan_id' => $pelanggan->id]);

        $item = ['jenis_jasa_id' => $this->jasa()->id, 'harga' => 100000];

        $this->actingAs($this->user)
            ->post('/pesanan', $this->dataPesanan($pelanggan, ['items' => [$item + ['pengukuran_id' => $ukuranLain->id]]]))
            ->assertSessionHasErrors(['items.0.pengukuran_id']);

        $this->actingAs($this->user)
            ->post('/pesanan', $this->dataPesanan($pelanggan, ['items' => [$item + ['pengukuran_id' => $ukuranSendiri->id]]]))
            ->assertSessionHasNoErrors();

        $pesanan = Pesanan::where('pelanggan_id', $pelanggan->id)->firstOrFail();
        $this->assertSame($ukuranSendiri->id, $pesanan->itemPesanan()->first()->pengukuran_id);
    }

    // ------------------------------------------------------------------
    // Web: detail, edit, batalkan
    // ------------------------------------------------------------------

    public function test_halaman_detail_pesanan_menampilkan_item_progress_dan_pembayaran(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, [], ['harga' => 120000, 'detail_pekerjaan' => 'Gaun pesta uji']);
        Pembayaran::create(['pesanan_id' => $pesanan->id, 'jumlah' => 20000, 'metode' => 'qris', 'tanggal_bayar' => now(), 'catatan' => 'DP awal pesanan']);
        $namaTahap = $pesanan->itemPesanan()->first()->progress()->first()->nama_tahapan;

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertStatus(200)
            ->assertSee($pesanan->nomor_pesanan)
            ->assertSee($pesanan->pelanggan->nama)
            ->assertSee('Gaun pesta uji')
            ->assertSee('Progress Pengerjaan')
            ->assertSee($namaTahap)
            ->assertSee('Pemakaian Bahan')
            ->assertSee('Foto Referensi')
            ->assertSee('Ringkasan Pembayaran')
            ->assertSee('Rp 100.000')
            ->assertSee('QRIS');
    }

    public function test_halaman_edit_pesanan_dapat_diakses(): void
    {
        $pesanan = $this->buatPesananDenganItem();

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}/edit")
            ->assertStatus(200)
            ->assertSee($pesanan->nomor_pesanan)
            ->assertSee('Simpan Perubahan')
            ->assertSee('Tambah Item Baru');
    }

    public function test_web_update_header_pesanan(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $deadlineBaru = now()->addDays(21)->toDateString();

        $this->actingAs($this->user)
            ->put("/pesanan/{$pesanan->id}", [
                'tanggal_pesan' => $pesanan->tanggal_pesan->toDateString(),
                'deadline' => $deadlineBaru,
                'catatan' => 'Catatan baru',
            ])
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $pesanan->refresh();
        $this->assertSame($deadlineBaru, $pesanan->deadline->toDateString());
        $this->assertSame('Catatan baru', $pesanan->catatan);
    }

    public function test_web_update_header_gagal_validasi(): void
    {
        $pesanan = $this->buatPesananDenganItem();

        $this->actingAs($this->user)
            ->put("/pesanan/{$pesanan->id}", ['tanggal_pesan' => '2026-10-10', 'deadline' => '2026-10-01'])
            ->assertSessionHasErrors(['deadline']);

        $this->actingAs($this->user)
            ->put("/pesanan/{$pesanan->id}", [])
            ->assertSessionHasErrors(['tanggal_pesan', 'deadline']);
    }

    public function test_web_batalkan_pesanan_dan_tidak_bisa_diubah_lagi(): void
    {
        $pesanan = $this->buatPesananDenganItem();

        $this->actingAs($this->user)
            ->delete("/pesanan/{$pesanan->id}")
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $this->assertSame('dibatalkan', $pesanan->fresh()->status);

        $this->actingAs($this->user)
            ->put("/pesanan/{$pesanan->id}", [
                'tanggal_pesan' => now()->toDateString(),
                'deadline' => now()->addDays(3)->toDateString(),
            ])
            ->assertSessionHasErrors(['pesanan']);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertStatus(200)
            ->assertSee('Dibatalkan')
            ->assertDontSee('Batalkan Pesanan');
    }

    // ------------------------------------------------------------------
    // Web: item pesanan
    // ------------------------------------------------------------------

    public function test_web_tambah_item_menyalin_template_tahapan(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $jasa = $this->jasa();

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/item", [
                'jenis_jasa_id' => $jasa->id,
                'harga' => 90000,
                'detail_pekerjaan' => 'Item tambahan',
            ])
            ->assertRedirect(route('pesanan.edit', $pesanan))
            ->assertSessionHas('success');

        $this->assertSame(2, $pesanan->itemPesanan()->count());
        $baru = $pesanan->itemPesanan()->where('detail_pekerjaan', 'Item tambahan')->firstOrFail();
        $this->assertSame($this->jumlahTahapan($jasa), $baru->progress()->count());
        $this->assertSame($pesanan->deadline->toDateString(), $baru->deadline->toDateString());
    }

    public function test_web_tambah_item_menolak_pengukuran_pelanggan_lain(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $ukuranLain = Pengukuran::factory()->create(['pelanggan_id' => $this->buatPelanggan()->id]);

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/item", [
                'jenis_jasa_id' => $this->jasa()->id,
                'harga' => 90000,
                'pengukuran_id' => $ukuranLain->id,
            ])
            ->assertSessionHasErrors(['pengukuran_id']);
    }

    public function test_web_update_item_pesanan(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}", [
                'harga' => 175000,
                'detail_pekerjaan' => 'Detail diperbarui',
                'catatan' => 'Catatan item baru',
            ])
            ->assertRedirect(route('pesanan.edit', $pesanan))
            ->assertSessionHas('success');

        $item->refresh();
        $this->assertEquals(175000.0, (float) $item->harga);
        $this->assertSame('Detail diperbarui', $item->detail_pekerjaan);

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}", [])
            ->assertSessionHasErrors(['harga']);
    }

    public function test_web_hapus_item_pesanan(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $kedua = $this->tambahItemFactory($pesanan);

        $this->actingAs($this->user)
            ->delete("/pesanan/item/{$kedua->id}")
            ->assertRedirect(route('pesanan.edit', $pesanan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('item_pesanan', ['id' => $kedua->id]);
        $this->assertSame(0, ProgressItem::where('item_pesanan_id', $kedua->id)->count());
    }

    public function test_web_item_terakhir_tidak_boleh_dihapus(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();

        $this->actingAs($this->user)
            ->delete("/pesanan/item/{$item->id}")
            ->assertSessionHasErrors(['item']);

        $this->assertDatabaseHas('item_pesanan', ['id' => $item->id]);
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_pesanan_memerlukan_token(): void
    {
        $this->getJson('/api/pesanan')->assertStatus(401);
        $this->postJson('/api/pesanan', [])->assertStatus(401);
        $this->putJson('/api/pesanan/item/1/progress', [])->assertStatus(401);
    }

    public function test_api_list_pesanan_dengan_filter_dan_meta(): void
    {
        $pelanggan = $this->buatPelanggan();
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-API-AKTIF']);
        $this->buatPesananDenganItem($pelanggan, ['nomor_pesanan' => 'ZZT-API-BATAL', 'status' => 'dibatalkan']);

        $response = $this->withHeaders($this->apiHeaders())
            ->getJson("/api/pesanan?pelanggan_id={$pelanggan->id}&status=aktif")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);

        $this->assertSame('ZZT-API-AKTIF', $response->json('data.0.nomor_pesanan'));
        $this->assertArrayHasKey('sisa_tagihan', $response->json('data.0'));
        $this->assertArrayHasKey('status_bayar', $response->json('data.0'));
    }

    public function test_api_crud_pesanan(): void
    {
        $headers = $this->apiHeaders();
        $pelanggan = $this->buatPelanggan();

        // 1. Create
        $create = $this->withHeaders($headers)
            ->postJson('/api/pesanan', $this->dataPesanan($pelanggan, ['dp' => 50000]))
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'aktif')
            ->assertJsonPath('data.pelanggan.id', $pelanggan->id);
        $id = $create->json('data.id');
        $this->assertCount(1, $create->json('data.item_pesanan'));
        $this->assertNotEmpty($create->json('data.item_pesanan.0.progress'));
        $this->assertCount(1, $create->json('data.pembayaran'));

        // 2. Show
        $show = $this->withHeaders($headers)
            ->getJson("/api/pesanan/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.status_pembayaran', 'DP');
        $this->assertEquals(100000.0, (float) $show->json('data.sisa_pembayaran'));

        // 3. Update
        $this->withHeaders($headers)
            ->putJson("/api/pesanan/{$id}", [
                'tanggal_pesan' => now()->toDateString(),
                'deadline' => now()->addDays(30)->toDateString(),
                'catatan' => 'Via API',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.catatan', 'Via API');

        // 4. Batalkan
        $this->withHeaders($headers)
            ->deleteJson("/api/pesanan/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertSame('dibatalkan', Pesanan::find($id)->status);
    }

    public function test_api_buat_pesanan_gagal_validasi(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->postJson('/api/pesanan', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pelanggan_id', 'tanggal_pesan', 'deadline', 'items']);

        $pelanggan = $this->buatPelanggan();
        $this->withHeaders($this->apiHeaders())
            ->postJson('/api/pesanan', $this->dataPesanan($pelanggan, ['dp' => 999999999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['dp']);
    }

    public function test_api_pesanan_tidak_ditemukan(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/pesanan/999999999')
            ->assertStatus(404);
    }

    public function test_api_tambah_ubah_dan_hapus_item(): void
    {
        $headers = $this->apiHeaders();
        $pesanan = $this->buatPesananDenganItem();

        $tambah = $this->withHeaders($headers)
            ->postJson("/api/pesanan/{$pesanan->id}/item", ['jenis_jasa_id' => $this->jasa()->id, 'harga' => 60000])
            ->assertStatus(201)
            ->assertJsonPath('success', true);
        $itemId = $tambah->json('data.id');
        $this->assertNotEmpty($tambah->json('data.progress'));

        $this->withHeaders($headers)
            ->putJson("/api/pesanan/item/{$itemId}", ['harga' => 65000, 'catatan' => 'Ubah via API'])
            ->assertStatus(200)
            ->assertJsonPath('data.catatan', 'Ubah via API');

        $this->withHeaders($headers)
            ->deleteJson("/api/pesanan/item/{$itemId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('item_pesanan', ['id' => $itemId]);

        $sisa = $pesanan->itemPesanan()->first();
        $this->withHeaders($headers)
            ->deleteJson("/api/pesanan/item/{$sisa->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['item']);
    }

    public function test_pesanan_factory_menghasilkan_state_yang_benar(): void
    {
        $pelanggan = $this->buatPelanggan();

        $terlambat = Pesanan::factory()->terlambat()->create(['pelanggan_id' => $pelanggan->id]);
        $this->assertTrue($terlambat->deadline->isPast());

        $batal = Pesanan::factory()->dibatalkan()->create(['pelanggan_id' => $pelanggan->id]);
        $this->assertSame('dibatalkan', $batal->status);

        $item = ItemPesanan::factory()->denganProgress()->create([
            'pesanan_id' => $terlambat->id,
            'jenis_jasa_id' => $this->jasa()->id,
        ]);
        $this->assertGreaterThan(0, $item->progress()->count());
    }
}
