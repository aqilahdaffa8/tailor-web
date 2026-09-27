<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilUsahaRequest;
use App\Services\MasterDataService;
use Illuminate\Http\JsonResponse;

class PengaturanController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}

    public function getProfil(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->getProfilUsaha(),
        ]);
    }

    public function updateProfil(UpdateProfilUsahaRequest $request): JsonResponse
    {
        $this->masterDataService->updateProfilUsaha($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan profil usaha dan invoice berhasil diperbarui.',
            'data' => $this->masterDataService->getProfilUsaha(),
        ]);
    }
}
