<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
    }

    /**
     * Halaman pelanggan memerlukan login
     */
    public function test_halaman_pelanggan_memerlukan_login(): void
    {
        $this->get('/pelanggan')->assertRedirect('/login');
    }

    /**
     * Halaman index pelanggan dapat diakses
     */
    public function test_halaman_index_pelanggan_dapat_diakses(): void
    {
        $this->actingAs($this->user)
            ->get('/pelanggan')
            ->assertStatus(200)
            ->assertSee('Daftar Pelanggan', false);
    }

    /**
     * Halaman tambah pelanggan dapat diakses
     */
    public function test_halaman_create_pelanggan_dapat_diakses(): void
    {
        $this->actingAs($this->user)
            ->get('/pelanggan/create')
            ->assertStatus(200);
    }

    /**
     * Web: Tambah pelanggan baru
     */
    public function test_web_tambah_pelanggan_baru(): void
    {
        $response = $this->actingAs($this->user)->post('/pelanggan', [
            'nama' => 'Siti Rahma Dewi',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Mawar No. 5, Bandung',
            'catatan' => 'Pelanggan VIP',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pelanggan', [
            'nama' => 'Siti Rahma Dewi',
            'no_hp' => '081234567890',
        ]);
    }

    /**
     * Web: Validasi gagal jika nama kosong
     */
    public function test_web_validasi_gagal_nama_kosong(): void
    {
        $this->actingAs($this->user)
            ->post('/pelanggan', ['nama' => '', 'no_hp' => '08123'])
            ->assertSessionHasErrors('nama');
    }

    /**
     * Web: Update data pelanggan
     */
    public function test_web_update_pelanggan(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $this->actingAs($this->user)
            ->put("/pelanggan/{$pelanggan->id}", [
                'nama' => 'Nama Diperbarui',
                'no_hp' => '089999888777',
                'alamat' => 'Alamat Baru',
            ])
            ->assertRedirect("/pelanggan/{$pelanggan->id}");

        $this->assertDatabaseHas('pelanggan', ['id' => $pelanggan->id, 'nama' => 'Nama Diperbarui']);
    }

    /**
     * Web: Hapus pelanggan
     */
    public function test_web_hapus_pelanggan(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $this->actingAs($this->user)
            ->delete("/pelanggan/{$pelanggan->id}")
            ->assertRedirect('/pelanggan');

        $this->assertDatabaseMissing('pelanggan', ['id' => $pelanggan->id]);
    }

    /**
     * Web: Search pelanggan
     */
    public function test_web_search_pelanggan(): void
    {
        Pelanggan::factory()->create(['nama' => 'Budi Santoso Unik']);

        $this->actingAs($this->user)
            ->get('/pelanggan?search=Budi+Santoso+Unik')
            ->assertStatus(200)
            ->assertSee('Budi Santoso Unik');
    }

    /**
     * API: CRUD Pelanggan
     */
    public function test_api_crud_pelanggan(): void
    {
        $token = $this->user->createToken('api-test')->plainTextToken;

        // List
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/pelanggan')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // Create
        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pelanggan', [
                'nama' => 'Pelanggan API Test',
                'no_hp' => '082233445566',
            ]);
        $create->assertStatus(201);
        $id = $create->json('data.id');

        // Show
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/pelanggan/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.nama', 'Pelanggan API Test');

        // Update
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/pelanggan/{$id}", [
                'nama' => 'Pelanggan API Update',
                'no_hp' => '082233445566',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.nama', 'Pelanggan API Update');

        // Delete
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/pelanggan/{$id}")
            ->assertStatus(200);
    }
}
