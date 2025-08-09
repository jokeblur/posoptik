# Perbaikan Logika Transaksi Penjualan BPJS

## Overview
Perbaikan logika transaksi penjualan untuk menangani kasus BPJS dengan benar sesuai dengan requirement yang diminta, termasuk penyimpanan harga default BPJS di database untuk keperluan laporan.

## Perubahan Logika

### 1. Jika Jenis Layanan Pasien Sama dengan Jenis Frame
- **Kondisi**: Pasien BPJS I memilih frame BPJS I, BPJS II memilih frame BPJS II, BPJS III memilih frame BPJS III
- **Jumlah Bayar**: Harga default BPJS (disimpan di database)
- **Status**: Lunas
- **Biaya Tambahan**: 0

### 2. Jika Jenis Frame Berbeda dengan Jenis Layanan
- **Kondisi**: Pasien BPJS memilih frame yang berbeda dengan layanannya (naik kelas)
- **Jumlah Bayar**: Harga default BPJS (disimpan di database)
- **Status**: Naik Kelas
- **Biaya Tambahan**: Kekurangan (harga frame - harga default BPJS)

## Perubahan Database

### Tabel `penjualan`
- Menambahkan kolom `transaction_status` (ENUM: 'Normal', 'Naik Kelas')
- Menambahkan kolom `pasien_service_type` (VARCHAR) - menyimpan jenis layanan BPJS
- Menambahkan kolom `bpjs_default_price` (DECIMAL) - menyimpan harga default BPJS
- Menambahkan kolom `total_additional_cost` (DECIMAL) - total biaya tambahan

### Tabel `penjualan_detail`
- Menambahkan kolom `additional_cost` (DECIMAL) untuk menyimpan biaya tambahan per item

## Perubahan Kode

### 1. BpjsPricingService.php
- Memperbaiki method `calculateFramePrice()` untuk mengembalikan status transaksi
- Menambahkan field `status` pada hasil perhitungan
- Menyederhanakan logika perhitungan biaya tambahan

### 2. PenjualanController.php
- Menambahkan logika untuk menyimpan `transaction_status`, `additional_cost`, dan informasi BPJS
- Update method `store()` untuk menangani status transaksi dan harga default
- Menambahkan kolom `status_transaksi` di method `data()`

### 3. LaporanBpjsController.php (BARU)
- Controller khusus untuk laporan transaksi BPJS
- Method `index()` - tampilan laporan
- Method `data()` - data untuk DataTables
- Method `summary()` - ringkasan statistik
- Method `export()` - export ke CSV

### 4. Views
- **show.blade.php**: Menampilkan status transaksi, biaya tambahan, dan informasi BPJS
- **index.blade.php**: Menambahkan kolom status transaksi di daftar penjualan
- **laporan/bpjs/index.blade.php** (BARU): Halaman laporan BPJS dengan filter dan summary

## Contoh Implementasi

### Kasus 1: BPJS III memilih frame BPJS III
```
Pasien: BPJS III
Frame: BPJS III
Harga Default: Rp 165.000
Jumlah Bayar: Rp 165.000
Status: Lunas
Biaya Tambahan: Rp 0
```

### Kasus 2: BPJS III memilih frame BPJS I (Naik Kelas)
```
Pasien: BPJS III
Frame: BPJS I
Harga Frame: Rp 500.000
Harga Default BPJS III: Rp 165.000
Jumlah Bayar: Rp 165.000 (harga default)
Status: Naik Kelas
Biaya Tambahan: Rp 335.000 (500.000 - 165.000)
```

## Fitur Laporan BPJS (BARU)

### 1. Filter Laporan
- Filter berdasarkan tanggal (start_date, end_date)
- Filter berdasarkan jenis transaksi:
  - BPJS Normal
  - BPJS Naik Kelas
  - Transaksi Umum
  - Semua BPJS

### 2. Summary Cards
- Total Transaksi
- BPJS Normal
- BPJS Naik Kelas
- Transaksi Umum

### 3. Summary Table
- Jumlah transaksi per jenis
- Total pendapatan per jenis
- Total harga default BPJS
- Total biaya tambahan

### 4. Data Table
- Detail transaksi dengan informasi lengkap
- Kolom: Tanggal, Kode, Pasien, Layanan, Status, Total, Default BPJS, Biaya Tambahan, Kasir, Cabang

### 5. Export Data
- Export ke format CSV
- Mengikuti filter yang dipilih

## Migration
Jalankan migration untuk menambahkan kolom baru:
```bash
php artisan migrate
```

## Routes
```php
// Laporan BPJS routes
Route::get('/laporan-bpjs', [LaporanBpjsController::class, 'index'])->name('laporan.bpjs');
Route::get('/laporan-bpjs/data', [LaporanBpjsController::class, 'data'])->name('laporan.bpjs.data');
Route::get('/laporan-bpjs/summary', [LaporanBpjsController::class, 'summary'])->name('laporan.bpjs.summary');
Route::get('/laporan-bpjs/export', [LaporanBpjsController::class, 'export'])->name('laporan.bpjs.export');
```

## Testing
Untuk menguji logika pricing BPJS, gunakan endpoint:
- `POST /penjualan/calculate-bpjs-price`
- `POST /penjualan/test-bpjs-pricing`

## Keuntungan Penyimpanan Harga Default BPJS

### 1. Laporan Akurat
- Data harga default tersimpan di database
- Tidak bergantung pada perhitungan real-time
- Konsistensi data historis

### 2. Analisis Bisnis
- Perbandingan pendapatan BPJS vs Umum
- Analisis tren naik kelas
- Perhitungan margin per jenis layanan

### 3. Audit Trail
- Tracking perubahan harga default BPJS
- Riwayat transaksi lengkap
- Compliance dengan regulasi

## Catatan
- Logika ini hanya berlaku untuk pasien dengan service_type BPJS I, BPJS II, atau BPJS III
- Untuk pasien non-BPJS, harga tetap menggunakan harga normal frame
- Status transaksi akan otomatis diupdate berdasarkan pilihan frame pasien
- Harga default BPJS disimpan untuk keperluan laporan dan audit
