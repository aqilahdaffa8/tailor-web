<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\ItemPesanan;
use App\Models\JenisJasa;
use App\Models\KategoriBahan;
use App\Models\Pelanggan;
use App\Models\PemakaianBahan;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
    }

    /**
     * @return array<string, string>
     */
    protected function apiHeaders(): array
    {
        $token = $this->user->createToken('api-inventory-test')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dataBahanValid(array $override = []): array
    {
        return array_merge([
            'kategori_bahan_id' => KategoriBahan::factory()->create()->id,
            'nama_bahan' => 'Kain Katun Uji Coba',
            'satuan' => 'Meter',
            'stok' => 25,
            'stok_minimum' => 5,
        ], $override);
    }

    /**
     * @return array<string, mixed>
     */
    protected function dataPeralatanValid(array $override = []): array
    {
        return array_merge([
            'nama_alat' => 'Mesin Jahit Uji Coba',
            'jumlah' => 2,
            'kondisi' => 'Baik',
            'tanggal_beli' => '2026-01-15',
            'catatan' => 'Ditaruh di ruang produksi',
        ], $override);
    }

    // ------------------------------------------------------------------
    // WEB: Akses halaman
    // ------------------------------------------------------------------

    public function test_halaman_inventory_memerlukan_login(): void
    {
        $this->get('/inventory')->assertRedirect('/login');
        $this->post('/inventory/bahan', [])->assertRedirect('/login');
    }

    public function test_halaman_inventory_dapat_diakses_user_login(): void
    {
        $bahan = Bahan::factory()->create(['nama_bahan' => 'Kain Drill Halaman Uji']);
        $alat = Peralatan::factory()->create(['nama_alat' => 'Gunting Halaman Uji']);

        $this->actingAs($this->user)
            ->get('/inventory')
            ->assertStatus(200)
            ->assertSee('Manajemen Inventaris')
            ->assertSee('Tambah Bahan Baru')
            ->assertSee('Tambah Alat Baru')
            ->assertSee($bahan->nama_bahan)
            ->assertSee($alat->nama_alat);
    }

    public function test_pencarian_bahan_di_halaman_inventory(): void
    {
        Bahan::factory()->create(['nama_bahan' => 'Benang Emas Cari']);
        Bahan::factory()->create(['nama_bahan' => 'Kancing Perak Lain']);

        $this->actingAs($this->user)
            ->get('/inventory?tab=bahan&search=Benang Emas Cari')
            ->assertStatus(200)
            ->assertSee('Benang Emas Cari')
            ->assertDontSee('Kancing Perak Lain');
    }

    public function test_filter_stok_menipis_di_halaman_inventory(): void
    {
        Bahan::factory()->create(['nama_bahan' => 'Kain Menipis Uji', 'stok' => 1, 'stok_minimum' => 5]);
        Bahan::factory()->create(['nama_bahan' => 'Kain Aman Uji', 'stok' => 50, 'stok_minimum' => 5]);

        $this->actingAs($this->user)
            ->get('/inventory?tab=bahan&menipis=1')
            ->assertStatus(200)
            ->assertSee('Kain Menipis Uji')
            ->assertDontSee('Kain Aman Uji');

        $this->actingAs($this->user)
            ->get('/inventory?tab=bahan')
            ->assertSee('Peringatan Stok Bahan Menipis');
    }

    // ------------------------------------------------------------------
    // WEB: CRUD Bahan
    // ------------------------------------------------------------------

    public function test_web_tambah_bahan_baru_dan_catat_stok_awal(): void
    {
        $data = $this->dataBahanValid();

        $this->actingAs($this->user)
            ->post('/inventory/bahan', $data)
            ->assertRedirect(route('inventory.index', ['tab' => 'bahan']))
            ->assertSessionHas('success');

        $bahan = Bahan::where('nama_bahan', 'Kain Katun Uji Coba')->firstOrFail();
        $this->assertEquals(25.0, (float) $bahan->stok);

        $riwayat = RiwayatStok::where('bahan_id', $bahan->id)->firstOrFail();
        $this->assertSame('masuk', $riwayat->jenis);
        $this->assertEquals(25.0, (float) $riwayat->jumlah);
        $this->assertEquals(0.0, (float) $riwayat->stok_sebelum);
        $this->assertEquals(25.0, (float) $riwayat->stok_sesudah);
    }

    public function test_web_tambah_bahan_tanpa_stok_awal_tidak_membuat_riwayat(): void
    {
        $this->actingAs($this->user)
            ->post('/inventory/bahan', $this->dataBahanValid(['nama_bahan' => 'Benang Stok Nol', 'stok' => 0]))
            ->assertRedirect();

        $bahan = Bahan::where('nama_bahan', 'Benang Stok Nol')->firstOrFail();
        $this->assertSame(0, RiwayatStok::where('bahan_id', $bahan->id)->count());
    }

    public function test_web_tambah_bahan_gagal_validasi(): void
    {
        $this->actingAs($this->user)
            ->post('/inventory/bahan', [])
            ->assertSessionHasErrors(['kategori_bahan_id', 'nama_bahan', 'satuan', 'stok', 'stok_minimum']);

        $this->actingAs($this->user)
            ->post('/inventory/bahan', $this->dataBahanValid(['stok' => -3]))
            ->assertSessionHasErrors(['stok']);
    }

    public function test_web_update_bahan_tidak_mengubah_stok(): void
    {
        $bahan = Bahan::factory()->create(['nama_bahan' => 'Kain Lama', 'stok' => 40, 'stok_minimum' => 5]);
        $kategoriBaru = KategoriBahan::factory()->create();

        $this->actingAs($this->user)
            ->put("/inventory/bahan/{$bahan->id}", [
                'kategori_bahan_id' => $kategoriBaru->id,
                'nama_bahan' => 'Kain Baru',
                'satuan' => 'Yard',
                'stok_minimum' => 8,
                'stok' => 999,
            ])
            ->assertRedirect(route('inventory.index', ['tab' => 'bahan']))
            ->assertSessionHas('success');

        $bahan->refresh();
        $this->assertSame('Kain Baru', $bahan->nama_bahan);
        $this->assertSame('Yard', $bahan->satuan);
        $this->assertSame($kategoriBaru->id, $bahan->kategori_bahan_id);
        $this->assertEquals(8.0, (float) $bahan->stok_minimum);
        $this->assertEquals(40.0, (float) $bahan->stok);
    }

    public function test_web_hapus_bahan(): void
    {
        $bahan = Bahan::factory()->create();

        $this->actingAs($this->user)
            ->delete("/inventory/bahan/{$bahan->id}")
            ->assertRedirect(route('inventory.index', ['tab' => 'bahan']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bahan', ['id' => $bahan->id]);
    }

    // ------------------------------------------------------------------
    // WEB: Stok masuk & riwayat
    // ------------------------------------------------------------------

    public function test_web_tambah_stok_masuk_menambah_stok_dan_riwayat(): void
    {
        $bahan = Bahan::factory()->create(['stok' => 10]);

        $this->actingAs($this->user)
            ->post("/inventory/bahan/{$bahan->id}/stok-masuk", [
                'jumlah' => 5.5,
                'keterangan' => 'Beli di Toko Kain Sentosa',
            ])
            ->assertRedirect(route('inventory.index', ['tab' => 'bahan']))
            ->assertSessionHas('success');

        $this->assertEquals(15.5, (float) $bahan->fresh()->stok);

        $riwayat = RiwayatStok::where('bahan_id', $bahan->id)->firstOrFail();
        $this->assertSame('masuk', $riwayat->jenis);
        $this->assertEquals(5.5, (float) $riwayat->jumlah);
        $this->assertEquals(10.0, (float) $riwayat->stok_sebelum);
        $this->assertEquals(15.5, (float) $riwayat->stok_sesudah);
        $this->assertSame('Beli di Toko Kain Sentosa', $riwayat->keterangan);
    }

    public function test_web_stok_masuk_tanpa_keterangan_memakai_keterangan_default(): void
    {
        $bahan = Bahan::factory()->create(['stok' => 1]);

        $this->actingAs($this->user)
            ->post("/inventory/bahan/{$bahan->id}/stok-masuk", ['jumlah' => 2])
            ->assertRedirect();

        $this->assertDatabaseHas('riwayat_stok', [
            'bahan_id' => $bahan->id,
            'keterangan' => 'Penambahan stok manual',
        ]);
    }

    public function test_web_stok_masuk_menolak_jumlah_nol_atau_negatif(): void
    {
        $bahan = Bahan::factory()->create(['stok' => 10]);

        foreach ([0, -5, 'abc'] as $jumlah) {
            $this->actingAs($this->user)
                ->post("/inventory/bahan/{$bahan->id}/stok-masuk", ['jumlah' => $jumlah])
                ->assertSessionHasErrors(['jumlah']);
        }

        $this->assertEquals(10.0, (float) $bahan->fresh()->stok);
        $this->assertSame(0, RiwayatStok::where('bahan_id', $bahan->id)->count());
    }

    public function test_halaman_riwayat_stok_bahan_dapat_diakses(): void
    {
        $bahan = Bahan::factory()->create(['nama_bahan' => 'Kain Riwayat Uji']);

        $this->actingAs($this->user)
            ->get("/inventory/bahan/{$bahan->id}/riwayat")
            ->assertStatus(200)
            ->assertSee('Riwayat Mutasi Stok')
            ->assertSee('Kain Riwayat Uji')
            ->assertSee('Belum ada riwayat mutasi');

        RiwayatStok::create([
            'bahan_id' => $bahan->id,
            'jenis' => 'masuk',
            'jumlah' => 10,
            'stok_sebelum' => 0,
            'stok_sesudah' => 10,
            'keterangan' => 'Pembelian awal uji',
        ]);

        $this->actingAs($this->user)
            ->get("/inventory/bahan/{$bahan->id}/riwayat")
            ->assertStatus(200)
            ->assertDontSee('Belum ada riwayat mutasi')
            ->assertSee('Pembelian awal uji');
    }

    // ------------------------------------------------------------------
    // WEB: CRUD Peralatan
    // ------------------------------------------------------------------

    public function test_web_tambah_peralatan_baru(): void
    {
        $this->actingAs($this->user)
            ->post('/inventory/peralatan', $this->dataPeralatanValid())
            ->assertRedirect(route('inventory.index', ['tab' => 'peralatan']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('peralatan', [
            'nama_alat' => 'Mesin Jahit Uji Coba',
            'jumlah' => 2,
            'kondisi' => 'Baik',
            'tanggal_beli' => '2026-01-15',
        ]);
    }

    public function test_web_tambah_peralatan_gagal_validasi(): void
    {
        $this->actingAs($this->user)
            ->post('/inventory/peralatan', [])
            ->assertSessionHasErrors(['nama_alat', 'jumlah', 'kondisi']);

        $this->actingAs($this->user)
            ->post('/inventory/peralatan', $this->dataPeralatanValid(['kondisi' => 'Hancur', 'jumlah' => 0]))
            ->assertSessionHasErrors(['kondisi', 'jumlah']);
    }

    public function test_web_update_peralatan(): void
    {
        $alat = Peralatan::factory()->create(['nama_alat' => 'Gunting Lama', 'kondisi' => 'Baik']);

        $this->actingAs($this->user)
            ->put("/inventory/peralatan/{$alat->id}", $this->dataPeralatanValid([
                'nama_alat' => 'Gunting Baru',
                'kondisi' => 'Perlu Servis',
                'jumlah' => 3,
            ]))
            ->assertRedirect(route('inventory.index', ['tab' => 'peralatan']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('peralatan', [
            'id' => $alat->id,
            'nama_alat' => 'Gunting Baru',
            'kondisi' => 'Perlu Servis',
            'jumlah' => 3,
        ]);
    }

    public function test_web_hapus_peralatan(): void
    {
        $alat = Peralatan::factory()->create();

        $this->actingAs($this->user)
            ->delete("/inventory/peralatan/{$alat->id}")
            ->assertRedirect(route('inventory.index', ['tab' => 'peralatan']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('peralatan', ['id' => $alat->id]);
    }

    // ------------------------------------------------------------------
    // MySQL Trigger: stok keluar otomatis dari pemakaian bahan
    // ------------------------------------------------------------------

    public function test_trigger_pemakaian_bahan_memotong_stok_dan_tampil_di_riwayat_api(): void
    {
        $bahan = Bahan::factory()->create(['stok' => 20, 'stok_minimum' => 2]);

        $pelanggan = Pelanggan::factory()->create();
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'TEST-INV-01',
            'pelanggan_id' => $pelanggan->id,
            'tanggal_pesan' => now()->toDateString(),
            'deadline' => now()->addDays(5)->toDateString(),
        ]);
        $item = ItemPesanan::create([
            'pesanan_id' => $pesanan->id,
            'jenis_jasa_id' => JenisJasa::firstOrFail()->id,
            'harga' => 100000,
        ]);

        $pemakaian = PemakaianBahan::create([
            'item_pesanan_id' => $item->id,
            'bahan_id' => $bahan->id,
            'jumlah' => 3.5,
        ]);

        $this->assertEquals(16.5, (float) $bahan->fresh()->stok);

        $response = $this->withHeaders($this->apiHeaders())
            ->getJson("/api/bahan/{$bahan->id}/riwayat")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $keluar = collect($response->json('data'))->firstWhere('jenis', 'keluar');
        $this->assertNotNull($keluar);
        $this->assertEquals(3.5, (float) $keluar['jumlah']);
        $this->assertEquals(20.0, (float) $keluar['stok_sebelum']);
        $this->assertEquals(16.5, (float) $keluar['stok_sesudah']);

        $pemakaian->delete();
        $this->assertEquals(20.0, (float) $bahan->fresh()->stok);
    }

    // ------------------------------------------------------------------
    // API: Bahan
    // ------------------------------------------------------------------

    public function test_api_inventory_memerlukan_token(): void
    {
        $this->getJson('/api/bahan')->assertStatus(401);
        $this->getJson('/api/peralatan')->assertStatus(401);
    }

    public function test_api_list_bahan_dan_filter_stok_menipis(): void
    {
        Bahan::factory()->create(['nama_bahan' => 'API Menipis Uji', 'stok' => 1, 'stok_minimum' => 5]);
        Bahan::factory()->create(['nama_bahan' => 'API Aman Uji', 'stok' => 50, 'stok_minimum' => 5]);

        $semua = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/bahan')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $namaSemua = collect($semua->json('data'))->pluck('nama_bahan');
        $this->assertTrue($namaSemua->contains('API Menipis Uji'));
        $this->assertTrue($namaSemua->contains('API Aman Uji'));

        $menipis = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/bahan?menipis=1')
            ->assertStatus(200);

        $namaMenipis = collect($menipis->json('data'))->pluck('nama_bahan');
        $this->assertTrue($namaMenipis->contains('API Menipis Uji'));
        $this->assertFalse($namaMenipis->contains('API Aman Uji'));
    }

    public function test_api_crud_bahan_dan_stok_masuk(): void
    {
        $headers = $this->apiHeaders();
        $data = $this->dataBahanValid(['nama_bahan' => 'API Kain Crud', 'stok' => 12]);

        // 1. Create
        $create = $this->withHeaders($headers)->postJson('/api/bahan', $data);
        $create->assertStatus(201)->assertJsonPath('success', true);
        $bahanId = $create->json('data.id');

        // 2. Show (memuat riwayat stok awal)
        $show = $this->withHeaders($headers)
            ->getJson("/api/bahan/{$bahanId}")
            ->assertStatus(200)
            ->assertJsonPath('data.nama_bahan', 'API Kain Crud');
        $this->assertCount(1, $show->json('data.riwayat_stok'));

        // 3. Update
        $this->withHeaders($headers)
            ->putJson("/api/bahan/{$bahanId}", [
                'kategori_bahan_id' => $data['kategori_bahan_id'],
                'nama_bahan' => 'API Kain Crud Update',
                'satuan' => 'Roll',
                'stok_minimum' => 3,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.nama_bahan', 'API Kain Crud Update')
            ->assertJsonPath('data.satuan', 'Roll');

        // 4. Stok masuk
        $stok = $this->withHeaders($headers)
            ->postJson("/api/bahan/{$bahanId}/stok-masuk", ['jumlah' => 8, 'keterangan' => 'Restock API'])
            ->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertEquals(20.0, (float) $stok->json('data.bahan.stok'));
        $this->assertSame('Restock API', $stok->json('data.riwayat.keterangan'));

        // 5. Riwayat: stok awal + stok masuk
        $riwayat = $this->withHeaders($headers)
            ->getJson("/api/bahan/{$bahanId}/riwayat")
            ->assertStatus(200);
        $this->assertCount(2, $riwayat->json('data'));

        // 6. Delete
        $this->withHeaders($headers)
            ->deleteJson("/api/bahan/{$bahanId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('bahan', ['id' => $bahanId]);
    }

    public function test_api_bahan_gagal_validasi(): void
    {
        $headers = $this->apiHeaders();
        $bahan = Bahan::factory()->create();

        $this->withHeaders($headers)
            ->postJson('/api/bahan', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kategori_bahan_id', 'nama_bahan', 'satuan', 'stok', 'stok_minimum']);

        $this->withHeaders($headers)
            ->postJson("/api/bahan/{$bahan->id}/stok-masuk", ['jumlah' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jumlah']);
    }

    // ------------------------------------------------------------------
    // API: Peralatan
    // ------------------------------------------------------------------

    public function test_api_crud_peralatan(): void
    {
        $headers = $this->apiHeaders();

        // 1. Create
        $create = $this->withHeaders($headers)
            ->postJson('/api/peralatan', $this->dataPeralatanValid(['nama_alat' => 'API Mesin Obras']));
        $create->assertStatus(201)->assertJsonPath('success', true);
        $alatId = $create->json('data.id');

        // 2. List + filter kondisi
        $list = $this->withHeaders($headers)
            ->getJson('/api/peralatan?kondisi=Baik')
            ->assertStatus(200);
        $this->assertTrue(collect($list->json('data'))->pluck('nama_alat')->contains('API Mesin Obras'));

        // 3. Show
        $this->withHeaders($headers)
            ->getJson("/api/peralatan/{$alatId}")
            ->assertStatus(200)
            ->assertJsonPath('data.nama_alat', 'API Mesin Obras');

        // 4. Update
        $this->withHeaders($headers)
            ->putJson("/api/peralatan/{$alatId}", $this->dataPeralatanValid([
                'nama_alat' => 'API Mesin Obras Update',
                'kondisi' => 'Rusak Ringan',
            ]))
            ->assertStatus(200)
            ->assertJsonPath('data.nama_alat', 'API Mesin Obras Update')
            ->assertJsonPath('data.kondisi', 'Rusak Ringan');

        // 5. Delete
        $this->withHeaders($headers)
            ->deleteJson("/api/peralatan/{$alatId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertDatabaseMissing('peralatan', ['id' => $alatId]);
    }

    public function test_api_peralatan_gagal_validasi(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->postJson('/api/peralatan', $this->dataPeralatanValid(['kondisi' => 'Hancur', 'jumlah' => 0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kondisi', 'jumlah']);
    }
}
