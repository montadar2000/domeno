#!/bin/sh
set -eu

cd /var/www/html

mkdir -p /var/data \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    if [ -f /var/data/app.key ]; then
        APP_KEY="$(cat /var/data/app.key)"
    else
        APP_KEY="$(php artisan key:generate --show)"
        printf '%s' "$APP_KEY" > /var/data/app.key
    fi
    export APP_KEY
fi

export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-/var/data/database.sqlite}"
mkdir -p "$(dirname "$DB_DATABASE")"
touch "$DB_DATABASE"

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
