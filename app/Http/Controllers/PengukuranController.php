<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengukuranRequest;
use App\Http\Requests\UpdatePengukuranRequest;
use App\Models\Pelanggan;
use App\Models\Pengukuran;
use App\Services\PengukuranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengukuranController extends Controller
{
    public function __construct(private readonly PengukuranService $pengukuranService) {}

    /**
     * Halaman daftar semua data pengukuran.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $pengukuranList = $this->pengukuranService->getAll($search);

        return view('pengukuran.index', [
            'pengukuranList' => $pengukuranList,
            'search' => $search,
        ]);
    }

    /**
     * Form tambah pengukuran baru.
     */
    public function create(Request $request): View
    {
        $pelangganId = $request->integer('pelanggan_id');
        $selectedPelanggan = $pelangganId ? Pelanggan::find($pelangganId) : null;
        $latestUkuran = $pelangganId ? $this->pengukuranService->getLatestByPelanggan($pelangganId) : null;
        $daftarPelanggan = Pelanggan::orderBy('nama')->get();

        return view('pengukuran.create', [
            'selectedPelanggan' => $selectedPelanggan,
            'latestUkuran' => $latestUkuran,
            'daftarPelanggan' => $daftarPelanggan,
        ]);
    }

    /**
     * Simpan data pengukuran baru.
     */
    public function store(StorePengukuranRequest $request): RedirectResponse
    {
        $pengukuran = $this->pengukuranService->create($request->validated());

        return redirect()->route('pengukuran.show', $pengukuran)
            ->with('success', 'Data pengukuran tubuh berhasil disimpan.');
    }

    /**
     * Halaman detail data pengukuran tubuh.
     */
    public function show(Pengukuran $pengukuran): View
    {
        $detail = $this->pengukuranService->find($pengukuran->id);

        return view('pengukuran.show', [
            'pengukuran' => $detail,
        ]);
    }

    /**
     * Form edit data pengukuran tubuh.
     */
    public function edit(Pengukuran $pengukuran): View
    {
        return view('pengukuran.edit', [
            'pengukuran' => $pengukuran->load('pelanggan'),
        ]);
    }

    /**
     * Update data pengukuran.
     */
    public function update(UpdatePengukuranRequest $request, Pengukuran $pengukuran): RedirectResponse
    {
        $this->pengukuranService->update($pengukuran, $request->validated());

        return redirect()->route('pengukuran.show', $pengukuran)
            ->with('success', 'Data pengukuran tubuh berhasil diperbarui.');
    }

    /**
     * Hapus data pengukuran.
     */
    public function destroy(Pengukuran $pengukuran): RedirectResponse
    {
        $pelangganId = $pengukuran->pelanggan_id;
        $this->pengukuranService->delete($pengukuran);

        return redirect()->route('pelanggan.show', $pelangganId)
            ->with('success', 'Data pengukuran berhasil dihapus.');
    }
}
