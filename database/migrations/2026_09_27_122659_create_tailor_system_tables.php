<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pelanggan
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_hp', 30)->nullable()->index();
            $table->text('alamat')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Jenis Jasa & Template Tahapan
        Schema::create('jenis_jasa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jasa');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_default', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('template_tahapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_jasa_id')->constrained('jenis_jasa')->cascadeOnDelete();
            $table->string('nama_tahapan');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        // 3. Library Pola (Kategori -> Jenis Pakaian -> Detail Pola per Size)
        Schema::create('kategori_pola', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_pakaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_pola_id')->constrained('kategori_pola')->cascadeOnDelete();
            $table->string('nama_jenis');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('pola_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_pakaian_id')->constrained('jenis_pakaian')->cascadeOnDelete();
            $table->string('size', 20); // S, M, L, XL, XXL, Custom
            $table->json('data_ukuran')->nullable();
            $table->string('foto_pola')->nullable();
            $table->string('file_pola')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 4. Pengukuran Pelanggan
        Schema::create('pengukuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->date('tanggal_ukur');
            $table->json('data_ukuran');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 5. Pesanan Header & Items
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pesanan', 50)->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->restrictOnDelete();
            $table->date('tanggal_pesan');
            $table->date('deadline')->index();
            $table->text('catatan')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'diambil', 'dibatalkan'])->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('item_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $table->foreignId('jenis_jasa_id')->constrained('jenis_jasa')->restrictOnDelete();
            $table->foreignId('pola_detail_id')->nullable()->constrained('pola_detail')->nullOnDelete();
            $table->foreignId('pengukuran_id')->nullable()->constrained('pengukuran')->nullOnDelete();
            $table->text('detail_pekerjaan')->nullable();
            $table->decimal('harga', 12, 2)->default(0);
            $table->date('deadline')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status_item', 50)->default('menunggu');
            $table->timestamps();
        });

        // 6. Progress Item Pesanan (Snapshot dari Template Tahapan)
        Schema::create('progress_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_pesanan_id')->constrained('item_pesanan')->cascadeOnDelete();
            $table->string('nama_tahapan');
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 7. Pembayaran / POS Jasa (Multiple Partial Payments)
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->string('metode', 50)->default('tunai'); // tunai, transfer, qris
            $table->dateTime('tanggal_bayar');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 8. Inventory Bahan & Pemakaian
        Schema::create('kategori_bahan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->timestamps();
        });

        Schema::create('bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_bahan_id')->constrained('kategori_bahan')->restrictOnDelete();
            $table->string('nama_bahan');
            $table->string('satuan', 30); // meter, gulung, lusin, pcs
            $table->decimal('stok', 10, 2)->default(0);
            $table->decimal('stok_minimum', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('pemakaian_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_pesanan_id')->constrained('item_pesanan')->cascadeOnDelete();
            $table->foreignId('bahan_id')->constrained('bahan')->restrictOnDelete();
            $table->decimal('jumlah', 10, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_id')->constrained('bahan')->cascadeOnDelete();
            $table->enum('jenis', ['masuk', 'keluar', 'penyesuaian']);
            $table->decimal('jumlah', 10, 2);
            $table->decimal('stok_sebelum', 10, 2);
            $table->decimal('stok_sesudah', 10, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 9. Peralatan
        Schema::create('peralatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_alat');
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('kondisi', 50)->default('baik'); // baik, rusak_ringan, rusak_berat
            $table->date('tanggal_beli')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 10. Keuangan - Pengeluaran
        Schema::create('kategori_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->timestamps();
        });

        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_pengeluaran_id')->nullable()->constrained('kategori_pengeluaran')->nullOnDelete();
            $table->decimal('nominal', 12, 2);
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 11. Foto Referensi (Item Pesanan)
        Schema::create('foto_referensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_pesanan_id')->constrained('item_pesanan')->cascadeOnDelete();
            $table->string('path_foto');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 12. Tutorial / Knowledge Base
        Schema::create('tutorial', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori', 50);
            $table->enum('tipe', ['artikel', 'video', 'pdf']);
            $table->longText('konten')->nullable();
            $table->text('file_or_url')->nullable();
            $table->timestamps();
        });

        // 13. Reminders
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe', 50); // deadline, terlambat, pembayaran, stok
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_tipe', 50)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 14. Pengaturan Sistem (Key-Value)
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('tutorial');
        Schema::dropIfExists('foto_referensi');
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('kategori_pengeluaran');
        Schema::dropIfExists('peralatan');
        Schema::dropIfExists('riwayat_stok');
        Schema::dropIfExists('pemakaian_bahan');
        Schema::dropIfExists('bahan');
        Schema::dropIfExists('kategori_bahan');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('progress_item');
        Schema::dropIfExists('item_pesanan');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('pengukuran');
        Schema::dropIfExists('pola_detail');
        Schema::dropIfExists('jenis_pakaian');
        Schema::dropIfExists('kategori_pola');
        Schema::dropIfExists('template_tahapan');
        Schema::dropIfExists('jenis_jasa');
        Schema::dropIfExists('pelanggan');
    }
};
