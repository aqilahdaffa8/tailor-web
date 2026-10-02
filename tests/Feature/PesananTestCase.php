<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\ItemPesanan;
use App\Models\JenisJasa;
use App\Models\KategoriBahan;
use App\Models\Pelanggan;
use App\Models\PemakaianBahan;
use App\Models\Pengukuran;
use App\Models\Pesanan;
use App\Models\TemplateTahapan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Basis test modul Pesanan.
 *
 * Stored Procedure sp_buat_pesanan & sp_catat_pemakaian_bahan memakai START TRANSACTION/COMMIT
 * sendiri, sehingga transaksi pembungkus DatabaseTransactions ikut ter-commit secara implisit
 * dan rollback otomatis tidak lagi berlaku. Karena itu semua data test diberi awalan
 * PREFIX dan dibersihkan manual di tearDown().
 */
abstract class PesananTestCase extends TestCase
{
    use DatabaseTransactions;

    protected const PREFIX = 'ZZ-TEST-PESANAN';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
    }

    protected function tearDown(): void
    {
        $this->bersihkanDataTest();

        parent::tearDown();
    }

    protected function bersihkanDataTest(): void
    {
        $pelangganIds = Pelanggan::where('nama', 'like', self::PREFIX.'%')->pluck('id');
        $pesananIds = Pesanan::whereIn('pelanggan_id', $pelangganIds)->pluck('id');
        $itemIds = ItemPesanan::whereIn('pesanan_id', $pesananIds)->pluck('id');

        // Hapus pemakaian lebih dulu agar trigger MySQL berjalan (cascade FK tidak memicu trigger).
        PemakaianBahan::whereIn('item_pesanan_id', $itemIds)->delete();
        Pesanan::whereIn('id', $pesananIds)->delete();
        Pengukuran::whereIn('pelanggan_id', $pelangganIds)->delete();
        Pelanggan::whereIn('id', $pelangganIds)->delete();
        Bahan::where('nama_bahan', 'like', self::PREFIX.'%')->delete();
    }

    /**
     * @return array<string, string>
     */
    protected function apiHeaders(): array
    {
        $token = $this->user->createToken('api-pesanan-test')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }

    protected function buatPelanggan(): Pelanggan
    {
        return Pelanggan::factory()->create(['nama' => self::PREFIX.' '.fake()->firstName()]);
    }

    /**
     * Jenis jasa dari seeder yang memiliki template tahapan.
     */
    protected function jasa(): JenisJasa
    {
        $id = TemplateTahapan::query()->orderBy('jenis_jasa_id')->value('jenis_jasa_id');

        return JenisJasa::findOrFail($id);
    }

    protected function jumlahTahapan(JenisJasa $jasa): int
    {
        return TemplateTahapan::where('jenis_jasa_id', $jasa->id)->count();
    }

    /**
     * Pesanan lewat factory (tanpa Stored Procedure) berisi satu item lengkap dengan progress.
     *
     * @param  array<string, mixed>  $pesananOverride
     * @param  array<string, mixed>  $itemOverride
     */
    protected function buatPesananDenganItem(?Pelanggan $pelanggan = null, array $pesananOverride = [], array $itemOverride = []): Pesanan
    {
        $pelanggan ??= $this->buatPelanggan();

        $pesanan = Pesanan::factory()->create(array_merge(['pelanggan_id' => $pelanggan->id], $pesananOverride));

        $this->tambahItemFactory($pesanan, $itemOverride);

        return $pesanan->fresh();
    }

    /**
     * @param  array<string, mixed>  $itemOverride
     */
    protected function tambahItemFactory(Pesanan $pesanan, array $itemOverride = []): ItemPesanan
    {
        return ItemPesanan::factory()->denganProgress()->create(array_merge([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => $this->jasa()->id,
            'harga' => 100000,
        ], $itemOverride));
    }

    protected function buatBahan(float $stok = 10, string $nama = 'Kain'): Bahan
    {
        $kategori = KategoriBahan::query()->first() ?? KategoriBahan::factory()->create();

        return Bahan::factory()->create([
            'kategori_bahan_id' => $kategori->id,
            'nama_bahan' => self::PREFIX.' '.$nama,
            'satuan' => 'Meter',
            'stok' => $stok,
            'stok_minimum' => 1,
        ]);
    }

    /**
     * Payload pembuatan pesanan yang valid.
     *
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function dataPesanan(Pelanggan $pelanggan, array $override = []): array
    {
        return array_merge([
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(7)->toDateString(),
            'catatan' => 'Pesanan uji coba',
            'items' => [
                [
                    'jenis_jasa_id' => $this->jasa()->id,
                    'harga' => 150000,
                    'detail_pekerjaan' => 'Kemeja uji coba',
                ],
            ],
        ], $override);
    }

    /**
     * Maju-kan seluruh tahapan hingga tahapan bernama $sampaiNama (atau semuanya bila null) selesai.
     */
    protected function selesaikanTahapan(ItemPesanan $item, ?string $sampaiNama = null): void
    {
        foreach ($item->progress()->get() as $tahap) {
            $tahap->update(['is_completed' => true, 'completed_at' => now()]);

            if ($sampaiNama !== null && strcasecmp($tahap->nama_tahapan, $sampaiNama) === 0) {
                break;
            }
        }
    }
}
