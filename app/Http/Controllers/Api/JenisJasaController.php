<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJenisJasaRequest;
use App\Http\Requests\UpdateJenisJasaRequest;
use App\Models\JenisJasa;
use App\Services\MasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JenisJasaController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $activeOnly = $request->boolean('active_only');
        $jasa = $this->masterDataService->getAllJenisJasa($activeOnly);

        return response()->json([
            'success' => true,
            'data' => $jasa,
        ]);
    }

    public function store(StoreJenisJasaRequest $request): JsonResponse
    {
        $jasa = $this->masterDataService->createJenisJasa($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Jenis jasa berhasil ditambahkan.',
            'data' => $jasa,
        ], 201);
    }

    public function show(JenisJasa $jenisJasa): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $jenisJasa->load('templateTahapan'),
        ]);
    }

    public function update(UpdateJenisJasaRequest $request, JenisJasa $jenisJasa): JsonResponse
    {
        $updated = $this->masterDataService->updateJenisJasa($jenisJasa, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Jenis jasa berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    public function destroy(JenisJasa $jenisJasa): JsonResponse
    {
        $this->masterDataService->deleteJenisJasa($jenisJasa);

        return response()->json([
            'success' => true,
            'message' => 'Jenis jasa berhasil dihapus.',
        ]);
    }
}
