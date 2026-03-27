#!/bin/sh
set -e

echo "==> Optimisation de l'autoload..."
composer dump-autoload --optimize --no-dev 2>/dev/null || true

echo "==> Génération de la clé applicative (si absente)..."
php artisan key:generate --no-interaction --force 2>/dev/null || true

echo "==> Attente de MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-agrofinanceplus}', '${DB_USERNAME:-agrofinance}', '${DB_PASSWORD:-secret}');" 2>/dev/null; do
  echo "MySQL non prêt — attente 2s..."
  sleep 2
done

echo "==> Migrations..."
php artisan migrate --force --no-interaction

echo "==> Cache config, routes, vues..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Démarrage PHP-FPM..."
exec php-fpm
