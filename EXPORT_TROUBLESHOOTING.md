# Panduan Mengatasi Masalah Export Lensa dan Frame

## Masalah
Export lensa dan frame tidak menghasilkan file yang bisa didownload.

## Penyebab Potensial dan Solusi

### 1. Package Laravel Excel Belum Terinstall dengan Benar

**Solusi:**
```bash
# Install composer jika belum ada
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install dependencies
composer install

# Update package Excel ke versi terbaru
composer require maatwebsite/excel

# Publish konfigurasi
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

### 2. Konfigurasi Service Provider Belum Ditambahkan

**Solusi:**
✅ Sudah ditambahkan di config/app.php:
- ExcelServiceProvider di providers
- Excel alias di aliases

### 3. Cache Konfigurasi Perlu Dibersihkan

**Solusi:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 4. Permissions Folder Storage

**Solusi:**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## Testing dan Debugging

### Route Test Baru
Telah ditambahkan route test untuk debugging:
- `/test/lensa-export` - untuk test export lensa
- `/test/frame-export` - untuk test export frame

Route ini akan menampilkan informasi JSON tentang:
- Status export
- Jumlah data yang akan diexport
- Sample data 
- Status instalasi package Excel

### Tombol Test di Interface
- ✅ Ditambahkan tombol "Test Export" di halaman lensa dan frame
- ✅ Ditambahkan alert untuk menampilkan pesan error/success

## Perubahan yang Sudah Dilakukan

### Export Classes
- ✅ Diperbaiki LensaExport dan FrameExport dengan menambahkan WithMapping dan ShouldAutoSize
- ✅ Ditambahkan filter berdasarkan user access
- ✅ Ditambahkan relasi untuk menampilkan nama cabang dan sales

### Controllers  
- ✅ Ditambahkan error handling pada method export
- ✅ Ditambahkan timestamp pada nama file export
- ✅ Ditambahkan try-catch untuk menangani error

### Routes
- ✅ Route export sudah terdefinisi dengan benar
- ✅ Ditambahkan route test untuk debugging

### Views
- ✅ Tombol export sudah ada di halaman lensa dan frame
- ✅ Ditambahkan alert untuk error/success messages
- ✅ Ditambahkan tombol test export

### Configuration
- ✅ Ditambahkan ExcelServiceProvider di config/app.php
- ✅ Ditambahkan Excel alias di config/app.php
- ✅ Dibuat file config/excel.php

## Langkah Debugging

1. **Test basic functionality:**
   - Akses `/test/lensa-export` dan `/test/frame-export`
   - Periksa apakah data muncul dan `excel_installed: true`

2. **Jika excel_installed: false:**
   ```bash
   composer require maatwebsite/excel
   php artisan config:clear
   ```

3. **Jika data kosong:**
   - Pastikan ada data lensa/frame di database
   - Periksa permission user untuk akses data

4. **Jika export button tidak berfungsi:**
   - Cek browser console untuk error JavaScript
   - Cek Network tab untuk melihat response dari server

## Jika Masih Bermasalah

1. **Cek log error:** storage/logs/laravel.log
2. **Cek browser console** untuk error JavaScript
3. **Test dengan route baru:** /test/lensa-export dan /test/frame-export
4. **Pastikan PHP extension** yang diperlukan sudah terinstall:
   - php-zip
   - php-xml
   - php-gd
   - php-mbstring
5. **Cek Network tab** di browser developer tools saat mengklik export

## File yang Telah Dimodifikasi

1. **app/Exports/LensaExport.php** - Improved export class
2. **app/Exports/FrameExport.php** - Improved export class  
3. **app/Http/Controllers/LensaController.php** - Added error handling
4. **app/Http/Controllers/FrameController.php** - Added error handling
5. **config/app.php** - Added Excel service provider and alias
6. **config/excel.php** - Added Excel configuration
7. **routes/web.php** - Added test routes
8. **resources/views/lensa/index.blade.php** - Added error alerts and test button
9. **resources/views/frame/index.blade.php** - Added error alerts and test button

## Contoh Command untuk Install Extension PHP

```bash
# Ubuntu/Debian
sudo apt-get install php-zip php-xml php-gd php-mbstring

# CentOS/RHEL
sudo yum install php-zip php-xml php-gd php-mbstring
```

## Restart Services

Setelah semua konfigurasi, restart web server:
```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart nginx

# PHP-FPM
sudo systemctl restart php-fpm
```
