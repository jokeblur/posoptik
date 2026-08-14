# Deploy Checklist VPS - POS Optik Melati

Panduan singkat buat deploy aplikasi Laravel + API v1 ke VPS.

## 1. Siapkan environment

- Pastikan VPS sudah punya web server, PHP sesuai requirement project, MySQL, dan Composer.
- Upload source code ke folder project.
- Install dependency:

```bash
composer install --no-dev --optimize-autoloader
```

## 2. Konfigurasi `.env`

Isi minimal ini dengan benar:

```env
APP_NAME="POS Optik Melati"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
```

Kalau `APP_KEY` belum ada:

```bash
php artisan key:generate
```

## 3. Jalankan migrasi

```bash
php artisan migrate --force
```

Kalau data awal perlu diisi, jalankan seeder yang diperlukan.

## 4. Storage dan permission

```bash
php artisan storage:link
```

Pastikan folder berikut bisa ditulis oleh web server:

- `storage/`
- `bootstrap/cache/`

## 5. Optimasi production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload -o
```

## 6. Tes API login Sanctum

Request:

```bash
POST /api/v1/auth/login
```

Body:

```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "android-mobile"
}
```

Kalau berhasil, response akan mengembalikan token Bearer.

## 7. Tes endpoint utama

Setelah dapat token, test endpoint berikut:

- `GET /api/v1/auth/me`
- `GET /api/v1/frames`
- `GET /api/v1/pasien`
- `GET /api/v1/penjualan`
- `GET /api/v1/search?q=...`
- `GET /api/v1/reports/transactions`
- `GET /api/v1/comments?penjualan_id=...`

Header yang dipakai:

```http
Authorization: Bearer <token>
Accept: application/json
```

## 8. Kalau API 500 di VPS

Checklist cepat:

- Cek `storage/logs/laravel.log`
- Pastikan migrasi sudah selesai
- Pastikan `APP_DEBUG=false` saat production
- Pastikan route cache sudah di-refresh setelah perubahan route
- Pastikan token Sanctum dikirim lewat header `Authorization`

## 8a. Kalau muncul error DB user `forge`

Error yang sering muncul:

```text
SQLSTATE[HY000] [1045] Access denied for user 'forge'@'localhost' (using password: NO)
```

Artinya Laravel tidak membaca konfigurasi database dari `.env`, lalu fallback ke default bawaan di `config/database.php`:

- database: `forge`
- username: `forge`
- password: kosong

Checklist perbaikan:

1. Pastikan file `.env` benar-benar ada di server.
2. Pastikan isi database di `.env` sesuai server:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_kamu
DB_USERNAME=user_database_kamu
DB_PASSWORD=password_database_kamu
```

3. Bersihkan semua cache config Laravel:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

4. Verifikasi config yang sedang dipakai Laravel:

```bash
php artisan tinker --execute="dump(config('database.connections.mysql.host')); dump(config('database.connections.mysql.database')); dump(config('database.connections.mysql.username'));"
```

Kalau output masih menunjuk ke `forge`, berarti salah satu dari ini terjadi:

- `.env` belum ter-upload
- `.env` salah isi
- permission `.env` tidak bisa dibaca user web server
- config cache lama belum dibersihkan

5. Tes login manual ke MySQL dari server:

```bash
mysql -u user_database_kamu -p
```

Kalau login MySQL gagal, berarti problemnya memang di credential database, bukan di Laravel.

6. Setelah `.env` diubah, restart service PHP/web server bila perlu.

Contoh:

```bash
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

Sesuaikan nama servicenya dengan environment VPS.

## 9. Struktur deploy yang aman

Urutan paling aman:

1. Upload source code
2. Set `.env`
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan key:generate`
5. `php artisan migrate --force`
6. `php artisan storage:link`
7. `php artisan config:cache`
8. `php artisan route:cache`
9. Test login API
10. Test endpoint data

## 10. Catatan API mobile

- Login sekali, simpan token Bearer.
- Semua request berikutnya kirim `Authorization: Bearer <token>`.
- Kalau token dihapus lewat logout, login ulang diperlukan.
