# Fitur Biaya Default BPJS - Modifikasi Data Biaya BPJS

## Deskripsi
Modifikasi tampilan biaya BPJS untuk menampilkan biaya default BPJS sesuai layanannya (BPJS I, II, III) di semua halaman terkait transaksi.

## Perubahan yang Dilakukan

### 1. Halaman Detail Transaksi (show.blade.php)

#### Tabel Detail Produk:
- **Kolom "Biaya BPJS"**: Menampilkan `bpjs_default_price` dari transaksi
- **Data**: Menggunakan biaya default BPJS sesuai layanan pasien
- **Kondisi**: Hanya untuk pasien dengan layanan BPJS I, II, III

#### Rincian Pembayaran:
- **"Biaya BPJS"**: Menampilkan `bpjs_default_price` dari transaksi
- **Data**: Menggunakan biaya default BPJS sesuai layanan pasien

### 2. Halaman Cetak Half Page (cetak_half.blade.php)

#### Tabel Detail Produk:
- **Kolom "Biaya BPJS"**: Menampilkan `bpjs_default_price` dari transaksi
- **Data**: Menggunakan biaya default BPJS sesuai layanan pasien

#### Bagian Total:
- **"Biaya BPJS"**: Menampilkan `bpjs_default_price` dari transaksi
- **Data**: Menggunakan biaya default BPJS sesuai layanan pasien

### 3. Halaman Cetak Struk (cetak.blade.php)

#### Tabel Detail Produk:
- **Kolom "Biaya BPJS"**: Menampilkan `bpjs_default_price` dari transaksi
- **Data**: Menggunakan biaya default BPJS sesuai layanan pasien

#### Bagian Total:
- **"Biaya BPJS"**: Menampilkan `bpjs_default_price` dari transaksi
- **Data**: Menggunakan biaya default BPJS sesuai layanan pasien

## Data yang Digunakan

### Field Database:
- **`bpjs_default_price`**: Biaya default BPJS yang disimpan di tabel `penjualan`
- **`pasien_service_type`**: Jenis layanan BPJS (BPJS I, II, III)

### Logika Tampilan:
```php
// Untuk BPJS
if($isBPJS) {
    echo format_uang($penjualan->bpjs_default_price);
} else {
    echo format_uang($detail->subtotal);
}
```

## File yang Dimodifikasi

### 1. resources/views/penjualan/show.blade.php
- **Tabel Detail Produk**: Data kolom "Biaya BPJS" menggunakan `bpjs_default_price`
- **Rincian Pembayaran**: Data "Biaya BPJS" menggunakan `bpjs_default_price`

### 2. resources/views/penjualan/cetak_half.blade.php
- **Tabel Detail Produk**: Data kolom "Biaya BPJS" menggunakan `bpjs_default_price`
- **Bagian Total**: Data "Biaya BPJS" menggunakan `bpjs_default_price`

### 3. resources/views/penjualan/cetak.blade.php
- **Tabel Detail Produk**: Data kolom "Biaya BPJS" menggunakan `bpjs_default_price`
- **Bagian Total**: Data "Biaya BPJS" menggunakan `bpjs_default_price`

## Tampilan yang Berbeda

### Sebelumnya (Menggunakan Subtotal):
```
Detail Produk:
│ Nama Produk    │ Jumlah │ Biaya BPJS │
│ Frame Premium  │   1    │  500.000   │  ← Subtotal dari harga produk
│ Lensa Anti     │   1    │  300.000   │  ← Subtotal dari harga produk

Rincian Pembayaran:
│ Biaya BPJS: Rp 800.000                                  │  ← Total subtotal
```

### Sekarang (Menggunakan Biaya Default BPJS):
```
Detail Produk:
│ Nama Produk    │ Jumlah │ Biaya BPJS │
│ Frame Premium  │   1    │  150.000   │  ← Biaya default BPJS
│ Lensa Anti     │   1    │  150.000   │  ← Biaya default BPJS

Rincian Pembayaran:
│ Biaya BPJS: Rp 150.000                                  │  ← Biaya default BPJS
```

## Keuntungan

### 1. Kesesuaian Regulasi BPJS
- Menampilkan biaya yang sesuai dengan ketentuan BPJS
- Tidak menampilkan harga komersial yang bisa membingungkan
- Biaya yang ditampilkan adalah biaya resmi BPJS

### 2. Konsistensi Data
- Semua halaman menampilkan biaya yang sama
- Data berasal dari field `bpjs_default_price` yang sudah disimpan
- Tidak ada perbedaan antara detail, cetak half page, dan cetak struk

### 3. Kejelasan Informasi
- Pasien BPJS melihat biaya yang sesuai dengan layanan mereka
- Tidak ada kebingungan tentang harga komersial vs biaya BPJS
- Informasi yang ditampilkan sesuai dengan ketentuan BPJS

## Testing

### Test Case 1: Pasien BPJS I
1. Buka detail transaksi untuk pasien BPJS I
2. Verifikasi kolom "Biaya BPJS" menampilkan biaya default BPJS I
3. Verifikasi rincian pembayaran menampilkan biaya default BPJS I
4. Cetak struk dan half page, verifikasi data sama

### Test Case 2: Pasien BPJS II
1. Buka detail transaksi untuk pasien BPJS II
2. Verifikasi kolom "Biaya BPJS" menampilkan biaya default BPJS II
3. Verifikasi rincian pembayaran menampilkan biaya default BPJS II
4. Cetak struk dan half page, verifikasi data sama

### Test Case 3: Pasien BPJS III
1. Buka detail transaksi untuk pasien BPJS III
2. Verifikasi kolom "Biaya BPJS" menampilkan biaya default BPJS III
3. Verifikasi rincian pembayaran menampilkan biaya default BPJS III
4. Cetak struk dan half page, verifikasi data sama

### Test Case 4: Pasien Umum
1. Buka detail transaksi untuk pasien umum
2. Verifikasi kolom "Subtotal" menampilkan subtotal produk
3. Verifikasi rincian pembayaran menampilkan subtotal produk
4. Cetak struk dan half page, verifikasi data sama

## Catatan Penting

- Perubahan hanya mempengaruhi tampilan data, tidak mengubah data yang disimpan
- Data `bpjs_default_price` harus sudah terisi saat transaksi dibuat
- Jika `bpjs_default_price` kosong, akan menampilkan 0 atau error
- Perubahan berlaku untuk semua halaman terkait transaksi BPJS
- Biaya default BPJS berbeda untuk setiap layanan (BPJS I, II, III) 