# Sistem QR Code Transaksi

## Overview
Sistem QR Code untuk transaksi penjualan yang memungkinkan kasir, passet, admin, dan super admin untuk scan QR Code untuk pencarian data transaksi dengan cepat dan update status pengerjaan.

## Fitur Utama

### 1. Generate QR Code Otomatis
- QR Code otomatis dibuat saat transaksi baru dibuat
- Format: `TRX` + tanggal (YYYYMMDD) + ID transaksi (6 digit)
- Contoh: `TRX2024073000001`

### 2. Scan QR Code
- Scanner kamera menggunakan HTML5 QR Code
- Input manual untuk kode QR Code
- Beep sound saat berhasil scan
- Real-time pencarian transaksi

### 3. Update Status Pengerjaan
- Update status berdasarkan role user:
  - **Passet**: Sedang Dikerjakan, Selesai Dikerjakan
  - **Kasir**: Sudah Diambil
  - **Admin/Super Admin**: Semua status
- Tracking waktu update status
- Konfirmasi sebelum update

### 4. Print QR Code
- Halaman print khusus QR Code
- Informasi transaksi lengkap
- Format yang mudah diprint

## Struktur Database

### Tabel `penjualan`
- `barcode` (VARCHAR, UNIQUE) - Kode QR Code transaksi (tetap menggunakan nama field barcode untuk kompatibilitas)

## Implementasi

### 1. QR Code Library
- Menggunakan `simplesoftwareio/simple-qrcode`
- Generate QR Code dengan ukuran yang dapat disesuaikan
- Support untuk berbagai format QR Code

### 2. BarcodeController
- `scan()` - Halaman scan QR Code
- `search()` - Pencarian transaksi berdasarkan QR Code
- `updateStatus()` - Update status pengerjaan
- `generateBarcode()` - Generate QR Code untuk transaksi
- `printBarcode()` - Print QR Code
- `bulkGenerateBarcode()` - Generate QR Code untuk semua transaksi

### 3. PenjualanController
- Otomatis generate QR Code saat transaksi dibuat
- Format: `TRX` + tanggal + ID

### 4. Views
- `barcode/scan.blade.php` - Halaman scan QR Code
- `barcode/print.blade.php` - Halaman print QR Code
- Update `penjualan/show.blade.php` - Tampilkan QR Code dan tombol print
- Update `penjualan/cetak.blade.php` - QR Code di struk transaksi
- Update `penjualan/cetak_half.blade.php` - QR Code di cetak half page

## Routes

```php
// QR Code routes
Route::get('/barcode/scan', [BarcodeController::class, 'scan'])->name('barcode.scan');
Route::post('/barcode/search', [BarcodeController::class, 'search'])->name('barcode.search');
Route::post('/barcode/update-status', [BarcodeController::class, 'updateStatus'])->name('barcode.update-status');
Route::post('/barcode/generate', [BarcodeController::class, 'generateBarcode'])->name('barcode.generate');
Route::get('/barcode/print/{id}', [BarcodeController::class, 'printBarcode'])->name('barcode.print');
Route::post('/barcode/bulk-generate', [BarcodeController::class, 'bulkGenerateBarcode'])->name('barcode.bulk-generate');
```

## Workflow

### 1. Transaksi Baru
1. Kasir membuat transaksi baru
2. Sistem otomatis generate QR Code
3. QR Code ditampilkan di detail transaksi
4. Kasir bisa print QR Code

### 2. Scan dan Update Status
1. User akses halaman scan QR Code
2. Scan QR Code dengan kamera atau input manual
3. Sistem menampilkan detail transaksi
4. User pilih status baru
5. Konfirmasi update status
6. Status berhasil diupdate

### 3. Role-based Access
- **Kasir**: Hanya bisa update status "Sudah Diambil"
- **Passet**: Bisa update status "Sedang Dikerjakan" dan "Selesai Dikerjakan"
- **Admin/Super Admin**: Bisa update semua status

## Teknologi yang Digunakan

### Frontend
- **HTML5 QR Code Scanner**: Library untuk scan QR Code
- **Chart.js**: Untuk visualisasi data
- **SweetAlert2**: Untuk konfirmasi dan notifikasi
- **jQuery**: Untuk AJAX dan DOM manipulation

### Backend
- **Laravel**: Framework utama
- **MySQL**: Database
- **Carbon**: Manipulasi tanggal
- **Simple QR Code**: Generate QR Code

## Keuntungan QR Code vs Barcode

### 1. Kapasitas Data
- QR Code: Hingga 7,089 karakter numerik
- Barcode: Hanya 20-25 karakter

### 2. Error Correction
- QR Code: Built-in error correction
- Barcode: Tidak ada error correction

### 3. Scanning
- QR Code: Bisa di-scan dari berbagai sudut
- Barcode: Harus sejajar dengan scanner

### 4. Ukuran
- QR Code: Lebih kecil untuk data yang sama
- Barcode: Lebih besar untuk data yang sama

## Cara Penggunaan

### 1. Untuk Kasir
1. Login sebagai kasir
2. Akses menu "Scan QR Code"
3. Scan QR Code transaksi
4. Update status menjadi "Sudah Diambil"

### 2. Untuk Passet
1. Login sebagai passet
2. Akses menu "Scan QR Code"
3. Scan QR Code transaksi
4. Update status sesuai progress pengerjaan

### 3. Untuk Admin/Super Admin
1. Login sebagai admin/super admin
2. Akses menu "Scan QR Code"
3. Scan QR Code transaksi
4. Update status sesuai kebutuhan

## QR Code di Cetak Transaksi

### 1. Struk Transaksi
- QR Code ditampilkan setelah informasi pasien
- Ukuran: 100px x 100px
- Label: "SCAN QR CODE"
- Border dashed untuk highlight

### 2. Cetak Half Page
- QR Code ditampilkan dalam section khusus
- Ukuran: 150px x 150px
- Label: "SCAN QR CODE UNTUK UPDATE STATUS"
- Border solid untuk emphasis

### 3. Print QR Code
- Halaman khusus untuk print QR Code
- Ukuran: 200px x 200px
- Informasi transaksi lengkap
- Format yang mudah diprint

## Troubleshooting

### 1. QR Code Tidak Terdeteksi
- Pastikan kamera berfungsi dengan baik
- Pastikan QR Code tidak rusak atau terlipat
- Coba input manual jika scan gagal

### 2. Status Tidak Bisa Diupdate
- Cek role user
- Pastikan transaksi ditemukan
- Cek permission untuk update status

### 3. QR Code Tidak Muncul
- Generate QR Code manual dari detail transaksi
- Cek apakah transaksi sudah memiliki QR Code
- Gunakan bulk generate untuk transaksi lama

### 4. QR Code Tidak Terprint
- Pastikan ukuran QR Code sesuai dengan printer
- Cek kualitas print
- Test print dulu

## Future Enhancement

### 1. Mobile App
- Aplikasi mobile untuk scan QR Code
- Push notification untuk update status
- Offline mode

### 2. Advanced Features
- QR Code dengan informasi tambahan (link, data JSON)
- QR Code untuk sharing link transaksi
- Integration dengan printer thermal

### 3. Analytics
- Tracking waktu pengerjaan
- Analisis performa passet
- Report status transaksi

### 4. QR Code Customization
- Logo di tengah QR Code
- Warna QR Code yang dapat disesuaikan
- Template QR Code yang berbeda

## Security

### 1. Authentication
- Semua route memerlukan login
- Role-based access control
- Session management

### 2. Validation
- Validasi input QR Code
- Sanitasi data
- CSRF protection

### 3. Logging
- Log semua aktivitas scan
- Log perubahan status
- Audit trail lengkap

## Performance

### 1. QR Code Generation
- Caching QR Code yang sudah dibuat
- Optimized library usage
- Efficient storage

### 2. Scanning Performance
- Fast QR Code detection
- Minimal lag saat scan
- Responsive interface

### 3. Print Quality
- High resolution QR Code
- Clear printing
- Proper sizing 