#!/bin/sh

set -e

cd /var/www/html

mkdir -p \
    storage/app/private \
    storage/app/eea \
    storage/app/archives \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction >/dev/null
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Waiting for the database…"

    until php -r '
        $host = getenv("DB_HOST") ?: "db";
        $port = getenv("DB_PORT") ?: "5432";
        $name = getenv("DB_DATABASE") ?: "clean_air";
        $user = getenv("DB_USERNAME") ?: "clean_air";
        $pass = getenv("DB_PASSWORD") ?: "clean_air";
        new PDO("pgsql:host={$host};port={$port};dbname={$name}", $user, $pass);
    ' 2>/dev/null; do
        sleep 2
    done

    php artisan migrate --force --no-interaction
    php artisan db:seed --force --no-interaction
fi

exec "$@"
