<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JenisJasaController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\PolaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile REST API Routes (Sanctum Authenticated)
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Protected Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // Master Data & Settings (Mobile Access)
    Route::prefix('master')->group(function () {
        Route::apiResource('jenis-jasa', JenisJasaController::class);

        Route::get('kategori-pola', [KategoriController::class, 'polaIndex']);
        Route::post('kategori-pola', [KategoriController::class, 'polaStore']);
        Route::delete('kategori-pola/{id}', [KategoriController::class, 'polaDestroy']);

        Route::get('kategori-bahan', [KategoriController::class, 'bahanIndex']);
        Route::post('kategori-bahan', [KategoriController::class, 'bahanStore']);
        Route::delete('kategori-bahan/{id}', [KategoriController::class, 'bahanDestroy']);

        Route::get('kategori-pengeluaran', [KategoriController::class, 'pengeluaranIndex']);
        Route::post('kategori-pengeluaran', [KategoriController::class, 'pengeluaranStore']);
        Route::delete('kategori-pengeluaran/{id}', [KategoriController::class, 'pengeluaranDestroy']);

        Route::get('pengaturan', [PengaturanController::class, 'getProfil']);
        Route::put('pengaturan', [PengaturanController::class, 'updateProfil']);
    });

    // Pelanggan
    Route::apiResource('pelanggan', PelangganController::class)->names([
        'index' => 'api.pelanggan.index',
        'store' => 'api.pelanggan.store',
        'show' => 'api.pelanggan.show',
        'update' => 'api.pelanggan.update',
        'destroy' => 'api.pelanggan.destroy',
    ]);

    // Library Pola
    Route::prefix('pola')->group(function () {
        Route::get('kategori', [PolaController::class, 'kategoriIndex']);
        Route::get('kategori/{id}/jenis-pakaian', [PolaController::class, 'jenisPakaianIndex']);
        Route::post('jenis-pakaian', [PolaController::class, 'jenisPakaianStore']);
        Route::put('jenis-pakaian/{jenisPakaian}', [PolaController::class, 'jenisPakaianUpdate']);
        Route::delete('jenis-pakaian/{jenisPakaian}', [PolaController::class, 'jenisPakaianDestroy']);

        Route::get('jenis-pakaian/{id}/detail', [PolaController::class, 'detailIndex']);
        Route::get('detail/{id}', [PolaController::class, 'detailShow']);
        Route::post('detail', [PolaController::class, 'detailStore']);
        Route::put('detail/{polaDetail}', [PolaController::class, 'detailUpdate']);
        Route::post('detail/{polaDetail}', [PolaController::class, 'detailUpdate']);
        Route::delete('detail/{polaDetail}', [PolaController::class, 'detailDestroy']);
        Route::delete('detail/{polaDetail}/foto', [PolaController::class, 'deleteFoto']);
        Route::delete('detail/{polaDetail}/file', [PolaController::class, 'deleteFile']);
    });
});
