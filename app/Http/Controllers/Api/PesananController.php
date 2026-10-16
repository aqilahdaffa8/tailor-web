<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdatePesananRequest;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function __construct(private readonly PesananService $pesananService) {}

    /**
     * GET /api/pesanan
     * Daftar pesanan: filter status, pelanggan_id, deadline (terlambat|mendekati|hari_ini), pembayaran (belum_lunas), search.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        $data = $this->pesananService->getAll(
            filters: [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'pelanggan_id' => $request->integer('pelanggan_id') ?: null,
                'deadline' => $request->string('deadline')->toString(),
                'pembayaran' => $request->string('pembayaran')->toString(),
            ],
            perPage: $request->integer('per_page', 15),
        );

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ]);
    }

    /**
     * POST /api/pesanan
     */
    public function store(StorePesananRequest $request): JsonResponse
    {
        $pesanan = $this->pesananService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $pesanan,
        ], 201);
    }

    /**
     * GET /api/pesanan/{pesanan}
     */
    public function show(Pesanan $pesanan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pesananService->find($pesanan->id),
        ]);
    }

    /**
     * PUT /api/pesanan/{pesanan}
     */
    public function update(UpdatePesananRequest $request, Pesanan $pesanan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pesananService->update($pesanan, $request->validated()),
        ]);
    }

    /**
     * DELETE /api/pesanan/{pesanan}
     * Membatalkan pesanan (status menjadi "dibatalkan").
     */
    public function destroy(Pesanan $pesanan): JsonResponse
    {
        $this->pesananService->batalkan($pesanan);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
        ]);
    }
}
