# Utilise une image PHP officielle avec FPM (FastCGI Process Manager)
FROM php:8.2-fpm-alpine

# Installe les dépendances système nécessaires
RUN apk add --no-cache \
    nginx \
    supervisor \
    build-base \
    autoconf \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    jpeg-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxml2-dev \
    freetype-dev \
    gmp-dev \
    sqlite-dev

# Installe les extensions PHP requises par Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    exif \
    pcntl \
    gd \
    mysqli \
    opcache \
    gmp \
    pdo_sqlite

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définit le répertoire de travail dans le conteneur
WORKDIR /var/www/html

# Copie le code de l'application dans le conteneur
COPY . .

# Définit les permissions pour le répertoire de stockage et le cache de Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Installe les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# Génère la clé d'application Laravel
RUN php artisan key:generate

# Lance les migrations de la base de données (si nécessaire)
# RUN php artisan migrate --force

# Expose le port 9000 pour PHP-FPM
EXPOSE 9000

# Commande par défaut pour exécuter PHP-FPM
CMD ["php-fpm"]