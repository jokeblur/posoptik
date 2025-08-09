# Fitur Hapus Transaksi Penjualan

## Deskripsi
Fitur ini memungkinkan Super Admin dan Admin untuk menghapus transaksi penjualan dari sistem. Fitur ini hanya tersedia untuk user dengan role "super admin" dan "admin".

## Fitur yang Ditambahkan

### 1. Controller Method (PenjualanController.php)
- **Method**: `destroy($id)`
- **Fungsi**: Menghapus transaksi dan detail transaksinya
- **Keamanan**: 
  - Hanya Super Admin dan Admin yang bisa mengakses
  - Admin hanya bisa menghapus transaksi dari cabangnya sendiri
  - Super Admin bisa menghapus transaksi dari semua cabang

### 2. Tombol Delete di Halaman Index (penjualan/index.blade.php)
- Tombol "Hapus" muncul di kolom aksi untuk setiap transaksi
- Hanya muncul untuk Super Admin dan Admin
- Menggunakan SweetAlert untuk konfirmasi
- JavaScript function: `hapusTransaksi(url)`

### 3. Tombol Delete di Halaman Detail (penjualan/show.blade.php)
- Tombol "Hapus Transaksi" di bagian footer
- Hanya muncul untuk Super Admin dan Admin
- Redirect ke halaman index setelah berhasil dihapus
- JavaScript function untuk handle delete action

## Keamanan

### Role-based Access Control
- **Super Admin**: Bisa menghapus transaksi dari semua cabang
- **Admin**: Hanya bisa menghapus transaksi dari cabangnya sendiri
- **Kasir & Passet Bantu**: Tidak bisa menghapus transaksi

### Validasi
- Pengecekan role user di controller
- Pengecekan branch access untuk admin
- CSRF protection untuk semua request delete
- Konfirmasi dialog sebelum penghapusan

## Cara Penggunaan

### Untuk Super Admin
1. Masuk ke menu "Daftar Transaksi"
2. Klik tombol "Hapus" pada transaksi yang ingin dihapus
3. Konfirmasi penghapusan di dialog yang muncul
4. Transaksi akan dihapus dari sistem

### Untuk Admin
1. Masuk ke menu "Daftar Transaksi"
2. Tombol "Hapus" hanya muncul untuk transaksi dari cabang admin tersebut
3. Klik tombol "Hapus" dan konfirmasi
4. Transaksi akan dihapus dari sistem

## Route
- **DELETE** `/penjualan/{id}` - Route untuk menghapus transaksi
- Route ini sudah termasuk dalam resource route `Route::resource('penjualan', PenjualanController::class)`

## Database Impact
- Menghapus record dari tabel `transaksi`
- Menghapus semua detail transaksi terkait dari tabel `penjualan_details`
- Menggunakan cascade delete untuk memastikan data integrity

## Error Handling
- Pesan error yang informatif jika gagal menghapus
- Validasi permission sebelum melakukan delete
- Rollback otomatis jika terjadi error saat delete

## Catatan Penting
- **Tindakan ini tidak dapat dibatalkan** - Transaksi yang dihapus tidak bisa dikembalikan
- Pastikan untuk melakukan backup data sebelum menggunakan fitur ini
- Hanya gunakan fitur ini untuk transaksi yang benar-benar perlu dihapus 