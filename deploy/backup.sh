#!/usr/bin/env bash

set -euo pipefail

# Skrip Backup Terotomasi untuk Tailor Management System
# Mengamankan database MySQL 8 (termasuk Stored Routines & Triggers) serta berkas unggahan publik (storage).
# Membaca kredensial dari file konfigurasi MySQL (~/.my.cnf) untuk mencegah eksposur password pada proses sistem.

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/tailor-web}"
MYSQL_CONF="${MYSQL_CONF:-$HOME/.my.cnf}"
DB_NAME="${DB_NAME:-tailor_production}"
RETENTION_DAYS=14
TIMESTAMP="$(date +'%Y%m%d_%H%M%S')"

echo "=========================================================="
echo " [BACKUP] Memulai proses pencadangan data: $TIMESTAMP"
echo " Direktori target: $BACKUP_DIR"
echo "=========================================================="

# 1. Pastikan folder backup tersedia
mkdir -p "$BACKUP_DIR"

# 2. Validasi keberadaan file konfigurasi MySQL
if [ ! -f "$MYSQL_CONF" ]; then
    echo " [ERROR] File konfigurasi MySQL ($MYSQL_CONF) tidak ditemukan!" >&2
    echo " Buat file tersebut dengan permission 600 berdasarkan contoh di deploy/my.cnf.example." >&2
    exit 1
fi

DB_BACKUP_FILE="$BACKUP_DIR/db_${DB_NAME}_${TIMESTAMP}.sql.gz"
STORAGE_BACKUP_FILE="$BACKUP_DIR/storage_${TIMESTAMP}.tar.gz"

# 3. Dump Database MySQL
# --routines: mencakup stored procedures dan stored functions
# --triggers: mencakup seluruh triggers bisnis
# --single-transaction: dump konsisten tanpa mengunci tabel InnoDB
# --defaults-extra-file: membaca kredensial aman dari file cnf
echo "==> Mencadangkan database MySQL '$DB_NAME'..."
mysqldump --defaults-extra-file="$MYSQL_CONF" \
    --routines \
    --triggers \
    --single-transaction \
    --quick \
    "$DB_NAME" | gzip > "$DB_BACKUP_FILE"

echo "    Database tersimpan di: $DB_BACKUP_FILE ($(du -h "$DB_BACKUP_FILE" | cut -f1))"

# 4. Backup Berkas Unggahan (storage/app/public)
if [ -d "$APP_DIR/storage/app/public" ]; then
    echo "==> Mencadangkan berkas unggahan storage/app/public..."
    tar -czf "$STORAGE_BACKUP_FILE" -C "$APP_DIR" storage/app/public
    echo "    Berkas storage tersimpan di: $STORAGE_BACKUP_FILE ($(du -h "$STORAGE_BACKUP_FILE" | cut -f1))"
else
    echo "==> Peringatan: Direktori $APP_DIR/storage/app/public tidak ditemukan, melewati backup storage."
fi

# 5. Rotasi Backup: Hapus file cadangan yang berumur lebih dari 14 hari
echo "==> Menghapus berkas cadangan yang lebih lama dari $RETENTION_DAYS hari..."
find "$BACKUP_DIR" -type f \( -name "db_*.sql.gz" -o -name "storage_*.tar.gz" \) -mtime "+$RETENTION_DAYS" -exec rm -f {} +

echo "=========================================================="
echo " [BACKUP] Pencadangan selesai pada $(date '+%Y-%m-%d %H:%M:%S')!"
echo "=========================================================="
