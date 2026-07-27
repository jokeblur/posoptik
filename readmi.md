# Panduan Penggunaan Aplikasi POS Optik Melati per Role

Dokumen ini fokus ke cara pakai aplikasi berdasarkan role user:
- Kasir
- Admin
- Super Admin
- Passet Bantu

## 1. Login

1. Buka halaman login aplikasi.
2. Masukkan email dan password.
3. Klik Login.

Jika berhasil, user akan masuk ke dashboard sesuai hak akses role.

## 2. Ringkasan Hak Akses

| Fitur | Super Admin | Admin | Kasir | Passet Bantu |
|---|:---:|:---:|:---:|:---:|
| Dashboard | Ya | Ya | Ya | Ya |
| Open/Close Day | Ya | Ya | Tidak | Tidak |
| Data Penjualan | Ya | Ya | Ya | Terbatas lihat proses |
| Buat/Edit Transaksi Penjualan | Ya | Ya | Ya | Tidak |
| Inventory (Frame/Lensa/Aksesoris) | Ya | Ya | Terbatas lihat/pakai saat transaksi | Tidak |
| Data Pasien | Ya | Ya | Ya | Tidak |
| Laporan POS | Ya | Ya | Tidak | Tidak |
| Laporan BPJS | Ya | Ya | Tidak | Tidak |
| Manajemen User | Ya | Ya (terbatas) | Tidak | Tidak |
| Pengerjaan Passet | Ya | Ya | Tidak | Ya |
| Scan Barcode/QR Status | Ya | Ya | Ya | Ya |

Catatan:
- Admin bekerja pada cabang aktif.
- Super Admin bisa lintas cabang.
- Kasir dan passet bantu fokus operasional harian.

## 3. SOP Role Kasir

### 3.1 Mulai Kerja
1. Login.
2. Pastikan toko sudah Open Day oleh admin.
3. Cek dashboard dan status transaksi aktif.

### 3.2 Input Transaksi Penjualan
1. Masuk menu Penjualan.
2. Klik Tambah Penjualan Baru.
3. Pilih pasien atau input manual pasien.
4. Pilih dokter (opsional/manual jika tidak ada).
5. Tambahkan item transaksi:
   - Frame
   - Lensa stok atau lensa gosok
   - Aksesoris
6. Atur jumlah item di keranjang.
7. Cek total, diskon, dan jumlah bayar.
8. Pilih cara pembayaran:
   - Cash
   - Transfer (wajib pilih bank: BNI/BRI/MANDIRI/BSI/BCA)
   - QRIS
9. Simpan transaksi.

### 3.3 Khusus Transaksi BPJS
1. Pilih pasien BPJS yang valid.
2. Lengkapi dokumen BPJS jika diminta (foto bukti/tanda tangan).
3. Jika status naik kelas, pastikan biaya tambahan terbaca.
4. Simpan dan cetak nota sesuai kebutuhan.

### 3.4 Setelah Simpan
1. Buka detail transaksi untuk cek data.
2. Cetak nota atau barcode jika diperlukan.
3. Lanjut proses status pengerjaan sampai siap diambil.

## 4. SOP Role Admin

### 4.1 Awal Hari
1. Login.
2. Buka menu Open/Close Day.
3. Lakukan Open Day untuk cabang.

### 4.2 Monitoring Operasional
1. Pantau daftar penjualan dan status pengerjaan.
2. Koreksi transaksi bila ada kesalahan (menu Edit Penjualan).
3. Pastikan metode pembayaran tercatat benar.
4. Pantau stok menipis (frame/lensa/aksesoris).

### 4.3 Laporan
1. Buka Laporan POS untuk omset harian/bulanan.
2. Buka Laporan BPJS untuk transaksi BPJS normal dan naik kelas.
3. Gunakan filter tanggal/cabang sesuai kebutuhan.
4. Export data bila perlu.

### 4.4 Akhir Hari
1. Pastikan transaksi penting sudah terproses.
2. Lakukan Close Day.

## 5. SOP Role Super Admin

### 5.1 Pengawasan Multi Cabang
1. Login.
2. Pilih cabang aktif dari selector cabang.
3. Ulangi monitoring untuk tiap cabang sesuai kebutuhan.

### 5.2 Kontrol Penuh Sistem
1. Kelola user dan role.
2. Audit transaksi dan pembetulan data bila diperlukan.
3. Akses seluruh laporan lintas cabang.
4. Pantau performa operasional dan stok seluruh cabang.

### 5.3 Tugas Strategis
1. Review omset umum vs BPJS.
2. Review transaksi BPJS naik kelas dan biaya tambahan.
3. Validasi kepatuhan input kasir/admin.

## 6. SOP Role Passet Bantu

### 6.1 Fokus Pengerjaan
1. Login.
2. Buka menu passet/pengerjaan.
3. Lihat daftar transaksi status Menunggu Pengerjaan.
4. Kerjakan pesanan sesuai antrian.

### 6.2 Update Status
1. Setelah pekerjaan selesai, update status ke Selesai Dikerjakan.
2. Bisa gunakan scan barcode/QR untuk mempercepat update status.
3. Pastikan transaksi berpindah status dengan benar.

### 6.3 Koordinasi
1. Informasikan transaksi selesai ke kasir/admin.
2. Hindari mengubah data finansial transaksi.

## 7. Alur Operasional Harian (Singkat)

1. Admin/Super Admin: Open Day.
2. Kasir: Input transaksi + pembayaran + dokumen BPJS jika perlu.
3. Passet Bantu: Proses pengerjaan dan update status selesai.
4. Kasir/Admin: Serah terima barang ke pasien, update status diambil.
5. Admin/Super Admin: Review laporan dan Close Day.

## 8. Checklist Cepat per Role

### Kasir
- Open Day sudah aktif.
- Data pasien benar.
- Item dan total benar.
- Metode pembayaran terisi.
- Transaksi tersimpan dan tercetak bila perlu.

### Admin
- Open/Close Day dilakukan tepat waktu.
- Monitoring status pengerjaan berjalan.
- Koreksi transaksi jika diperlukan.
- Laporan harian direview.

### Super Admin
- Monitoring lintas cabang.
- Audit user, transaksi, dan laporan.
- Validasi kinerja cabang.

### Passet Bantu
- Ambil antrian menunggu.
- Update status selesai tepat waktu.
- Koordinasi dengan kasir/admin.

## 9. Troubleshooting Singkat

1. Tidak bisa input transaksi:
   - Cek Open Day cabang sudah dibuka.
2. Pilihan bank transfer tidak muncul:
   - Pastikan cara pembayaran dipilih Transfer.
3. Transaksi BPJS tidak sesuai:
   - Cek jenis layanan pasien, item frame/lensa, dan status transaksi BPJS.
4. Tidak bisa akses menu tertentu:
   - Cek role user dan hak akses.

---

Dokumen ini dibuat untuk panduan operasional harian agar tiap role bekerja sesuai tanggung jawabnya.
