#!/bin/bash

# Script untuk memperbaiki masalah deployment
echo "=== Memperbaiki Masalah Deployment ==="

# 1. Set permissions yang tepat
echo "1. Setting permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# 2. Clear semua cache Laravel
echo "2. Clearing Laravel cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

# 3. Generate key baru jika diperlukan
echo "3. Generating application key..."
php artisan key:generate --force

# 4. Optimize untuk production
echo "4. Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Jalankan migration jika ada
echo "5. Running migrations..."
php artisan migrate --force

# 6. Restart web server
echo "6. Restarting web server..."
sudo systemctl restart apache2
# atau jika menggunakan nginx:
# sudo systemctl restart nginx

echo "=== Deployment fix completed ==="
