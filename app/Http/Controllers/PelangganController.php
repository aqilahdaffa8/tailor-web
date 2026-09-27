<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePelangganRequest;
use App\Http\Requests\UpdatePelangganRequest;
use App\Models\Pelanggan;
use App\Services\PelangganService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function __construct(private readonly PelangganService $pelangganService) {}

    /**
     * Daftar semua pelanggan dengan pencarian.
     */
    public function index(Request $request): View
    {
        $pelanggan = $this->pelangganService->getAll(
            search: $request->string('search')->toString(),
        );

        return view('pelanggan.index', [
            'pelanggan' => $pelanggan,
            'search' => $request->string('search')->toString(),
        ]);
    }

    /**
     * Form tambah pelanggan baru.
     */
    public function create(): View
    {
        return view('pelanggan.create');
    }

    /**
     * Simpan pelanggan baru.
     */
    public function store(StorePelangganRequest $request): RedirectResponse
    {
        $pelanggan = $this->pelangganService->create($request->validated());

        return redirect()->route('pelanggan.show', $pelanggan)
            ->with('success', "Pelanggan {$pelanggan->nama} berhasil ditambahkan.");
    }

    /**
     * Detail pelanggan & riwayat pesanan.
     */
    public function show(Pelanggan $pelanggan): View
    {
        $detail = $this->pelangganService->findWithRiwayat($pelanggan->id);

        return view('pelanggan.show', ['pelanggan' => $detail]);
    }

    /**
     * Form edit pelanggan.
     */
    public function edit(Pelanggan $pelanggan): View
    {
        return view('pelanggan.edit', ['pelanggan' => $pelanggan]);
    }

    /**
     * Update data pelanggan.
     */
    public function update(UpdatePelangganRequest $request, Pelanggan $pelanggan): RedirectResponse
    {
        $this->pelangganService->update($pelanggan, $request->validated());

        return redirect()->route('pelanggan.show', $pelanggan)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Hapus pelanggan.
     */
    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        $nama = $pelanggan->nama;
        $this->pelangganService->delete($pelanggan);

        return redirect()->route('pelanggan.index')
            ->with('success', "Pelanggan {$nama} berhasil dihapus.");
    }
}
