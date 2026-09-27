<?php

namespace App\Services;

use App\Models\Pelanggan;
use Illuminate\Pagination\LengthAwarePaginator;

class PelangganService
{
    /**
     * Ambil daftar pelanggan dengan pencarian & pagination.
     *
     * @return LengthAwarePaginator<Pelanggan>
     */
    public function getAll(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return Pelanggan::query()
            ->when($search, function ($query, string $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            })
            ->withCount('pesanan')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Buat pelanggan baru.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Pelanggan
    {
        return Pelanggan::create([
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
            'alamat' => $data['alamat'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);
    }

    /**
     * Ambil detail pelanggan beserta riwayat pesanan.
     */
    public function findWithRiwayat(int $id): Pelanggan
    {
        return Pelanggan::with([
            'pesanan' => fn ($q) => $q->withCount('itemPesanan')->orderByDesc('created_at'),
            'pengukuran' => fn ($q) => $q->orderByDesc('created_at'),
        ])->findOrFail($id);
    }

    /**
     * Update data pelanggan.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Pelanggan $pelanggan, array $data): Pelanggan
    {
        $pelanggan->update([
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
            'alamat' => $data['alamat'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $pelanggan->fresh();
    }

    /**
     * Hapus pelanggan (hanya jika tidak punya pesanan aktif).
     */
    public function delete(Pelanggan $pelanggan): void
    {
        $pelanggan->delete();
    }
}
