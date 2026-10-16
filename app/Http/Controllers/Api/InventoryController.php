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
        $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        $bahan = $this->inventoryService->paginateBahan(
            search: $request->string('search')->toString(),
            kategoriId: $request->has('kategori_id') ? $request->integer('kategori_id') : null,
            hanyaMenipis: $request->boolean('menipis'),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => $bahan->items(),
            'meta' => [
                'current_page' => $bahan->currentPage(),
                'last_page' => $bahan->lastPage(),
                'per_page' => $bahan->perPage(),
                'total' => $bahan->total(),
            ],
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
        $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        $peralatan = $this->inventoryService->paginatePeralatan(
            search: $request->string('search')->toString(),
            kondisi: $request->string('kondisi')->toString() ?: null,
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => $peralatan->items(),
            'meta' => [
                'current_page' => $peralatan->currentPage(),
                'last_page' => $peralatan->lastPage(),
                'per_page' => $peralatan->perPage(),
                'total' => $peralatan->total(),
            ],
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
