<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdatePesananRequest;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function __construct(private readonly PesananService $pesananService) {}

    /**
     * Daftar pesanan dengan filter status, pelanggan, deadline, dan pencarian.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'pelanggan_id' => $request->integer('pelanggan_id') ?: null,
            'deadline' => $request->string('deadline')->toString(),
            'pembayaran' => $request->string('pembayaran')->toString(),
        ];

        return view('pesanan.index', [
            'pesananList' => $this->pesananService->getAll($filters),
            'filters' => $filters,
            'daftarPelanggan' => $this->pesananService->getOpsiForm()['daftarPelanggan'],
        ]);
    }

    /**
     * Form pembuatan pesanan baru.
     */
    public function create(Request $request): View
    {
        return view('pesanan.create', array_merge($this->pesananService->getOpsiForm(), [
            'pelangganTerpilih' => $request->integer('pelanggan_id') ?: null,
        ]));
    }

    /**
     * Simpan pesanan baru (header + item + progress).
     */
    public function store(StorePesananRequest $request): RedirectResponse
    {
        $pesanan = $this->pesananService->create($request->validated());

        return redirect()->route('pesanan.show', $pesanan)
            ->with('success', "Pesanan {$pesanan->nomor_pesanan} berhasil dibuat.");
    }

    /**
     * Detail lengkap pesanan.
     */
    public function show(Pesanan $pesanan): View
    {
        return view('pesanan.show', [
            'pesanan' => $this->pesananService->find($pesanan->id),
            'daftarBahan' => $this->pesananService->getDaftarBahan(),
        ]);
    }

    /**
     * Form edit header pesanan, item, dan penambahan item.
     */
    public function edit(Pesanan $pesanan): View
    {
        return view('pesanan.edit', array_merge($this->pesananService->getOpsiForm(), [
            'pesanan' => $this->pesananService->find($pesanan->id),
        ]));
    }

    /**
     * Update header pesanan.
     */
    public function update(UpdatePesananRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $this->pesananService->update($pesanan, $request->validated());

        return redirect()->route('pesanan.show', $pesanan)
            ->with('success', 'Data pesanan berhasil diperbarui.');
    }

    /**
     * Batalkan pesanan.
     */
    public function destroy(Pesanan $pesanan): RedirectResponse
    {
        $this->pesananService->batalkan($pesanan);

        return redirect()->route('pesanan.show', $pesanan)
            ->with('success', "Pesanan {$pesanan->nomor_pesanan} telah dibatalkan.");
    }
}
