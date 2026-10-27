# ---------------------------------------------------------------------------
# Image produksi untuk Kamus Bahasa Daerah API.
#
# Dipakai oleh platform yang tidak punya runtime PHP bawaan, misalnya Render.
# Dibangun dari php:8.2-apache karena versi itu sama dengan versi pengembangan
# (8.2.12), sehingga perilakunya tidak berubah antara lokal dan server.
# ---------------------------------------------------------------------------
FROM php:8.2-apache

# ---------------------------------------------------------------------------
# Ekstensi PHP
# ---------------------------------------------------------------------------
# pdo_mysql wajib untuk koneksi database, zip dibutuhkan Composer, dan
# mbstring/bcmath dipakai Laravel di beberapa tempat.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libonig-dev \
        libpng-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip mbstring bcmath opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer diambil dari image resmi agar versinya pasti.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# Dependensi
# ---------------------------------------------------------------------------
# composer.json dan composer.lock disalin lebih dulu supaya lapisan ini
# di-cache dan tidak diulang setiap kali kode berubah.
COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

# Sisa kode disalin setelah dependensi terpasang.
COPY . .

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && php artisan package:discover --ansi

# ---------------------------------------------------------------------------
# Document root
# ---------------------------------------------------------------------------
# Apache harus menyajikan direktori public/, bukan akar proyek, agar berkas
# .env dan source code tidak dapat diakses lewat HTTP.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

# Izinkan .htaccess dan aturan Laravel di direktori public.
RUN printf '%s\n' \
        '<Directory /var/www/html/public>' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# ---------------------------------------------------------------------------
# Izin tulis
# ---------------------------------------------------------------------------
# Laravel menulis ke storage/ dan bootstrap/cache/ saat berjalan.
RUN mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Port 80 adalah default image; skrip start menggantinya bila PORT diset.
EXPOSE 80

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
