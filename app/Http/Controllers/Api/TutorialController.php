<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterTutorialRequest;
use App\Http\Requests\StoreTutorialRequest;
use App\Http\Requests\UpdateTutorialRequest;
use App\Models\Tutorial;
use App\Services\TutorialService;
use Illuminate\Http\JsonResponse;

/**
 * Catatan klien: untuk mengunggah file (PDF/gambar) gunakan multipart/form-data.
 * Karena PHP tidak mem-parsing multipart pada PUT, kirim POST dengan field `_method=PUT` saat memperbarui dengan file.
 */
class TutorialController extends Controller
{
    public function __construct(private readonly TutorialService $tutorialService) {}

    /**
     * GET /api/tutorial?kategori=&tipe=&search=&per_page=
     */
    public function index(FilterTutorialRequest $request): JsonResponse
    {
        $halaman = $this->tutorialService->daftar(
            $request->input('kategori'),
            $request->input('tipe'),
            $request->input('search'),
            $request->integer('per_page', 12),
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
     * POST /api/tutorial
     */
    public function store(StoreTutorialRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Tutorial berhasil ditambahkan.',
            'data' => $this->tutorialService->buat($request->validated()),
        ], 201);
    }

    /**
     * GET /api/tutorial/{tutorial}
     */
    public function show(Tutorial $tutorial): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $tutorial]);
    }

    /**
     * PUT /api/tutorial/{tutorial}
     */
    public function update(UpdateTutorialRequest $request, Tutorial $tutorial): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Tutorial berhasil diperbarui.',
            'data' => $this->tutorialService->ubah($tutorial, $request->validated()),
        ]);
    }

    /**
     * DELETE /api/tutorial/{tutorial}
     */
    public function destroy(Tutorial $tutorial): JsonResponse
    {
        $this->tutorialService->hapus($tutorial);

        return response()->json(['success' => true, 'message' => 'Tutorial berhasil dihapus.']);
    }
}
