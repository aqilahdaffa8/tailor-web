<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriRequest;
use App\Services\MasterDataService;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}

    // --- Kategori Pola ---
    public function polaIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->getAllKategoriPola(),
        ]);
    }

    public function polaStore(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = $this->masterDataService->createKategoriPola($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori pola berhasil ditambahkan.',
            'data' => $kategori,
        ], 201);
    }

    public function polaDestroy(int $id): JsonResponse
    {
        $this->masterDataService->deleteKategoriPola($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pola berhasil dihapus.',
        ]);
    }

    // --- Kategori Bahan ---
    public function bahanIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->getAllKategoriBahan(),
        ]);
    }

    public function bahanStore(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = $this->masterDataService->createKategoriBahan($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori bahan berhasil ditambahkan.',
            'data' => $kategori,
        ], 201);
    }

    public function bahanDestroy(int $id): JsonResponse
    {
        $this->masterDataService->deleteKategoriBahan($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori bahan berhasil dihapus.',
        ]);
    }

    // --- Kategori Pengeluaran ---
    public function pengeluaranIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->getAllKategoriPengeluaran(),
        ]);
    }

    public function pengeluaranStore(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = $this->masterDataService->createKategoriPengeluaran($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori pengeluaran berhasil ditambahkan.',
            'data' => $kategori,
        ], 201);
    }

    public function pengeluaranDestroy(int $id): JsonResponse
    {
        $this->masterDataService->deleteKategoriPengeluaran($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pengeluaran berhasil dihapus.',
        ]);
    }

    // --- Kategori Tutorial ---
    public function tutorialIndex(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->getAllKategoriTutorial(),
        ]);
    }

    public function tutorialStore(StoreKategoriRequest $request): JsonResponse
    {
        $kategori = $this->masterDataService->createKategoriTutorial($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori tutorial berhasil ditambahkan.',
            'data' => $kategori,
        ], 201);
    }

    public function tutorialDestroy(int $id): JsonResponse
    {
        $this->masterDataService->deleteKategoriTutorial($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori tutorial berhasil dihapus.',
        ]);
    }
}
