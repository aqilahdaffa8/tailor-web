# Tailor Management System — Rencana Deployment Produksi

> Dokumen panduan dan checklist deployment aplikasi Tailor Management System (`tailor-web`) ke server VPS (Ubuntu 24.04 / 22.04 LTS, Nginx, PHP 8.3-FPM, MySQL 8).

---

## Arsitektur Server Produksi

```text
               INTERNET
                  │ (Port 80/443 HTTPS)
            ┌─────▼─────┐
            │   Nginx   │ (Reverse Proxy, SSL, Static Files, Security Headers)
            └─────┬─────┘
                  │ (FastCGI Unix Socket)
            ┌─────▼─────────┐
            │  PHP 8.3-FPM  │ (Laravel 13: Web Blade + REST API Sanctum)
            └─────┬─────────┘
                  │ (Port 3306)
            ┌─────▼─────────┐
            │    MySQL 8    │ (InnoDB, Triggers, Stored Functions & Procedures)
            └───────────────┘
```

Prasyarat Arsitektur:
- **MySQL 8.0+ Wajib**: Aplikasi mengandalkan database trigger, stored function, dan stored procedure. SQLite tidak didukung.
- **PHP 8.3+**: Dilengkapi ekstensi `php-fpm`, `php-mysql`, `php-intl`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-bcmath`.
- **Node.js 20+ & npm**: Untuk kompilasi aset frontend Tailwind CSS & Alpine.js via Vite.
- **Storage Lokal**: Menggunakan `storage/app/public` yang ditautkan ke `public/storage`.

---

## Tahap D1 — Pengamanan Kode & Database Pengujian

Tahap ini mencakup isolasi database pengujian, eliminasi kredensial default seeder, pembersihan paket yang tidak terpakai, serta konfigurasi reverse proxy dan token Sanctum.

### Pekerjaan:
- [x] Konfigurasi database test terpisah (`tailor_web_test`) di `phpunit.xml`.
- [x] Pembuatan perintah artisan `user:buat-owner` dengan validasi password interaktif minimal 12 karakter.
- [x] Penghapusan akun default `owner@tailor.test` dari seeder dan demo box pada tampilan login.
- [x] Penyesuaian test fixture agar menggunakan factory dinamis.
- [x] Pembersihan dependensi `livewire/livewire` yang tidak digunakan dari `composer.json` dan `composer.lock`.
- [x] Konfigurasi `trustProxies` berbasis environment di `bootstrap/app.php`.
- [x] Dukungan variabel `SANCTUM_TOKEN_EXPIRATION` di `config/sanctum.php`.
- [x] Penambahan `*.sqlite` ke `.gitignore` dan pembersihan file sqlite lokal.
- [x] Standardisasi kode dengan Laravel Pint.

### Deliverable Tahap D1
- [x] Seluruh pengujian (245 test) lulus hijau di database `tailor_web_test`
- [x] Perintah `php artisan user:buat-owner` berfungsi interaktif dan teruji
- [x] Repositori bersih dari file database lokal dan dependensi mati

---

## Tahap D2 — Persiapan Server VPS & Stack Dependensi

Persiapan paket sistem operasi Ubuntu dan instalasi runtime yang dibutuhkan.

### Langkah Kerja:
- Update paket OS: `sudo apt update && sudo apt upgrade -y`
- Instalasi Nginx: `sudo apt install nginx -y`
- Instalasi PHP 8.3 dan ekstensi pendukung:
  ```bash
  sudo apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
      php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-cli
  ```
- Instalasi Composer & Node.js (via NodeSource LTS):
  ```bash
  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
  sudo apt install -y nodejs
  ```
- Pengaturan firewall UFW (izinkan OpenSSH, HTTP, HTTPS).

### Deliverable Tahap D2
- [ ] Server Ubuntu siap dengan Nginx, PHP 8.3-FPM, Node.js 20, dan Composer aktif
- [ ] Layanan Nginx dan PHP-FPM berjalan sebagai systemd service

---

## Tahap D3 — Konfigurasi Database MySQL 8 & Hak Akses Stored Routines

Penyetelan database MySQL 8 agar mendukung trigger dan stored routine yang digunakan aplikasi.

### Prasyarat MySQL Wajib:
1. **`log_bin_trust_function_creators`**:
   Jika binary logging aktif di MySQL (`log_bin = ON`), pembuatan stored function memerlukan parameter ini bernilai `1` agar fungsi deterministik dapat dibuat tanpa error privilese superuser:
   ```sql
   SET GLOBAL log_bin_trust_function_creators = 1;
   ```
   Tambahkan juga ke `/etc/mysql/mysql.conf.d/mysqld.cnf`:
   ```ini
   [mysqld]
   log_bin_trust_function_creators = 1
   ```

2. **Hak Akses User Database**:
   User aplikasi harus memiliki izin routine dan trigger:
   ```sql
   CREATE DATABASE tailor_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'tailor_user'@'127.0.0.1' IDENTIFIED BY 'StrongPasswordHere!';
   GRANT ALL PRIVILEGES ON tailor_production.* TO 'tailor_user'@'127.0.0.1';
   FLUSH PRIVILEGES;
   ```

### Deliverable Tahap D3
- [ ] Database `tailor_production` terbuat dengan charset `utf8mb4`
- [ ] Parameter `log_bin_trust_function_creators` aktif
- [ ] User database dapat menjalankan trigger, function, dan procedure

---

## Tahap D4 — Konfigurasi Lingkungan Produksi & Web Server

Deploy file konfigurasi web server dan environment produksi.

### Langkah Kerja:
- Salin `deploy/nginx.conf.example` ke `/etc/nginx/sites-available/tailor-web` dan aktifkan ke `sites-enabled/`.
- Terbitkan sertifikat SSL Let's Encrypt menggunakan `certbot --nginx -d your-domain.com`.
- Salin `deploy/.env.production.example` ke `.env` pada folder proyek di VPS.
- Generate application key: `php artisan key:generate`.
- Atur kepemilikan folder storage dan bootstrap cache ke user web server:
  ```bash
  sudo chown -R www-data:www-data /var/www/tailor-web/storage /var/www/tailor-web/bootstrap/cache
  sudo chmod -R 775 /var/www/tailor-web/storage /var/www/tailor-web/bootstrap/cache
  ```

### Deliverable Tahap D4
- [ ] Nginx mengarah ke `public/` dengan SSL aktif dan security headers
- [ ] Environment produksi `.env` terkonfigurasi (`APP_ENV=production`, `APP_DEBUG=false`)
- [ ] Izin direktori `storage` dan `bootstrap/cache` dapat ditulis oleh `www-data`

---

## Tahap D5 — Otomasi Deployment & Caching

Penerapan skrip pembaruan terotomasi dan idempoten.

### Langkah Kerja:
- Uji eksekusi skrip `deploy/deploy.sh` saat rilis versi baru.
- Pastikan alur: `artisan down` → `git pull` → `composer install --no-dev` → `npm run build` → `migrate --force` → `cache` → `artisan up`.
- Jalankan perintah pembuatan akun owner pertama kali:
  ```bash
  php artisan user:buat-owner
  ```

### Deliverable Tahap D5
- [ ] `deploy/deploy.sh` berhasil berjalan tanpa intervensi manual
- [ ] Route, config, dan view berhasil di-cache
- [ ] Akun pemilik pertama berhasil dibuat dan dapat login

---

## Tahap D6 — Strategi Backup & Uji Restore Database

Pencadangan berkala database dan file unggahan serta verifikasi integritas data cadangan.

### Skrip Backup:
- Memanfaatkan `deploy/backup.sh` dan file kredensial aman `deploy/my.cnf.example` (`chmod 600 ~/.my.cnf`).
- Dump mencakup routines, triggers, dan single-transaction:
  ```bash
  mysqldump --defaults-extra-file=~/.my.cnf --routines --triggers --single-transaction tailor_production | gzip > backup.sql.gz
  ```
- Backup berkas `storage/app/public`.
- Rotasi otomatis pembersihan berkas backup > 14 hari.

### Prosedur Uji Restore (Verifikasi Integritas):
1. Buat database sementara untuk pengujian pemulihan:
   ```sql
   CREATE DATABASE tailor_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Ekstrak dan impor file dump cadangan:
   ```bash
   gunzip -c /var/backups/tailor-web/db_tailor_production_YYYYMMDD.sql.gz | mysql --defaults-extra-file=~/.my.cnf tailor_restore_test
   ```
3. Verifikasi ketersediaan tabel, triggers, dan routines:
   ```sql
   USE tailor_restore_test;
   SHOW TABLES;
   SHOW TRIGGERS;
   SHOW PROCEDURE STATUS WHERE Db = 'tailor_restore_test';
   SHOW FUNCTION STATUS WHERE Db = 'tailor_restore_test';
   ```
4. Ekstrak file storage publik ke folder uji:
   ```bash
   tar -xzf /var/backups/tailor-web/storage_YYYYMMDD.tar.gz -C /tmp/
   ```
5. Hapus database uji setelah selesai:
   ```sql
   DROP DATABASE tailor_restore_test;
   ```

### Deliverable Tahap D6
- [ ] `deploy/backup.sh` berjalan sukses menghasilkan dump DB dan tar storage
- [ ] File cadangan berhasil diuji restore ke database temporer tanpa error
- [ ] Kredensial MySQL tidak terekspos pada proses shell

---

## Tahap D7 — Otomasi Penjadwalan & Verifikasi Pasca-Deploy

Pemasangan daemon cron untuk scheduler Laravel dan backup harian serta verifikasi akhir.

### Langkah Kerja:
- Daftarkan crontab dari `deploy/cron.example`:
  ```bash
  * * * * * cd /var/www/tailor-web && php artisan schedule:run >> /dev/null 2>&1
  0 2 * * * /var/www/tailor-web/deploy/backup.sh >> /var/log/tailor-backup.log 2>&1
  ```
- Verifikasi endpoint kesehatan dan rute API:
  ```bash
  curl -I https://your-domain.com/up
  php artisan route:list
  ```
- Uji alur login web dan login API mobile.

### Deliverable Tahap D7
- [ ] Scheduler Laravel berjalan aktif setiap menit
- [ ] Cron backup harian terpasang di sistem
- [ ] Endpoint `/up` merespons HTTP 200 OK
- [ ] Login web dan mobile API berfungsi normal di server produksi
