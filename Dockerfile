FROM php:8.2-fpm-alpine

# Mettez à jour et installez les dépendances avec apk
RUN apk update && apk add --no-cache \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    libsodium-dev \
    postgresql-dev \
    mysql-client \
    mysql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip sodium

# Installez Composer (comme dans votre version originale)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Installez Node.js sur Alpine
RUN apk add --no-cache nodejs npm

WORKDIR /var/www/html

COPY . .

EXPOSE 8000

# Exécutez les commandes d'installation
RUN composer install --no-dev --optimize-autoloader
RUN npm install

# Commandes d'exécution finales
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000