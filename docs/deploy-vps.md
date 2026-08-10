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
