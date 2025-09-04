# Solusi Masalah "Situs ini tidak dapat dijangkau" Setelah Deploy

## Masalah yang Ditemukan
Error `ERR_FAILED` dan "Situs ini tidak dapat dijangkau" setelah deploy ke VPS biasanya disebabkan oleh:

1. **SSL Certificate tidak valid atau expired**
2. **Konfigurasi HTTPS yang salah**
3. **Session configuration bermasalah**
4. **Trusted proxies tidak dikonfigurasi**
5. **Cache Laravel yang corrupt**

## Solusi Lengkap

### 1. Perbaikan SSL Certificate
```bash
# Cek status SSL
curl -I https://pos.optikmelati.site/

# Jika menggunakan Let's Encrypt, renew certificate
sudo certbot renew --dry-run
sudo certbot renew

# Restart web server
sudo systemctl restart apache2
# atau
sudo systemctl restart nginx
```

### 2. Perbaikan Konfigurasi Laravel
File `.env` di VPS harus memiliki konfigurasi berikut:

```env
APP_NAME="POS Optik Melati"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pos.optikmelati.site

# Session configuration untuk HTTPS
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=posoptikmelati
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Jalankan Script Perbaikan
```bash
# Upload dan jalankan script deploy_fix.sh
chmod +x deploy_fix.sh
./deploy_fix.sh
```

### 4. Perbaikan Manual (jika script tidak berhasil)
```bash
# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Clear cache Laravel
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

# Generate key baru
php artisan key:generate --force

# Optimize untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart web server
sudo systemctl restart apache2
```

### 5. Perbaikan Konfigurasi Web Server

#### Untuk Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Handle Laravel routes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

#### Untuk Nginx
```nginx
server {
    listen 80;
    server_name pos.optikmelati.site;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name pos.optikmelati.site;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    root /path/to/your/laravel/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. Troubleshooting Lanjutan

#### Cek Log Error
```bash
# Laravel log
tail -f storage/logs/laravel.log

# Web server log
tail -f /var/log/apache2/error.log
# atau
tail -f /var/log/nginx/error.log

# System log
journalctl -u apache2 -f
# atau
journalctl -u nginx -f
```

#### Test Koneksi
```bash
# Test dari server
curl -I https://pos.optikmelati.site/
curl -I http://pos.optikmelati.site/

# Test DNS
nslookup pos.optikmelati.site
dig pos.optikmelati.site
```

#### Cek Port dan Service
```bash
# Cek port 80 dan 443
netstat -tlnp | grep :80
netstat -tlnp | grep :443

# Cek status service
systemctl status apache2
systemctl status nginx
systemctl status mysql
```

### 7. Perbaikan DNS (jika diperlukan)
Pastikan DNS record untuk domain sudah benar:
- A record: `pos.optikmelati.site` → IP server VPS
- CNAME record (jika menggunakan subdomain)

### 8. Testing Setelah Perbaikan
1. Akses `https://pos.optikmelati.site/`
2. Test login/logout
3. Test navigasi antar halaman
4. Test fitur utama aplikasi
5. Cek console browser untuk error JavaScript

## Catatan Penting
- Pastikan firewall tidak memblokir port 80 dan 443
- Pastikan SSL certificate valid dan tidak expired
- Pastikan domain DNS sudah propagate dengan benar
- Monitor log error untuk masalah yang mungkin muncul

## Jika Masih Bermasalah
1. Cek status server VPS (CPU, RAM, Disk)
2. Cek apakah ada service yang down
3. Cek konfigurasi firewall
4. Hubungi provider VPS jika diperlukan
