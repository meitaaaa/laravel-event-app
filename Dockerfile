# Gunakan image resmi PHP 8.2 dengan Apache
FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Aktifkan mod_rewrite agar route Laravel berfungsi
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy semua file Laravel ke dalam container
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install semua dependency Laravel
RUN composer install --no-dev --optimize-autoloader

# Generate key Laravel (optional, Render bisa generate juga)
RUN php artisan key:generate || true

# Set permission untuk folder storage dan bootstrap
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Jalankan Laravel menggunakan artisan serve
CMD php artisan serve --host=0.0.0.0 --port=10000

# Expose port 10000
EXPOSE 10000
