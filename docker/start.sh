#!/usr/bin/env sh
set -e

echo "==> Memulai inisialisasi kontainer tailor-web..."

# 1. Pastikan direktori runtime dan storage ada
echo "==> Memastikan hak akses storage dan bootstrap/cache..."
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache

# 2. Database SSL CA dari environment variable DB_SSL_CA_PEM
# Wajib diekspor sebelum config:cache agar nilai MYSQL_ATTR_SSL_CA tercatat di cache
if [ -n "$DB_SSL_CA_PEM" ]; then
    echo "==> Menulis sertifikat CA dari DB_SSL_CA_PEM..."
    CA_FILE="/app/storage/ca.pem"
    printf "%s\n" "$DB_SSL_CA_PEM" > "$CA_FILE"
    chmod 644 "$CA_FILE"
    export MYSQL_ATTR_SSL_CA="$CA_FILE"
fi

chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# 3. Cache konfigurasi Laravel untuk performa produksi
echo "==> Melakukan caching konfigurasi, rute, dan view..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Storage symlink (jika PUBLIC_DISK_DRIVER bernilai local)
if [ "${PUBLIC_DISK_DRIVER:-local}" = "local" ]; then
    php artisan storage:link --force || true
fi

# 5. Konfigurasi Port Nginx dari env PORT (default Render: 10000)
PORT="${PORT:-10000}"
echo "==> Menyetel port Nginx ke $PORT..."
sed -i "s/__PORT__/$PORT/g" /etc/nginx/nginx.conf

# 6. Jalankan PHP-FPM di latar belakang
echo "==> Menjalankan PHP-FPM..."
php-fpm -D

# 7. Jalankan Nginx di foreground
echo "==> Menjalankan Nginx di foreground (port $PORT)..."
exec nginx -g "daemon off;"
