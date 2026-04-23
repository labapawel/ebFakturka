#!/bin/sh
set -e

cd /var/www/html

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force
fi

# Wait for external database to be reachable
if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "pgsql" ]; then
    HOST="${DB_HOST:-localhost}"
    PORT="${DB_PORT}"
    if [ -z "$PORT" ]; then
        [ "$DB_CONNECTION" = "mysql" ] && PORT=3306 || PORT=5432
    fi

    echo "Waiting for $DB_CONNECTION at $HOST:$PORT..."
    until nc -z "$HOST" "$PORT" 2>/dev/null; do
        sleep 1
    done
    echo "Database is reachable."
fi

# Ensure SQLite database file exists
if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    touch "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
fi

# Run migrations
php artisan migrate --force

# Clear and cache config for production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Fix storage permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

exec "$@"
