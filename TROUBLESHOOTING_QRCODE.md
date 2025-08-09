# Troubleshooting QR Code Scan

## Masalah: "Transaksi tidak ditemukan" saat scan QR Code

### 1. Penyebab Umum

#### A. QR Code tidak di-generate dengan benar

-   **Gejala**: QR Code kosong atau tidak muncul
-   **Solusi**:
    -   Pastikan transaksi memiliki barcode
    -   Cek apakah QR Code library terinstall: `composer require simplesoftwareio/simple-qrcode`
    -   Cek apakah ada error di console browser

#### B. URL QR Code salah

-   **Gejala**: QR Code muncul tapi scan tidak berfungsi
-   **Solusi**:
    -   Cek URL yang di-generate: `{{ url('/barcode/scan/' . $penjualan->barcode) }}`
    -   Pastikan route `barcode.scan.direct` terdaftar
    -   Test URL manual di browser

#### C. Transaksi tidak ada di database

-   **Gejala**: Error "Transaksi tidak ditemukan"
-   **Solusi**:
    -   Cek apakah barcode ada di database
    -   Cek apakah transaksi memiliki data lengkap

### 2. Langkah Troubleshooting

#### Step 1: Cek Data Transaksi

```bash
# Cek total transaksi
php artisan tinker --execute="echo 'Total transaksi: ' . App\Models\Transaksi::count();"

# Cek transaksi dengan barcode
php artisan tinker --execute="echo 'Transaksi dengan barcode: ' . App\Models\Transaksi::whereNotNull('barcode')->count();"

# Cek sample barcode
php artisan tinker --execute="echo 'Sample barcode: ' . App\Models\Transaksi::whereNotNull('barcode')->first()->barcode ?? 'Tidak ada';"
```

#### Step 2: Cek Route

```bash
# Cek route barcode
php artisan route:list | grep barcode

# Cek route scan direct
php artisan route:list | grep barcode.scan.direct
```

#### Step 3: Cek URL yang di-generate

```bash
# Test URL generation
php artisan tinker --execute="echo url('/barcode/scan/TRX20250730000038');"
```

#### Step 4: Cek Log

```bash
# Cek log Laravel
tail -f storage/logs/laravel.log

# Atau cek log terbaru
cat storage/logs/laravel.log | tail -20
```

### 3. Debug QR Code Generation

#### A. Test QR Code Generation

Buat halaman test: `resources/views/test_qrcode.blade.php`

```php
<!DOCTYPE html>
<html>
<head>
    <title>Test QR Code</title>
</head>
<body>
    <h1>Test QR Code Generation</h1>

    <h2>Barcode: TRX20250730000038</h2>
    <p>URL: {{ url('/barcode/scan/TRX20250730000038') }}</p>

    <h3>QR Code:</h3>
    <div>
        {!! QrCode::size(200)->generate(url('/barcode/scan/TRX20250730000038')) !!}
    </div>

    <h3>Test Link:</h3>
    <a href="{{ url('/barcode/scan/TRX20250730000038') }}" target="_blank">
        Test Scan Direct
    </a>
</body>
</html>
```

#### B. Tambah Route Test

```php
// Di routes/web.php
Route::get('/test-qrcode', function() {
    return view('test_qrcode');
})->name('test.qrcode');
```

### 4. Debug Controller

#### A. Tambah Logging di BarcodeController

```php
public function scanDirect($barcode)
{
    // Log untuk debug
    Log::info('Scan Direct - Barcode: ' . $barcode);

    // Cari transaksi berdasarkan barcode
    $transaksi = Transaksi::with('user', 'branch', 'pasien', 'dokter', 'details.itemable')
        ->where('barcode', $barcode)
        ->first();

    Log::info('Scan Direct - Transaksi found: ' . ($transaksi ? 'Yes' : 'No'));

    if (!$transaksi) {
        Log::warning('Scan Direct - Transaksi tidak ditemukan untuk barcode: ' . $barcode);
        return view('barcode.scan_direct', ['error' => 'Transaksi tidak ditemukan untuk barcode: ' . $barcode]);
    }

    Log::info('Scan Direct - Transaksi ditemukan: ' . $transaksi->kode_penjualan);
    return view('barcode.scan_direct', ['transaksi' => $transaksi, 'barcode' => $barcode]);
}
```

#### B. Cek Query Database

```php
// Test query manual
php artisan tinker --execute="echo 'Transaksi: ' . App\Models\Transaksi::where('barcode', 'TRX20250730000038')->first()->kode_penjualan ?? 'Tidak ada';"

// Cek relationship
php artisan tinker --execute="echo 'Pasien: ' . App\Models\Transaksi::where('barcode', 'TRX20250730000038')->first()->pasien->nama_pasien ?? 'Tidak ada';"
```

### 5. Solusi Umum

#### A. Clear Cache

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### B. Cek Dependencies

```bash
# Cek QR Code library
composer show simplesoftwareio/simple-qrcode

# Reinstall jika perlu
composer require simplesoftwareio/simple-qrcode
```

#### C. Cek Database

```bash
# Cek migration
php artisan migrate:status

# Rollback dan migrate ulang jika perlu
php artisan migrate:rollback --step=1
php artisan migrate
```

### 6. Test Manual

#### A. Test URL Langsung

1. Buka browser
2. Akses: `http://localhost/opmelati/barcode/scan/TRX20250730000038`
3. Cek apakah halaman muncul

#### B. Test QR Code Scanner

1. Buka aplikasi QR Code scanner di HP
2. Scan QR Code dari halaman test
3. Cek apakah URL terbuka di browser

#### C. Test Update Status

1. Buka halaman scan direct
2. Pilih status baru
3. Klik update status
4. Cek apakah berhasil

### 7. Error Messages

#### A. "Transaksi tidak ditemukan"

-   **Penyebab**: Barcode tidak ada di database
-   **Solusi**: Cek apakah transaksi memiliki barcode

#### B. "Route not found"

-   **Penyebab**: Route tidak terdaftar
-   **Solusi**: Cek routes/web.php dan clear cache

#### C. "Class not found"

-   **Penyebab**: Library tidak terinstall
-   **Solusi**: Install QR Code library

#### D. "CSRF token mismatch"

-   **Penyebab**: CSRF token tidak valid
-   **Solusi**: Tambah meta tag CSRF di view

### 8. Best Practices

#### A. QR Code Generation

-   Gunakan URL lengkap: `url('/barcode/scan/' . $barcode)`
-   Pastikan ukuran QR Code minimal 100px
-   Test QR Code dengan berbagai scanner

#### B. Error Handling

-   Tambah logging untuk debug
-   Berikan pesan error yang informatif
-   Handle kasus transaksi tidak ditemukan

#### C. Security

-   Validasi barcode format
-   Rate limiting untuk mencegah abuse
-   Logging untuk audit trail

### 9. Monitoring

#### A. Log Monitoring

```bash
# Monitor log real-time
tail -f storage/logs/laravel.log | grep "Scan Direct"

# Cek error log
tail -f storage/logs/laravel.log | grep "ERROR"
```

#### B. Database Monitoring

```sql
-- Cek transaksi tanpa barcode
SELECT COUNT(*) FROM penjualan WHERE barcode IS NULL;

-- Cek transaksi dengan barcode
SELECT COUNT(*) FROM penjualan WHERE barcode IS NOT NULL;
```

### 10. Contact Support

Jika masalah masih berlanjut:

1. Cek log error lengkap
2. Screenshot error message
3. Informasi environment (PHP version, Laravel version)
4. Steps to reproduce
