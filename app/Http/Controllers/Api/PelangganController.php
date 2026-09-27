<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use App\Models\Pelanggan;
use App\Services\PelangganService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function __construct(private readonly PelangganService $pelangganService) {}

    /**
     * GET /api/pelanggan
     * Daftar pelanggan dengan pencarian & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->pelangganService->getAll(
            search: $request->string('search')->toString(),
            perPage: (int) $request->input('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ],
        ]);
    }

    /**
     * POST /api/pelanggan
     */
    public function store(StorePelangganRequest $request): JsonResponse
    {
        $pelanggan = $this->pelangganService->create($request->validated());

        return response()->json(['success' => true, 'data' => $pelanggan], 201);
    }

    /**
     * GET /api/pelanggan/{id}
     */
    public function show(int $id): JsonResponse
    {
        $pelanggan = $this->pelangganService->findWithRiwayat($id);

        return response()->json(['success' => true, 'data' => $pelanggan]);
    }

    /**
     * PUT /api/pelanggan/{pelanggan}
     */
    public function update(UpdatePelangganRequest $request, Pelanggan $pelanggan): JsonResponse
    {
        $updated = $this->pelangganService->update($pelanggan, $request->validated());

        return response()->json(['success' => true, 'data' => $updated]);
    }

    /**
     * DELETE /api/pelanggan/{pelanggan}
     */
    public function destroy(Pelanggan $pelanggan): JsonResponse
    {
        $this->pelangganService->delete($pelanggan);

        return response()->json(['success' => true, 'message' => 'Pelanggan dihapus.']);
    }
}
