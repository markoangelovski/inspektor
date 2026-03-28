#!/bin/bash

# Exit immediately if a command fails
set -e

cd /home/site/wwwroot

echo ">>> Copying nginx config..."
cp /home/site/wwwroot/default /etc/nginx/sites-available/default

echo ">>> Reloading nginx..."
service nginx reload

echo ">>> Running migrations..."
php artisan migrate --force

echo ">>> Optimizing Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
# php artisan db:seed --force

echo ">>> Permissions..."
chown -R www-data:www-data storage bootstrap/cache

echo ">>> Starting Laravel Horizon in background..."
php artisan horizon > /home/site/wwwroot/horizon.log 2>&1 &

echo ">>> Starting PHP-FPM..."
exec php-fpm
