<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterKeuanganRequest;
use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\Pengeluaran;
use App\Services\KeuanganService;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KeuanganController extends Controller
{
    public function __construct(
        private readonly KeuanganService $keuanganService,
        private readonly MasterDataService $masterDataService,
    ) {}

    /**
     * Halaman keuangan: tab Ringkasan, Pendapatan (otomatis dari pembayaran), dan Pengeluaran.
     */
    public function index(FilterKeuanganRequest $request): View
    {
        ['awal' => $awal, 'akhir' => $akhir] = $this->keuanganService->tentukanPeriode(
            $request->input('dari'),
            $request->input('sampai'),
        );

        $tab = $request->input('tab', 'ringkasan');
        $kategoriId = $request->filled('kategori_id') ? (int) $request->input('kategori_id') : null;

        return view('keuangan.index', [
            'tab' => $tab,
            'dari' => $awal->toDateString(),
            'sampai' => $akhir->toDateString(),
            'kategoriId' => $kategoriId,
            'ringkasan' => $this->keuanganService->ringkasan($awal, $akhir),
            'pendapatanList' => $tab === 'pendapatan'
                ? $this->keuanganService->daftarPendapatan($awal, $akhir)->withQueryString()
                : null,
            'pengeluaranList' => $tab === 'pengeluaran'
                ? $this->keuanganService->daftarPengeluaran($awal, $akhir, $kategoriId)->withQueryString()
                : null,
            'kategoriList' => $this->masterDataService->getAllKategoriPengeluaran(),
        ]);
    }

    public function storePengeluaran(StorePengeluaranRequest $request): RedirectResponse
    {
        $this->keuanganService->buatPengeluaran($request->validated());

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function updatePengeluaran(UpdatePengeluaranRequest $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->keuanganService->ubahPengeluaran($pengeluaran, $request->validated());

        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroyPengeluaran(Pengeluaran $pengeluaran): RedirectResponse
    {
        $this->keuanganService->hapusPengeluaran($pengeluaran);

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
