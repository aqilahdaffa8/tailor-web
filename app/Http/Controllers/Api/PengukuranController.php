<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengukuranRequest;
use App\Http\Requests\UpdatePengukuranRequest;
use App\Models\Pengukuran;
use App\Services\PengukuranService;
use Illuminate\Http\JsonResponse;

class PengukuranController extends Controller
{
    public function __construct(private readonly PengukuranService $pengukuranService) {}

    /**
     * GET /api/pelanggan/{id}/pengukuran
     * Daftar riwayat pengukuran untuk pelanggan tertentu.
     */
    public function indexByPelanggan(int $id): JsonResponse
    {
        $riwayat = $this->pengukuranService->getByPelanggan($id);

        return response()->json([
            'success' => true,
            'data' => $riwayat,
        ]);
    }

    /**
     * GET /api/pelanggan/{id}/pengukuran/latest
     * Data pengukuran terakhir pelanggan.
     */
    public function latestByPelanggan(int $id): JsonResponse
    {
        $latest = $this->pengukuranService->getLatestByPelanggan($id);

        return response()->json([
            'success' => true,
            'data' => $latest,
        ]);
    }

    /**
     * POST /api/pengukuran
     */
    public function store(StorePengukuranRequest $request): JsonResponse
    {
        $pengukuran = $this->pengukuranService->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $pengukuran,
        ], 201);
    }

    /**
     * GET /api/pengukuran/{pengukuran}
     */
    public function show(Pengukuran $pengukuran): JsonResponse
    {
        $detail = $this->pengukuranService->find($pengukuran->id);

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    /**
     * PUT /api/pengukuran/{pengukuran}
     */
    public function update(UpdatePengukuranRequest $request, Pengukuran $pengukuran): JsonResponse
    {
        $updated = $this->pengukuranService->update($pengukuran, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    /**
     * DELETE /api/pengukuran/{pengukuran}
     */
    public function destroy(Pengukuran $pengukuran): JsonResponse
    {
        $this->pengukuranService->delete($pengukuran);

        return response()->json([
            'success' => true,
            'message' => 'Data pengukuran berhasil dihapus.',
        ]);
    }
}
