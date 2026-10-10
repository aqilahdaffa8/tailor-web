<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private const LIMIT_MAKSIMAL = 100;

    public function __construct(
        private readonly PesananService $pesananService,
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * Seluruh metric ringkasan dashboard.
     * Sisa tagihan dihitung oleh Stored Function fn_sisa_pembayaran.
     *
     * @return array{
     *     periode: array{label: string, awal: string, akhir: string},
     *     pesanan_aktif: int,
     *     pesanan_mendekati_deadline: int,
     *     pesanan_terlambat: int,
     *     pembayaran_belum_lunas: array{jumlah_pesanan: int, total_sisa: float},
     *     keuangan_bulan_ini: array{pendapatan: float, pengeluaran: float, selisih: float},
     *     stok_menipis: int
     * }
     */
    public function ringkasan(): array
    {
        $awal = now()->startOfMonth();
        $akhir = now()->endOfMonth();

        $pendapatan = (float) Pembayaran::query()
            ->whereBetween('tanggal_bayar', [$awal, $akhir])
            ->sum('jumlah');

        $pengeluaran = (float) Pengeluaran::query()
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->sum('nominal');

        return [
            'periode' => [
                'label' => $awal->copy()->locale('id')->translatedFormat('F Y'),
                'awal' => $awal->toDateString(),
                'akhir' => $akhir->toDateString(),
            ],
            'pesanan_aktif' => Pesanan::aktif()->count(),
            'pesanan_mendekati_deadline' => Pesanan::mendekatiDeadline()->count(),
            'pesanan_terlambat' => Pesanan::terlambat()->count(),
            'pembayaran_belum_lunas' => [
                'jumlah_pesanan' => Pesanan::belumLunas()->count(),
                'total_sisa' => (float) Pesanan::belumLunas()->sum(DB::raw('fn_sisa_pembayaran(pesanan.id)')),
            ],
            'keuangan_bulan_ini' => [
                'pendapatan' => $pendapatan,
                'pengeluaran' => $pengeluaran,
                'selisih' => $pendapatan - $pengeluaran,
            ],
            'stok_menipis' => $this->inventoryService->getBahanMenipis()->count(),
        ];
    }

    /**
     * Seluruh data yang dibutuhkan halaman dashboard (ringkasan + daftar singkat).
     *
     * @return array<string, mixed>
     */
    public function dataDashboard(int $limit = 5): array
    {
        return [
            'ringkasan' => $this->ringkasan(),
            'pesananTerlambat' => $this->pesananTerlambat($limit),
            'pesananMendekati' => $this->pesananMendekatiDeadline($limit),
            'stokMenipis' => $this->stokMenipis($limit),
        ];
    }

    /**
     * Pesanan aktif, deadline terdekat di atas.
     *
     * @return Collection<int, Pesanan>
     */
    public function pesananAktif(int $limit = 10): Collection
    {
        return collect($this->pesananService->getAll(['status' => 'aktif'], $this->batasi($limit))->items());
    }

    /**
     * Pesanan aktif yang deadline-nya sudah lewat, yang paling lama di atas.
     *
     * @return Collection<int, Pesanan>
     */
    public function pesananTerlambat(int $limit = 10): Collection
    {
        return collect($this->pesananService->getAll(['deadline' => 'terlambat'], $this->batasi($limit))->items());
    }

    /**
     * Pesanan aktif dengan deadline dalam 3 hari ke depan (H-3).
     *
     * @return Collection<int, Pesanan>
     */
    public function pesananMendekatiDeadline(int $limit = 10): Collection
    {
        return collect($this->pesananService->getAll(['deadline' => 'mendekati'], $this->batasi($limit))->items());
    }

    /**
     * Bahan dengan stok <= stok minimum, yang paling menipis di atas.
     *
     * @return Collection<int, Bahan>
     */
    public function stokMenipis(int $limit = 10): Collection
    {
        return $this->inventoryService->getBahanMenipis()->take($this->batasi($limit))->values();
    }

    private function batasi(int $limit): int
    {
        return max(1, min($limit, self::LIMIT_MAKSIMAL));
    }
}
