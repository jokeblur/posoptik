# Fitur Simpan Pasien dan Lanjut ke Transaksi

## Deskripsi
Fitur ini memungkinkan user untuk menyimpan data pasien baru dan langsung melanjutkan ke halaman transaksi penjualan dengan data pasien tersebut sudah terisi otomatis.

## Fitur yang Ditambahkan

### 1. Tombol Baru di Form Pasien (pasien/form.blade.php)
- **Tombol**: "Simpan & Lanjut ke Transaksi"
- **Lokasi**: Di bagian footer modal form pasien
- **Warna**: Hijau (btn-success)
- **ID**: `btn-simpan-transaksi`

### 2. Method Baru di PasienController (PasienController.php)
- **Method**: `storeAndRedirect(Request $request)`
- **Fungsi**: Menyimpan data pasien dan mengembalikan URL redirect ke halaman transaksi
- **Response**: JSON dengan message dan redirect_url

### 3. Route Baru (routes/web.php)
- **Route**: `POST /pasien/store-and-redirect`
- **Name**: `pasien.store-and-redirect`
- **Controller**: `PasienController@storeAndRedirect`

### 4. JavaScript Handler (pasien/index.blade.php)
- **Function**: Event handler untuk tombol "Simpan & Lanjut ke Transaksi"
- **Validasi**: Form validation sebelum submit
- **AJAX**: POST request ke endpoint baru
- **Redirect**: Otomatis redirect ke halaman transaksi setelah berhasil

### 5. Auto-fill di Halaman Transaksi (penjualan/create.blade.php)
- **Parameter**: Menerima `pasien_id` dari URL
- **Auto-fill**: Data pasien otomatis terisi
- **Detail**: Menampilkan detail pasien dan resep
- **Dokter**: Auto-select dokter dari resep terakhir
- **Notifikasi**: SweetAlert untuk konfirmasi data terisi

## Cara Kerja

### 1. User Mengisi Form Pasien
1. User membuka halaman Data Pasien
2. Klik tombol "Tambah pasien"
3. Mengisi form data pasien dan resep
4. Klik tombol "Simpan & Lanjut ke Transaksi"

### 2. Proses Penyimpanan
1. JavaScript melakukan validasi form
2. AJAX request ke `pasien.store-and-redirect`
3. Controller menyimpan data pasien dan resep
4. Response berisi URL redirect ke halaman transaksi

### 3. Redirect ke Transaksi
1. JavaScript menerima response
2. Menampilkan pesan sukses
3. Redirect ke halaman transaksi dengan parameter `pasien_id`

### 4. Auto-fill Data di Transaksi
1. Halaman transaksi menerima parameter `pasien_id`
2. Controller mengambil data pasien dan resep
3. JavaScript auto-fill semua field terkait
4. Menampilkan notifikasi data berhasil diisi

## Keuntungan

### 1. Efisiensi Workflow
- Mengurangi langkah manual
- Data pasien langsung tersedia di transaksi
- Tidak perlu mencari pasien lagi

### 2. User Experience
- Workflow yang lebih smooth
- Feedback visual yang jelas
- Auto-completion data

### 3. Data Consistency
- Data pasien dan resep terjamin konsisten
- Dokter otomatis terpilih sesuai resep
- Detail pasien lengkap tersedia

## Teknis

### Database Impact
- Sama seperti penyimpanan pasien biasa
- Tidak ada perubahan struktur database
- Menggunakan transaction untuk data integrity

### Security
- CSRF protection tetap aktif
- Validasi form tetap berjalan
- Permission check sesuai role user

### Error Handling
- Validasi form di client-side
- Error handling di server-side
- Pesan error yang informatif

## File yang Dimodifikasi

1. **resources/views/pasien/form.blade.php**
   - Menambahkan tombol "Simpan & Lanjut ke Transaksi"

2. **app/Http/Controllers/PasienController.php**
   - Menambahkan method `storeAndRedirect()`

3. **routes/web.php**
   - Menambahkan route `pasien.store-and-redirect`

4. **resources/views/pasien/index.blade.php**
   - Menambahkan JavaScript handler untuk tombol baru

5. **app/Http/Controllers/PenjualanController.php**
   - Memodifikasi method `create()` untuk menerima parameter `pasien_id`

6. **resources/views/penjualan/create.blade.php**
   - Menambahkan auto-fill logic untuk data pasien

## Testing

### Test Case 1: Simpan Pasien Baru
1. Buka halaman Data Pasien
2. Klik "Tambah pasien"
3. Isi form dengan data lengkap
4. Klik "Simpan & Lanjut ke Transaksi"
5. Verifikasi redirect ke halaman transaksi
6. Verifikasi data pasien terisi otomatis

### Test Case 2: Validasi Form
1. Isi form tidak lengkap
2. Klik "Simpan & Lanjut ke Transaksi"
3. Verifikasi form validation berjalan
4. Verifikasi tidak ada redirect

### Test Case 3: Error Handling
1. Simulasi error database
2. Klik "Simpan & Lanjut ke Transaksi"
3. Verifikasi pesan error ditampilkan
4. Verifikasi tidak ada redirect

## Catatan Penting

- Fitur ini hanya tersedia untuk user yang memiliki akses ke menu pasien dan transaksi
- Data pasien yang disimpan sama persis dengan penyimpanan biasa
- Auto-fill di halaman transaksi hanya berjalan jika ada parameter `pasien_id`
- Jika tidak ada parameter, halaman transaksi berjalan normal seperti biasa 