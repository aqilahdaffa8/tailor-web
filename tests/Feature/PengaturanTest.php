<?php

namespace Tests\Feature;

use App\Models\JenisJasa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
    }

    /**
     * Halaman pengaturan memerlukan login
     */
    public function test_halaman_pengaturan_memerlukan_login(): void
    {
        $response = $this->get('/pengaturan');
        $response->assertRedirect('/login');
    }

    /**
     * Halaman pengaturan dapat diakses user login
     */
    public function test_halaman_pengaturan_dapat_diakses_user(): void
    {
        $response = $this->actingAs($this->user)->get('/pengaturan');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Atelier', false);
    }

    /**
     * Web: Tambah jenis jasa baru beserta tahapan kerjanya
     */
    public function test_web_tambah_jenis_jasa_beserta_tahapan(): void
    {
        $response = $this->actingAs($this->user)->post('/pengaturan/jasa', [
            'nama_jasa' => 'Jahit Jas Pria Eksklusif',
            'harga_default' => 750000,
            'deskripsi' => 'Jas formal pria wol',
            'is_active' => 1,
            'tahapan' => [
                'Konsultasi & Ukur',
                'Pemotongan Pola',
                'Fitting Pertama',
                'Penjahitan Akhir',
                'Finishing & Packing',
            ],
        ]);

        $response->assertRedirect('/pengaturan?tab=jasa');
        $this->assertDatabaseHas('jenis_jasa', [
            'nama_jasa' => 'Jahit Jas Pria Eksklusif',
            'harga_default' => 750000,
        ]);

        $jasa = JenisJasa::where('nama_jasa', 'Jahit Jas Pria Eksklusif')->first();
        $this->assertEquals(5, $jasa->templateTahapan()->count());
    }

    /**
     * Web: Update profil usaha & format invoice
     */
    public function test_web_update_profil_usaha(): void
    {
        $response = $this->actingAs($this->user)->put('/pengaturan/profil', [
            'nama_usaha' => 'Atelier Jahit Mewah',
            'pemilik' => 'Pak Budi Hartono',
            'no_hp' => '081288887777',
            'alamat' => 'Jl. Atelier Fashion No. 8',
            'prefix_invoice' => 'ATELIER',
            'catatan_kaki_invoice' => 'Garansi permak 14 hari.',
        ]);

        $response->assertRedirect('/pengaturan?tab=profil');
        $this->assertDatabaseHas('pengaturan', ['key' => 'profil_usaha']);
        $this->assertDatabaseHas('pengaturan', ['key' => 'pengaturan_invoice']);
    }

    /**
     * Mobile API: CRUD Jenis Jasa
     */
    public function test_api_crud_jenis_jasa(): void
    {
        $token = $this->user->createToken('api-test')->plainTextToken;

        // List
        $list = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/master/jenis-jasa');
        $list->assertStatus(200)->assertJsonPath('success', true);

        // Create
        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/master/jenis-jasa', [
                'nama_jasa' => 'Jahit Blouse Sutra API',
                'harga_default' => 180000,
                'tahapan' => ['Pola', 'Jahit', 'Selesai'],
            ]);
        $create->assertStatus(201);
        $jasaId = $create->json('data.id');

        // Show
        $show = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/master/jenis-jasa/'.$jasaId);
        $show->assertStatus(200)->assertJsonPath('data.nama_jasa', 'Jahit Blouse Sutra API');

        // Delete
        $delete = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/master/jenis-jasa/'.$jasaId);
        $delete->assertStatus(200);
    }

    /**
     * Mobile API: Get & Update Pengaturan
     */
    public function test_api_pengaturan_profil(): void
    {
        $token = $this->user->createToken('api-test')->plainTextToken;

        // Get Profil
        $get = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/master/pengaturan');
        $get->assertStatus(200)->assertJsonPath('success', true);

        // Update Profil
        $update = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/master/pengaturan', [
                'nama_usaha' => 'Atelier API Mobile',
                'pemilik' => 'Owner Mobile',
                'no_hp' => '089999111222',
                'alamat' => 'Jl. Mobile App No. 1',
                'prefix_invoice' => 'MOB',
            ]);

        $update->assertStatus(200)
            ->assertJsonPath('data.nama_usaha', 'Atelier API Mobile');
    }
}
