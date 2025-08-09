# Fitur Detail Transaksi BPJS - Modifikasi Halaman Detail

## Deskripsi
Modifikasi halaman detail transaksi penjualan untuk menangani layanan BPJS I, II, dan III dengan tampilan yang berbeda dari transaksi umum.

## Perubahan yang Dilakukan

### 1. Tabel Detail Produk

#### Untuk Pasien BPJS:
- **Kolom "Harga Satuan"**: Disembunyikan
- **Kolom "Subtotal"**: Diubah menjadi "Biaya BPJS"
- **Layout**: Lebih sederhana tanpa informasi harga satuan

#### Untuk Pasien Umum:
- **Kolom "Harga Satuan"**: Tetap ditampilkan
- **Kolom "Subtotal"**: Tetap seperti biasa
- **Layout**: Normal seperti sebelumnya

### 2. Rincian Pembayaran

#### Untuk Pasien BPJS:
- **Subtotal**: Diubah menjadi "Biaya BPJS"
- **Diskon**: **Disembunyikan** (tidak ditampilkan)
- **Total**: **Disembunyikan** (tidak ditampilkan)
- **Dibayar**: **Disembunyikan** (tidak ditampilkan)
- **Kekurangan**: **Disembunyikan** (tidak ditampilkan)
- **Biaya Tambahan**: Tetap ditampilkan jika ada
- **Status**: Tetap ditampilkan

#### Untuk Pasien Umum:
- **Subtotal**: Tetap seperti biasa
- **Diskon**: Tetap ditampilkan
- **Total**: Tetap ditampilkan
- **Dibayar**: Tetap ditampilkan
- **Kekurangan**: Tetap ditampilkan
- **Biaya Tambahan**: Tetap ditampilkan jika ada
- **Status**: Tetap ditampilkan

## Logika Deteksi BPJS

```php
$isBPJS = $penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']);
```

## File yang Dimodifikasi

### resources/views/penjualan/show.blade.php
- **Tabel Detail Produk**: Modifikasi kolom untuk BPJS
- **Rincian Pembayaran**: Kondisional untuk BPJS vs non-BPJS

## Tampilan yang Berbeda

### Untuk Pasien BPJS:
```
┌─────────────────────────────────────────────────────────┐
│ Detail Produk                                           │
├─────────────────────────────────────────────────────────┤
│ Nama Produk    │ Jumlah │ Biaya BPJS │ Biaya Tambahan  │
├────────────────┼────────┼────────────┼─────────────────┤
│ Frame Premium  │   1    │  150.000   │       -         │
│ Lensa Anti     │   1    │  200.000   │       -         │
├─────────────────────────────────────────────────────────┤
│ Rincian Pembayaran                                      │
├─────────────────────────────────────────────────────────┤
│ Biaya BPJS: Rp 350.000                                  │
│ Status: Lunas                                            │
└─────────────────────────────────────────────────────────┘
```

### Untuk Pasien Umum:
```
┌─────────────────────────────────────────────────────────┐
│ Detail Produk                                           │
├─────────────────────────────────────────────────────────┤
│ Nama Produk    │ Jumlah │ Harga Satuan │ Subtotal      │
├────────────────┼────────┼──────────────┼───────────────┤
│ Frame Premium  │   1    │   500.000    │  500.000      │
│ Lensa Anti     │   1    │   300.000    │  300.000      │
├─────────────────────────────────────────────────────────┤
│ Rincian Pembayaran                                      │
├─────────────────────────────────────────────────────────┤
│ Subtotal: Rp 800.000                                    │
│ Diskon: Rp 50.000                                       │
│ Total: Rp 750.000                                       │
│ Dibayar: Rp 750.000                                     │
│ Kekurangan: Rp 0                                         │
│ Status: Lunas                                            │
└─────────────────────────────────────────────────────────┘
```

## Keuntungan

### 1. Kesesuaian Regulasi BPJS
- Tidak menampilkan harga satuan yang bisa membingungkan
- Fokus pada biaya BPJS yang sudah ditentukan
- Menghindari kebingungan pasien tentang harga

### 2. Kejelasan Informasi
- Informasi yang ditampilkan sesuai dengan kebutuhan BPJS
- Tidak ada informasi yang tidak relevan untuk BPJS
- Tampilan yang lebih bersih dan fokus

### 3. Konsistensi
- Tampilan yang seragam untuk semua transaksi BPJS
- Tidak ada inkonsistensi informasi
- Sesuai dengan ketentuan BPJS

## Testing

### Test Case 1: Pasien BPJS I
1. Buka detail transaksi untuk pasien BPJS I
2. Verifikasi kolom "Harga Satuan" tidak ditampilkan
3. Verifikasi kolom "Subtotal" menjadi "Biaya BPJS"
4. Verifikasi diskon, total, dibayar, kekurangan tidak ditampilkan
5. Verifikasi status tetap ditampilkan

### Test Case 2: Pasien Umum
1. Buka detail transaksi untuk pasien umum
2. Verifikasi kolom "Harga Satuan" ditampilkan
3. Verifikasi kolom "Subtotal" tetap seperti biasa
4. Verifikasi semua informasi pembayaran ditampilkan
5. Verifikasi status tetap ditampilkan

### Test Case 3: Pasien BPJS II/III
1. Buka detail transaksi untuk pasien BPJS II atau III
2. Verifikasi tampilan sama dengan BPJS I
3. Verifikasi semua informasi BPJS ditampilkan dengan benar

## Catatan Penting

- Perubahan hanya mempengaruhi tampilan detail transaksi, tidak mengubah data yang disimpan
- Logika deteksi BPJS berdasarkan `service_type` pasien
- Jika pasien tidak memiliki data `service_type`, akan dianggap sebagai pasien umum
- Perubahan ini berbeda dengan modifikasi halaman cetak yang sudah dilakukan sebelumnya
- Halaman detail transaksi sekarang menampilkan informasi yang lebih sesuai untuk BPJS 