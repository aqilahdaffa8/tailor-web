<?php

namespace App\Services;

use App\Models\Tutorial;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Tutorial / knowledge base.
 * - artikel : teks (konten) + gambar sampul opsional (file di disk public)
 * - video   : tautan YouTube (disimpan di file_or_url, ditampilkan sebagai embed)
 * - pdf     : file PDF di disk public (file_or_url berisi path)
 */
class TutorialService
{
    /**
     * @return LengthAwarePaginator<Tutorial>
     */
    public function daftar(?string $kategori = null, ?string $tipe = null, ?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return Tutorial::query()
            ->when($kategori, fn ($query, string $kategori) => $query->where('kategori', $kategori))
            ->when($tipe, fn ($query, string $tipe) => $query->where('tipe', $tipe))
            ->when($search, fn ($query, string $search) => $query->where(
                fn ($kondisi) => $kondisi->where('judul', 'like', "%{$search}%")->orWhere('konten', 'like', "%{$search}%")
            ))
            ->orderBy('kategori')
            ->orderBy('judul')
            ->paginate($perPage);
    }

    /**
     * Daftar kategori yang sudah dipakai, urut abjad.
     *
     * @return Collection<int, string>
     */
    public function daftarKategori(): Collection
    {
        return Tutorial::query()->select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }

    /**
     * @param  array<string, mixed>  $data  Hasil validasi (boleh memuat UploadedFile pada 'file' / 'gambar').
     */
    public function buat(array $data): Tutorial
    {
        return Tutorial::create($this->atribut($data, null));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function ubah(Tutorial $tutorial, array $data): Tutorial
    {
        $tutorial->update($this->atribut($data, $tutorial));

        return $tutorial->fresh();
    }

    public function hapus(Tutorial $tutorial): void
    {
        $this->hapusFile($this->pathFile($tutorial));
        $tutorial->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function atribut(array $data, ?Tutorial $lama): array
    {
        $konten = isset($data['konten']) && trim((string) $data['konten']) !== '' ? $data['konten'] : null;

        return [
            'judul' => trim((string) $data['judul']),
            'kategori' => trim((string) $data['kategori']),
            'tipe' => $data['tipe'],
            'konten' => $konten,
            'file_or_url' => $this->tentukanFileOrUrl($data['tipe'], $data, $lama),
        ];
    }

    /**
     * Tentukan isi file_or_url sekaligus membuang file lama yang sudah tidak terpakai.
     *
     * @param  array<string, mixed>  $data
     */
    private function tentukanFileOrUrl(string $tipe, array $data, ?Tutorial $lama): ?string
    {
        $fileLama = $lama ? $this->pathFile($lama) : null;

        if ($tipe === 'video') {
            $this->hapusFile($fileLama);

            return trim((string) $data['video_url']);
        }

        $upload = $tipe === 'pdf' ? ($data['file'] ?? null) : ($data['gambar'] ?? null);

        if ($upload instanceof UploadedFile) {
            $this->hapusFile($fileLama);

            return $upload->store($tipe === 'pdf' ? 'tutorial/pdf' : 'tutorial/gambar', 'public');
        }

        // Tanpa upload baru: file lama dipertahankan hanya bila tipe tidak berubah (dan tidak diminta dihapus).
        if ($lama && $lama->tipe === $tipe) {
            if ($tipe === 'artikel' && ! empty($data['hapus_gambar'])) {
                $this->hapusFile($fileLama);

                return null;
            }

            return $lama->file_or_url;
        }

        $this->hapusFile($fileLama);

        return null;
    }

    /**
     * Path file fisik tutorial (hanya untuk tipe pdf/artikel), null bila tidak ada.
     */
    private function pathFile(Tutorial $tutorial): ?string
    {
        return in_array($tutorial->tipe, ['pdf', 'artikel'], true) ? $tutorial->file_or_url : null;
    }

    private function hapusFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
