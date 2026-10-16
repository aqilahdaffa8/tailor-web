<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\Peralatan;
use App\Models\RiwayatStok;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Ambil daftar semua bahan dengan opsi filter pencarian, kategori, dan stok menipis.
     *
     * @return Collection<int, Bahan>
     */
    public function getAllBahan(string $search = '', ?int $kategoriId = null, bool $hanyaMenipis = false): Collection
    {
        return $this->queryBahan($search, $kategoriId, $hanyaMenipis)->get();
    }

    /**
     * Versi paginated dari getAllBahan() (dipakai API).
     *
     * @return LengthAwarePaginator<Bahan>
     */
    public function paginateBahan(string $search = '', ?int $kategoriId = null, bool $hanyaMenipis = false, int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryBahan($search, $kategoriId, $hanyaMenipis)->paginate($perPage);
    }

    /**
     * @return Builder<Bahan>
     */
    private function queryBahan(string $search, ?int $kategoriId, bool $hanyaMenipis): Builder
    {
        return Bahan::query()
            ->with('kategoriBahan')
            ->when($search, function ($query, string $search) {
                $query->where('nama_bahan', 'like', "%{$search}%");
            })
            ->when($kategoriId, function ($query, int $kategoriId) {
                $query->where('kategori_bahan_id', $kategoriId);
            })
            ->when($hanyaMenipis, function ($query) {
                $query->whereColumn('stok', '<=', 'stok_minimum');
            })
            ->orderBy('nama_bahan');
    }

    /**
     * Ambil daftar bahan yang stoknya menipis (<= stok_minimum).
     *
     * @return Collection<int, Bahan>
     */
    public function getBahanMenipis(): Collection
    {
        return Bahan::query()
            ->with('kategoriBahan')
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok')
            ->get();
    }

    /**
     * Ambil satu bahan spesifik beserta riwayat mutasi stoknya.
     */
    public function findBahan(int $id): Bahan
    {
        return Bahan::with(['kategoriBahan', 'riwayatStok'])->findOrFail($id);
    }

    /**
     * Buat data bahan baru. Jika stok awal > 0, catat mutasi stok awal.
     *
     * @param  array<string, mixed>  $data
     */
    public function createBahan(array $data): Bahan
    {
        return DB::transaction(function () use ($data) {
            $bahan = Bahan::create([
                'kategori_bahan_id' => $data['kategori_bahan_id'],
                'nama_bahan' => $data['nama_bahan'],
                'satuan' => $data['satuan'],
                'stok' => $data['stok'],
                'stok_minimum' => $data['stok_minimum'],
            ]);

            if ((float) $data['stok'] > 0) {
                RiwayatStok::create([
                    'bahan_id' => $bahan->id,
                    'jenis' => 'masuk',
                    'jumlah' => $data['stok'],
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $data['stok'],
                    'keterangan' => 'Stok awal pendaftaran bahan',
                ]);
            }

            return $bahan;
        });
    }

    /**
     * Update data nama, kategori, satuan, dan stok minimum bahan.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateBahan(Bahan $bahan, array $data): Bahan
    {
        $bahan->update([
            'kategori_bahan_id' => $data['kategori_bahan_id'],
            'nama_bahan' => $data['nama_bahan'],
            'satuan' => $data['satuan'],
            'stok_minimum' => $data['stok_minimum'],
        ]);

        return $bahan->fresh();
    }

    /**
     * Hapus bahan inventaris.
     */
    public function deleteBahan(Bahan $bahan): void
    {
        $bahan->delete();
    }

    /**
     * Catat penambahan stok bahan masuk.
     */
    public function tambahStokMasuk(Bahan $bahan, float $jumlah, ?string $keterangan = null): RiwayatStok
    {
        return DB::transaction(function () use ($bahan, $jumlah, $keterangan) {
            $stokSebelum = (float) $bahan->stok;
            $stokSesudah = $stokSebelum + $jumlah;

            $bahan->update(['stok' => $stokSesudah]);

            return RiwayatStok::create([
                'bahan_id' => $bahan->id,
                'jenis' => 'masuk',
                'jumlah' => $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'keterangan' => $keterangan ?: 'Penambahan stok manual',
            ]);
        });
    }

    /**
     * Ambil riwayat mutasi stok untuk suatu bahan.
     *
     * @return Collection<int, RiwayatStok>
     */
    public function getRiwayatStok(Bahan $bahan): Collection
    {
        return $bahan->riwayatStok()->get();
    }

    /**
     * Ambil semua data peralatan penjahit.
     *
     * @return Collection<int, Peralatan>
     */
    public function getAllPeralatan(string $search = '', ?string $kondisi = null): Collection
    {
        return $this->queryPeralatan($search, $kondisi)->get();
    }

    /**
     * Versi paginated dari getAllPeralatan() (dipakai API).
     *
     * @return LengthAwarePaginator<Peralatan>
     */
    public function paginatePeralatan(string $search = '', ?string $kondisi = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryPeralatan($search, $kondisi)->paginate($perPage);
    }

    /**
     * @return Builder<Peralatan>
     */
    private function queryPeralatan(string $search, ?string $kondisi): Builder
    {
        return Peralatan::query()
            ->when($search, function ($query, string $search) {
                $query->where('nama_alat', 'like', "%{$search}%");
            })
            ->when($kondisi, function ($query, string $kondisi) {
                $query->where('kondisi', $kondisi);
            })
            ->orderBy('nama_alat');
    }

    /**
     * Ambil satu data peralatan spesifik.
     */
    public function findPeralatan(int $id): Peralatan
    {
        return Peralatan::findOrFail($id);
    }

    /**
     * Catat peralatan baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPeralatan(array $data): Peralatan
    {
        return Peralatan::create([
            'nama_alat' => $data['nama_alat'],
            'jumlah' => $data['jumlah'],
            'kondisi' => $data['kondisi'],
            'tanggal_beli' => $data['tanggal_beli'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);
    }

    /**
     * Update data peralatan.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePeralatan(Peralatan $peralatan, array $data): Peralatan
    {
        $peralatan->update([
            'nama_alat' => $data['nama_alat'],
            'jumlah' => $data['jumlah'],
            'kondisi' => $data['kondisi'],
            'tanggal_beli' => $data['tanggal_beli'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $peralatan->fresh();
    }

    /**
     * Hapus peralatan.
     */
    public function deletePeralatan(Peralatan $peralatan): void
    {
        $peralatan->delete();
    }
}
