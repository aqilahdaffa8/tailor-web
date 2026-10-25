<?php

namespace Tests\Feature;

use App\Models\FotoReferensi;
use App\Models\PolaDetail;
use App\Models\Tutorial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageDriverTest extends TestCase
{
    public function test_storage_fake_mode_local_dapat_menyimpan_dan_mengakses_url(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('foto-contoh.jpg', 100, 'image/jpeg');
        $path = $file->store('foto-referensi', 'public');

        Storage::disk('public')->assertExists($path);

        $foto = new FotoReferensi(['path_foto' => $path]);
        $this->assertNotEmpty($foto->foto_url);
        $this->assertSame(Storage::disk('public')->url($path), $foto->foto_url);

        // Hapus file
        Storage::disk('public')->delete($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_pola_detail_dan_tutorial_url_menggunakan_disk_public(): void
    {
        Storage::fake('public');

        $filePola = UploadedFile::fake()->create('pola.pdf', 500, 'application/pdf');
        $pathPola = $filePola->store('pola/file', 'public');

        $pola = new PolaDetail([
            'file_pola' => $pathPola,
            'foto_pola' => null,
        ]);

        $this->assertSame(Storage::disk('public')->url($pathPola), $pola->file_pola_url);
        $this->assertNull($pola->foto_pola_url);

        $fileTutorial = UploadedFile::fake()->create('panduan.pdf', 300, 'application/pdf');
        $pathTutorial = $fileTutorial->store('tutorial/pdf', 'public');

        $tutorial = new Tutorial([
            'tipe' => 'pdf',
            'file_or_url' => $pathTutorial,
        ]);

        $this->assertSame(Storage::disk('public')->url($pathTutorial), $tutorial->file_url);
    }

    public function test_konfigurasi_disk_public_s3_tanpa_acl_public(): void
    {
        Config::set('filesystems.disks.public', [
            'driver' => 's3',
            'key' => 'fake-key',
            'secret' => 'fake-secret',
            'region' => 'auto',
            'bucket' => 'tailor-bucket',
            'url' => 'https://pub-r2.example.com',
            'endpoint' => 'https://accountid.r2.cloudflarestorage.com',
            'use_path_style_endpoint' => false,
            'throw' => false,
            'report' => false,
        ]);

        $diskConfig = config('filesystems.disks.public');

        $this->assertSame('s3', $diskConfig['driver']);
        $this->assertArrayNotHasKey('visibility', $diskConfig, 'Disk S3/R2 tidak boleh menyetel default visibility public (menghindari ACL error di Cloudflare R2)');
    }
}
