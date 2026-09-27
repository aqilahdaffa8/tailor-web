<?php

namespace Tests\Feature;

use App\Models\JenisPakaian;
use App\Models\KategoriPola;
use App\Models\PolaDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PolaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
    }

    /**
     * Halaman pola memerlukan login.
     */
    public function test_halaman_pola_memerlukan_login(): void
    {
        $this->get('/pola')->assertRedirect('/login');
    }

    /**
     * Halaman index pola dapat diakses user login.
     */
    public function test_halaman_index_pola_dapat_diakses(): void
    {
        $this->actingAs($this->user)
            ->get('/pola')
            ->assertStatus(200)
            ->assertSee('Library Pola Pakaian', false);
    }

    /**
     * Web: Tambah jenis pakaian baru dalam kategori.
     */
    public function test_web_tambah_jenis_pakaian(): void
    {
        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();

        $response = $this->actingAs($this->user)->post('/pola/jenis', [
            'kategori_pola_id' => $kategori->id,
            'nama_jenis' => 'Kemeja Batik Modern Test',
            'deskripsi' => 'Kemeja pola lengan panjang formal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('jenis_pakaian', [
            'kategori_pola_id' => $kategori->id,
            'nama_jenis' => 'Kemeja Batik Modern Test',
        ]);
    }

    /**
     * Web: Tambah pola detail beserta foto dan file pola.
     */
    public function test_web_tambah_pola_detail_dengan_file_dan_foto(): void
    {
        Storage::fake('public');

        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();
        $jenis = JenisPakaian::factory()->create(['kategori_pola_id' => $kategori->id]);

        $foto = UploadedFile::fake()->image('sketsa_pola.jpg', 600, 600);
        $file = UploadedFile::fake()->create('panduan_potongan.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/pola/detail', [
            'jenis_pakaian_id' => $jenis->id,
            'size' => 'L',
            'data_ukuran' => [
                'lingkar_dada' => 104,
                'panjang_baju' => 74,
                'panjang_lengan' => 62,
            ],
            'foto_pola' => $foto,
            'file_pola' => $file,
            'catatan' => 'Gunakan kampuh 1.5 cm',
        ]);

        $response->assertRedirect('/pola/jenis/'.$jenis->id);

        $this->assertDatabaseHas('pola_detail', [
            'jenis_pakaian_id' => $jenis->id,
            'size' => 'L',
        ]);

        $pola = PolaDetail::where('jenis_pakaian_id', $jenis->id)->where('size', 'L')->first();
        $this->assertNotNull($pola->foto_pola);
        $this->assertNotNull($pola->file_pola);
        Storage::disk('public')->assertExists($pola->foto_pola);
        Storage::disk('public')->assertExists($pola->file_pola);
    }

    /**
     * Web: Update pola detail.
     */
    public function test_web_update_pola_detail(): void
    {
        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();
        $jenis = JenisPakaian::factory()->create(['kategori_pola_id' => $kategori->id]);
        $pola = PolaDetail::factory()->create(['jenis_pakaian_id' => $jenis->id, 'size' => 'M']);

        $response = $this->actingAs($this->user)->put('/pola/detail/'.$pola->id, [
            'size' => 'XL',
            'data_ukuran' => [
                'lingkar_dada' => 110,
            ],
            'catatan' => 'Catatan revisi XL',
        ]);

        $response->assertRedirect('/pola/jenis/'.$jenis->id);
        $this->assertDatabaseHas('pola_detail', [
            'id' => $pola->id,
            'size' => 'XL',
            'catatan' => 'Catatan revisi XL',
        ]);
    }

    /**
     * Web: Update pola detail dengan menghapus foto dan file yang ada.
     */
    public function test_web_update_pola_detail_dapat_menghapus_foto_dan_file(): void
    {
        Storage::fake('public');

        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();
        $jenis = JenisPakaian::factory()->create(['kategori_pola_id' => $kategori->id]);

        $foto = UploadedFile::fake()->image('pola_awal.jpg');
        $file = UploadedFile::fake()->create('dokumen_awal.pdf', 50, 'application/pdf');

        $fotoPath = $foto->store('pola/foto', 'public');
        $filePath = $file->store('pola/file', 'public');

        $pola = PolaDetail::factory()->create([
            'jenis_pakaian_id' => $jenis->id,
            'size' => 'M',
            'foto_pola' => $fotoPath,
            'file_pola' => $filePath,
        ]);

        Storage::disk('public')->assertExists($fotoPath);
        Storage::disk('public')->assertExists($filePath);

        $response = $this->actingAs($this->user)->put('/pola/detail/'.$pola->id, [
            'size' => 'M',
            'hapus_foto_pola' => 1,
            'hapus_file_pola' => 1,
        ]);

        $response->assertRedirect('/pola/jenis/'.$jenis->id);

        $pola->refresh();
        $this->assertNull($pola->foto_pola);
        $this->assertNull($pola->file_pola);
        Storage::disk('public')->assertMissing($fotoPath);
        Storage::disk('public')->assertMissing($filePath);
    }

    /**
     * Web: Hapus pola detail.
     */
    public function test_web_hapus_pola_detail(): void
    {
        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();
        $jenis = JenisPakaian::factory()->create(['kategori_pola_id' => $kategori->id]);
        $pola = PolaDetail::factory()->create(['jenis_pakaian_id' => $jenis->id]);

        $response = $this->actingAs($this->user)->delete('/pola/detail/'.$pola->id);

        $response->assertRedirect('/pola/jenis/'.$jenis->id);
        $this->assertDatabaseMissing('pola_detail', ['id' => $pola->id]);
    }

    /**
     * API: Get kategori dan CRUD jenis pakaian.
     */
    public function test_api_crud_kategori_dan_jenis_pakaian(): void
    {
        $token = $this->user->createToken('api-pola-test')->plainTextToken;
        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();

        // 1. Get Kategori
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/pola/kategori')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2. Create Jenis Pakaian
        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pola/jenis-pakaian', [
                'kategori_pola_id' => $kategori->id,
                'nama_jenis' => 'Celana Chino Pria API',
                'deskripsi' => 'Celana chino stretch slim',
            ]);
        $create->assertStatus(201);
        $jenisId = $create->json('data.id');

        // 3. Update Jenis Pakaian
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/pola/jenis-pakaian/'.$jenisId, [
                'kategori_pola_id' => $kategori->id,
                'nama_jenis' => 'Celana Chino Pria Updated',
                'deskripsi' => 'Revisi deskripsi chino',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.nama_jenis', 'Celana Chino Pria Updated');

        // 4. Delete Jenis Pakaian
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/pola/jenis-pakaian/'.$jenisId)
            ->assertStatus(200);
    }

    /**
     * API: CRUD Pola Detail.
     */
    public function test_api_crud_pola_detail(): void
    {
        $token = $this->user->createToken('api-pola-test')->plainTextToken;
        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();
        $jenis = JenisPakaian::factory()->create(['kategori_pola_id' => $kategori->id]);

        // 1. Create Pola Detail
        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pola/detail', [
                'jenis_pakaian_id' => $jenis->id,
                'size' => 'M',
                'data_ukuran' => [
                    'lingkar_dada' => 98,
                    'panjang_baju' => 70,
                ],
                'catatan' => 'Catatan pola API',
            ]);
        $create->assertStatus(201);
        $polaId = $create->json('data.id');

        // 2. Show Pola Detail
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/pola/detail/'.$polaId)
            ->assertStatus(200)
            ->assertJsonPath('data.size', 'M');

        // 3. Update Pola Detail
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/pola/detail/'.$polaId, [
                'size' => 'L',
                'data_ukuran' => [
                    'lingkar_dada' => 102,
                ],
                'catatan' => 'Ukuran diubah ke L',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.size', 'L');

        // 4. Delete Pola Detail
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/pola/detail/'.$polaId)
            ->assertStatus(200);
    }

    /**
     * Web & API: Endpoint hapus instan foto dan file dokumen pola.
     */
    public function test_instant_delete_foto_dan_file_web_dan_api(): void
    {
        Storage::fake('public');

        $kategori = KategoriPola::first() ?? KategoriPola::factory()->create();
        $jenis = JenisPakaian::factory()->create(['kategori_pola_id' => $kategori->id]);

        $foto = UploadedFile::fake()->image('pola.jpg');
        $file = UploadedFile::fake()->create('pola.pdf', 50, 'application/pdf');

        $fotoPath = $foto->store('pola/foto', 'public');
        $filePath = $file->store('pola/file', 'public');

        $pola = PolaDetail::factory()->create([
            'jenis_pakaian_id' => $jenis->id,
            'foto_pola' => $fotoPath,
            'file_pola' => $filePath,
        ]);

        // 1. Web instant delete foto (JSON request)
        $this->actingAs($this->user)
            ->deleteJson("/pola/detail/{$pola->id}/foto")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $pola->refresh();
        $this->assertNull($pola->foto_pola);
        Storage::disk('public')->assertMissing($fotoPath);

        // 2. Web instant delete file (JSON request)
        $this->actingAs($this->user)
            ->deleteJson("/pola/detail/{$pola->id}/file")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $pola->refresh();
        $this->assertNull($pola->file_pola);
        Storage::disk('public')->assertMissing($filePath);
    }
}
