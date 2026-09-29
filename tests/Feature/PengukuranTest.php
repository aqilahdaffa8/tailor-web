<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pengukuran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PengukuranTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
    }

    /**
     * Halaman pengukuran memerlukan login.
     */
    public function test_halaman_pengukuran_memerlukan_login(): void
    {
        $this->get('/pengukuran')->assertRedirect('/login');
    }

    /**
     * Halaman index pengukuran dapat diakses user login.
     */
    public function test_halaman_index_pengukuran_dapat_diakses(): void
    {
        $this->actingAs($this->user)
            ->get('/pengukuran')
            ->assertStatus(200)
            ->assertSee('Data Pengukuran Tubuh', false);
    }

    /**
     * Web: Catat pengukuran tubuh baru.
     */
    public function test_web_catat_pengukuran_baru(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this->actingAs($this->user)->post('/pengukuran', [
            'pelanggan_id' => $pelanggan->id,
            'tanggal_ukur' => '2026-09-28',
            'data_ukuran' => [
                'lingkar_dada' => 96,
                'lingkar_pinggang' => 80,
                'panjang_baju' => 72,
            ],
            'catatan' => 'Preferensi pas badan (slim fit)',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengukuran', [
            'pelanggan_id' => $pelanggan->id,
            'tanggal_ukur' => '2026-09-28',
            'catatan' => 'Preferensi pas badan (slim fit)',
        ]);
    }

    /**
     * Web: Update data pengukuran.
     */
    public function test_web_update_pengukuran(): void
    {
        $pelanggan = Pelanggan::factory()->create();
        $pengukuran = Pengukuran::factory()->create([
            'pelanggan_id' => $pelanggan->id,
            'tanggal_ukur' => '2026-09-28',
        ]);

        $response = $this->actingAs($this->user)->put("/pengukuran/{$pengukuran->id}", [
            'tanggal_ukur' => '2026-09-29',
            'data_ukuran' => [
                'lingkar_dada' => 98,
                'lingkar_pinggang' => 82,
            ],
            'catatan' => 'Revisi ukuran pinggang',
        ]);

        $response->assertRedirect("/pengukuran/{$pengukuran->id}");
        $this->assertDatabaseHas('pengukuran', [
            'id' => $pengukuran->id,
            'tanggal_ukur' => '2026-09-29',
            'catatan' => 'Revisi ukuran pinggang',
        ]);
    }

    /**
     * Web: Hapus data pengukuran.
     */
    public function test_web_hapus_pengukuran(): void
    {
        $pelanggan = Pelanggan::factory()->create();
        $pengukuran = Pengukuran::factory()->create(['pelanggan_id' => $pelanggan->id]);

        $response = $this->actingAs($this->user)->delete("/pengukuran/{$pengukuran->id}");

        $response->assertRedirect("/pelanggan/{$pelanggan->id}");
        $this->assertDatabaseMissing('pengukuran', ['id' => $pengukuran->id]);
    }

    /**
     * API: Get riwayat pengukuran dan latest per pelanggan.
     */
    public function test_api_get_pengukuran_per_pelanggan_dan_latest(): void
    {
        $token = $this->user->createToken('api-pengukuran-test')->plainTextToken;
        $pelanggan = Pelanggan::factory()->create();

        Pengukuran::factory()->create([
            'pelanggan_id' => $pelanggan->id,
            'tanggal_ukur' => '2026-09-25',
        ]);

        $latest = Pengukuran::factory()->create([
            'pelanggan_id' => $pelanggan->id,
            'tanggal_ukur' => '2026-09-28',
            'data_ukuran' => ['lingkar_dada' => 102],
        ]);

        // 1. List riwayat
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/pelanggan/{$pelanggan->id}/pengukuran")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // 2. Latest
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/pelanggan/{$pelanggan->id}/pengukuran/latest")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $latest->id)
            ->assertJsonPath('data.data_ukuran.lingkar_dada', 102);
    }

    /**
     * API: CRUD Pengukuran.
     */
    public function test_api_crud_pengukuran(): void
    {
        $token = $this->user->createToken('api-pengukuran-test')->plainTextToken;
        $pelanggan = Pelanggan::factory()->create();

        // 1. Create
        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pengukuran', [
                'pelanggan_id' => $pelanggan->id,
                'tanggal_ukur' => '2026-09-28',
                'data_ukuran' => [
                    'lingkar_dada' => 100,
                    'panjang_baju' => 75,
                ],
                'catatan' => 'Catatan ukuran API',
            ]);
        $create->assertStatus(201);
        $pengukuranId = $create->json('data.id');

        // 2. Show
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/pengukuran/{$pengukuranId}")
            ->assertStatus(200)
            ->assertJsonPath('data.catatan', 'Catatan ukuran API');

        // 3. Update
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/pengukuran/{$pengukuranId}", [
                'tanggal_ukur' => '2026-09-28',
                'data_ukuran' => [
                    'lingkar_dada' => 104,
                ],
                'catatan' => 'Catatan API Update',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.catatan', 'Catatan API Update');

        // 4. Delete
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/pengukuran/{$pengukuranId}")
            ->assertStatus(200);
    }
}
