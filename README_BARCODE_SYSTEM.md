# Sistem Barcode Transaksi

## Overview
Sistem barcode untuk transaksi penjualan yang memungkinkan kasir, passet, admin, dan super admin untuk scan barcode untuk pencarian data transaksi dengan cepat dan update status pengerjaan.

## Fitur Utama

### 1. Generate Barcode Otomatis
- Barcode otomatis dibuat saat transaksi baru dibuat
- Format: `TRX` + tanggal (YYYYMMDD) + ID transaksi (6 digit)
- Contoh: `TRX2024073000001`

### 2. Scan Barcode
- Scanner kamera menggunakan HTML5 QR Code
- Input manual untuk kode barcode
- Beep sound saat berhasil scan
- Real-time pencarian transaksi

### 3. Update Status Pengerjaan
- Update status berdasarkan role user:
  - **Passet**: Sedang Dikerjakan, Selesai Dikerjakan
  - **Kasir**: Sudah Diambil
  - **Admin/Super Admin**: Semua status
- Tracking waktu update status
- Konfirmasi sebelum update

### 4. Print Barcode
- Halaman print khusus barcode
- Informasi transaksi lengkap
- Format yang mudah diprint

## Struktur Database

### Tabel `penjualan`
- `barcode` (VARCHAR, UNIQUE) - Kode barcode transaksi

## Implementasi

### 1. BarcodeController
- `scan()` - Halaman scan barcode
- `search()` - Pencarian transaksi berdasarkan barcode
- `updateStatus()` - Update status pengerjaan
- `generateBarcode()` - Generate barcode untuk transaksi
- `printBarcode()` - Print barcode
- `bulkGenerateBarcode()` - Generate barcode untuk semua transaksi

### 2. PenjualanController
- Otomatis generate barcode saat transaksi dibuat
- Format: `TRX` + tanggal + ID

### 3. Views
- `barcode/scan.blade.php` - Halaman scan barcode
- `barcode/print.blade.php` - Halaman print barcode
- Update `penjualan/show.blade.php` - Tampilkan barcode dan tombol print

## Routes

```php
// Barcode routes
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
2. Sistem otomatis generate barcode
3. Barcode ditampilkan di detail transaksi
4. Kasir bisa print barcode

### 2. Scan dan Update Status
1. User akses halaman scan barcode
2. Scan barcode dengan kamera atau input manual
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
- **HTML5 QR Code Scanner**: Library untuk scan barcode
- **Chart.js**: Untuk visualisasi data
- **SweetAlert2**: Untuk konfirmasi dan notifikasi
- **jQuery**: Untuk AJAX dan DOM manipulation

### Backend
- **Laravel**: Framework utama
- **MySQL**: Database
- **Carbon**: Manipulasi tanggal

## Keuntungan

### 1. Efisiensi
- Pencarian transaksi cepat dengan scan
- Update status real-time
- Tidak perlu scroll atau search manual

### 2. Akurasi
- Barcode unik untuk setiap transaksi
- Validasi otomatis
- Tracking perubahan status

### 3. User Experience
- Interface yang user-friendly
- Feedback visual dan audio
- Responsive design

### 4. Audit Trail
- Log perubahan status
- Tracking user yang melakukan update
- Timestamp untuk setiap perubahan

## Cara Penggunaan

### 1. Untuk Kasir
1. Login sebagai kasir
2. Akses menu "Scan Barcode"
3. Scan barcode transaksi
4. Update status menjadi "Sudah Diambil"

### 2. Untuk Passet
1. Login sebagai passet
2. Akses menu "Scan Barcode"
3. Scan barcode transaksi
4. Update status sesuai progress pengerjaan

### 3. Untuk Admin/Super Admin
1. Login sebagai admin/super admin
2. Akses menu "Scan Barcode"
3. Scan barcode transaksi
4. Update status sesuai kebutuhan

## Troubleshooting

### 1. Barcode Tidak Terdeteksi
- Pastikan kamera berfungsi dengan baik
- Pastikan barcode tidak rusak atau terlipat
- Coba input manual jika scan gagal

### 2. Status Tidak Bisa Diupdate
- Cek role user
- Pastikan transaksi ditemukan
- Cek permission untuk update status

### 3. Barcode Tidak Muncul
- Generate barcode manual dari detail transaksi
- Cek apakah transaksi sudah memiliki barcode
- Gunakan bulk generate untuk transaksi lama

## Future Enhancement

### 1. Mobile App
- Aplikasi mobile untuk scan barcode
- Push notification untuk update status
- Offline mode

### 2. Advanced Features
- QR Code untuk sharing link transaksi
- Barcode dengan informasi tambahan
- Integration dengan printer thermal

### 3. Analytics
- Tracking waktu pengerjaan
- Analisis performa passet
- Report status transaksi

## Security

### 1. Authentication
- Semua route memerlukan login
- Role-based access control
- Session management

### 2. Validation
- Validasi input barcode
- Sanitasi data
- CSRF protection

### 3. Logging
- Log semua aktivitas scan
- Log perubahan status
- Audit trail lengkap 