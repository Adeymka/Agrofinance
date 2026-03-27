# ── Stage 1 : Build assets frontend (Node/Vite) ──────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --prefer-offline
COPY . .
RUN npm run build

# ── Stage 2 : Base PHP ────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS base

LABEL maintainer="AgroFinance+"

RUN apk add --no-cache \
    bash \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ── Stage 3 : Dépendances Composer (prod, sans dev) ──────────────────────────
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ── Stage 4 : Image finale auto-suffisante ────────────────────────────────────
FROM base AS app

# Code source de l'application
COPY . .

# Vendor PHP (depuis le stage deps)
COPY --from=deps /var/www/vendor ./vendor

# Assets compilés par Vite (public/build/)
COPY --from=frontend /app/public/build ./public/build

# Permissions storage et bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Config PHP : opcache et paramètres production
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini     /usr/local/etc/php/conf.d/custom.ini

# Entrypoint : attend MySQL, migrations, cache config/routes/vues, puis php-fpm
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]

# ── Stage 5 : Worker queue (génération PDF en arrière-plan) ──────────────────
FROM app AS worker

ENTRYPOINT []
CMD ["php", "artisan", "queue:work", "redis", \
     "--queue=rapports", \
     "--sleep=3", \
     "--tries=3", \
     "--timeout=120", \
     "--max-jobs=500"]
