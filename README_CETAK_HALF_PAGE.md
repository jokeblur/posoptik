# Fitur Cetak Transaksi Half Page

## Overview
Fitur cetak transaksi untuk printer biasa dengan ukuran setengah halaman (A5) yang menampilkan barcode transaksi untuk memudahkan tracking dan update status.

## Fitur Utama

### 1. Ukuran Half Page (A5)
- Ukuran kertas: A5 (148mm x 210mm)
- Margin: 10mm
- Font: Courier New untuk konsistensi printer
- Optimized untuk printer biasa

### 2. Header dengan Logo
- Logo Optik Melati dari folder public
- Nama perusahaan: "OPTIK MELATI"
- Nama cabang dan alamat
- Nomor telepon cabang

### 3. Informasi Transaksi Lengkap
- Kode transaksi
- Tanggal dan waktu
- Kasir yang melayani
- Nama pasien
- Dokter yang menangani
- Status pembayaran dan pengerjaan
- Tanggal siap (jika ada)

### 4. Barcode Transaksi
- Tampil jika transaksi memiliki barcode
- Format: `*BARCODE*` dengan font khusus
- Label "SCAN BARCODE UNTUK UPDATE STATUS"
- Memudahkan tracking dengan scanner

### 5. Detail Produk
- Tabel produk dengan border
- Informasi: No, Produk, Qty, Harga, Subtotal
- Support untuk Frame, Lensa, dan Aksesoris
- Tampilan jenis frame untuk produk frame

### 6. Rincian Pembayaran
- Subtotal
- Biaya tambahan (jika ada)
- Diskon
- Total (highlighted)
- Dibayar
- Kekurangan (jika ada)

### 7. Footer
- Pesan terima kasih
- Kebijakan pengembalian
- Timestamp cetak

## Implementasi

### 1. Controller Method
```php
public function cetakHalf($id)
{
    $penjualan = Transaksi::with('details.itemable', 'user', 'branch', 'pasien', 'dokter')->findOrFail($id);
    return view('penjualan.cetak_half', compact('penjualan'));
}
```

### 2. Route
```php
Route::get('/penjualan/{penjualan}/cetak-half', [PenjualanController::class, 'cetakHalf'])->name('penjualan.cetak-half');
```

### 3. View
- File: `resources/views/penjualan/cetak_half.blade.php`
- CSS optimized untuk print
- Responsive design

## Styling CSS

### 1. Page Setup
```css
@page {
    size: A5;
    margin: 10mm;
}
```

### 2. Typography
- Font: Courier New (monospace)
- Base size: 10px
- Line height: 1.2
- Optimized untuk printer

### 3. Layout
- Header dengan logo dan informasi cabang
- Informasi transaksi dalam format flex
- Tabel produk dengan border
- Total section dengan highlight
- Footer dengan pesan

### 4. Status Badges
- Warna berbeda untuk setiap status
- Background color untuk visibility
- Font size kecil untuk efisiensi ruang

## Keuntungan

### 1. Efisiensi Kertas
- Ukuran A5 menghemat kertas
- Informasi lengkap dalam setengah halaman
- Cocok untuk printer biasa

### 2. Barcode Integration
- Barcode transaksi terintegrasi
- Memudahkan tracking dengan scanner
- Update status cepat

### 3. Informasi Lengkap
- Semua informasi transaksi penting
- Status pembayaran dan pengerjaan
- Detail produk dan rincian biaya

### 4. Professional Look
- Header dengan logo perusahaan
- Layout yang rapi dan terstruktur
- Font yang konsisten

## Cara Penggunaan

### 1. Dari Detail Transaksi
1. Buka detail transaksi
2. Klik tombol "Cetak Half Page"
3. Halaman cetak akan terbuka di tab baru
4. Klik "Print Transaksi" atau Ctrl+P

### 2. Print Settings
- Paper size: A5
- Orientation: Portrait
- Margins: Default
- Scale: 100%

### 3. Printer Setup
- Pilih printer biasa
- Kualitas print: Normal
- Paper type: Plain paper

## Perbedaan dengan Cetak Full Page

| Aspek | Full Page | Half Page |
|-------|-----------|-----------|
| Ukuran | A4 | A5 |
| Kertas | Lebih banyak | Hemat |
| Informasi | Lengkap + detail | Lengkap + ringkas |
| Barcode | Tidak ada | Ada |
| Printer | Thermal/Receipt | Printer biasa |
| Layout | Receipt style | Document style |

## Troubleshooting

### 1. Logo Tidak Muncul
- Pastikan file `optik melati.png` ada di folder `public`
- Cek permission file
- Refresh cache browser

### 2. Print Tidak Rapi
- Pastikan ukuran A5 di printer settings
- Cek margin printer
- Test print dulu

### 3. Barcode Tidak Tampil
- Pastikan transaksi memiliki barcode
- Generate barcode jika belum ada
- Cek format barcode

### 4. Font Tidak Konsisten
- Pastikan Courier New terinstall
- Gunakan font fallback
- Cek browser compatibility

## Future Enhancement

### 1. QR Code
- Tambahkan QR code untuk link transaksi
- Memudahkan akses mobile
- Integration dengan app

### 2. Customization
- Template yang bisa dikustomisasi
- Logo cabang yang berbeda
- Layout yang fleksibel

### 3. Batch Print
- Print multiple transaksi
- Bulk generate barcode
- Export PDF

### 4. Digital Copy
- Email transaksi
- WhatsApp integration
- Cloud storage

## Security

### 1. Access Control
- Hanya user yang berhak bisa cetak
- Log aktivitas cetak
- Watermark untuk security

### 2. Data Protection
- Tidak expose sensitive data
- Masking untuk informasi pribadi
- Secure print options

## Performance

### 1. Loading Speed
- Optimized CSS
- Minimal JavaScript
- Efficient queries

### 2. Print Quality
- High resolution logo
- Clear typography
- Proper spacing

### 3. File Size
- Compressed images
- Minimal external resources
- Efficient HTML structure 