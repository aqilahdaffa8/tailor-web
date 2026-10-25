<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\JenisJasaController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\KeuanganController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PengukuranController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\PesananItemController;
use App\Http\Controllers\Api\PolaController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\TutorialController;
use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile REST API Routes (Sanctum Authenticated)
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('api.login');

// Endpoint Cron Webhook Eksternal (mis. cron-job.org)
Route::post('/internal/cron/reminder', [CronController::class, 'reminder'])
    ->middleware('throttle:10,1')
    ->name('api.cron.reminder');

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

        Route::get('kategori-tutorial', [KategoriController::class, 'tutorialIndex']);
        Route::post('kategori-tutorial', [KategoriController::class, 'tutorialStore']);
        Route::delete('kategori-tutorial/{id}', [KategoriController::class, 'tutorialDestroy']);

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

    // Dashboard
    Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
        Route::get('summary', [DashboardController::class, 'summary'])->name('summary');
        Route::get('pesanan-aktif', [DashboardController::class, 'pesananAktif'])->name('pesanan-aktif');
        Route::get('pesanan-mendekati-deadline', [DashboardController::class, 'pesananMendekatiDeadline'])->name('pesanan-mendekati-deadline');
        Route::get('pesanan-terlambat', [DashboardController::class, 'pesananTerlambat'])->name('pesanan-terlambat');
        Route::get('stok-menipis', [DashboardController::class, 'stokMenipis'])->name('stok-menipis');
    });

    // Pesanan Jahit
    Route::post('pesanan/{pesanan}/item', [PesananItemController::class, 'store'])->name('api.pesanan.item.store');
    Route::get('pesanan/{pesanan}/pembayaran', [PembayaranController::class, 'index'])->name('api.pesanan.pembayaran.index');
    Route::post('pesanan/{pesanan}/pembayaran', [PembayaranController::class, 'store'])->name('api.pesanan.pembayaran.store');
    Route::get('pesanan/{pesanan}/invoice', [PembayaranController::class, 'invoice'])->name('api.pesanan.invoice');
    Route::get('pesanan/{pesanan}/invoice/pdf', [PembayaranController::class, 'invoicePdf'])->name('api.pesanan.invoice.pdf');
    Route::delete('pembayaran/{pembayaran}', [PembayaranController::class, 'destroy'])->name('api.pembayaran.destroy');
    Route::put('pesanan/item/{item}', [PesananItemController::class, 'update'])->name('api.pesanan.item.update');
    Route::delete('pesanan/item/{item}', [PesananItemController::class, 'destroy'])->name('api.pesanan.item.destroy');
    Route::put('pesanan/item/{item}/progress', [PesananItemController::class, 'progress'])->name('api.pesanan.item.progress');
    Route::post('pesanan/item/{item}/bahan', [PesananItemController::class, 'storeBahan'])->name('api.pesanan.item.bahan.store');
    Route::delete('pesanan/pemakaian/{pemakaian}', [PesananItemController::class, 'destroyBahan'])->name('api.pesanan.pemakaian.destroy');
    Route::post('pesanan/item/{item}/foto', [PesananItemController::class, 'storeFoto'])->name('api.pesanan.item.foto.store');
    Route::delete('pesanan/foto/{foto}', [PesananItemController::class, 'destroyFoto'])->name('api.pesanan.foto.destroy');
    Route::apiResource('pesanan', PesananController::class)->names([
        'index' => 'api.pesanan.index',
        'store' => 'api.pesanan.store',
        'show' => 'api.pesanan.show',
        'update' => 'api.pesanan.update',
        'destroy' => 'api.pesanan.destroy',
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

    // Pengukuran Pelanggan
    Route::get('pelanggan/{id}/pengukuran', [PengukuranController::class, 'indexByPelanggan']);
    Route::get('pelanggan/{id}/pengukuran/latest', [PengukuranController::class, 'latestByPelanggan']);
    Route::apiResource('pengukuran', PengukuranController::class)->names([
        'index' => 'api.pengukuran.index',
        'store' => 'api.pengukuran.store',
        'show' => 'api.pengukuran.show',
        'update' => 'api.pengukuran.update',
        'destroy' => 'api.pengukuran.destroy',
    ]);

    // Tutorial / Knowledge Base
    Route::apiResource('tutorial', TutorialController::class)->names([
        'index' => 'api.tutorial.index',
        'store' => 'api.tutorial.store',
        'show' => 'api.tutorial.show',
        'update' => 'api.tutorial.update',
        'destroy' => 'api.tutorial.destroy',
    ]);

    // Reminder
    Route::get('reminders', [ReminderController::class, 'index'])->name('api.reminders.index');
    Route::put('reminders/{reminder}/dismiss', [ReminderController::class, 'dismiss'])->name('api.reminders.dismiss');

    // Keuangan
    Route::prefix('keuangan')->name('api.keuangan.')->group(function () {
        Route::get('ringkasan', [KeuanganController::class, 'ringkasan'])->name('ringkasan');
        Route::get('pendapatan', [KeuanganController::class, 'pendapatan'])->name('pendapatan');
    });
    Route::apiResource('pengeluaran', PengeluaranController::class)->except('show')->names([
        'index' => 'api.pengeluaran.index',
        'store' => 'api.pengeluaran.store',
        'update' => 'api.pengeluaran.update',
        'destroy' => 'api.pengeluaran.destroy',
    ]);

    // Inventory (Bahan & Peralatan)
    Route::get('bahan', [InventoryController::class, 'bahanIndex']);
    Route::post('bahan', [InventoryController::class, 'bahanStore']);
    Route::get('bahan/{bahan}', [InventoryController::class, 'bahanShow']);
    Route::put('bahan/{bahan}', [InventoryController::class, 'bahanUpdate']);
    Route::delete('bahan/{bahan}', [InventoryController::class, 'bahanDestroy']);
    Route::post('bahan/{bahan}/stok-masuk', [InventoryController::class, 'stokMasuk']);
    Route::get('bahan/{bahan}/riwayat', [InventoryController::class, 'bahanRiwayat']);

    Route::get('peralatan', [InventoryController::class, 'peralatanIndex'])->name('api.peralatan.index');
    Route::post('peralatan', [InventoryController::class, 'peralatanStore'])->name('api.peralatan.store');
    Route::get('peralatan/{peralatan}', [InventoryController::class, 'peralatanShow'])->name('api.peralatan.show');
    Route::put('peralatan/{peralatan}', [InventoryController::class, 'peralatanUpdate'])->name('api.peralatan.update');
    Route::delete('peralatan/{peralatan}', [InventoryController::class, 'peralatanDestroy'])->name('api.peralatan.destroy');
});
