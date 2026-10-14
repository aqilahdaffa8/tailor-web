<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Keuangan: pendapatan dihitung otomatis dari tabel pembayaran (tidak diinput manual),
 * sedangkan pengeluaran dicatat manual. Selisih = pendapatan - pengeluaran per periode.
 */
class KeuanganService
{
    /**
     * Tentukan rentang periode. Tanpa input, memakai bulan berjalan.
     * Batas awal dimulai 00:00:00 dan batas akhir berakhir 23:59:59 sehingga kedua hari ikut terhitung.
     *
     * @return array{awal: Carbon, akhir: Carbon}
     */
    public function tentukanPeriode(?string $dari = null, ?string $sampai = null): array
    {
        $awal = $dari ? Carbon::parse($dari)->startOfDay() : now()->startOfMonth();
        $akhir = $sampai ? Carbon::parse($sampai)->endOfDay() : now()->endOfMonth();

        if ($akhir->lt($awal)) {
            $akhir = $awal->copy()->endOfDay();
        }

        return ['awal' => $awal, 'akhir' => $akhir];
    }

    /**
     * Total pendapatan dan pengeluaran pada periode (ringan, tanpa rincian).
     *
     * @return array{pendapatan: float, pengeluaran: float, selisih: float}
     */
    public function totalPeriode(Carbon $awal, Carbon $akhir): array
    {
        $pendapatan = (float) $this->queryPendapatan($awal, $akhir)->sum('jumlah');
        $pengeluaran = (float) $this->queryPengeluaran($awal, $akhir)->sum('nominal');

        return [
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'selisih' => round($pendapatan - $pengeluaran, 2),
        ];
    }

    /**
     * Ringkasan keuangan lengkap untuk satu periode.
     *
     * @return array{
     *     periode: array{dari: string, sampai: string, label: string},
     *     pendapatan: float,
     *     pengeluaran: float,
     *     selisih: float,
     *     jumlah_pembayaran: int,
     *     jumlah_pengeluaran: int,
     *     pendapatan_per_metode: array<int, array{metode: string, total: float, jumlah: int}>,
     *     pengeluaran_per_kategori: array<int, array{kategori_id: int|null, kategori: string, total: float, jumlah: int}>
     * }
     */
    public function ringkasan(Carbon $awal, Carbon $akhir): array
    {
        $perMetode = $this->queryPendapatan($awal, $akhir)
            ->selectRaw('metode, SUM(jumlah) as total, COUNT(*) as jumlah')
            ->groupBy('metode')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($baris) => [
                'metode' => (string) $baris->metode,
                'total' => (float) $baris->total,
                'jumlah' => (int) $baris->jumlah,
            ])
            ->values()
            ->all();

        $perKategori = $this->queryPengeluaran($awal, $akhir)
            ->with('kategori')
            ->selectRaw('kategori_pengeluaran_id, SUM(nominal) as total, COUNT(*) as jumlah')
            ->groupBy('kategori_pengeluaran_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($baris) => [
                'kategori_id' => $baris->kategori_pengeluaran_id,
                'kategori' => $baris->kategori?->nama_kategori ?? 'Tanpa kategori',
                'total' => (float) $baris->total,
                'jumlah' => (int) $baris->jumlah,
            ])
            ->values()
            ->all();

        return [
            'periode' => [
                'dari' => $awal->toDateString(),
                'sampai' => $akhir->toDateString(),
                'label' => $awal->copy()->locale('id')->translatedFormat('j F Y')
                    .' - '.$akhir->copy()->locale('id')->translatedFormat('j F Y'),
            ],
            ...$this->totalPeriode($awal, $akhir),
            'jumlah_pembayaran' => array_sum(array_column($perMetode, 'jumlah')),
            'jumlah_pengeluaran' => array_sum(array_column($perKategori, 'jumlah')),
            'pendapatan_per_metode' => $perMetode,
            'pengeluaran_per_kategori' => $perKategori,
        ];
    }

    /**
     * Daftar pendapatan = pembayaran yang masuk pada periode.
     *
     * @return LengthAwarePaginator<Pembayaran>
     */
    public function daftarPendapatan(Carbon $awal, Carbon $akhir, int $perPage = 15, string $pageName = 'page'): LengthAwarePaginator
    {
        return $this->queryPendapatan($awal, $akhir)
            ->with('pesanan.pelanggan')
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], $pageName);
    }

    /**
     * @return LengthAwarePaginator<Pengeluaran>
     */
    public function daftarPengeluaran(Carbon $awal, Carbon $akhir, ?int $kategoriId = null, int $perPage = 15, string $pageName = 'page'): LengthAwarePaginator
    {
        return $this->queryPengeluaran($awal, $akhir, $kategoriId)
            ->with('kategori')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], $pageName);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function buatPengeluaran(array $data): Pengeluaran
    {
        return Pengeluaran::create($data)->load('kategori');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function ubahPengeluaran(Pengeluaran $pengeluaran, array $data): Pengeluaran
    {
        $pengeluaran->update($data);

        return $pengeluaran->fresh('kategori');
    }

    public function hapusPengeluaran(Pengeluaran $pengeluaran): void
    {
        $pengeluaran->delete();
    }

    /**
     * @return Builder<Pembayaran>
     */
    private function queryPendapatan(Carbon $awal, Carbon $akhir): Builder
    {
        return Pembayaran::query()->whereBetween('tanggal_bayar', [$awal, $akhir]);
    }

    /**
     * @return Builder<Pengeluaran>
     */
    private function queryPengeluaran(Carbon $awal, Carbon $akhir, ?int $kategoriId = null): Builder
    {
        return Pengeluaran::query()
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->when($kategoriId, fn (Builder $query) => $query->where('kategori_pengeluaran_id', $kategoriId));
    }
}
