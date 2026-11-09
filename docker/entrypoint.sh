#!/bin/sh
set -e

PORT="${PORT:-10000}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage
php artisan package:discover --ansi || true
php artisan config:cache || true

# Apply pending migrations on every deploy so the schema never lags the code.
php artisan migrate --force || echo "WARNING: php artisan migrate failed; check the migration output above."

exec "$@"