<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterKeuanganRequest;
use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\KeuanganService;
use Illuminate\Http\JsonResponse;

class PengeluaranController extends Controller
{
    public function __construct(private readonly KeuanganService $keuanganService) {}

    /**
     * GET /api/pengeluaran?dari=&sampai=&kategori_id=&per_page=
     */
    public function index(FilterKeuanganRequest $request): JsonResponse
    {
        ['awal' => $awal, 'akhir' => $akhir] = $this->keuanganService->tentukanPeriode(
            $request->input('dari'),
            $request->input('sampai'),
        );

        $halaman = $this->keuanganService->daftarPengeluaran(
            $awal,
            $akhir,
            $request->filled('kategori_id') ? (int) $request->input('kategori_id') : null,
            $request->integer('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => $halaman->items(),
            'meta' => [
                'current_page' => $halaman->currentPage(),
                'last_page' => $halaman->lastPage(),
                'per_page' => $halaman->perPage(),
                'total' => $halaman->total(),
            ],
        ]);
    }

    /**
     * POST /api/pengeluaran
     */
    public function store(StorePengeluaranRequest $request): JsonResponse
    {
        $pengeluaran = $this->keuanganService->buatPengeluaran($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dicatat.',
            'data' => $pengeluaran,
        ], 201);
    }

    /**
     * PUT /api/pengeluaran/{pengeluaran}
     */
    public function update(UpdatePengeluaranRequest $request, Pengeluaran $pengeluaran): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil diperbarui.',
            'data' => $this->keuanganService->ubahPengeluaran($pengeluaran, $request->validated()),
        ]);
    }

    /**
     * DELETE /api/pengeluaran/{pengeluaran}
     */
    public function destroy(Pengeluaran $pengeluaran): JsonResponse
    {
        $this->keuanganService->hapusPengeluaran($pengeluaran);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus.',
        ]);
    }
}
