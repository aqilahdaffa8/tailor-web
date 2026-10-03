<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\PembayaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PembayaranController extends Controller
{
    public function __construct(private readonly PembayaranService $pembayaranService) {}

    /**
     * POST /api/pesanan/{pesanan}/pembayaran
     */
    public function store(StorePembayaranRequest $request, Pesanan $pesanan): JsonResponse
    {
        $pembayaran = $this->pembayaranService->catat($pesanan, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat.',
            'data' => [
                'pembayaran' => $pembayaran,
                'ringkasan' => $this->pembayaranService->ringkasan($pesanan),
            ],
        ], 201);
    }

    /**
     * GET /api/pesanan/{pesanan}/pembayaran
     */
    public function index(Pesanan $pesanan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'ringkasan' => $this->pembayaranService->ringkasan($pesanan),
                'riwayat' => $this->pembayaranService->riwayat($pesanan),
            ],
        ]);
    }

    /**
     * GET /api/pesanan/{pesanan}/invoice
     */
    public function invoice(Pesanan $pesanan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pembayaranService->dataInvoice($pesanan),
        ]);
    }

    /**
     * GET /api/pesanan/{pesanan}/invoice/pdf
     * Mengunduh invoice sebagai file PDF.
     */
    public function invoicePdf(Pesanan $pesanan): Response
    {
        return $this->pembayaranService
            ->buatInvoicePdf($pesanan)
            ->download($this->pembayaranService->namaFileInvoice($pesanan));
    }

    /**
     * DELETE /api/pembayaran/{pembayaran}
     */
    public function destroy(Pembayaran $pembayaran): JsonResponse
    {
        $pesanan = $pembayaran->pesanan;

        $this->pembayaranService->batalkan($pembayaran);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dibatalkan.',
            'data' => ['ringkasan' => $this->pembayaranService->ringkasan($pesanan)],
        ]);
    }
}
