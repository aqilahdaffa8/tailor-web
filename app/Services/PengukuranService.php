<?php

namespace App\Services;

use App\Models\Pengukuran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PengukuranService
{
    /**
     * Ambil daftar semua pengukuran dengan pencarian pelanggan dan pagination.
     *
     * @return LengthAwarePaginator<Pengukuran>
     */
    public function getAll(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return Pengukuran::query()
            ->with('pelanggan')
            ->when($search, function ($query, string $search) {
                $query->whereHas('pelanggan', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('tanggal_ukur')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ambil riwayat semua pengukuran untuk satu pelanggan tertentu.
     *
     * @return Collection<int, Pengukuran>
     */
    public function getByPelanggan(int $pelangganId): Collection
    {
        return Pengukuran::where('pelanggan_id', $pelangganId)
            ->orderByDesc('tanggal_ukur')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Ambil data pengukuran tubuh terakhir dari seorang pelanggan.
     */
    public function getLatestByPelanggan(int $pelangganId): ?Pengukuran
    {
        return Pengukuran::where('pelanggan_id', $pelangganId)
            ->orderByDesc('tanggal_ukur')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Ambil detail data pengukuran berdasarkan ID.
     */
    public function find(int $id): Pengukuran
    {
        return Pengukuran::with(['pelanggan', 'itemPesanan'])->findOrFail($id);
    }

    /**
     * Catat pengukuran baru pelanggan.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Pengukuran
    {
        // Bersihkan nilai null / kosong dari array data_ukuran
        $dataUkuran = array_filter($data['data_ukuran'] ?? [], fn ($val) => $val !== null && $val !== '');

        return Pengukuran::create([
            'pelanggan_id' => $data['pelanggan_id'],
            'tanggal_ukur' => $data['tanggal_ukur'],
            'data_ukuran' => $dataUkuran,
            'catatan' => $data['catatan'] ?? null,
        ]);
    }

    /**
     * Update data pengukuran.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Pengukuran $pengukuran, array $data): Pengukuran
    {
        $dataUkuran = array_filter($data['data_ukuran'] ?? [], fn ($val) => $val !== null && $val !== '');

        $pengukuran->update([
            'tanggal_ukur' => $data['tanggal_ukur'],
            'data_ukuran' => $dataUkuran,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $pengukuran->fresh();
    }

    /**
     * Hapus data pengukuran.
     */
    public function delete(Pengukuran $pengukuran): void
    {
        $pengukuran->delete();
    }
}
