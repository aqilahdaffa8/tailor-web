<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\RedirectResponse;

class PesananItemController extends Controller
{
    public function __construct(
        private readonly PesananService $pesananService,
        private readonly ProgressService $progressService,
    ) {}

    /**
     * Tambah item ke pesanan.
     */
    public function store(StoreItemPesananRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $this->pesananService->tambahItem($pesanan, $request->validated());

        return redirect()->route('pesanan.edit', $pesanan)
            ->with('success', 'Item pesanan berhasil ditambahkan.');
    }

    /**
     * Update data item.
     */
    public function update(UpdateItemPesananRequest $request, ItemPesanan $item): RedirectResponse
    {
        $this->pesananService->updateItem($item, $request->validated());

        return redirect()->route('pesanan.edit', $item->pesanan_id)
            ->with('success', 'Item pesanan berhasil diperbarui.');
    }

    /**
     * Hapus item dari pesanan.
     */
    public function destroy(ItemPesanan $item): RedirectResponse
    {
        $this->pesananService->hapusItem($item);

        return redirect()->route('pesanan.edit', $item->pesanan_id)
            ->with('success', 'Item pesanan berhasil dihapus.');
    }

    /**
     * Majukan atau mundurkan progress item.
     */
    public function progress(UpdateProgressRequest $request, ItemPesanan $item): RedirectResponse
    {
        $this->progressService->ubahProgress($item, $request->validated('aksi'));

        return redirect()->route('pesanan.show', $item->pesanan_id)
            ->with('success', 'Progress item pesanan berhasil diperbarui.');
    }

    /**
     * Catat pemakaian bahan pada item (stok terpotong otomatis).
     */
    public function storeBahan(StorePemakaianBahanRequest $request, ItemPesanan $item): RedirectResponse
    {
        $pemakaian = $this->pesananService->catatPemakaianBahan($item, $request->validated());

        return redirect()->route('pesanan.show', $item->pesanan_id)
            ->with('success', "Pemakaian {$pemakaian->bahan->nama_bahan} berhasil dicatat dan stok dipotong.");
    }

    /**
     * Hapus catatan pemakaian bahan (stok dikembalikan otomatis).
     */
    public function destroyBahan(PemakaianBahan $pemakaian): RedirectResponse
    {
        $pesananId = $pemakaian->itemPesanan->pesanan_id;

        $this->pesananService->hapusPemakaianBahan($pemakaian);

        return redirect()->route('pesanan.show', $pesananId)
            ->with('success', 'Pemakaian bahan dihapus dan stok telah dikembalikan.');
    }

    /**
     * Unggah foto referensi item.
     */
    public function storeFoto(StoreFotoReferensiRequest $request, ItemPesanan $item): RedirectResponse
    {
        $this->pesananService->uploadFoto($item, $request->file('foto'), $request->validated('keterangan'));

        return redirect()->route('pesanan.show', $item->pesanan_id)
            ->with('success', 'Foto referensi berhasil diunggah.');
    }

    /**
     * Hapus foto referensi item.
     */
    public function destroyFoto(FotoReferensi $foto): RedirectResponse
    {
        $pesananId = $foto->itemPesanan->pesanan_id;

        $this->pesananService->hapusFoto($foto);

        return redirect()->route('pesanan.show', $pesananId)
            ->with('success', 'Foto referensi berhasil dihapus.');
    }
}
