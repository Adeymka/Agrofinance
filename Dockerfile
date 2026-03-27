# ── Build ────────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS base

LABEL maintainer="AgroFinance+"

RUN apk add --no-cache \
    bash \
    git \
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

# Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ── Dependencies ─────────────────────────────────────────────────────────────
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# ── App ───────────────────────────────────────────────────────────────────────
FROM base AS app

COPY --from=deps /var/www/vendor ./vendor
COPY . .

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# PHP opcache config
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini     /usr/local/etc/php/conf.d/custom.ini

EXPOSE 9000
CMD ["php-fpm"]

# ── Worker (queue rapports PDF) ───────────────────────────────────────────────
FROM app AS worker

CMD ["php", "artisan", "queue:work", "redis", "--queue=rapports", "--sleep=3", "--tries=3", "--timeout=120", "--max-jobs=500"]
