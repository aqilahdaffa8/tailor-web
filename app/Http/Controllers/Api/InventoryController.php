<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StokMasukRequest;
use App\Http\Requests\StoreBahanRequest;
use App\Http\Requests\StorePeralatanRequest;
use App\Http\Requests\UpdateBahanRequest;
use App\Http\Requests\UpdatePeralatanRequest;
use App\Models\Bahan;
use App\Models\Peralatan;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    /**
     * GET /api/bahan
     */
    public function bahanIndex(Request $request): JsonResponse
    {
        $bahan = $this->inventoryService->getAllBahan(
            search: $request->string('search')->toString(),
            kategoriId: $request->has('kategori_id') ? $request->integer('kategori_id') : null,
            hanyaMenipis: $request->boolean('menipis')
        );

        return response()->json([
            'success' => true,
            'data' => $bahan,
        ]);
    }

    /**
     * POST /api/bahan
     */
    public function bahanStore(StoreBahanRequest $request): JsonResponse
    {
        $bahan = $this->inventoryService->createBahan($request->validated());

        return response()->json([
            'success' => true,
            'data' => $bahan,
        ], 201);
    }

    /**
     * GET /api/bahan/{bahan}
     */
    public function bahanShow(Bahan $bahan): JsonResponse
    {
        $detail = $this->inventoryService->findBahan($bahan->id);

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    /**
     * PUT /api/bahan/{bahan}
     */
    public function bahanUpdate(UpdateBahanRequest $request, Bahan $bahan): JsonResponse
    {
        $updated = $this->inventoryService->updateBahan($bahan, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    /**
     * DELETE /api/bahan/{bahan}
     */
    public function bahanDestroy(Bahan $bahan): JsonResponse
    {
        $this->inventoryService->deleteBahan($bahan);

        return response()->json([
            'success' => true,
            'message' => 'Bahan berhasil dihapus.',
        ]);
    }

    /**
     * POST /api/bahan/{bahan}/stok-masuk
     */
    public function stokMasuk(StokMasukRequest $request, Bahan $bahan): JsonResponse
    {
        $riwayat = $this->inventoryService->tambahStokMasuk(
            $bahan,
            (float) $request->validated('jumlah'),
            $request->validated('keterangan')
        );

        return response()->json([
            'success' => true,
            'message' => 'Stok bahan berhasil ditambahkan.',
            'data' => [
                'bahan' => $bahan->fresh(),
                'riwayat' => $riwayat,
            ],
        ]);
    }

    /**
     * GET /api/bahan/{bahan}/riwayat
     */
    public function bahanRiwayat(Bahan $bahan): JsonResponse
    {
        $riwayat = $this->inventoryService->getRiwayatStok($bahan);

        return response()->json([
            'success' => true,
            'data' => $riwayat,
        ]);
    }

    /**
     * GET /api/peralatan
     */
    public function peralatanIndex(Request $request): JsonResponse
    {
        $peralatan = $this->inventoryService->getAllPeralatan(
            search: $request->string('search')->toString(),
            kondisi: $request->string('kondisi')->toString() ?: null
        );

        return response()->json([
            'success' => true,
            'data' => $peralatan,
        ]);
    }

    /**
     * POST /api/peralatan
     */
    public function peralatanStore(StorePeralatanRequest $request): JsonResponse
    {
        $peralatan = $this->inventoryService->createPeralatan($request->validated());

        return response()->json([
            'success' => true,
            'data' => $peralatan,
        ], 201);
    }

    /**
     * GET /api/peralatan/{peralatan}
     */
    public function peralatanShow(Peralatan $peralatan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $peralatan,
        ]);
    }

    /**
     * PUT /api/peralatan/{peralatan}
     */
    public function peralatanUpdate(UpdatePeralatanRequest $request, Peralatan $peralatan): JsonResponse
    {
        $updated = $this->inventoryService->updatePeralatan($peralatan, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    /**
     * DELETE /api/peralatan/{peralatan}
     */
    public function peralatanDestroy(Peralatan $peralatan): JsonResponse
    {
        $this->inventoryService->deletePeralatan($peralatan);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil dihapus.',
        ]);
    }
}
