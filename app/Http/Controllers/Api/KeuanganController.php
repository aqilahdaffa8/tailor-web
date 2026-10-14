<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterKeuanganRequest;
use App\Models\Pembayaran;
use App\Services\KeuanganService;
use Illuminate\Http\JsonResponse;

class KeuanganController extends Controller
{
    public function __construct(private readonly KeuanganService $keuanganService) {}

    /**
     * GET /api/keuangan/ringkasan?dari=&sampai=
     * Pendapatan, pengeluaran, dan selisih untuk periode (default: bulan berjalan).
     */
    public function ringkasan(FilterKeuanganRequest $request): JsonResponse
    {
        ['awal' => $awal, 'akhir' => $akhir] = $this->keuanganService->tentukanPeriode(
            $request->input('dari'),
            $request->input('sampai'),
        );

        return response()->json([
            'success' => true,
            'data' => $this->keuanganService->ringkasan($awal, $akhir),
        ]);
    }

    /**
     * GET /api/keuangan/pendapatan?dari=&sampai=&per_page=
     * Daftar pembayaran yang masuk pada periode.
     */
    public function pendapatan(FilterKeuanganRequest $request): JsonResponse
    {
        ['awal' => $awal, 'akhir' => $akhir] = $this->keuanganService->tentukanPeriode(
            $request->input('dari'),
            $request->input('sampai'),
        );

        $halaman = $this->keuanganService->daftarPendapatan($awal, $akhir, $request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => collect($halaman->items())->map(fn (Pembayaran $pembayaran) => [
                'id' => $pembayaran->id,
                'tanggal_bayar' => $pembayaran->tanggal_bayar->toDateTimeString(),
                'jumlah' => (float) $pembayaran->jumlah,
                'metode' => $pembayaran->metode,
                'catatan' => $pembayaran->catatan,
                'pesanan' => [
                    'id' => $pembayaran->pesanan_id,
                    'nomor_pesanan' => $pembayaran->pesanan?->nomor_pesanan,
                    'pelanggan' => $pembayaran->pesanan?->pelanggan?->nama,
                ],
            ])->all(),
            'meta' => [
                'current_page' => $halaman->currentPage(),
                'last_page' => $halaman->lastPage(),
                'per_page' => $halaman->perPage(),
                'total' => $halaman->total(),
            ],
        ]);
    }
}
