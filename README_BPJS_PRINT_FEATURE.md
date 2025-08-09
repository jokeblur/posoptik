# Fitur Cetak BPJS - Modifikasi Halaman Cetak

## Deskripsi
Modifikasi halaman cetak penjualan untuk menangani layanan BPJS I, II, dan III dengan tampilan yang berbeda dari transaksi umum.

## Perubahan yang Dilakukan

### 1. Informasi Transaksi (Kedua Halaman Cetak)
- **Menambahkan**: Informasi jenis layanan BPJS
- **Menambahkan**: Nomor BPJS pasien
- **Lokasi**: Di bagian informasi transaksi setelah data dokter

### 2. Tabel Detail Produk

#### Halaman Cetak Half Page (cetak_half.blade.php)
- **Kolom Harga**: Disembunyikan untuk BPJS
- **Kolom Subtotal**: Diubah menjadi "Biaya BPJS"
- **Lebar Kolom**: Produk diperlebar (65% vs 45%)
- **Kondisi**: Hanya untuk pasien dengan layanan BPJS I, II, III

#### Halaman Cetak Struk (cetak.blade.php)
- **Informasi Harga**: Disembunyikan untuk BPJS
- **Kolom Header**: "Subtotal" menjadi "Biaya BPJS"
- **Detail Produk**: Tidak menampilkan "Qty x Harga" untuk BPJS

### 3. Bagian Total

#### Untuk BPJS:
- **Subtotal**: Diubah menjadi "Biaya BPJS"
- **Diskon**: Disembunyikan (tidak ditampilkan)
- **Biaya Tambahan**: Tetap ditampilkan jika ada
- **Total**: Tetap ditampilkan
- **Dibayar**: Tetap ditampilkan
- **Kekurangan**: Tetap ditampilkan

#### Untuk Non-BPJS:
- **Subtotal**: Tetap seperti biasa
- **Diskon**: Tetap ditampilkan
- **Biaya Tambahan**: Tetap ditampilkan jika ada
- **Total**: Tetap ditampilkan
- **Dibayar**: Tetap ditampilkan
- **Kekurangan**: Tetap ditampilkan

## Logika Deteksi BPJS

```php
$isBPJS = $penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']);
```

## File yang Dimodifikasi

### 1. resources/views/penjualan/cetak_half.blade.php
- **Informasi Transaksi**: Menambahkan jenis layanan dan nomor BPJS
- **Tabel Detail**: Modifikasi kolom untuk BPJS
- **Bagian Total**: Kondisional untuk BPJS vs non-BPJS

### 2. resources/views/penjualan/cetak.blade.php
- **Informasi Transaksi**: Menambahkan jenis layanan dan nomor BPJS
- **Tabel Detail**: Modifikasi tampilan untuk BPJS
- **Bagian Total**: Kondisional untuk BPJS vs non-BPJS

## Tampilan yang Berbeda

### Untuk Pasien BPJS:
```
┌─────────────────────────────────────┐
│ Jenis Layanan: BPJS I               │
│ No. BPJS: 1234567890123456          │
├─────────────────────────────────────┤
│ No │ Produk                    │ Qty │ Biaya BPJS │
├────┼───────────────────────────┼─────┼────────────┤
│ 1  │ Frame Premium             │ 1   │ 150.000    │
│ 2  │ Lensa Anti Radiasi        │ 1   │ 200.000    │
├─────────────────────────────────────┤
│ Biaya BPJS: Rp 350.000              │
│ TOTAL: Rp 350.000                   │
│ Dibayar: Rp 350.000                 │
└─────────────────────────────────────┘
```

### Untuk Pasien Umum:
```
┌─────────────────────────────────────┐
├─────────────────────────────────────┤
│ No │ Produk                    │ Qty │ Harga      │ Subtotal   │
├────┼───────────────────────────┼─────┼────────────┼────────────┤
│ 1  │ Frame Premium             │ 1   │ 500.000    │ 500.000    │
│ 2  │ Lensa Anti Radiasi        │ 1   │ 300.000    │ 300.000    │
├─────────────────────────────────────┤
│ Subtotal: Rp 800.000                │
│ Diskon: Rp 50.000                   │
│ TOTAL: Rp 750.000                   │
│ Dibayar: Rp 750.000                 │
└─────────────────────────────────────┘
```

## Keuntungan

### 1. Kesesuaian Regulasi BPJS
- Tidak menampilkan harga satuan yang bisa membingungkan
- Fokus pada biaya BPJS yang sudah ditentukan
- Menghindari kebingungan pasien tentang harga

### 2. Kejelasan Informasi
- Jenis layanan BPJS jelas terlihat
- Nomor BPJS tersedia untuk referensi
- Biaya yang ditampilkan sesuai dengan ketentuan BPJS

### 3. Konsistensi
- Kedua halaman cetak (struk dan half page) konsisten
- Tampilan yang seragam untuk semua transaksi BPJS
- Tidak ada inkonsistensi informasi

## Testing

### Test Case 1: Pasien BPJS I
1. Buat transaksi untuk pasien dengan layanan BPJS I
2. Cetak struk dan half page
3. Verifikasi informasi BPJS ditampilkan
4. Verifikasi harga satuan disembunyikan
5. Verifikasi diskon disembunyikan

### Test Case 2: Pasien Umum
1. Buat transaksi untuk pasien umum
2. Cetak struk dan half page
3. Verifikasi informasi BPJS tidak ditampilkan
4. Verifikasi harga satuan ditampilkan
5. Verifikasi diskon ditampilkan

### Test Case 3: Pasien BPJS II/III
1. Buat transaksi untuk pasien BPJS II atau III
2. Verifikasi tampilan sama dengan BPJS I
3. Verifikasi semua informasi BPJS ditampilkan

## Catatan Penting

- Perubahan hanya mempengaruhi tampilan cetak, tidak mengubah data yang disimpan
- Logika deteksi BPJS berdasarkan `service_type` pasien
- Jika pasien tidak memiliki data `service_type`, akan dianggap sebagai pasien umum
- Perubahan berlaku untuk kedua jenis cetak (struk dan half page) 