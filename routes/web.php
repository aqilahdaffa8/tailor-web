<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PolaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Pengaturan & Master Data
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::post('/jasa', [PengaturanController::class, 'storeJenisJasa'])->name('jasa.store');
        Route::put('/jasa/{jenisJasa}', [PengaturanController::class, 'updateJenisJasa'])->name('jasa.update');
        Route::delete('/jasa/{jenisJasa}', [PengaturanController::class, 'deleteJenisJasa'])->name('jasa.destroy');

        Route::post('/kategori-pola', [PengaturanController::class, 'storeKategoriPola'])->name('pola.store');
        Route::delete('/kategori-pola/{id}', [PengaturanController::class, 'deleteKategoriPola'])->name('pola.destroy');

        Route::post('/kategori-bahan', [PengaturanController::class, 'storeKategoriBahan'])->name('bahan.store');
        Route::delete('/kategori-bahan/{id}', [PengaturanController::class, 'deleteKategoriBahan'])->name('bahan.destroy');

        Route::post('/kategori-pengeluaran', [PengaturanController::class, 'storeKategoriPengeluaran'])->name('pengeluaran.store');
        Route::delete('/kategori-pengeluaran/{id}', [PengaturanController::class, 'deleteKategoriPengeluaran'])->name('pengeluaran.destroy');

        Route::put('/profil', [PengaturanController::class, 'updateProfil'])->name('profil.update');
    });

    // Pelanggan
    Route::resource('pelanggan', PelangganController::class);

    // Library Pola
    Route::prefix('pola')->name('pola.')->group(function () {
        Route::get('/', [PolaController::class, 'index'])->name('index');
        Route::post('/jenis', [PolaController::class, 'storeJenis'])->name('jenis.store');
        Route::delete('/jenis/{jenisPakaian}', [PolaController::class, 'destroyJenis'])->name('jenis.destroy');
        Route::get('/jenis/{jenisPakaian}', [PolaController::class, 'show'])->name('show');

        Route::get('/jenis/{jenisPakaian}/detail/create', [PolaController::class, 'createDetail'])->name('detail.create');
        Route::post('/detail', [PolaController::class, 'storeDetail'])->name('detail.store');
        Route::get('/detail/{polaDetail}/edit', [PolaController::class, 'editDetail'])->name('detail.edit');
        Route::put('/detail/{polaDetail}', [PolaController::class, 'updateDetail'])->name('detail.update');
        Route::delete('/detail/{polaDetail}', [PolaController::class, 'destroyDetail'])->name('detail.destroy');
        Route::delete('/detail/{polaDetail}/foto', [PolaController::class, 'deleteFoto'])->name('detail.delete-foto');
        Route::delete('/detail/{polaDetail}/file', [PolaController::class, 'deleteFile'])->name('detail.delete-file');
    });
});
