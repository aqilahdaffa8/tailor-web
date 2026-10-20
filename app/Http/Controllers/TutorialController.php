<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterTutorialRequest;
use App\Http\Requests\StoreTutorialRequest;
use App\Http\Requests\UpdateTutorialRequest;
use App\Models\Tutorial;
use App\Services\TutorialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutorialController extends Controller
{
    public function __construct(private readonly TutorialService $tutorialService) {}

    public function index(FilterTutorialRequest $request): View
    {
        return view('tutorial.index', [
            'tutorial' => $this->tutorialService->daftar(
                $request->input('kategori'),
                $request->input('tipe'),
                $request->input('search'),
            )->withQueryString(),
            'kategoriList' => $this->tutorialService->daftarKategori(),
            'kategoriModelList' => $this->tutorialService->daftarKategoriModel(),
            'kategori' => $request->input('kategori'),
            'tipe' => $request->input('tipe'),
            'search' => $request->input('search'),
        ]);
    }

    public function create(): View
    {
        return view('tutorial.create', [
            'kategoriList' => $this->tutorialService->daftarKategori(),
            'kategoriModelList' => $this->tutorialService->daftarKategoriModel(),
        ]);
    }

    public function store(StoreTutorialRequest $request): RedirectResponse
    {
        $tutorial = $this->tutorialService->buat($request->validated());

        return redirect()->route('tutorial.show', $tutorial)
            ->with('success', 'Tutorial berhasil ditambahkan.');
    }

    public function show(Tutorial $tutorial): View
    {
        return view('tutorial.show', ['tutorial' => $tutorial]);
    }

    public function edit(Tutorial $tutorial): View
    {
        return view('tutorial.edit', [
            'tutorial' => $tutorial,
            'kategoriList' => $this->tutorialService->daftarKategori(),
            'kategoriModelList' => $this->tutorialService->daftarKategoriModel(),
        ]);
    }

    public function update(UpdateTutorialRequest $request, Tutorial $tutorial): RedirectResponse
    {
        $this->tutorialService->ubah($tutorial, $request->validated());

        return redirect()->route('tutorial.show', $tutorial)
            ->with('success', 'Tutorial berhasil diperbarui.');
    }

    public function destroy(Tutorial $tutorial): RedirectResponse
    {
        $this->tutorialService->hapus($tutorial);

        return redirect()->route('tutorial.index')
            ->with('success', 'Tutorial berhasil dihapus.');
    }

    /**
     * Unduh file PDF tutorial.
     */
    public function unduh(Tutorial $tutorial): StreamedResponse
    {
        abort_unless(
            $tutorial->tipe === 'pdf'
                && $tutorial->file_or_url
                && Storage::disk('public')->exists($tutorial->file_or_url),
            404
        );

        return Storage::disk('public')->download($tutorial->file_or_url, Str::slug($tutorial->judul).'.pdf');
    }
}
