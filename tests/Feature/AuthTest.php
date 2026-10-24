<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'testowner@tailor.test',
            'name' => 'Owner Test',
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * Halaman login web dapat ditampilkan
     */
    public function test_halaman_login_web_dapat_dibuka(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Tailor Management System');
        $response->assertSee('Masuk');
    }

    /**
     * Pengguna dapat login via web session
     */
    public function test_user_dapat_login_via_web(): void
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Login web gagal jika kredensial salah
     */
    public function test_user_gagal_login_jika_password_salah(): void
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Pengguna dapat logout dari web
     */
    public function test_user_dapat_logout_web(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Halaman dashboard dilindungi oleh middleware auth
     */
    public function test_dashboard_memerlukan_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Mobile API Login mengembalikan Token Sanctum
     */
    public function test_mobile_api_login_berhasil_mengembalikan_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password123',
            'device_name' => 'Android-Pixel-Test',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'token_type',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertEquals('Bearer', $response->json('token_type'));
        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Endpoint API /api/me memerlukan token Sanctum
     */
    public function test_mobile_api_me_memerlukan_token(): void
    {
        // Tanpa token -> 401 Unauthorized
        $unauthorized = $this->getJson('/api/me');
        $unauthorized->assertStatus(401);

        // Dengan token -> 200 OK
        $token = $this->user->createToken('test-token')->plainTextToken;

        $authorized = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me');

        $authorized->assertStatus(200)
            ->assertJsonPath('data.email', $this->user->email);
    }

    /**
     * Mobile API Logout mencabut token yang sedang digunakan
     */
    public function test_mobile_api_logout_mencabut_token(): void
    {
        $newToken = $this->user->createToken('test-logout-token');
        $plainToken = $newToken->plainTextToken;
        $tokenId = $newToken->accessToken->id;

        $response = $this->withHeader('Authorization', 'Bearer '.$plainToken)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Pastikan token terhapus dari tabel personal_access_tokens
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);

        // Reset auth guard pada aplikasi testing
        $this->app['auth']->forgetGuards();

        // Akses kembali dengan token yang sama harus ditolak (401)
        $afterLogout = $this->withHeader('Authorization', 'Bearer '.$plainToken)
            ->getJson('/api/me');

        $afterLogout->assertStatus(401);
    }
}
