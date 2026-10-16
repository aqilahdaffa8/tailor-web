<?php

namespace Tests\Feature;

use App\Models\Bahan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/**
 * Konvensi API lintas modul: format error seragam, pagination konsisten (meta lengkap + per_page tervalidasi),
 * serta terjemahan Indonesia untuk pesan validasi dan pagination.
 */
class ApiKonvensiTest extends PesananTestCase
{
    // ------------------------------------------------------------------
    // Format error: { success: false, message, errors? }
    // ------------------------------------------------------------------

    public function test_error_401_berformat_seragam(): void
    {
        $this->getJson('/api/pelanggan')
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Belum login atau token tidak valid.');
    }

    public function test_error_404_data_dan_route_berformat_seragam(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/tutorial/999999999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Data tidak ditemukan.');

        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/endpoint-yang-tidak-ada')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_error_422_berformat_seragam_dengan_pesan_indonesia(): void
    {
        $response = $this->withHeaders($this->apiHeaders())
            ->postJson('/api/tutorial', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['judul', 'kategori', 'tipe']);

        $this->assertIsString($response->json('message'));
        $this->assertNotSame('', $response->json('message'));
        $this->assertSame('Judul wajib diisi.', $response->json('errors.judul.0'));
    }

    public function test_error_405_berformat_seragam(): void
    {
        $this->getJson('/api/login')
            ->assertStatus(405)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Metode HTTP tidak diizinkan untuk endpoint ini.')
            ->assertHeader('Allow');
    }

    public function test_login_api_dibatasi_dan_error_429_berformat_seragam(): void
    {
        $kredensial = ['email' => 'tidak-ada@example.test', 'password' => 'salah-semua'];

        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/login', $kredensial)
                ->assertStatus(422)
                ->assertJsonPath('success', false);
        }

        $this->postJson('/api/login', $kredensial)
            ->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Terlalu banyak permintaan. Coba lagi beberapa saat lagi.')
            ->assertHeader('Retry-After');
    }

    public function test_error_500_disembunyikan_saat_produksi(): void
    {
        Route::get('/api/uji-error-server', fn () => throw new \RuntimeException('detail-rahasia-internal'));
        config(['app.debug' => false]);

        $this->getJson('/api/uji-error-server')
            ->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Terjadi kesalahan pada server.')
            ->assertDontSee('detail-rahasia-internal');
    }

    public function test_error_500_tetap_detail_saat_debug_aktif(): void
    {
        Route::get('/api/uji-error-server', fn () => throw new \RuntimeException('detail-rahasia-internal'));
        config(['app.debug' => true]);

        $this->getJson('/api/uji-error-server')
            ->assertStatus(500)
            ->assertJsonPath('message', 'detail-rahasia-internal');
    }

    public function test_respons_sukses_tetap_memakai_flag_success(): void
    {
        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/tutorial')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    // ------------------------------------------------------------------
    // Pagination konsisten
    // ------------------------------------------------------------------

    /**
     * @return array<string, string>
     */
    protected function endpointBerpagination(): array
    {
        return [
            'pelanggan' => '/api/pelanggan',
            'pesanan' => '/api/pesanan',
            'bahan' => '/api/bahan',
            'peralatan' => '/api/peralatan',
            'tutorial' => '/api/tutorial',
            'pengeluaran' => '/api/pengeluaran',
            'pendapatan' => '/api/keuangan/pendapatan',
        ];
    }

    public function test_semua_list_berpagination_memiliki_meta_lengkap(): void
    {
        foreach ($this->endpointBerpagination() as $nama => $uri) {
            $this->withHeaders($this->apiHeaders())
                ->getJson("{$uri}?per_page=3")
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('meta.per_page', 3)
                ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
        }
    }

    public function test_per_page_tidak_valid_ditolak_di_semua_list(): void
    {
        foreach ($this->endpointBerpagination() as $nama => $uri) {
            foreach (['0', '101', 'abc'] as $nilai) {
                $this->withHeaders($this->apiHeaders())
                    ->getJson("{$uri}?per_page={$nilai}")
                    ->assertStatus(422)
                    ->assertJsonPath('success', false)
                    ->assertJsonValidationErrors('per_page');
            }
        }
    }

    public function test_list_bahan_api_terpaginasi(): void
    {
        foreach (['A', 'B', 'C'] as $huruf) {
            Bahan::factory()->create(['nama_bahan' => self::PREFIX." Pag {$huruf}"]);
        }

        $halaman1 = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/bahan?'.http_build_query(['search' => self::PREFIX.' Pag', 'per_page' => 2]))
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);
        $this->assertCount(2, $halaman1->json('data'));

        $halaman2 = $this->withHeaders($this->apiHeaders())
            ->getJson('/api/bahan?'.http_build_query(['search' => self::PREFIX.' Pag', 'per_page' => 2, 'page' => 2]))
            ->assertOk();
        $this->assertCount(1, $halaman2->json('data'));
    }

    // ------------------------------------------------------------------
    // Terjemahan Indonesia
    // ------------------------------------------------------------------

    public function test_pesan_validasi_dan_pagination_tersedia_dalam_bahasa_indonesia(): void
    {
        app()->setLocale('id');

        $validator = Validator::make(['nama' => ''], ['nama' => 'required']);
        $this->assertTrue($validator->fails());
        $this->assertSame('Nama wajib diisi.', $validator->errors()->first('nama'));

        $validator = Validator::make(['jumlah' => 'abc'], ['jumlah' => 'numeric']);
        $this->assertTrue($validator->fails());
        $this->assertSame('Jumlah harus berupa angka.', $validator->errors()->first('jumlah'));

        $this->assertSame('&laquo; Sebelumnya', __('pagination.previous'));
        $this->assertSame('Berikutnya &raquo;', __('pagination.next'));
    }

    public function test_file_bahasa_indonesia_memiliki_semua_kunci_validasi_bawaan(): void
    {
        $berkasInggris = base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

        if (! file_exists($berkasInggris)) {
            $this->markTestSkipped('File validasi bawaan framework tidak ditemukan.');
        }

        $inggris = require $berkasInggris;
        $indonesia = require base_path('lang/id/validation.php');

        $kunciKurang = array_diff(array_keys($inggris), array_keys($indonesia));

        $this->assertSame([], array_values($kunciKurang), 'Kunci validasi belum diterjemahkan: '.implode(', ', $kunciKurang));
    }
}
