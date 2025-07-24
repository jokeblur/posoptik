# Update Fitur Transaksi Penjualan

## Fitur Baru yang Ditambahkan

### 1. Tombol Cari Aksesoris ✅
- **Lokasi**: Form transaksi penjualan, di sebelah tombol "Cari Frame" dan "Cari Lensa"
- **Warna**: Warning (kuning/orange)
- **Fungsi**: Membuka modal untuk memilih produk aksesoris
- **Validasi**: Tombol disabled jika stok habis
- **Stok**: Otomatis berkurang saat transaksi disimpan

### 2. Input Manual Nama Pasien ✅
- **Lokasi**: 
  - Tombol "Input Manual" di samping input pasien
  - Tombol "Input Manual Pasien" di dalam modal pilih pasien
- **Fungsi**: Memungkinkan transaksi untuk pasien yang tidak terdaftar di database
- **Validasi**: Nama pasien wajib diisi (baik dari database maupun manual)

### 3. Database Changes ✅
- **Tabel**: `penjualan`
- **Field Baru**: `nama_pasien_manual` (nullable string)
- **Relasi**: `pasien_id` sekarang nullable
- **Migration**: `2025_08_01_000003_add_nama_pasien_manual_to_penjualan_table.php`

### 4. Model Updates ✅
- **Transaksi Model**: Ditambah accessor `getNamaPasienAttribute()`
- **Logic**: Return nama dari relasi pasien jika ada, atau nama manual jika tidak

### 5. Controller Updates ✅
- **PenjualanController@create**: Mengirim data aksesoris ke view
- **PenjualanController@store**: 
  - Validasi kondisional untuk pasien_id vs nama manual
  - Support untuk menyimpan aksesoris
  - Pengurangan stok otomatis untuk semua jenis produk
- **PenjualanController@data**: Menambah kolom nama_pasien di DataTables

### 6. View Updates ✅
- **create.blade.php**: Tombol cari aksesoris, input manual pasien, validasi JS
- **modal_aksesoris.blade.php**: Modal baru untuk pilih aksesoris (BARU)
- **modal_pasien.blade.php**: Tombol input manual + modal input manual (UPDATE)
- **index.blade.php**: Kolom pasien di tabel penjualan (UPDATE)
- **show.blade.php**: Detail pasien manual di halaman detail (UPDATE)
- **cetak.blade.php**: Nama pasien manual di invoice (UPDATE)

## Cara Penggunaan

### Transaksi dengan Aksesoris
1. Buka halaman "Tambah Penjualan Baru"
2. Klik tombol "Cari Aksesoris" (warna kuning)
3. Pilih produk aksesoris dari modal
4. Produk akan masuk ke keranjang

### Transaksi dengan Pasien Manual
1. Di input pasien, klik "Input Manual" ATAU
2. Klik "Cari" → lalu "Input Manual Pasien"
3. Masukkan nama pasien yang tidak terdaftar
4. Lanjutkan transaksi normal

## Testing Checklist

- [ ] Tombol "Cari Aksesoris" muncul dan berfungsi
- [ ] Modal aksesoris menampilkan data dengan benar
- [ ] Aksesoris bisa ditambah ke keranjang
- [ ] Stok aksesoris berkurang setelah transaksi
- [ ] Tombol "Input Manual" berfungsi dari kedua lokasi
- [ ] Modal input manual pasien berfungsi
- [ ] Transaksi tersimpan dengan nama pasien manual
- [ ] Kolom pasien muncul di tabel penjualan
- [ ] Detail transaksi menampilkan nama pasien manual
- [ ] Invoice/struk menampilkan nama pasien manual
- [ ] Validasi keranjang kosong berfungsi
- [ ] Validasi nama pasien kosong berfungsi

## Migration Command
```bash
php artisan migrate
```

## Files Modified/Created
- `resources/views/penjualan/create.blade.php` (MODIFIED)
- `resources/views/penjualan/modal_aksesoris.blade.php` (NEW)
- `resources/views/penjualan/modal_pasien.blade.php` (MODIFIED)
- `resources/views/penjualan/index.blade.php` (MODIFIED)
- `resources/views/penjualan/show.blade.php` (MODIFIED)
- `resources/views/penjualan/cetak.blade.php` (MODIFIED)
- `app/Http/Controllers/PenjualanController.php` (MODIFIED)
- `app/Models/Transaksi.php` (MODIFIED)
- `database/migrations/2025_08_01_000003_add_nama_pasien_manual_to_penjualan_table.php` (NEW)