<?php

namespace App\Services;

use App\Models\JenisPakaian;
use App\Models\KategoriPola;
use App\Models\PolaDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PolaService
{
    /**
     * Ambil semua kategori pola beserta jenis pakaian dan jumlah polanya.
     *
     * @return Collection<int, KategoriPola>
     */
    public function getAllKategori(string $search = ''): Collection
    {
        return KategoriPola::query()
            ->when($search, function ($query, string $search) {
                $query->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhereHas('jenisPakaian', function ($q) use ($search) {
                        $q->where('nama_jenis', 'like', "%{$search}%");
                    });
            })
            ->with(['jenisPakaian' => function ($query) {
                $query->withCount('polaDetail')->orderBy('nama_jenis');
            }])
            ->orderBy('nama_kategori')
            ->get();
    }

    /**
     * Ambil kategori dengan relasi jenis pakaian dan pola detail.
     */
    public function findKategori(int $id): KategoriPola
    {
        return KategoriPola::with(['jenisPakaian' => function ($q) {
            $q->withCount('polaDetail')->with('polaDetail');
        }])->findOrFail($id);
    }

    /**
     * Ambil jenis pakaian dengan kategori dan daftar pola detailnya.
     */
    public function findJenisPakaian(int $id): JenisPakaian
    {
        return JenisPakaian::with(['kategoriPola', 'polaDetail' => function ($q) {
            $q->orderBy('size');
        }])->findOrFail($id);
    }

    /**
     * Buat jenis pakaian baru di bawah kategori.
     *
     * @param  array<string, mixed>  $data
     */
    public function createJenisPakaian(array $data): JenisPakaian
    {
        return JenisPakaian::create([
            'kategori_pola_id' => $data['kategori_pola_id'],
            'nama_jenis' => $data['nama_jenis'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);
    }

    /**
     * Update jenis pakaian.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateJenisPakaian(JenisPakaian $jenisPakaian, array $data): JenisPakaian
    {
        $jenisPakaian->update([
            'nama_jenis' => $data['nama_jenis'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);

        return $jenisPakaian->fresh();
    }

    /**
     * Hapus jenis pakaian beserta file-file pola detail di dalamnya.
     */
    public function deleteJenisPakaian(JenisPakaian $jenisPakaian): void
    {
        foreach ($jenisPakaian->polaDetail as $pola) {
            $this->hapusFilePola($pola);
        }

        $jenisPakaian->delete();
    }

    /**
     * Ambil semua detail pola untuk jenis pakaian tertentu.
     *
     * @return Collection<int, PolaDetail>
     */
    public function getPolaDetailByJenis(int $jenisPakaianId): Collection
    {
        return PolaDetail::where('jenis_pakaian_id', $jenisPakaianId)
            ->orderBy('size')
            ->get();
    }

    /**
     * Ambil satu pola detail spesifik.
     */
    public function findPolaDetail(int $id): PolaDetail
    {
        return PolaDetail::with('jenisPakaian.kategoriPola')->findOrFail($id);
    }

    /**
     * Buat pola detail baru dengan upload foto/file pola jika ada.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPolaDetail(array $data, ?UploadedFile $foto = null, ?UploadedFile $file = null): PolaDetail
    {
        $fotoPath = null;
        if ($foto) {
            $fotoPath = $foto->store('pola/foto', 'public');
        }

        $filePath = null;
        if ($file) {
            $filePath = $file->store('pola/file', 'public');
        }

        return PolaDetail::create([
            'jenis_pakaian_id' => $data['jenis_pakaian_id'],
            'size' => $data['size'],
            'data_ukuran' => $data['data_ukuran'] ?? null,
            'foto_pola' => $fotoPath,
            'file_pola' => $filePath,
            'catatan' => $data['catatan'] ?? null,
        ]);
    }

    /**
     * Update pola detail dengan opsional penggantian foto/file pola.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePolaDetail(PolaDetail $polaDetail, array $data, ?UploadedFile $foto = null, ?UploadedFile $file = null): PolaDetail
    {
        $fotoPath = $polaDetail->foto_pola;
        if ($foto) {
            if ($polaDetail->foto_pola && Storage::disk('public')->exists($polaDetail->foto_pola)) {
                Storage::disk('public')->delete($polaDetail->foto_pola);
            }
            $fotoPath = $foto->store('pola/foto', 'public');
        } elseif (! empty($data['hapus_foto_pola'])) {
            if ($polaDetail->foto_pola && Storage::disk('public')->exists($polaDetail->foto_pola)) {
                Storage::disk('public')->delete($polaDetail->foto_pola);
            }
            $fotoPath = null;
        }

        $filePath = $polaDetail->file_pola;
        if ($file) {
            if ($polaDetail->file_pola && Storage::disk('public')->exists($polaDetail->file_pola)) {
                Storage::disk('public')->delete($polaDetail->file_pola);
            }
            $filePath = $file->store('pola/file', 'public');
        } elseif (! empty($data['hapus_file_pola'])) {
            if ($polaDetail->file_pola && Storage::disk('public')->exists($polaDetail->file_pola)) {
                Storage::disk('public')->delete($polaDetail->file_pola);
            }
            $filePath = null;
        }

        $polaDetail->update([
            'size' => $data['size'],
            'data_ukuran' => $data['data_ukuran'] ?? $polaDetail->data_ukuran,
            'foto_pola' => $fotoPath,
            'file_pola' => $filePath,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $polaDetail->fresh();
    }

    /**
     * Hapus foto pola secara instan.
     */
    public function deleteFotoPola(PolaDetail $polaDetail): void
    {
        if ($polaDetail->foto_pola && Storage::disk('public')->exists($polaDetail->foto_pola)) {
            Storage::disk('public')->delete($polaDetail->foto_pola);
        }
        $polaDetail->update(['foto_pola' => null]);
    }

    /**
     * Hapus file dokumen pola secara instan.
     */
    public function deleteFilePola(PolaDetail $polaDetail): void
    {
        if ($polaDetail->file_pola && Storage::disk('public')->exists($polaDetail->file_pola)) {
            Storage::disk('public')->delete($polaDetail->file_pola);
        }
        $polaDetail->update(['file_pola' => null]);
    }

    /**
     * Hapus pola detail beserta file fisiknya di disk public.
     */
    public function deletePolaDetail(PolaDetail $polaDetail): void
    {
        $this->hapusFilePola($polaDetail);
        $polaDetail->delete();
    }

    /**
     * Helper untuk menghapus file fisik foto & dokumen pola.
     */
    private function hapusFilePola(PolaDetail $polaDetail): void
    {
        if ($polaDetail->foto_pola && Storage::disk('public')->exists($polaDetail->foto_pola)) {
            Storage::disk('public')->delete($polaDetail->foto_pola);
        }

        if ($polaDetail->file_pola && Storage::disk('public')->exists($polaDetail->file_pola)) {
            Storage::disk('public')->delete($polaDetail->file_pola);
        }
    }
}
