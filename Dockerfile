# Utilise une image PHP officielle avec Alpine pour la légèreté.
FROM php:8.2-fpm-alpine

# Installe les dépendances système requises pour Laravel et PHP
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    git \
    build-base \
    autoconf \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    icu-dev \
    libxml2-dev \
    sqlite-dev \
    curl-dev \
    openssl-dev \
    libintl \
    libjpeg-turbo-dev \
    php82-dom \
    php82-dev

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    zip \
    gd \
    intl \
    exif \
    pcntl \
    bcmath \
    opcache \
    xml \
    mbstring

# Configure Nginx pour servir l'application Laravel
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copie le fichier de configuration PHP-FPM
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Crée un répertoire pour l'application et définit les permissions
WORKDIR /var/www/html

# Copie l'application Laravel dans le conteneur
COPY . /var/www/html

# Installe Composer globalement
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Installe les dépendances Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Ajuste les permissions pour le stockage et le cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose le port 80 (Nginx)
EXPOSE 80

# Configure Supervisor pour gérer Nginx et PHP-FPM
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Commande par défaut pour lancer l'application avec Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]