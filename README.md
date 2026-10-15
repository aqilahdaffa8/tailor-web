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

## Arsitektur

Business logic hanya ada di `app/Services`. Controller web (`app/Http/Controllers`) dan API (`app/Http/Controllers/Api`) hanya memanggil service yang sama.
Rencana pengerjaan: `development_plan.md`. Spesifikasi: `Tailor_Management_System.md`. Arah desain: `DESIGN.md`.

## API

Login: `POST /api/login` (dibatasi 5 percobaan/menit) mengembalikan token Bearer. Daftar endpoint: `php artisan route:list --path=api`.
