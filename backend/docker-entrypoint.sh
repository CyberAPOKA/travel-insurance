#!/bin/sh
set -e

# In Docker, the mounted host .env often points to sqlite. Always apply .env.docker
# so artisan, HTTP requests, and migrations use the same MySQL/Redis stack.
if [ -f .env.docker ] && [ -n "${DB_HOST:-}" ]; then
    if [ -f .env ]; then
        APP_KEY=$(grep '^APP_KEY=' .env | head -1 | cut -d= -f2- || true)
    fi

    cp .env.docker .env

    if [ -n "$APP_KEY" ]; then
        sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    fi
elif [ ! -f .env ]; then
    cp .env.docker .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --prefer-dist
fi

rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
php artisan package:discover --ansi
php artisan config:clear

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    rm -f database/database.sqlite
fi

echo "Waiting for MySQL..."
until php -r "
try {
    new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    exit(0);
} catch (Throwable \$e) {
    exit(1);
}
" >/dev/null 2>&1; do
    sleep 2
done

php artisan migrate --force --no-interaction

exec "$@"
