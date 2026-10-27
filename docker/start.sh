#!/bin/sh
#
# Skrip start untuk image produksi.
#
# Tugasnya tiga: menyesuaikan port Apache dengan variabel PORT dari platform,
# menyiapkan database, lalu menjalankan Apache di depan.
#
set -e

PORT="${PORT:-80}"

echo "[start] menyiapkan Apache pada port ${PORT}"

# Platform seperti Render memberi port lewat variabel PORT dan meneruskan
# trafik ke port tersebut, sedangkan image Apache bawaan mendengarkan di 80.
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Konfigurasi Laravel yang ter-cache akan mengunci nilai lama dan mengabaikan
# variabel lingkungan yang baru. Membersihkannya memastikan APP_KEY serta
# kredensial database dari platform benar-benar terbaca.
php artisan config:clear

# Migrasi dan data awal. Keduanya idempoten, jadi aman dijalankan setiap
# container hidup kembali.
if [ "${SKIP_MIGRATIONS}" != "true" ]; then
    echo "[start] menjalankan migrasi"
    php artisan migrate --force

    if [ "${SKIP_SEED}" != "true" ]; then
        echo "[start] mengisi data awal"
        php artisan db:seed --force
    fi
fi

# Cache produksi dibuat setelah variabel lingkungan tersedia, bukan saat build.
php artisan route:cache
php artisan view:cache

echo "[start] Apache dijalankan"
exec apache2-foreground
