# Perbaikan Bug Logout di VPS

## Masalah yang Ditemukan
Setelah deploy ke VPS, aplikasi mengalami masalah:
- Login berhasil
- Setelah logout, URL tidak bisa diakses lagi
- Error 404 atau redirect loop

## Penyebab Masalah
1. **Konflik Guard Authentication**: Aplikasi menggunakan `auth:sanctum` di route utama tetapi `web` guard di Fortify
2. **Session Configuration**: Session driver menggunakan database yang mungkin bermasalah di VPS
3. **Route Protection**: Beberapa route tidak memiliki fallback yang tepat

## Perbaikan yang Dilakukan

### 1. Perbaikan Guard Authentication
**File: `routes/web.php`**
```php
// SEBELUM
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

// SESUDAH
Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
```

**File: `config/jetstream.php`**
```php
// SEBELUM
'guard' => 'sanctum',

// SESUDAH
'guard' => 'web',
```

### 2. Perbaikan Session Configuration
**File: `config/session.php`**
```php
// SEBELUM
'driver' => env('SESSION_DRIVER', 'database'),

// SESUDAH
'driver' => env('SESSION_DRIVER', 'file'),
```

### 3. Penambahan Route Fallback
**File: `routes/web.php`**
```php
// Route khusus untuk logout dengan redirect yang aman
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Fallback route untuk menangani masalah akses setelah logout
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
```

### 4. Perbaikan Middleware Authenticate
**File: `app/Http/Middleware/Authenticate.php`**
```php
protected function redirectTo($request)
{
    if (! $request->expectsJson()) {
        // Pastikan route login tersedia
        if (Route::has('login')) {
            return route('login');
        }
        // Fallback ke URL login jika route tidak tersedia
        return url('/login');
    }
}
```

## Konfigurasi Environment untuk VPS

Pastikan file `.env` di VPS memiliki konfigurasi berikut:

```env
APP_NAME="POS Optik Melati"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=posoptikmelati
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

## Langkah Deployment

1. **Upload file yang sudah diperbaiki ke VPS**
2. **Set permissions yang tepat:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   chown -R www-data:www-data storage/
   chown -R www-data:www-data bootstrap/cache/
   ```

3. **Clear cache dan config:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

4. **Generate key baru jika diperlukan:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan migration jika ada:**
   ```bash
   php artisan migrate --force
   ```

## Testing

Setelah deployment, test fitur berikut:
1. Login ke aplikasi
2. Navigasi ke berbagai halaman
3. Logout dari aplikasi
4. Coba akses URL yang sebelumnya tidak bisa diakses
5. Login kembali

## Catatan Penting

- Pastikan `APP_URL` di `.env` sesuai dengan domain VPS
- Jika menggunakan HTTPS, set `SESSION_SECURE_COOKIE=true`
- Pastikan folder `storage/framework/sessions` memiliki permission yang tepat
- Monitor log error di `storage/logs/laravel.log` jika masih ada masalah

## Troubleshooting

Jika masih ada masalah:

1. **Check log error:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Test route:**
   ```bash
   php artisan route:list
   ```

3. **Check session:**
   ```bash
   ls -la storage/framework/sessions/
   ```

4. **Clear semua cache:**
   ```bash
   php artisan optimize:clear
   ```
