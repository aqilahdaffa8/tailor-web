# Tailor Management System (tailor-web)

Aplikasi manajemen usaha jahit (satu pemilik): pelanggan, pesanan, pola, pengukuran, progres, pembayaran, inventory, dan dashboard.
Satu aplikasi Laravel 13 melayani **web (Blade + Alpine.js + Tailwind)** dan **REST API (Sanctum)** untuk aplikasi mobile.

## Kebutuhan

- PHP 8.3+ dan Composer
- **MySQL 8+** (wajib: trigger, stored function, dan stored procedure; SQLite tidak didukung)
- Node.js 20+ dan npm

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database MySQL, lalu sesuaikan `DB_*` di `.env`:

```sql
CREATE DATABASE tailor_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

Jalankan: `composer run dev`.

## Test

```bash
php artisan test
```

Test berjalan di database yang sama dengan `.env` (belum ada database test terpisah) dan membutuhkan data seeder (`php artisan migrate --seed`).
Data yang dibuat test diberi awalan `ZZ-TEST-PESANAN` (atau periode Maret 2001 untuk test keuangan) dan dibersihkan otomatis.
Jangan menjalankan test pada database produksi; setelah aplikasi live, sebaiknya pindah ke database test terpisah.

## Reminder (scheduler)

Pengecekan reminder (deadline, terlambat, pembayaran, pesanan siap diambil, stok menipis) berjalan harian pukul 07:00 lewat Laravel Scheduler.
Di server, tambahkan satu entri cron:

```
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

Saat pengembangan, jalankan manual dengan `php artisan reminder:sinkronkan` atau tombol "Periksa sekarang" di panel bell.

## Konvensi API

Semua endpoint ada di bawah `/api` dan memakai token Bearer (Sanctum), kecuali `POST /api/login`.

**Respons sukses**

```json
{ "success": true, "message": "opsional", "data": {}, "meta": {} }
```

**Respons error** (selalu `success: false`)

| Status | Arti | Isi |
|--------|------|-----|
| 401 | Belum login / token tidak valid | `message` |
| 403 | Tidak punya akses | `message` |
| 404 | Data atau endpoint tidak ditemukan | `message` |
| 405 | Metode HTTP tidak diizinkan | `message` |
| 422 | Validasi gagal | `message`, `errors` (per field) |
| 429 | Terlalu banyak permintaan (login dibatasi 5/menit) | `message`, header `Retry-After` |
| 500 | Kesalahan server | `message` (detail disembunyikan saat `APP_DEBUG=false`) |

**Pagination** berlaku untuk list yang bisa membesar: pelanggan, pesanan, bahan, peralatan, tutorial, pengeluaran, dan pendapatan.
Parameter `?page=` dan `?per_page=` (1-100, default 15; tutorial 12) menghasilkan:

```json
"meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
```

List yang sengaja **tidak** dipaginasi karena kecil atau terbatas: master data (kategori, jenis jasa), daftar per induk (pengukuran per pelanggan, pembayaran per pesanan, pola per kategori, riwayat stok), widget dashboard (parameter `limit`), dan reminder aktif.

**Upload file** (PDF/gambar) memakai `multipart/form-data`. Untuk memperbarui data **dengan file**, kirim `POST` dengan field `_method=PUT`.

Pesan validasi berbahasa Indonesia (`lang/id`). Atur `APP_LOCALE=id` di `.env`.

## Arsitektur

Business logic hanya ada di `app/Services`. Controller web (`app/Http/Controllers`) dan API (`app/Http/Controllers/Api`) hanya memanggil service yang sama.
Rencana pengerjaan: `development_plan.md`. Spesifikasi: `Tailor_Management_System.md`. Arah desain: `DESIGN.md`.

## API

Login: `POST /api/login` (dibatasi 5 percobaan/menit) mengembalikan token Bearer. Daftar endpoint: `php artisan route:list --path=api`.
