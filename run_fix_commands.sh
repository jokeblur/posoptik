#!/bin/bash

# Script untuk menjalankan perbaikan deployment langsung di VPS
echo "=== Memulai Perbaikan Deployment ==="

# 1. Navigate ke direktori aplikasi Laravel
echo "1. Navigate ke direktori aplikasi..."
cd /var/www/html/posoptikmelati
# atau sesuaikan dengan path aplikasi Anda
# cd /path/to/your/laravel/app

# 2. Set permissions yang tepat
echo "2. Setting permissions..."
sudo chmod -R 755 storage/
sudo chmod -R 755 bootstrap/cache/
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/

# 3. Clear semua cache Laravel
echo "3. Clearing Laravel cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

# 4. Generate key baru jika diperlukan
echo "4. Generating application key..."
php artisan key:generate --force

# 5. Optimize untuk production
echo "5. Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Jalankan migration jika ada
echo "6. Running migrations..."
php artisan migrate --force

# 7. Restart web server
echo "7. Restarting web server..."
sudo systemctl restart apache2
# atau jika menggunakan nginx:
# sudo systemctl restart nginx

echo "=== Perbaikan selesai ==="
echo "Silakan test akses ke https://pos.optikmelati.site/"
