<?php

namespace Tests\Feature;

use App\Models\FotoReferensi;
use App\Models\PemakaianBahan;
use App\Models\Pembayaran;
use App\Models\RiwayatStok;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PesananProgressTest extends PesananTestCase
{
    // ------------------------------------------------------------------
    // Progress (maju / mundur) & sinkronisasi status
    // ------------------------------------------------------------------

    public function test_web_majukan_progress_menyelesaikan_tahapan_pertama(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $pertama = $item->progress()->first();
        $this->assertTrue($pertama->is_completed);
        $this->assertNotNull($pertama->completed_at);
        $this->assertSame('diproses', $item->fresh()->status_item);
        $this->assertSame('aktif', $pesanan->fresh()->status);
    }

    public function test_web_mundurkan_progress_membatalkan_tahapan_terakhir(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();

        $this->actingAs($this->user)->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'maju']);
        $this->actingAs($this->user)->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'maju']);
        $this->assertSame(2, $item->progress()->where('is_completed', true)->count());

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'mundur'])
            ->assertSessionHas('success');

        $this->assertSame(1, $item->progress()->where('is_completed', true)->count());
        $kedua = $item->progress()->skip(1)->first();
        $this->assertFalse($kedua->is_completed);
        $this->assertNull($kedua->completed_at);

        $this->actingAs($this->user)->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'mundur']);
        $this->assertSame('menunggu', $item->fresh()->status_item);
    }

    public function test_web_mundur_di_tahapan_pertama_ditolak(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'mundur'])
            ->assertSessionHasErrors(['aksi']);
    }

    public function test_web_maju_setelah_semua_tahapan_selesai_ditolak(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();
        $this->selesaikanTahapan($item);

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
            ->assertSessionHasErrors(['aksi']);
    }

    public function test_web_progress_menolak_aksi_tidak_valid(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'loncat'])
            ->assertSessionHasErrors(['aksi']);

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", [])
            ->assertSessionHasErrors(['aksi']);
    }

    public function test_status_item_dan_pesanan_berubah_saat_tahap_selesai_dan_diambil(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();
        $headers = $this->apiHeaders();
        $total = $item->progress()->count();

        // Majukan sampai tahapan "Selesai" tuntas.
        $urutanSelesai = $item->progress()->get()->search(fn ($t) => strcasecmp($t->nama_tahapan, 'Selesai') === 0);
        $this->assertNotFalse($urutanSelesai, 'Template tahapan harus memiliki tahap "Selesai".');

        for ($i = 0; $i <= $urutanSelesai; $i++) {
            $response = $this->withHeaders($headers)
                ->putJson("/api/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
                ->assertStatus(200);
        }

        $this->assertSame('selesai', $item->fresh()->status_item);
        $this->assertSame('selesai', $pesanan->fresh()->status);
        $this->assertSame('selesai', $response->json('data.status_pesanan'));

        // Pesanan harus lunas sebelum tahap pengambilan barang dapat diselesaikan.
        Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'jumlah' => 100000,
            'metode' => 'tunai',
            'tanggal_bayar' => now(),
        ]);

        // Majukan sampai seluruh tahapan tuntas -> diambil.
        for ($i = $urutanSelesai + 1; $i < $total; $i++) {
            $response = $this->withHeaders($headers)
                ->putJson("/api/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
                ->assertStatus(200);
        }

        $this->assertSame('diambil', $pesanan->fresh()->status);
        $this->assertSame('diambil', $response->json('data.status_pesanan'));

        // Mundur satu tahap -> kembali selesai.
        $this->withHeaders($headers)
            ->putJson("/api/pesanan/item/{$item->id}/progress", ['aksi' => 'mundur'])
            ->assertStatus(200)
            ->assertJsonPath('data.status_pesanan', 'selesai');
    }

    public function test_pesanan_baru_selesai_jika_semua_item_selesai(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $pertama = $pesanan->itemPesanan()->first();
        $kedua = $this->tambahItemFactory($pesanan);
        $headers = $this->apiHeaders();

        $this->selesaikanTahapan($pertama, 'Selesai');
        $this->selesaikanTahapan($kedua, 'Selesai');

        // Sinkronisasi dipicu lewat satu aksi progress: mundur lalu maju di item pertama.
        $this->withHeaders($headers)->putJson("/api/pesanan/item/{$pertama->id}/progress", ['aksi' => 'mundur'])->assertStatus(200);
        $this->assertSame('aktif', $pesanan->fresh()->status);

        $this->withHeaders($headers)->putJson("/api/pesanan/item/{$pertama->id}/progress", ['aksi' => 'maju'])->assertStatus(200);
        $this->assertSame('selesai', $pesanan->fresh()->status);
    }

    public function test_menambah_item_baru_mengembalikan_pesanan_selesai_ke_aktif(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['status' => 'selesai']);
        $this->selesaikanTahapan($pesanan->itemPesanan()->first(), 'Selesai');

        $this->actingAs($this->user)
            ->post("/pesanan/{$pesanan->id}/item", ['jenis_jasa_id' => $this->jasa()->id, 'harga' => 50000])
            ->assertSessionHas('success');

        $this->assertSame('aktif', $pesanan->fresh()->status);
    }

    public function test_progress_pesanan_dibatalkan_ditolak(): void
    {
        $pesanan = $this->buatPesananDenganItem(null, ['status' => 'dibatalkan']);
        $item = $pesanan->itemPesanan()->first();

        $this->actingAs($this->user)
            ->put("/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
            ->assertSessionHasErrors(['aksi']);

        $this->withHeaders($this->apiHeaders())
            ->putJson("/api/pesanan/item/{$item->id}/progress", ['aksi' => 'maju'])
            ->assertStatus(422);

        $this->assertSame(0, $item->progress()->where('is_completed', true)->count());
    }

    // ------------------------------------------------------------------
    // Pemakaian bahan (Stored Procedure + Trigger)
    // ------------------------------------------------------------------

    public function test_web_catat_pemakaian_bahan_memotong_stok(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();
        $bahan = $this->buatBahan(10);

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 2.5, 'catatan' => 'Untuk badan'])
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $this->assertEquals(7.5, (float) $bahan->fresh()->stok);
        $this->assertDatabaseHas('pemakaian_bahan', [
            'item_pesanan_id' => $item->id,
            'bahan_id' => $bahan->id,
            'catatan' => 'Untuk badan',
        ]);

        $keluar = RiwayatStok::where('bahan_id', $bahan->id)->where('jenis', 'keluar')->firstOrFail();
        $this->assertEquals(2.5, (float) $keluar->jumlah);
        $this->assertEquals(10.0, (float) $keluar->stok_sebelum);
        $this->assertEquals(7.5, (float) $keluar->stok_sesudah);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertSee($bahan->nama_bahan);
    }

    public function test_web_pemakaian_bahan_melebihi_stok_ditolak(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();
        $bahan = $this->buatBahan(2);

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 5])
            ->assertSessionHasErrors(['jumlah']);

        $this->assertEquals(2.0, (float) $bahan->fresh()->stok);
        $this->assertSame(0, PemakaianBahan::where('item_pesanan_id', $item->id)->count());
    }

    public function test_web_pemakaian_bahan_gagal_validasi(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();
        $bahan = $this->buatBahan(5);

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/bahan", [])
            ->assertSessionHasErrors(['bahan_id', 'jumlah']);

        foreach ([0, -1, 'abc'] as $jumlah) {
            $this->actingAs($this->user)
                ->post("/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => $jumlah])
                ->assertSessionHasErrors(['jumlah']);
        }

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/bahan", ['bahan_id' => 999999999, 'jumlah' => 1])
            ->assertSessionHasErrors(['bahan_id']);
    }

    public function test_web_hapus_pemakaian_bahan_mengembalikan_stok(): void
    {
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();
        $bahan = $this->buatBahan(10);

        $this->actingAs($this->user)->post("/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 4]);
        $this->assertEquals(6.0, (float) $bahan->fresh()->stok);

        $pemakaian = PemakaianBahan::where('item_pesanan_id', $item->id)->firstOrFail();

        $this->actingAs($this->user)
            ->delete("/pesanan/pemakaian/{$pemakaian->id}")
            ->assertRedirect(route('pesanan.show', $item->pesanan_id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pemakaian_bahan', ['id' => $pemakaian->id]);
        $this->assertEquals(10.0, (float) $bahan->fresh()->stok);
    }

    public function test_item_dengan_pemakaian_bahan_tidak_boleh_dihapus(): void
    {
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();
        $kedua = $this->tambahItemFactory($pesanan);
        $bahan = $this->buatBahan(10);

        $this->actingAs($this->user)->post("/pesanan/item/{$kedua->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 3]);

        $this->actingAs($this->user)
            ->delete("/pesanan/item/{$kedua->id}")
            ->assertSessionHasErrors(['item']);

        $this->assertDatabaseHas('item_pesanan', ['id' => $kedua->id]);
        $this->assertEquals(7.0, (float) $bahan->fresh()->stok);
    }

    public function test_pemakaian_bahan_pada_pesanan_dibatalkan_ditolak(): void
    {
        $item = $this->buatPesananDenganItem(null, ['status' => 'dibatalkan'])->itemPesanan()->first();
        $bahan = $this->buatBahan(10);

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 1])
            ->assertSessionHasErrors(['pesanan']);

        $this->assertEquals(10.0, (float) $bahan->fresh()->stok);
    }

    public function test_api_catat_dan_hapus_pemakaian_bahan(): void
    {
        $headers = $this->apiHeaders();
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();
        $bahan = $this->buatBahan(10);

        $catat = $this->withHeaders($headers)
            ->postJson("/api/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 3, 'catatan' => 'API'])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.bahan_id', $bahan->id);
        $this->assertEquals(7.0, (float) $bahan->fresh()->stok);

        $this->withHeaders($headers)
            ->postJson("/api/pesanan/item/{$item->id}/bahan", ['bahan_id' => $bahan->id, 'jumlah' => 100])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jumlah']);

        $this->withHeaders($headers)
            ->deleteJson('/api/pesanan/pemakaian/'.$catat->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertEquals(10.0, (float) $bahan->fresh()->stok);
    }

    // ------------------------------------------------------------------
    // Foto referensi
    // ------------------------------------------------------------------

    public function test_web_unggah_dan_hapus_foto_referensi(): void
    {
        Storage::fake('public');
        $pesanan = $this->buatPesananDenganItem();
        $item = $pesanan->itemPesanan()->first();

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/foto", [
                'foto' => UploadedFile::fake()->image('model-baju.jpg', 600, 600),
                'keterangan' => 'Model kerah',
            ])
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $foto = FotoReferensi::where('item_pesanan_id', $item->id)->firstOrFail();
        $this->assertSame('Model kerah', $foto->keterangan);
        Storage::disk('public')->assertExists($foto->path_foto);

        $this->actingAs($this->user)
            ->get("/pesanan/{$pesanan->id}")
            ->assertSee('Model kerah');

        $this->actingAs($this->user)
            ->delete("/pesanan/foto/{$foto->id}")
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('foto_referensi', ['id' => $foto->id]);
        Storage::disk('public')->assertMissing($foto->path_foto);
    }

    public function test_web_unggah_foto_menolak_berkas_bukan_gambar(): void
    {
        Storage::fake('public');
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/foto", ['foto' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors(['foto']);

        $this->actingAs($this->user)
            ->post("/pesanan/item/{$item->id}/foto", [])
            ->assertSessionHasErrors(['foto']);

        $this->assertSame(0, FotoReferensi::where('item_pesanan_id', $item->id)->count());
    }

    public function test_api_unggah_dan_hapus_foto_referensi(): void
    {
        Storage::fake('public');
        $headers = $this->apiHeaders();
        $item = $this->buatPesananDenganItem()->itemPesanan()->first();

        $unggah = $this->withHeaders($headers)
            ->post("/api/pesanan/item/{$item->id}/foto", [
                'foto' => UploadedFile::fake()->image('referensi.png', 400, 400),
                'keterangan' => 'Via API',
            ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.keterangan', 'Via API');

        Storage::disk('public')->assertExists($unggah->json('data.path_foto'));

        $this->withHeaders($headers)
            ->deleteJson('/api/pesanan/foto/'.$unggah->json('data.id'))
            ->assertStatus(200);

        Storage::disk('public')->assertMissing($unggah->json('data.path_foto'));
    }

    public function test_hapus_item_ikut_menghapus_file_foto(): void
    {
        Storage::fake('public');
        $pesanan = $this->buatPesananDenganItem();
        $kedua = $this->tambahItemFactory($pesanan);

        $this->actingAs($this->user)->post("/pesanan/item/{$kedua->id}/foto", [
            'foto' => UploadedFile::fake()->image('hapus.jpg'),
        ]);
        $path = FotoReferensi::where('item_pesanan_id', $kedua->id)->firstOrFail()->path_foto;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->user)->delete("/pesanan/item/{$kedua->id}")->assertSessionHas('success');

        Storage::disk('public')->assertMissing($path);
    }
}
