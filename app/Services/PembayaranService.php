<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranService
{
    public function __construct(private readonly MasterDataService $masterDataService) {}

    /**
     * Ringkasan keuangan pesanan. Sisa dan status dihitung oleh Stored Function MySQL
     * (fn_sisa_pembayaran & fn_status_pembayaran).
     *
     * @return array{total_tagihan: float, total_dibayar: float, sisa: float, status: string}
     */
    public function ringkasan(Pesanan $pesanan): array
    {
        $hasil = DB::selectOne(
            'SELECT
                fn_sisa_pembayaran(?) AS sisa,
                fn_status_pembayaran(?) AS status,
                (SELECT COALESCE(SUM(harga), 0) FROM item_pesanan WHERE pesanan_id = ?) AS total_tagihan,
                (SELECT COALESCE(SUM(jumlah), 0) FROM pembayaran WHERE pesanan_id = ?) AS total_dibayar',
            [$pesanan->id, $pesanan->id, $pesanan->id, $pesanan->id]
        );

        return [
            'total_tagihan' => (float) $hasil->total_tagihan,
            'total_dibayar' => (float) $hasil->total_dibayar,
            'sisa' => (float) $hasil->sisa,
            'status' => (string) $hasil->status,
        ];
    }

    public function hitungSisa(Pesanan $pesanan): float
    {
        return $this->ringkasan($pesanan)['sisa'];
    }

    /**
     * Riwayat pembayaran pesanan, terbaru di atas.
     *
     * @return Collection<int, Pembayaran>
     */
    public function riwayat(Pesanan $pesanan): Collection
    {
        return $pesanan->pembayaran()
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Catat pembayaran (mendukung pembayaran bertahap/cicilan).
     * Pesanan dikunci selama pencatatan agar dua pembayaran bersamaan tidak melampaui tagihan.
     *
     * @param  array<string, mixed>  $data
     */
    public function catat(Pesanan $pesanan, array $data): Pembayaran
    {
        return DB::transaction(function () use ($pesanan, $data) {
            Pesanan::whereKey($pesanan->id)->lockForUpdate()->first();
            $pesanan->refresh();

            if ($pesanan->status === 'dibatalkan') {
                throw ValidationException::withMessages([
                    'pesanan' => 'Pembayaran tidak dapat dicatat karena pesanan sudah dibatalkan.',
                ]);
            }

            $sisa = $this->hitungSisa($pesanan);
            $jumlah = (float) $data['jumlah'];

            if ($sisa <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Pesanan ini sudah lunas, tidak ada sisa tagihan.',
                ]);
            }

            if (round($jumlah - $sisa, 2) > 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jumlah pembayaran melebihi sisa tagihan (Rp '.number_format($sisa, 0, ',', '.').').',
                ]);
            }

            return $pesanan->pembayaran()->create([
                'jumlah' => $jumlah,
                'metode' => $data['metode'],
                'tanggal_bayar' => ($data['tanggal_bayar'] ?? null) ?: now(),
                'catatan' => $data['catatan'] ?? null,
            ]);
        });
    }

    /**
     * Batalkan (hapus) catatan pembayaran.
     */
    public function batalkan(Pembayaran $pembayaran): void
    {
        $pembayaran->delete();
    }

    /**
     * Buat dokumen PDF invoice (A4) dari template khusus dompdf.
     */
    public function buatInvoicePdf(Pesanan $pesanan): DomPdf
    {
        return Pdf::loadView('pesanan.invoice-pdf', $this->dataInvoice($pesanan))
            ->setPaper('a4', 'portrait');
    }

    /**
     * Nama file unduhan invoice, mis. Invoice-ORD-20261003-0001.pdf.
     */
    public function namaFileInvoice(Pesanan $pesanan): string
    {
        return 'Invoice-'.preg_replace('/[^A-Za-z0-9\-]/', '-', $pesanan->nomor_pesanan).'.pdf';
    }

    /**
     * Data lengkap untuk invoice (halaman cetak web maupun JSON API).
     *
     * @return array<string, mixed>
     */
    public function dataInvoice(Pesanan $pesanan): array
    {
        $pesanan->load([
            'pelanggan',
            'itemPesanan' => fn ($query) => $query->orderBy('id'),
            'itemPesanan.jenisJasa',
            'itemPesanan.polaDetail.jenisPakaian',
            'pembayaran' => fn ($query) => $query->orderBy('tanggal_bayar')->orderBy('id'),
        ]);

        return [
            'usaha' => $this->masterDataService->getProfilUsaha(),
            'pesanan' => $pesanan,
            'ringkasan' => $this->ringkasan($pesanan),
            'dicetak_pada' => now(),
        ];
    }
}
