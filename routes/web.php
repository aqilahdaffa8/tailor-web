<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PengukuranController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PesananItemController;
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

    // Pesanan Jahit
    Route::prefix('pesanan')->name('pesanan.')->group(function () {
        Route::post('/{pesanan}/item', [PesananItemController::class, 'store'])->name('item.store');
        Route::put('/item/{item}', [PesananItemController::class, 'update'])->name('item.update');
        Route::delete('/item/{item}', [PesananItemController::class, 'destroy'])->name('item.destroy');
        Route::put('/item/{item}/progress', [PesananItemController::class, 'progress'])->name('item.progress');
        Route::post('/item/{item}/bahan', [PesananItemController::class, 'storeBahan'])->name('item.bahan.store');
        Route::delete('/pemakaian/{pemakaian}', [PesananItemController::class, 'destroyBahan'])->name('pemakaian.destroy');
        Route::post('/item/{item}/foto', [PesananItemController::class, 'storeFoto'])->name('item.foto.store');
        Route::delete('/foto/{foto}', [PesananItemController::class, 'destroyFoto'])->name('foto.destroy');
    });
    Route::resource('pesanan', PesananController::class);

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

    // Pengukuran Pelanggan
    Route::resource('pengukuran', PengukuranController::class);

    // Inventory (Bahan & Peralatan)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/bahan', [InventoryController::class, 'storeBahan'])->name('bahan.store');
        Route::put('/bahan/{bahan}', [InventoryController::class, 'updateBahan'])->name('bahan.update');
        Route::delete('/bahan/{bahan}', [InventoryController::class, 'destroyBahan'])->name('bahan.destroy');
        Route::post('/bahan/{bahan}/stok-masuk', [InventoryController::class, 'stokMasuk'])->name('bahan.stok-masuk');
        Route::get('/bahan/{bahan}/riwayat', [InventoryController::class, 'riwayatBahan'])->name('bahan.riwayat');

        Route::post('/peralatan', [InventoryController::class, 'storePeralatan'])->name('peralatan.store');
        Route::put('/peralatan/{peralatan}', [InventoryController::class, 'updatePeralatan'])->name('peralatan.update');
        Route::delete('/peralatan/{peralatan}', [InventoryController::class, 'destroyPeralatan'])->name('peralatan.destroy');
    });
});
