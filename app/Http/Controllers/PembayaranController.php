<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function __construct(private readonly PembayaranService $pembayaranService) {}

    /**
     * Catat pembayaran (DP, cicilan, atau pelunasan).
     */
    public function store(StorePembayaranRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $pembayaran = $this->pembayaranService->catat($pesanan, $request->validated());

        $status = $this->pembayaranService->ringkasan($pesanan)['status'];

        return redirect()->route('pesanan.show', $pesanan)
            ->with('success', 'Pembayaran Rp '.number_format((float) $pembayaran->jumlah, 0, ',', '.')." berhasil dicatat. Status pembayaran: {$status}.");
    }

    /**
     * Batalkan catatan pembayaran.
     */
    public function destroy(Pembayaran $pembayaran): RedirectResponse
    {
        $pesananId = $pembayaran->pesanan_id;

        $this->pembayaranService->batalkan($pembayaran);

        return redirect()->route('pesanan.show', $pesananId)
            ->with('success', 'Pembayaran berhasil dibatalkan.');
    }

    /**
     * Halaman invoice siap cetak / simpan sebagai PDF dari browser.
     */
    public function invoice(Pesanan $pesanan): View
    {
        return view('pesanan.invoice', $this->pembayaranService->dataInvoice($pesanan));
    }

    /**
     * Unduh invoice dalam format PDF.
     */
    public function invoicePdf(Pesanan $pesanan): Response
    {
        return $this->pembayaranService
            ->buatInvoicePdf($pesanan)
            ->download($this->pembayaranService->namaFileInvoice($pesanan));
    }
}
