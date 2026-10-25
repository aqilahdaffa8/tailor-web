# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ==========================================
# Stage 2: Runtime PHP 8.4-FPM + Nginx
# ==========================================
FROM php:8.4-fpm-alpine

# Install sistem dependensi dan ekstensi PHP yang dibutuhkan
RUN apk add --no-cache \
    nginx \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    fontconfig \
    ttf-dejavu \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        intl \
        opcache

# Salin Composer binary resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Konfigurasi direktori kerja
WORKDIR /app

# Salin definisi Composer terlebih dahulu untuk layer caching
COPY composer.json composer.lock ./

# Install vendor produksi
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Salin kode aplikasi
COPY . .

# Salin hasil build aset Vite dari stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Jalankan post-autoload dump composer
RUN composer dump-autoload --optimize --no-dev

# Konfigurasi PHP, Nginx, dan script start
COPY docker/php.ini /usr/local/etc/php/conf.d/tailor.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Pastikan kepemilikan direktori storage dan cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Port default Render Web Service
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/start.sh"]
