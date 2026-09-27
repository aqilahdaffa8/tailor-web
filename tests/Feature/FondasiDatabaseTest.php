<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\ItemPesanan;
use App\Models\JenisJasa;
use App\Models\KategoriBahan;
use App\Models\Pelanggan;
use App\Models\PemakaianBahan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\ProgressItem;
use App\Models\RiwayatStok;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FondasiDatabaseTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Pastikan master data dari seeder terpasang dengan baik
     */
    public function test_master_data_tersedia(): void
    {
        $this->assertDatabaseHas('jenis_jasa', [
            'nama_jasa' => 'Jahit Kemeja',
        ]);

        $this->assertDatabaseHas('kategori_pola', [
            'nama_kategori' => 'Kemeja',
        ]);

        $this->assertDatabaseHas('kategori_bahan', [
            'nama_kategori' => 'Kain',
        ]);
    }

    /**
     * Uji relasi Pesanan Header-Item dan Pelanggan
     */
    public function test_relasi_pesanan_dan_item(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Budi Santoso',
            'no_hp' => '081299998888',
            'alamat' => 'Jl. Merdeka No. 10',
        ]);

        $jasa = JenisJasa::where('nama_jasa', 'Jahit Kemeja')->first();

        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'TEST-001',
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(7)->toDateString(),
            'status' => 'aktif',
        ]);

        $item = ItemPesanan::create([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => $jasa->id,
            'detail_pekerjaan' => 'Kemeja Batik Lengan Panjang',
            'harga' => 150000,
        ]);

        $this->assertEquals(1, $pesanan->itemPesanan()->count());
        $this->assertEquals(150000, $pesanan->total_harga);
        $this->assertEquals($pelanggan->id, $pesanan->pelanggan->id);
    }

    /**
     * Uji MySQL Trigger: auto kurangi stok dan catat riwayat stok saat pemakaian bahan
     */
    public function test_trigger_pemakaian_bahan_memotong_stok_otomatis(): void
    {
        $kategori = KategoriBahan::first();
        $bahan = Bahan::create([
            'kategori_bahan_id' => $kategori->id,
            'nama_bahan' => 'Kain Katun Toyobo Test',
            'satuan' => 'meter',
            'stok' => 50,
            'stok_minimum' => 5,
        ]);

        $pelanggan = Pelanggan::create(['nama' => 'Testing Pelanggan']);
        $jasa = JenisJasa::first();
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'TEST-TRG-01',
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(5)->toDateString(),
        ]);

        $item = ItemPesanan::create([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => $jasa->id,
            'harga' => 100000,
        ]);

        // Insert pemakaian bahan 3.5 meter
        $pemakaian = PemakaianBahan::create([
            'item_pesanan_id' => $item->id,
            'bahan_id' => $bahan->id,
            'jumlah' => 3.5,
        ]);

        // Cek stok berkurang via Trigger
        $bahan->refresh();
        $this->assertEquals(46.5, (float) $bahan->stok);

        // Cek riwayat stok terisi otomatis via Trigger
        $riwayat = RiwayatStok::where('bahan_id', $bahan->id)
            ->where('jenis', 'keluar')
            ->first();

        $this->assertNotNull($riwayat);
        $this->assertEquals(3.5, (float) $riwayat->jumlah);
        $this->assertEquals(50.0, (float) $riwayat->stok_sebelum);
        $this->assertEquals(46.5, (float) $riwayat->stok_sesudah);

        // Hapus pemakaian bahan -> stok harus kembali via delete Trigger
        $pemakaian->delete();
        $bahan->refresh();
        $this->assertEquals(50.0, (float) $bahan->stok);
    }

    /**
     * Uji Stored Function MySQL: hitung sisa pembayaran dan status pembayaran
     */
    public function test_stored_function_sisa_dan_status_pembayaran(): void
    {
        $pelanggan = Pelanggan::create(['nama' => 'Pelanggan POS Test']);
        $jasa = JenisJasa::first();

        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'TEST-POS-01',
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(3)->toDateString(),
        ]);

        // Tambah 2 item pesanan (total: 250.000)
        ItemPesanan::create([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => $jasa->id,
            'harga' => 150000,
        ]);
        ItemPesanan::create([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => $jasa->id,
            'harga' => 100000,
        ]);

        // Kondisi 1: Belum ada pembayaran
        $this->assertEquals(250000, $pesanan->sisa_pembayaran);
        $this->assertEquals('Belum Bayar', $pesanan->status_pembayaran);

        // Kondisi 2: DP sebagian (100.000)
        Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'jumlah' => 100000,
            'metode' => 'transfer',
            'tanggal_bayar' => now(),
        ]);
        $this->assertEquals(150000, $pesanan->sisa_pembayaran);
        $this->assertEquals('DP', $pesanan->status_pembayaran);

        // Kondisi 3: Pelunasan (150.000)
        Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'jumlah' => 150000,
            'metode' => 'tunai',
            'tanggal_bayar' => now(),
        ]);
        $this->assertEquals(0, $pesanan->sisa_pembayaran);
        $this->assertEquals('Lunas', $pesanan->status_pembayaran);
    }

    /**
     * Uji Stored Procedure: sp_buat_pesanan dengan COMMIT dan copy template tahapan otomatis
     */
    public function test_stored_procedure_buat_pesanan_commit(): void
    {
        $pelanggan = Pelanggan::create(['nama' => 'Pelanggan SP Test']);
        $jasa = JenisJasa::where('nama_jasa', 'Jahit Kemeja')->first();

        $nomorPesanan = 'SP-ORD-'.uniqid();

        // Panggil Stored Procedure sp_buat_pesanan
        DB::statement('
            CALL sp_buat_pesanan(
                ?,
                ?,
                "2026-10-01",
                "2026-10-08",
                "Catatan SP",
                ?,
                175000,
                NULL,
                NULL,
                "Kemeja Katun Formal",
                @p_pesanan_id,
                @p_item_pesanan_id,
                @p_status_code,
                @p_status_message
            )
        ', [$nomorPesanan, $pelanggan->id, $jasa->id]);

        $res = DB::selectOne('
            SELECT @p_pesanan_id AS pesanan_id, 
                   @p_item_pesanan_id AS item_pesanan_id, 
                   @p_status_code AS status_code, 
                   @p_status_message AS status_message
        ');

        $this->assertEquals(200, $res->status_code);
        $this->assertNotNull($res->pesanan_id);
        $this->assertNotNull($res->item_pesanan_id);

        // Pastikan header pesanan ter-COMMIT
        $this->assertDatabaseHas('pesanan', [
            'id' => $res->pesanan_id,
            'nomor_pesanan' => $nomorPesanan,
        ]);

        // Pastikan template tahapan tersalin ke progress_item secara atomik
        $progressCount = ProgressItem::where('item_pesanan_id', $res->item_pesanan_id)->count();
        $this->assertEquals(7, $progressCount); // Jahit Kemeja punya 7 tahapan default
    }

    /**
     * Uji Stored Procedure: sp_catat_pemakaian_bahan ROLLBACK jika stok tidak cukup
     */
    public function test_stored_procedure_pemakaian_bahan_rollback_stok_kurang(): void
    {
        $kategori = KategoriBahan::first();
        $bahan = Bahan::create([
            'kategori_bahan_id' => $kategori->id,
            'nama_bahan' => 'Kain Sutra Langka',
            'satuan' => 'meter',
            'stok' => 5, // Stok hanya 5 meter
            'stok_minimum' => 1,
        ]);

        $pelanggan = Pelanggan::create(['nama' => 'Pelanggan Sutra']);
        $jasa = JenisJasa::first();
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'SP-SUTRA-'.uniqid(),
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(5)->toDateString(),
        ]);
        $item = ItemPesanan::create([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => $jasa->id,
            'harga' => 300000,
        ]);

        // Coba minta pemakaian 10 meter (melebihi stok 5) -> Harus ROLLBACK
        DB::statement('
            CALL sp_catat_pemakaian_bahan(
                ?,
                ?,
                10,
                "Minta 10 meter kain sutra",
                @p_status_code,
                @p_status_message
            )
        ', [$item->id, $bahan->id]);

        $res = DB::selectOne('
            SELECT @p_status_code AS status_code, 
                   @p_status_message AS status_message
        ');

        // Status code 400 (Bad Request - Stok tidak cukup)
        $this->assertEquals(400, $res->status_code);
        $this->assertStringContainsString('Stok tidak mencukupi', $res->status_message);

        // Pastikan tidak ada data yang masuk ke pemakaian_bahan (karena di-ROLLBACK)
        $this->assertDatabaseMissing('pemakaian_bahan', [
            'item_pesanan_id' => $item->id,
            'bahan_id' => $bahan->id,
        ]);

        // Pastikan stok tidak berubah
        $bahan->refresh();
        $this->assertEquals(5, (float) $bahan->stok);
    }
}
