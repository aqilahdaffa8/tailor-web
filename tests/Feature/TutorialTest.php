<?php

namespace Tests\Feature;

use App\Models\Tutorial;
use App\Services\TutorialService;
use Database\Seeders\TutorialSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Test modul Tutorial. Data test ditandai awalan PREFIX pada judul dan dibersihkan di tearDown().
 * File yang diunggah memakai Storage::fake('public') sehingga tidak menyentuh disk asli.
 */
class TutorialTest extends PesananTestCase
{
    private const KATEGORI = self::PREFIX.' Kategori Uji';

    protected function bersihkanDataTest(): void
    {
        parent::bersihkanDataTest();

        Tutorial::where('judul', 'like', self::PREFIX.'%')->delete();
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function dataArtikel(array $override = []): array
    {
        return array_merge([
            'judul' => self::PREFIX.' Artikel',
            'kategori' => self::KATEGORI,
            'tipe' => 'artikel',
            'konten' => "Baris satu\nBaris dua",
        ], $override);
    }

    protected function buatTutorial(string $nama, array $atribut = []): Tutorial
    {
        return Tutorial::factory()->create(array_merge([
            'judul' => self::PREFIX.' '.$nama,
            'kategori' => self::KATEGORI,
        ], $atribut));
    }

    protected function pdfFake(string $nama = 'panduan.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($nama, 100, 'application/pdf');
    }

    // ------------------------------------------------------------------
    // Akses & model
    // ------------------------------------------------------------------

    public function test_route_tutorial_memerlukan_login(): void
    {
        $this->get('/tutorial')->assertRedirect('/login');
        $this->get('/tutorial/create')->assertRedirect('/login');
        $this->post('/tutorial', [])->assertRedirect('/login');
        $this->get('/tutorial/1')->assertRedirect('/login');
        $this->put('/tutorial/1', [])->assertRedirect('/login');
        $this->delete('/tutorial/1')->assertRedirect('/login');
        $this->get('/tutorial/1/unduh')->assertRedirect('/login');

        $this->getJson('/api/tutorial')->assertStatus(401);
        $this->postJson('/api/tutorial', [])->assertStatus(401);
        $this->getJson('/api/tutorial/1')->assertStatus(401);
        $this->putJson('/api/tutorial/1', [])->assertStatus(401);
        $this->deleteJson('/api/tutorial/1')->assertStatus(401);
    }

    public function test_id_youtube_dikenali_dari_berbagai_format_tautan(): void
    {
        $id = 'abcdefghijk';

        foreach ([
            "https://www.youtube.com/watch?v={$id}",
            "https://youtube.com/watch?v={$id}&t=10s",
            "https://m.youtube.com/watch?v={$id}",
            "https://youtu.be/{$id}",
            "https://youtu.be/{$id}?si=xyz",
            "https://www.youtube.com/embed/{$id}",
            "https://www.youtube.com/shorts/{$id}",
            "https://www.youtube-nocookie.com/embed/{$id}",
        ] as $url) {
            $this->assertSame($id, Tutorial::idYoutube($url), $url);
        }

        foreach ([
            null,
            '',
            'bukan url',
            "https://vimeo.com/{$id}",
            "https://youtube.evil.com/watch?v={$id}",
            "https://evil.com/youtube.com/watch?v={$id}",
            'https://www.youtube.com/watch?v=pendek',
            "javascript:alert('{$id}')",
            "ftp://www.youtube.com/watch?v={$id}",
        ] as $url) {
            $this->assertNull(Tutorial::idYoutube($url), (string) $url);
        }
    }

    public function test_accessor_embed_url_dan_file_url(): void
    {
        $video = Tutorial::factory()->video('https://youtu.be/abcdefghijk')->make();
        $pdf = Tutorial::factory()->pdf('tutorial/pdf/a.pdf')->make();
        $artikel = Tutorial::factory()->make();

        $this->assertSame('https://www.youtube-nocookie.com/embed/abcdefghijk', $video->embed_url);
        $this->assertNull($video->file_url);
        $this->assertNull($pdf->embed_url);
        $this->assertStringEndsWith('tutorial/pdf/a.pdf', $pdf->file_url);
        $this->assertNull($artikel->embed_url);
        $this->assertNull($artikel->file_url);
    }

    // ------------------------------------------------------------------
    // Web: daftar & detail
    // ------------------------------------------------------------------

    public function test_web_index_menampilkan_dan_memfilter_tutorial(): void
    {
        $this->buatTutorial('Kain Katun', ['tipe' => 'artikel', 'konten' => 'Memilih kain katun']);
        $this->buatTutorial('Video Jahit', ['tipe' => 'video', 'file_or_url' => 'https://youtu.be/abcdefghijk']);
        $lain = $this->buatTutorial('Manual Mesin', ['kategori' => self::PREFIX.' Kategori Lain', 'tipe' => 'pdf', 'file_or_url' => 'tutorial/pdf/x.pdf']);

        $this->actingAs($this->user)
            ->get('/tutorial?'.http_build_query(['kategori' => self::KATEGORI]))
            ->assertOk()
            ->assertSee(self::PREFIX.' Kain Katun')
            ->assertSee(self::PREFIX.' Video Jahit')
            ->assertDontSee($lain->judul);

        $this->actingAs($this->user)
            ->get('/tutorial?'.http_build_query(['kategori' => self::KATEGORI, 'tipe' => 'video']))
            ->assertOk()
            ->assertSee(self::PREFIX.' Video Jahit')
            ->assertDontSee(self::PREFIX.' Kain Katun');

        $this->actingAs($this->user)
            ->get('/tutorial?'.http_build_query(['search' => 'Memilih kain katun']))
            ->assertOk()
            ->assertSee(self::PREFIX.' Kain Katun')
            ->assertDontSee(self::PREFIX.' Video Jahit');
    }

    public function test_web_filter_tipe_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->user)
            ->get('/tutorial?tipe=lainnya')
            ->assertSessionHasErrors('tipe');
    }

    public function test_web_detail_artikel_menampilkan_teks_dengan_aman(): void
    {
        $tutorial = $this->buatTutorial('Aman', ['konten' => "<script>alert(1)</script>\nBaris dua"]);

        $this->actingAs($this->user)
            ->get("/tutorial/{$tutorial->id}")
            ->assertOk()
            ->assertSee(self::PREFIX.' Aman')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_web_detail_video_menampilkan_embed(): void
    {
        $tutorial = $this->buatTutorial('Video', ['tipe' => 'video', 'file_or_url' => 'https://www.youtube.com/watch?v=abcdefghijk']);

        $this->actingAs($this->user)
            ->get("/tutorial/{$tutorial->id}")
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk', false);
    }

    public function test_web_halaman_create_dan_edit_tampil(): void
    {
        $tutorial = $this->buatTutorial('Edit');

        $this->actingAs($this->user)->get('/tutorial/create')->assertOk();
        $this->actingAs($this->user)->get("/tutorial/{$tutorial->id}/edit")->assertOk()->assertSee(self::PREFIX.' Edit');
    }

    // ------------------------------------------------------------------
    // Web: simpan
    // ------------------------------------------------------------------

    public function test_web_simpan_artikel_dengan_gambar_sampul(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->post('/tutorial', $this->dataArtikel(['gambar' => UploadedFile::fake()->image('sampul.jpg', 400, 300)]))
            ->assertSessionHasNoErrors();

        $tutorial = Tutorial::where('judul', self::PREFIX.' Artikel')->firstOrFail();
        $this->assertSame('artikel', $tutorial->tipe);
        $this->assertSame("Baris satu\nBaris dua", $tutorial->konten);
        $this->assertNotNull($tutorial->file_or_url);
        Storage::disk('public')->assertExists($tutorial->file_or_url);
    }

    public function test_web_simpan_video_youtube(): void
    {
        $this->actingAs($this->user)
            ->post('/tutorial', [
                'judul' => self::PREFIX.' Video',
                'kategori' => self::KATEGORI,
                'tipe' => 'video',
                'video_url' => 'https://youtu.be/abcdefghijk',
            ])
            ->assertSessionHasNoErrors();

        $tutorial = Tutorial::where('judul', self::PREFIX.' Video')->firstOrFail();
        $this->assertSame('https://youtu.be/abcdefghijk', $tutorial->file_or_url);
        $this->assertNull($tutorial->konten);
    }

    public function test_web_simpan_pdf_dan_unduh(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->post('/tutorial', [
                'judul' => self::PREFIX.' Panduan PDF',
                'kategori' => self::KATEGORI,
                'tipe' => 'pdf',
                'file' => $this->pdfFake(),
            ])
            ->assertSessionHasNoErrors();

        $tutorial = Tutorial::where('judul', self::PREFIX.' Panduan PDF')->firstOrFail();
        Storage::disk('public')->assertExists($tutorial->file_or_url);

        $this->actingAs($this->user)
            ->get("/tutorial/{$tutorial->id}/unduh")
            ->assertOk()
            ->assertDownload();
    }

    public function test_web_unduh_gagal_untuk_tipe_bukan_pdf_atau_file_hilang(): void
    {
        Storage::fake('public');

        $artikel = $this->buatTutorial('Bukan PDF');
        $hilang = $this->buatTutorial('PDF Hilang', ['tipe' => 'pdf', 'file_or_url' => 'tutorial/pdf/tidak-ada.pdf']);

        $this->actingAs($this->user)->get("/tutorial/{$artikel->id}/unduh")->assertNotFound();
        $this->actingAs($this->user)->get("/tutorial/{$hilang->id}/unduh")->assertNotFound();
    }

    public function test_web_validasi_simpan(): void
    {
        Storage::fake('public');

        // Artikel tanpa isi, kategori terlalu panjang
        $this->actingAs($this->user)
            ->post('/tutorial', ['judul' => self::PREFIX.' X', 'kategori' => str_repeat('k', 51), 'tipe' => 'artikel'])
            ->assertSessionHasErrors(['kategori', 'konten']);

        // Tipe tidak dikenal
        $this->actingAs($this->user)
            ->post('/tutorial', ['judul' => self::PREFIX.' X', 'kategori' => 'K', 'tipe' => 'audio'])
            ->assertSessionHasErrors('tipe');

        // Video: wajib ada tautan, dan harus YouTube
        $this->actingAs($this->user)
            ->post('/tutorial', ['judul' => self::PREFIX.' X', 'kategori' => 'K', 'tipe' => 'video'])
            ->assertSessionHasErrors('video_url');
        $this->actingAs($this->user)
            ->post('/tutorial', ['judul' => self::PREFIX.' X', 'kategori' => 'K', 'tipe' => 'video', 'video_url' => 'https://vimeo.com/12345'])
            ->assertSessionHasErrors('video_url');

        // PDF: wajib ada file, dan harus PDF
        $this->actingAs($this->user)
            ->post('/tutorial', ['judul' => self::PREFIX.' X', 'kategori' => 'K', 'tipe' => 'pdf'])
            ->assertSessionHasErrors('file');
        $this->actingAs($this->user)
            ->post('/tutorial', [
                'judul' => self::PREFIX.' X', 'kategori' => 'K', 'tipe' => 'pdf',
                'file' => UploadedFile::fake()->image('bukan-pdf.png'),
            ])
            ->assertSessionHasErrors('file');

        // Gambar sampul harus gambar
        $this->actingAs($this->user)
            ->post('/tutorial', $this->dataArtikel(['gambar' => $this->pdfFake('sampul.pdf')]))
            ->assertSessionHasErrors('gambar');

        $this->assertSame(0, Tutorial::where('judul', self::PREFIX.' X')->count());
    }

    // ------------------------------------------------------------------
    // Web: ubah & hapus
    // ------------------------------------------------------------------

    public function test_web_ubah_data_dasar_tanpa_mengubah_file(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('lama.jpg')->store('tutorial/gambar', 'public');
        $tutorial = $this->buatTutorial('Ubah', ['file_or_url' => $path]);

        $this->actingAs($this->user)
            ->put("/tutorial/{$tutorial->id}", $this->dataArtikel(['judul' => self::PREFIX.' Ubah Baru', 'konten' => 'Isi baru']))
            ->assertSessionHasNoErrors();

        $tutorial->refresh();
        $this->assertSame(self::PREFIX.' Ubah Baru', $tutorial->judul);
        $this->assertSame('Isi baru', $tutorial->konten);
        $this->assertSame($path, $tutorial->file_or_url);
        Storage::disk('public')->assertExists($path);
    }

    public function test_web_ganti_file_pdf_menghapus_file_lama(): void
    {
        Storage::fake('public');
        $lama = $this->pdfFake('lama.pdf')->store('tutorial/pdf', 'public');
        $tutorial = $this->buatTutorial('Ganti PDF', ['tipe' => 'pdf', 'konten' => null, 'file_or_url' => $lama]);

        $this->actingAs($this->user)
            ->put("/tutorial/{$tutorial->id}", [
                'judul' => $tutorial->judul,
                'kategori' => self::KATEGORI,
                'tipe' => 'pdf',
                'file' => $this->pdfFake('baru.pdf'),
            ])
            ->assertSessionHasNoErrors();

        $tutorial->refresh();
        $this->assertNotSame($lama, $tutorial->file_or_url);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($tutorial->file_or_url);
    }

    public function test_web_ubah_pdf_tanpa_file_baru_mempertahankan_file(): void
    {
        Storage::fake('public');
        $path = $this->pdfFake('tetap.pdf')->store('tutorial/pdf', 'public');
        $tutorial = $this->buatTutorial('Tetap PDF', ['tipe' => 'pdf', 'konten' => null, 'file_or_url' => $path]);

        $this->actingAs($this->user)
            ->put("/tutorial/{$tutorial->id}", [
                'judul' => self::PREFIX.' Tetap PDF Baru',
                'kategori' => self::KATEGORI,
                'tipe' => 'pdf',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($path, $tutorial->fresh()->file_or_url);
        Storage::disk('public')->assertExists($path);
    }

    public function test_web_ubah_tipe_dari_pdf_ke_video_menghapus_file(): void
    {
        Storage::fake('public');
        $path = $this->pdfFake('lama.pdf')->store('tutorial/pdf', 'public');
        $tutorial = $this->buatTutorial('PDF ke Video', ['tipe' => 'pdf', 'konten' => null, 'file_or_url' => $path]);

        $this->actingAs($this->user)
            ->put("/tutorial/{$tutorial->id}", [
                'judul' => $tutorial->judul,
                'kategori' => self::KATEGORI,
                'tipe' => 'video',
                'video_url' => 'https://youtu.be/abcdefghijk',
            ])
            ->assertSessionHasNoErrors();

        $tutorial->refresh();
        $this->assertSame('video', $tutorial->tipe);
        $this->assertSame('https://youtu.be/abcdefghijk', $tutorial->file_or_url);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_web_ubah_tipe_dari_video_ke_pdf_wajib_mengunggah_file(): void
    {
        Storage::fake('public');
        $tutorial = $this->buatTutorial('Video ke PDF', ['tipe' => 'video', 'file_or_url' => 'https://youtu.be/abcdefghijk']);

        $this->actingAs($this->user)
            ->put("/tutorial/{$tutorial->id}", [
                'judul' => $tutorial->judul,
                'kategori' => self::KATEGORI,
                'tipe' => 'pdf',
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame('video', $tutorial->fresh()->tipe);
    }

    public function test_web_hapus_gambar_sampul_artikel(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('sampul.jpg')->store('tutorial/gambar', 'public');
        $tutorial = $this->buatTutorial('Hapus Gambar', ['file_or_url' => $path]);

        $this->actingAs($this->user)
            ->put("/tutorial/{$tutorial->id}", $this->dataArtikel(['judul' => $tutorial->judul, 'hapus_gambar' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertNull($tutorial->fresh()->file_or_url);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_web_hapus_tutorial_menghapus_data_dan_file(): void
    {
        Storage::fake('public');
        $path = $this->pdfFake('hapus.pdf')->store('tutorial/pdf', 'public');
        $tutorial = $this->buatTutorial('Hapus', ['tipe' => 'pdf', 'konten' => null, 'file_or_url' => $path]);

        $this->actingAs($this->user)
            ->delete("/tutorial/{$tutorial->id}")
            ->assertRedirect('/tutorial')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tutorial', ['id' => $tutorial->id]);
        Storage::disk('public')->assertMissing($path);
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    public function test_api_daftar_tutorial_dengan_filter_dan_meta(): void
    {
        $this->buatTutorial('API Satu');
        $this->buatTutorial('API Dua', ['tipe' => 'video', 'file_or_url' => 'https://youtu.be/abcdefghijk']);
        $this->buatTutorial('API Lain', ['kategori' => self::PREFIX.' Kategori Lain']);

        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/tutorial?'.http_build_query(['kategori' => self::KATEGORI]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['id', 'judul', 'kategori', 'tipe', 'konten', 'file_or_url', 'embed_url', 'file_url']], 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);

        $this->withHeaders($this->apiHeaders())
            ->getJson('/api/tutorial?'.http_build_query(['kategori' => self::KATEGORI, 'tipe' => 'video']))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.embed_url', 'https://www.youtube-nocookie.com/embed/abcdefghijk');
    }

    public function test_api_crud_tutorial(): void
    {
        $headers = $this->apiHeaders();

        $id = $this->withHeaders($headers)
            ->postJson('/api/tutorial', $this->dataArtikel())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.judul', self::PREFIX.' Artikel')
            ->json('data.id');

        $this->withHeaders($headers)
            ->getJson("/api/tutorial/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        $this->withHeaders($headers)
            ->putJson("/api/tutorial/{$id}", $this->dataArtikel(['judul' => self::PREFIX.' Artikel Revisi']))
            ->assertOk()
            ->assertJsonPath('data.judul', self::PREFIX.' Artikel Revisi');

        $this->withHeaders($headers)
            ->deleteJson("/api/tutorial/{$id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tutorial', ['id' => $id]);
    }

    public function test_api_simpan_pdf_dengan_upload_file(): void
    {
        Storage::fake('public');

        $response = $this->withHeaders($this->apiHeaders() + ['Accept' => 'application/json'])
            ->post('/api/tutorial', [
                'judul' => self::PREFIX.' PDF API',
                'kategori' => self::KATEGORI,
                'tipe' => 'pdf',
                'file' => $this->pdfFake(),
            ])
            ->assertCreated();

        $path = Tutorial::findOrFail($response->json('data.id'))->file_or_url;
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull($response->json('data.file_url'));
    }

    public function test_api_validasi_dan_tidak_ditemukan(): void
    {
        $headers = $this->apiHeaders();

        $this->withHeaders($headers)
            ->postJson('/api/tutorial', ['tipe' => 'video'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['judul', 'kategori', 'video_url']);

        $this->withHeaders($headers)->getJson('/api/tutorial/999999999')->assertNotFound();
        $this->withHeaders($headers)->putJson('/api/tutorial/999999999', $this->dataArtikel())->assertNotFound();
        $this->withHeaders($headers)->deleteJson('/api/tutorial/999999999')->assertNotFound();
        $this->withHeaders($headers)->getJson('/api/tutorial?per_page=500')->assertStatus(422)->assertJsonValidationErrors('per_page');
    }

    // ------------------------------------------------------------------
    // Service & seeder
    // ------------------------------------------------------------------

    public function test_daftar_kategori_unik_dan_terurut(): void
    {
        $this->buatTutorial('K1', ['kategori' => self::PREFIX.' Zeta']);
        $this->buatTutorial('K2', ['kategori' => self::PREFIX.' Alfa']);
        $this->buatTutorial('K3', ['kategori' => self::PREFIX.' Alfa']);

        $kategori = app(TutorialService::class)->daftarKategori()
            ->filter(fn (string $k) => str_starts_with($k, self::PREFIX))
            ->values()
            ->all();

        $this->assertSame([self::PREFIX.' Alfa', self::PREFIX.' Zeta'], $kategori);
    }

    public function test_seeder_tutorial_idempotent(): void
    {
        $seeder = new TutorialSeeder;
        $seeder->run();
        $jumlah = Tutorial::count();
        $seeder->run();

        $this->assertSame($jumlah, Tutorial::count());
        $this->assertDatabaseHas('tutorial', ['judul' => 'Cara Mengambil Ukuran Badan', 'tipe' => 'artikel']);
    }
}
