<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJenisJasaRequest;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateJenisJasaRequest;
use App\Http\Requests\UpdateProfilUsahaRequest;
use App\Models\JenisJasa;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}

    public function index(): View
    {
        return view('pengaturan.index', [
            'daftarJasa' => $this->masterDataService->getAllJenisJasa(),
            'kategoriPola' => $this->masterDataService->getAllKategoriPola(),
            'kategoriBahan' => $this->masterDataService->getAllKategoriBahan(),
            'kategoriPengeluaran' => $this->masterDataService->getAllKategoriPengeluaran(),
            'kategoriTutorial' => $this->masterDataService->getAllKategoriTutorial(),
            'profil' => $this->masterDataService->getProfilUsaha(),
        ]);
    }

    public function storeJenisJasa(StoreJenisJasaRequest $request): RedirectResponse
    {
        $this->masterDataService->createJenisJasa($request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'jasa'])
            ->with('success', 'Jenis jasa dan tahapan berhasil ditambahkan.');
    }

    public function updateJenisJasa(UpdateJenisJasaRequest $request, JenisJasa $jenisJasa): RedirectResponse
    {
        $this->masterDataService->updateJenisJasa($jenisJasa, $request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'jasa'])
            ->with('success', 'Jenis jasa dan tahapan berhasil diperbarui.');
    }

    public function deleteJenisJasa(JenisJasa $jenisJasa): RedirectResponse
    {
        $this->masterDataService->deleteJenisJasa($jenisJasa);

        return redirect()->route('pengaturan.index', ['tab' => 'jasa'])
            ->with('success', 'Jenis jasa berhasil dihapus.');
    }

    public function storeKategoriPola(StoreKategoriRequest $request): RedirectResponse
    {
        $this->masterDataService->createKategoriPola($request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'pola'])
            ->with('success', 'Kategori pola berhasil ditambahkan.');
    }

    public function updateKategoriPola(StoreKategoriRequest $request, int $id): RedirectResponse
    {
        $this->masterDataService->updateKategoriPola($id, $request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'pola'])
            ->with('success', 'Kategori pola berhasil diperbarui.');
    }

    public function deleteKategoriPola(int $id): RedirectResponse
    {
        $this->masterDataService->deleteKategoriPola($id);

        return redirect()->route('pengaturan.index', ['tab' => 'pola'])
            ->with('success', 'Kategori pola berhasil dihapus.');
    }

    public function storeKategoriBahan(StoreKategoriRequest $request): RedirectResponse
    {
        $this->masterDataService->createKategoriBahan($request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'bahan'])
            ->with('success', 'Kategori bahan berhasil ditambahkan.');
    }

    public function updateKategoriBahan(StoreKategoriRequest $request, int $id): RedirectResponse
    {
        $this->masterDataService->updateKategoriBahan($id, $request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'bahan'])
            ->with('success', 'Kategori bahan berhasil diperbarui.');
    }

    public function deleteKategoriBahan(int $id): RedirectResponse
    {
        $this->masterDataService->deleteKategoriBahan($id);

        return redirect()->route('pengaturan.index', ['tab' => 'bahan'])
            ->with('success', 'Kategori bahan berhasil dihapus.');
    }

    public function storeKategoriPengeluaran(StoreKategoriRequest $request): RedirectResponse
    {
        $this->masterDataService->createKategoriPengeluaran($request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'pengeluaran'])
            ->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function updateKategoriPengeluaran(StoreKategoriRequest $request, int $id): RedirectResponse
    {
        $this->masterDataService->updateKategoriPengeluaran($id, $request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'pengeluaran'])
            ->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    public function deleteKategoriPengeluaran(int $id): RedirectResponse
    {
        $this->masterDataService->deleteKategoriPengeluaran($id);

        return redirect()->route('pengaturan.index', ['tab' => 'pengeluaran'])
            ->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }

    public function storeKategoriTutorial(StoreKategoriRequest $request): RedirectResponse
    {
        $this->masterDataService->createKategoriTutorial($request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'tutorial'])
            ->with('success', 'Kategori tutorial berhasil ditambahkan.');
    }

    public function updateKategoriTutorial(StoreKategoriRequest $request, int $id): RedirectResponse
    {
        $this->masterDataService->updateKategoriTutorial($id, $request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'tutorial'])
            ->with('success', 'Kategori tutorial berhasil diperbarui.');
    }

    public function deleteKategoriTutorial(int $id): RedirectResponse
    {
        $this->masterDataService->deleteKategoriTutorial($id);

        return redirect()->route('pengaturan.index', ['tab' => 'tutorial'])
            ->with('success', 'Kategori tutorial berhasil dihapus.');
    }

    public function updateProfil(UpdateProfilUsahaRequest $request): RedirectResponse
    {
        $this->masterDataService->updateProfilUsaha($request->validated());

        return redirect()->route('pengaturan.index', ['tab' => 'profil'])
            ->with('success', 'Profil usaha dan format invoice berhasil diperbarui.');
    }
}
