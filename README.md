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

Buat dua database MySQL (satu untuk pengembangan, satu khusus test), lalu sesuaikan `DB_*` di `.env`:

```sql
CREATE DATABASE tailor_web  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE tailor_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
```

Jalankan: `composer run dev`.

## Test

Test memakai database `tailor_test` (diatur di `phpunit.xml`), terpisah dari data pengembangan.
Siapkan skemanya sekali:

```bash
DB_DATABASE=tailor_test php artisan migrate
php artisan test
```

## Arsitektur

Business logic hanya ada di `app/Services`. Controller web (`app/Http/Controllers`) dan API (`app/Http/Controllers/Api`) hanya memanggil service yang sama.
Rencana pengerjaan: `development_plan.md`. Spesifikasi: `Tailor_Management_System.md`. Arah desain: `DESIGN.md`.

## API

Login: `POST /api/login` (dibatasi 5 percobaan/menit) mengembalikan token Bearer. Daftar endpoint: `php artisan route:list --path=api`.
