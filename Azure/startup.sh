#!/bin/bash

# Exit immediately if a command fails
set -e

cd /home/site/wwwroot

echo ">>> Copying nginx config..."
cp /home/site/wwwroot/default /etc/nginx/sites-available/default

echo ">>> Reloading nginx..."
service nginx reload

echo ">>> Ensuring Laravel storage directories..."
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p bootstrap/cache

echo ">>> Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Also add following env var to Azure Web App to enable OPcache: PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d:/home/site/php
echo ">>> Installing custom PHP opcache config..."
mkdir -p /home/site/php
cp /home/site/wwwroot/php/opcache.ini /home/site/php/opcache.ini

echo ">>> Starting PHP-FPM..."
exec php-fpm