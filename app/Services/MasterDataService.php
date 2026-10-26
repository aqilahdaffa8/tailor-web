<?php

namespace App\Services;

use App\Models\JenisJasa;
use App\Models\KategoriBahan;
use App\Models\KategoriPengeluaran;
use App\Models\KategoriPola;
use App\Models\KategoriTutorial;
use App\Models\Pengaturan;
use App\Models\TemplateTahapan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MasterDataService
{
    /**
     * Ambil seluruh jenis jasa beserta template tahapannya
     *
     * @return Collection<int, JenisJasa>
     */
    public function getAllJenisJasa(bool $activeOnly = false): Collection
    {
        $query = JenisJasa::with('templateTahapan');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('nama_jasa')->get();
    }

    /**
     * Buat jenis jasa baru beserta tahapan kerjanya
     */
    public function createJenisJasa(array $data): JenisJasa
    {
        return DB::transaction(function () use ($data) {
            $jasa = JenisJasa::create([
                'nama_jasa' => $data['nama_jasa'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'harga_default' => $data['harga_default'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $tahapan = $data['tahapan'] ?? ['Menunggu Dikerjakan', 'Pengerjaan', 'Selesai', 'Diambil Pelanggan'];
            foreach ($tahapan as $index => $namaTahapan) {
                if (filled($namaTahapan)) {
                    TemplateTahapan::create([
                        'jenis_jasa_id' => $jasa->id,
                        'nama_tahapan' => trim($namaTahapan),
                        'urutan' => $index + 1,
                    ]);
                }
            }

            return $jasa->load('templateTahapan');
        });
    }

    /**
     * Update jenis jasa dan sinkronisasi tahapan kerjanya
     */
    public function updateJenisJasa(JenisJasa $jasa, array $data): JenisJasa
    {
        return DB::transaction(function () use ($jasa, $data) {
            $jasa->update([
                'nama_jasa' => $data['nama_jasa'] ?? $jasa->nama_jasa,
                'deskripsi' => array_key_exists('deskripsi', $data) ? $data['deskripsi'] : $jasa->deskripsi,
                'harga_default' => $data['harga_default'] ?? $jasa->harga_default,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $jasa->is_active,
            ]);

            if (isset($data['tahapan']) && is_array($data['tahapan'])) {
                $jasa->templateTahapan()->delete();
                foreach ($data['tahapan'] as $index => $namaTahapan) {
                    if (filled($namaTahapan)) {
                        TemplateTahapan::create([
                            'jenis_jasa_id' => $jasa->id,
                            'nama_tahapan' => trim($namaTahapan),
                            'urutan' => $index + 1,
                        ]);
                    }
                }
            }

            return $jasa->load('templateTahapan');
        });
    }

    /**
     * Hapus jenis jasa
     */
    public function deleteJenisJasa(JenisJasa $jasa): bool
    {
        return $jasa->delete();
    }

    // --- Kategori Pola ---
    public function getAllKategoriPola(): Collection
    {
        return KategoriPola::withCount('jenisPakaian')->orderBy('nama_kategori')->get();
    }

    public function createKategoriPola(array $data): KategoriPola
    {
        return KategoriPola::create($data);
    }

    public function updateKategoriPola(int $id, array $data): bool
    {
        $kategori = KategoriPola::findOrFail($id);

        return $kategori->update($data);
    }

    public function deleteKategoriPola(int $id): bool
    {
        return (bool) KategoriPola::destroy($id);
    }

    // --- Kategori Bahan ---
    public function getAllKategoriBahan(): Collection
    {
        return KategoriBahan::withCount('bahan')->orderBy('nama_kategori')->get();
    }

    public function createKategoriBahan(array $data): KategoriBahan
    {
        return KategoriBahan::create($data);
    }

    public function updateKategoriBahan(int $id, array $data): bool
    {
        $kategori = KategoriBahan::findOrFail($id);

        return $kategori->update($data);
    }

    public function deleteKategoriBahan(int $id): bool
    {
        return (bool) KategoriBahan::destroy($id);
    }

    // --- Kategori Pengeluaran ---
    public function getAllKategoriPengeluaran(): Collection
    {
        return KategoriPengeluaran::withCount('pengeluaran')->orderBy('nama_kategori')->get();
    }

    public function createKategoriPengeluaran(array $data): KategoriPengeluaran
    {
        return KategoriPengeluaran::create($data);
    }

    public function updateKategoriPengeluaran(int $id, array $data): bool
    {
        $kategori = KategoriPengeluaran::findOrFail($id);

        return $kategori->update($data);
    }

    public function deleteKategoriPengeluaran(int $id): bool
    {
        return (bool) KategoriPengeluaran::destroy($id);
    }

    // --- Kategori Tutorial ---
    public function getAllKategoriTutorial(): Collection
    {
        return KategoriTutorial::withCount('tutorial')->orderBy('nama_kategori')->get();
    }

    public function createKategoriTutorial(array $data): KategoriTutorial
    {
        return KategoriTutorial::create($data);
    }

    public function updateKategoriTutorial(int $id, array $data): bool
    {
        $kategori = KategoriTutorial::findOrFail($id);

        return $kategori->update($data);
    }

    public function deleteKategoriTutorial(int $id): bool
    {
        return (bool) KategoriTutorial::destroy($id);
    }

    // --- Pengaturan Profil Usaha & Invoice ---
    public function getProfilUsaha(): array
    {
        $profil = Pengaturan::get('profil_usaha', [
            'nama_usaha' => 'Tailor Mandiri',
            'pemilik' => 'Owner',
            'no_hp' => '-',
            'alamat' => '-',
        ]);

        $invoice = Pengaturan::get('pengaturan_invoice', [
            'prefix' => 'ORD',
            'catatan_kaki' => 'Terima kasih atas kepercayaan Anda.',
        ]);

        return array_merge($profil, [
            'prefix_invoice' => $invoice['prefix'] ?? 'ORD',
            'catatan_kaki_invoice' => $invoice['catatan_kaki'] ?? '',
        ]);
    }

    public function updateProfilUsaha(array $data): void
    {
        Pengaturan::set('profil_usaha', [
            'nama_usaha' => $data['nama_usaha'],
            'pemilik' => $data['pemilik'],
            'no_hp' => $data['no_hp'],
            'alamat' => $data['alamat'] ?? '',
        ]);

        Pengaturan::set('pengaturan_invoice', [
            'prefix' => $data['prefix_invoice'] ?? 'ORD',
            'catatan_kaki' => $data['catatan_kaki_invoice'] ?? '',
        ]);
    }
}
