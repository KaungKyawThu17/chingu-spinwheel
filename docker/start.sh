#!/bin/sh
set -eu

: "${APP_KEY:?Set a persistent APP_KEY in the service environment before starting Laravel.}"

php artisan config:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
