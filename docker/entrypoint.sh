#!/bin/sh
# This script is sourced by serversideup/php's entrypoint before s6-overlay
# starts Nginx and PHP-FPM. Do not use "exec" or "exit" here.

echo "Starting Laravel bootstrap..."

# ── Wait for the database to be reachable ─────────────────────────────────────
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for database at $DB_HOST:${DB_PORT:-5432}..."
    max_retries=30
    retries=0
    until php -r "
        \$conn = @new PDO(
            'pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE}',
            '${DB_USERNAME}', '${DB_PASSWORD}'
        );
    " 2>/dev/null; do
        retries=$((retries + 1))
        if [ "$retries" -ge "$max_retries" ]; then
            echo "❌ Database not available after $max_retries retries. Exiting."
            exit 1
        fi
        echo "   ... attempt $retries/$max_retries"
        sleep 2
    done
    echo "✅ Database is ready."
fi

echo "📁 Ensuring storage directory structure exists..."
# The storage/ tree lives on a named volume which is empty on first start.
# Laravel cannot boot at all without these directories, so we create them
# here -- before package:discover -- rather than relying on the Dockerfile
# RUN step, which only applies to the image layer, not the mounted volume.
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

echo "🔍 Discovering packages..."
php artisan package:discover --ansi

# Note: migrations, config:cache, route:cache, view:cache, and storage:link
# are all handled automatically by the serversideup/php image via the
# AUTORUN_ENABLED and related environment variables. See docker-compose.yml.

echo "Bootstrap complete."