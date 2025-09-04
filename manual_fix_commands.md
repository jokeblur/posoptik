# Manual Fix Commands - Jalankan Satu per Satu

## Langkah 1: SSH ke VPS
```bash
ssh user@your-vps-ip
```

## Langkah 2: Navigate ke direktori aplikasi
```bash
cd /var/www/html/posoptikmelati
# atau sesuaikan dengan path aplikasi Anda
```

## Langkah 3: Set Permissions
```bash
sudo chmod -R 755 storage/
sudo chmod -R 755 bootstrap/cache/
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
```

## Langkah 4: Clear Cache Laravel
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

## Langkah 5: Generate Application Key
```bash
php artisan key:generate --force
```

## Langkah 6: Optimize untuk Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Langkah 7: Run Migration (jika ada)
```bash
php artisan migrate --force
```

## Langkah 8: Restart Web Server
```bash
# Untuk Apache
sudo systemctl restart apache2

# Untuk Nginx
sudo systemctl restart nginx
```

## Langkah 9: Test Akses
```bash
# Test dari server
curl -I https://pos.optikmelati.site/
```

## Langkah 10: Cek Log jika Masih Error
```bash
# Laravel log
tail -f storage/logs/laravel.log

# Web server log
tail -f /var/log/apache2/error.log
# atau
tail -f /var/log/nginx/error.log
```
