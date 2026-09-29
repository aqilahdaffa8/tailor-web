<?php

namespace App\Http\Controllers;

use App\Http\Requests\StokMasukRequest;
use App\Http\Requests\StoreBahanRequest;
use App\Http\Requests\StorePeralatanRequest;
use App\Http\Requests\UpdateBahanRequest;
use App\Http\Requests\UpdatePeralatanRequest;
use App\Models\Bahan;
use App\Models\KategoriBahan;
use App\Models\Peralatan;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    /**
     * Halaman utama Inventory (Tab Bahan & Tab Peralatan).
     */
    public function index(Request $request): View
    {
        $activeTab = $request->string('tab')->toString() ?: 'bahan';
        $search = $request->string('search')->toString();
        $kategoriId = $request->has('kategori_id') && $request->get('kategori_id') !== '' ? $request->integer('kategori_id') : null;
        $hanyaMenipis = $request->boolean('menipis');

        $bahanList = $this->inventoryService->getAllBahan($search, $kategoriId, $hanyaMenipis);
        $peralatanList = $this->inventoryService->getAllPeralatan($search, $request->string('kondisi')->toString() ?: null);
        $kategoriBahanList = KategoriBahan::orderBy('nama_kategori')->get();
        $bahanMenipisCount = $this->inventoryService->getBahanMenipis()->count();

        return view('inventory.index', [
            'activeTab' => $activeTab,
            'search' => $search,
            'kategoriId' => $kategoriId,
            'hanyaMenipis' => $hanyaMenipis,
            'bahanList' => $bahanList,
            'peralatanList' => $peralatanList,
            'kategoriBahanList' => $kategoriBahanList,
            'bahanMenipisCount' => $bahanMenipisCount,
        ]);
    }

    /**
     * Tambah bahan baru.
     */
    public function storeBahan(StoreBahanRequest $request): RedirectResponse
    {
        $bahan = $this->inventoryService->createBahan($request->validated());

        return redirect()->route('inventory.index', ['tab' => 'bahan'])
            ->with('success', "Bahan {$bahan->nama_bahan} berhasil ditambahkan.");
    }

    /**
     * Update data bahan.
     */
    public function updateBahan(UpdateBahanRequest $request, Bahan $bahan): RedirectResponse
    {
        $this->inventoryService->updateBahan($bahan, $request->validated());

        return redirect()->route('inventory.index', ['tab' => 'bahan'])
            ->with('success', "Data bahan {$bahan->nama_bahan} berhasil diperbarui.");
    }

    /**
     * Hapus bahan.
     */
    public function destroyBahan(Bahan $bahan): RedirectResponse
    {
        $nama = $bahan->nama_bahan;
        $this->inventoryService->deleteBahan($bahan);

        return redirect()->route('inventory.index', ['tab' => 'bahan'])
            ->with('success', "Bahan {$nama} berhasil dihapus.");
    }

    /**
     * Tambah stok bahan masuk.
     */
    public function stokMasuk(StokMasukRequest $request, Bahan $bahan): RedirectResponse
    {
        $this->inventoryService->tambahStokMasuk(
            $bahan,
            (float) $request->validated('jumlah'),
            $request->validated('keterangan')
        );

        return redirect()->route('inventory.index', ['tab' => 'bahan'])
            ->with('success', "Stok bahan {$bahan->nama_bahan} berhasil ditambah {$request->validated('jumlah')} {$bahan->satuan}.");
    }

    /**
     * Halaman riwayat mutasi stok bahan.
     */
    public function riwayatBahan(Bahan $bahan): View
    {
        $riwayat = $this->inventoryService->getRiwayatStok($bahan);

        return view('inventory.riwayat', [
            'bahan' => $bahan->load('kategoriBahan'),
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Tambah peralatan baru.
     */
    public function storePeralatan(StorePeralatanRequest $request): RedirectResponse
    {
        $alat = $this->inventoryService->createPeralatan($request->validated());

        return redirect()->route('inventory.index', ['tab' => 'peralatan'])
            ->with('success', "Peralatan {$alat->nama_alat} berhasil ditambahkan.");
    }

    /**
     * Update peralatan.
     */
    public function updatePeralatan(UpdatePeralatanRequest $request, Peralatan $peralatan): RedirectResponse
    {
        $this->inventoryService->updatePeralatan($peralatan, $request->validated());

        return redirect()->route('inventory.index', ['tab' => 'peralatan'])
            ->with('success', "Peralatan {$peralatan->nama_alat} berhasil diperbarui.");
    }

    /**
     * Hapus peralatan.
     */
    public function destroyPeralatan(Peralatan $peralatan): RedirectResponse
    {
        $nama = $peralatan->nama_alat;
        $this->inventoryService->deletePeralatan($peralatan);

        return redirect()->route('inventory.index', ['tab' => 'peralatan'])
            ->with('success', "Peralatan {$nama} berhasil dihapus.");
    }
}
