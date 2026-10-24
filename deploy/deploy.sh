#!/usr/bin/env bash

set -euo pipefail

# Skrip Deployment Idempoten untuk Tailor Management System
# Menjalankan pembaruan kode, migrasi database, kompilasi aset, dan caching produksi secara aman.

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "=========================================================="
echo " [DEPLOY] Memulai deployment di: $APP_DIR"
echo " Tanggal/Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================================="

# Pastikan aplikasi kembali aktif (artisan up) meskipun terjadi kegagalan di tengah proses
cleanup() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo "=========================================================="
        echo " [ERROR] Terjadi kesalahan selama deployment (Exit code: $exit_code)!"
        echo " [DEPLOY] Memastikan aplikasi kembali up dari mode maintenance..."
        php artisan up || true
        echo "=========================================================="
    fi
}
trap cleanup EXIT

# 1. Aktifkan Mode Maintenance
echo "==> Mengaktifkan mode maintenance..."
php artisan down --render="errors::503" --retry=60 || true

# 2. Tarik kode terbaru dari repository
echo "==> Menarik pembaruan Git terbaru..."
git pull origin main

# 3. Instal dependensi PHP produksi (tanpa dev package)
echo "==> Memperbarui dependensi Composer (--no-dev)..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Instal dependensi JavaScript & kompilasi aset Vite
echo "==> Mengompilasi aset Vite (Tailwind CSS, Alpine.js)..."
if [ -f "package-lock.json" ]; then
    npm ci --no-audit
else
    npm install --no-audit
fi
npm run build

# 5. Jalankan migrasi database
echo "==> Menjalankan migrasi database..."
php artisan migrate --force

# 6. Pastikan symlink storage publik tersedia
if [ ! -L "public/storage" ]; then
    echo "==> Membuat symlink storage publik..."
    php artisan storage:link
else
    echo "==> Symlink public/storage sudah ada."
fi

# 7. Hangatkan cache produksi Laravel (JANGAN panggil optimize:clear setelah build)
echo "==> Membangun cache konfigurasi, route, dan view..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Nonaktifkan Mode Maintenance
echo "==> Menonaktifkan mode maintenance (Aplikasi Online)..."
php artisan up

# Hapus trap karena proses selesai sukses
trap - EXIT

echo "=========================================================="
echo " [DEPLOY] Deployment sukses diselesaikan pada $(date '+%Y-%m-%d %H:%M:%S')!"
echo "=========================================================="
