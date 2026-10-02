<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFotoReferensiRequest;
use App\Http\Requests\StoreItemPesananRequest;
use App\Http\Requests\StorePemakaianBahanRequest;
use App\Http\Requests\UpdateItemPesananRequest;
use App\Http\Requests\UpdateProgressRequest;
use App\Models\FotoReferensi;
use App\Models\ItemPesanan;
use App\Models\PemakaianBahan;
use App\Models\Pesanan;
use App\Services\PesananService;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;

class PesananItemController extends Controller
{
    public function __construct(
        private readonly PesananService $pesananService,
        private readonly ProgressService $progressService,
    ) {}

    /**
     * POST /api/pesanan/{pesanan}/item
     */
    public function store(StoreItemPesananRequest $request, Pesanan $pesanan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pesananService->tambahItem($pesanan, $request->validated()),
        ], 201);
    }

    /**
     * PUT /api/pesanan/item/{item}
     */
    public function update(UpdateItemPesananRequest $request, ItemPesanan $item): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pesananService->updateItem($item, $request->validated()),
        ]);
    }

    /**
     * DELETE /api/pesanan/item/{item}
     */
    public function destroy(ItemPesanan $item): JsonResponse
    {
        $this->pesananService->hapusItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Item pesanan berhasil dihapus.',
        ]);
    }

    /**
     * PUT /api/pesanan/item/{item}/progress
     * Body: {"aksi": "maju" | "mundur"}
     */
    public function progress(UpdateProgressRequest $request, ItemPesanan $item): JsonResponse
    {
        $diperbarui = $this->progressService->ubahProgress($item, $request->validated('aksi'));

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $diperbarui,
                'tahapan_saat_ini' => $this->progressService->tahapanSaatIni($diperbarui),
                'status_pesanan' => $diperbarui->pesanan->status,
            ],
        ]);
    }

    /**
     * POST /api/pesanan/item/{item}/bahan
     */
    public function storeBahan(StorePemakaianBahanRequest $request, ItemPesanan $item): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pemakaian bahan berhasil dicatat dan stok dipotong.',
            'data' => $this->pesananService->catatPemakaianBahan($item, $request->validated()),
        ], 201);
    }

    /**
     * DELETE /api/pesanan/pemakaian/{pemakaian}
     */
    public function destroyBahan(PemakaianBahan $pemakaian): JsonResponse
    {
        $this->pesananService->hapusPemakaianBahan($pemakaian);

        return response()->json([
            'success' => true,
            'message' => 'Pemakaian bahan dihapus dan stok telah dikembalikan.',
        ]);
    }

    /**
     * POST /api/pesanan/item/{item}/foto
     */
    public function storeFoto(StoreFotoReferensiRequest $request, ItemPesanan $item): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pesananService->uploadFoto($item, $request->file('foto'), $request->validated('keterangan')),
        ], 201);
    }

    /**
     * DELETE /api/pesanan/foto/{foto}
     */
    public function destroyFoto(FotoReferensi $foto): JsonResponse
    {
        $this->pesananService->hapusFoto($foto);

        return response()->json([
            'success' => true,
            'message' => 'Foto referensi berhasil dihapus.',
        ]);
    }
}
