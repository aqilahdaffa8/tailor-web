<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJenisPakaianRequest;
use App\Http\Requests\StorePolaDetailRequest;
use App\Http\Requests\UpdatePolaDetailRequest;
use App\Models\JenisPakaian;
use App\Models\KategoriPola;
use App\Models\PolaDetail;
use App\Services\PolaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolaController extends Controller
{
    public function __construct(private readonly PolaService $polaService) {}

    /**
     * Halaman utama Library Pola (Daftar Kategori & Jenis Pakaian).
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kategoriList = $this->polaService->getAllKategori($search);
        $allKategori = KategoriPola::orderBy('nama_kategori')->get();

        return view('pola.index', [
            'kategoriList' => $kategoriList,
            'allKategori' => $allKategori,
            'search' => $search,
        ]);
    }

    /**
     * Tambah jenis pakaian baru dalam suatu kategori.
     */
    public function storeJenis(StoreJenisPakaianRequest $request): RedirectResponse
    {
        $jenis = $this->polaService->createJenisPakaian($request->validated());

        return redirect()->route('pola.show', $jenis)
            ->with('success', "Jenis pakaian {$jenis->nama_jenis} berhasil ditambahkan.");
    }

    /**
     * Hapus jenis pakaian.
     */
    public function destroyJenis(JenisPakaian $jenisPakaian): RedirectResponse
    {
        $nama = $jenisPakaian->nama_jenis;
        $this->polaService->deleteJenisPakaian($jenisPakaian);

        return redirect()->route('pola.index')
            ->with('success', "Jenis pakaian {$nama} dan seluruh polanya berhasil dihapus.");
    }

    /**
     * Halaman detail jenis pakaian dan daftar pola per size.
     */
    public function show(JenisPakaian $jenisPakaian): View
    {
        $detail = $this->polaService->findJenisPakaian($jenisPakaian->id);

        return view('pola.show', [
            'jenisPakaian' => $detail,
        ]);
    }

    /**
     * Form tambah pola detail (size baru) untuk jenis pakaian tertentu.
     */
    public function createDetail(JenisPakaian $jenisPakaian): View
    {
        return view('pola.create-detail', [
            'jenisPakaian' => $jenisPakaian->load('kategoriPola'),
        ]);
    }

    /**
     * Simpan pola detail baru.
     */
    public function storeDetail(StorePolaDetailRequest $request): RedirectResponse
    {
        $pola = $this->polaService->createPolaDetail(
            $request->validated(),
            $request->file('foto_pola'),
            $request->file('file_pola')
        );

        return redirect()->route('pola.show', $pola->jenis_pakaian_id)
            ->with('success', "Pola size {$pola->size} berhasil ditambahkan.");
    }

    /**
     * Form edit pola detail.
     */
    public function editDetail(PolaDetail $polaDetail): View
    {
        return view('pola.edit-detail', [
            'polaDetail' => $polaDetail->load('jenisPakaian.kategoriPola'),
        ]);
    }

    /**
     * Update pola detail.
     */
    public function updateDetail(UpdatePolaDetailRequest $request, PolaDetail $polaDetail): RedirectResponse
    {
        $this->polaService->updatePolaDetail(
            $polaDetail,
            $request->validated(),
            $request->file('foto_pola'),
            $request->file('file_pola')
        );

        return redirect()->route('pola.show', $polaDetail->jenis_pakaian_id)
            ->with('success', "Pola size {$polaDetail->size} berhasil diperbarui.");
    }

    /**
     * Hapus pola detail.
     */
    public function destroyDetail(PolaDetail $polaDetail): RedirectResponse
    {
        $jenisId = $polaDetail->jenis_pakaian_id;
        $size = $polaDetail->size;
        $this->polaService->deletePolaDetail($polaDetail);

        return redirect()->route('pola.show', $jenisId)
            ->with('success', "Pola size {$size} berhasil dihapus.");
    }

    /**
     * Hapus foto pola secara instan.
     */
    public function deleteFoto(PolaDetail $polaDetail, Request $request)
    {
        $this->polaService->deleteFotoPola($polaDetail);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Foto pola berhasil dihapus.']);
        }

        return back()->with('success', 'Foto pola berhasil dihapus.');
    }

    /**
     * Hapus file dokumen pola secara instan.
     */
    public function deleteFile(PolaDetail $polaDetail, Request $request)
    {
        $this->polaService->deleteFilePola($polaDetail);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'File pola berhasil dihapus.']);
        }

        return back()->with('success', 'File dokumen pola berhasil dihapus.');
    }
}
