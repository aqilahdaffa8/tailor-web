<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJenisPakaianRequest;
use App\Http\Requests\StorePolaDetailRequest;
use App\Http\Requests\UpdatePolaDetailRequest;
use App\Models\JenisPakaian;
use App\Models\PolaDetail;
use App\Services\PolaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolaController extends Controller
{
    public function __construct(private readonly PolaService $polaService) {}

    /**
     * GET /api/pola/kategori
     * Daftar semua kategori pola beserta jenis pakaian di dalamnya.
     */
    public function kategoriIndex(Request $request): JsonResponse
    {
        $kategori = $this->polaService->getAllKategori(
            search: $request->string('search')->toString()
        );

        return response()->json([
            'success' => true,
            'data' => $kategori,
        ]);
    }

    /**
     * GET /api/pola/kategori/{id}/jenis-pakaian
     */
    public function jenisPakaianIndex(int $id): JsonResponse
    {
        $kategori = $this->polaService->findKategori($id);

        return response()->json([
            'success' => true,
            'data' => $kategori->jenisPakaian,
        ]);
    }

    /**
     * POST /api/pola/jenis-pakaian
     */
    public function jenisPakaianStore(StoreJenisPakaianRequest $request): JsonResponse
    {
        $jenis = $this->polaService->createJenisPakaian($request->validated());

        return response()->json([
            'success' => true,
            'data' => $jenis,
        ], 201);
    }

    /**
     * PUT /api/pola/jenis-pakaian/{jenisPakaian}
     */
    public function jenisPakaianUpdate(StoreJenisPakaianRequest $request, JenisPakaian $jenisPakaian): JsonResponse
    {
        $updated = $this->polaService->updateJenisPakaian($jenisPakaian, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    /**
     * DELETE /api/pola/jenis-pakaian/{jenisPakaian}
     */
    public function jenisPakaianDestroy(JenisPakaian $jenisPakaian): JsonResponse
    {
        $this->polaService->deleteJenisPakaian($jenisPakaian);

        return response()->json([
            'success' => true,
            'message' => 'Jenis pakaian berhasil dihapus.',
        ]);
    }

    /**
     * GET /api/pola/jenis-pakaian/{id}/detail
     */
    public function detailIndex(int $id): JsonResponse
    {
        $details = $this->polaService->getPolaDetailByJenis($id);

        return response()->json([
            'success' => true,
            'data' => $details,
        ]);
    }

    /**
     * GET /api/pola/detail/{id}
     */
    public function detailShow(int $id): JsonResponse
    {
        $pola = $this->polaService->findPolaDetail($id);

        return response()->json([
            'success' => true,
            'data' => $pola,
        ]);
    }

    /**
     * POST /api/pola/detail
     */
    public function detailStore(StorePolaDetailRequest $request): JsonResponse
    {
        $pola = $this->polaService->createPolaDetail(
            $request->validated(),
            $request->file('foto_pola'),
            $request->file('file_pola')
        );

        return response()->json([
            'success' => true,
            'data' => $pola,
        ], 201);
    }

    /**
     * POST or PUT /api/pola/detail/{polaDetail}
     */
    public function detailUpdate(UpdatePolaDetailRequest $request, PolaDetail $polaDetail): JsonResponse
    {
        $updated = $this->polaService->updatePolaDetail(
            $polaDetail,
            $request->validated(),
            $request->file('foto_pola'),
            $request->file('file_pola')
        );

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    /**
     * DELETE /api/pola/detail/{polaDetail}
     */
    public function detailDestroy(PolaDetail $polaDetail): JsonResponse
    {
        $this->polaService->deletePolaDetail($polaDetail);

        return response()->json([
            'success' => true,
            'message' => 'Pola detail berhasil dihapus.',
        ]);
    }

    /**
     * DELETE /api/pola/detail/{polaDetail}/foto
     */
    public function deleteFoto(PolaDetail $polaDetail): JsonResponse
    {
        $this->polaService->deleteFotoPola($polaDetail);

        return response()->json([
            'success' => true,
            'message' => 'Foto pola berhasil dihapus.',
        ]);
    }

    /**
     * DELETE /api/pola/detail/{polaDetail}/file
     */
    public function deleteFile(PolaDetail $polaDetail): JsonResponse
    {
        $this->polaService->deleteFilePola($polaDetail);

        return response()->json([
            'success' => true,
            'message' => 'File dokumen pola berhasil dihapus.',
        ]);
    }
}
