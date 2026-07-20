# OPTIK MELATI — Panduan Penggunaan Aplikasi POS

Aplikasi Point of Sale (POS) untuk manajemen toko optik, mencakup penjualan, inventori, laporan keuangan, dan manajemen karyawan.

---

## DAFTAR ISI

1. [Login](#1-login)
2. [Dashboard](#2-dashboard)
3. [Administrasi — Open/Close Day](#3-administrasi--openclose-day)
4. [Master Data](#4-master-data)
5. [Inventory](#5-inventory)
6. [Transfer Stok](#6-transfer-stok)
7. [Transaksi](#7-transaksi)
8. [Laporan](#8-laporan)
9. [Manajemen Karyawan](#9-manajemen-karyawan)
10. [Manajemen Keuangan](#10-manajemen-keuangan)
11. [Settings](#11-settings)
12. [Hak Akses per Role](#12-hak-akses-per-role)

---

## 1. LOGIN

**URL:** `/login`

1. Buka browser, akses alamat aplikasi (contoh: `http://localhost/posoptik`)
2. Masukkan **Email** dan **Password**
3. Klik tombol **Login**

> **Default Role:**
> | Role | Akses |
> |---|---|
> | Super Admin | Semua menu |
> | Admin | Semua menu kecuali Laporan Laba Rugi, Manajemen Karyawan, dan Manajemen Keuangan |
> | Kasir | Transaksi & Transfer Stok |
> | Passet Bantu | Pengerjaan & Scan QR Code |

---

## 2. DASHBOARD

**Akses:** Semua role

Halaman utama setelah login. Menampilkan:

- **Ringkasan harian** — total transaksi, omset hari ini, jumlah pasien
- **Stok menipis** — lensa, frame, dan aksesoris dengan stok di bawah batas (khusus Admin & Super Admin)
- **Grafik omset** — chart penjualan per bulan
- **Status pengerjaan** — jumlah order menunggu, dikerjakan, selesai

### Pilih Cabang Aktif (Admin & Super Admin)
Di navbar atas terdapat **dropdown cabang**. Pilih cabang yang ingin dilihat datanya. Semua halaman akan menyesuaikan dengan cabang yang dipilih.

---

## 3. ADMINISTRASI — OPEN/CLOSE DAY

**Akses:** Admin, Super Admin  
**Menu:** Administrasi → Open/Close Day

Fitur untuk membuka dan menutup toko setiap hari. **Kasir tidak dapat melakukan transaksi jika toko belum dibuka.**

### Cara Membuka Toko (Open Day)
1. Klik menu **Open/Close Day**
2. Klik tombol **Buka Toko** pada cabang yang ingin dibuka
3. Status berubah menjadi **Buka** (hijau)

### Cara Menutup Toko (Close Day)
1. Klik tombol **Tutup Toko** pada cabang yang ingin ditutup
2. Status berubah menjadi **Tutup** (merah)
3. Setelah ditutup, kasir tidak bisa menambah transaksi baru

> **Catatan:** Open Day harus dilakukan setiap hari. Status reset otomatis keesokan harinya.

---

## 4. MASTER DATA

**Akses:** Admin, Super Admin

### 4.1 Data Cabang
**Menu:** Master Data → Data Cabang

Kelola data cabang/outlet toko.

| Aksi | Cara |
|---|---|
| Tambah cabang | Klik **Tambah Cabang**, isi nama, kode, alamat, nomor telepon |
| Edit cabang | Klik ikon pensil pada baris cabang |
| Hapus cabang | Klik ikon hapus (tidak bisa dihapus jika masih ada user/stok terkait) |

### 4.2 Data Dokter
**Menu:** Master Data → Data Dokter

Kelola data dokter yang merujuk pasien.

| Aksi | Cara |
|---|---|
| Tambah dokter | Klik **Tambah**, isi nama dokter dan informasi lainnya |
| Edit / Hapus | Gunakan tombol aksi di tabel |

### 4.3 Data User
**Menu:** Master Data → Data User

Kelola akun pengguna aplikasi.

| Field | Keterangan |
|---|---|
| Nama | Nama lengkap pengguna |
| Email | Digunakan untuk login |
| Password | Minimal 8 karakter |
| Role | super admin / admin / kasir / passet bantu |
| Cabang | Cabang tempat user bertugas |

---

## 5. INVENTORY

**Akses:** Admin, Super Admin

### 5.1 Data Frame
**Menu:** Inventory → Data Frame

Kelola stok kacamata (frame).

**Tambah Frame:**
1. Klik **Tambah Frame**
2. Isi: kode, merk, warna, harga beli, harga jual, stok, cabang
3. Klik **Simpan**

**Import dari Excel:**
1. Klik **Import Excel**
2. Download template terlebih dahulu
3. Isi data di template, lalu upload

**Export:**
- Klik **Export Excel** untuk mengunduh semua data frame

**Filter Stok Menipis:** Tampil otomatis di bagian atas jika stok ≤ batas minimum.

---

### 5.2 Data Lensa
**Menu:** Inventory → Data Lensa

Kelola stok lensa kacamata.

**Tipe Stok Lensa:**
| Tipe | Keterangan |
|---|---|
| Ready Stock | Lensa tersedia di gudang, masuk perhitungan stok menipis |
| Custom Order | Lensa dipesan khusus, **tidak masuk** peringatan stok menipis |

**Field utama:** kode, merk, type, index, coating, SPH, CYL, ADD, harga beli, harga jual, stok

**Import/Export:** Tersedia tombol Import Excel dan Export (sama seperti frame).

---

### 5.3 Data Aksesoris
**Menu:** Inventory → Data Aksesoris

Kelola stok aksesoris pendukung (tali kacamata, cairan pembersih, dll.)

---

### 5.4 Data Kategori
**Menu:** Inventory → Data Kategori

Kelola kategori produk untuk pengelompokan.

---

## 6. TRANSFER STOK

**Akses:** Semua role kecuali Passet Bantu

### 6.1 Dashboard Transfer Stok
Ringkasan status permintaan transfer stok antar cabang — menunggu, disetujui, selesai, ditolak.

### 6.2 Transfer Stok Antar Cabang
**Menu:** Transfer Stok → Transfer Stok Antar Cabang

**Cara Membuat Transfer:**
1. Klik **Buat Transfer Baru**
2. Pilih cabang asal dan tujuan
3. Pilih produk dan jumlah
4. Klik **Kirim Permintaan**

**Alur Persetujuan:**
```
Kasir buat permintaan → Admin/Super Admin setujui → Stok otomatis dipindah
```

| Status | Keterangan |
|---|---|
| Menunggu | Permintaan belum diproses |
| Disetujui | Admin menyetujui, stok dikurangi dari cabang asal |
| Selesai | Stok sudah diterima di cabang tujuan |
| Ditolak | Permintaan ditolak |

---

## 7. TRANSAKSI

**Akses:** Kasir, Admin, Super Admin

### 7.1 Data Pasien
**Menu:** Transaksi → Data Pasien

Kelola data pasien/pelanggan.

**Tambah Pasien:**
1. Klik **Tambah Pasien**
2. Isi: nama, nomor HP, alamat, jenis layanan (Umum / BPJS I / BPJS II / BPJS III)
3. Isi data resep (jika ada): SPH, CYL, AXIS, ADD untuk mata kanan dan kiri
4. Klik **Simpan**

**Dari halaman pasien langsung ke transaksi:**
- Klik tombol **Buat Transaksi** pada baris pasien yang dipilih

---

### 7.2 Data Penjualan
**Menu:** Transaksi → Data Penjualan

#### Syarat Tambah Penjualan
> Toko **harus sudah dibuka** (Open Day). Jika belum, kasir akan diarahkan kembali dengan pesan error.

**Cara Membuat Transaksi Baru:**
1. Klik **Tambah Penjualan**
2. Pilih pasien dari daftar (atau buat pasien baru)
3. Pilih produk:
   - Frame → pilih dari stok cabang
   - Lensa → pilih dari stok cabang
   - Aksesoris → pilih dari stok cabang
4. Atur kuantitas dan harga
5. Isi nominal pembayaran (bayar penuh atau DP)
6. Pilih dokter pemeriksa (opsional)
7. Klik **Simpan Transaksi**

**Status Pembayaran:**
| Status | Kondisi |
|---|---|
| Lunas | Bayar = Total |
| Belum Lunas | Bayar < Total (DP) |

**Status Pengerjaan:**
| Status | Keterangan |
|---|---|
| Menunggu Pengerjaan | Baru dibuat |
| Sedang Dikerjakan | Passet Bantu sedang proses |
| Selesai | Order sudah jadi |
| Diambil | Pelanggan sudah ambil |

**Layanan BPJS:**
- Untuk pasien BPJS, harga otomatis mengikuti tarif yang ditetapkan (BPJS I/II/III)

**Cetak Nota:**
- Klik ikon cetak → pilih **Cetak Full** (A4) atau **Cetak Half** (setengah halaman)

---

### 7.3 Scan Barcode / QR Code
**Menu:** Transaksi → Scan Barcode

1. Buka halaman scan
2. Arahkan kamera ke barcode/QR Code pada nota
3. Informasi transaksi otomatis tampil
4. Update status pengerjaan dari halaman ini

---

## 8. LAPORAN

**Akses:** Admin, Super Admin (Laporan Laba Rugi khusus Super Admin)

### 8.1 Laporan POS
**Menu:** Laporan → Laporan POS

Ringkasan transaksi per periode:
- Omset harian dan bulanan
- Rekap DP (belum lunas)
- Rekap lunas
- Total piutang
- Omset per layanan (Umum, BPJS I/II/III)
- Detail transaksi per hari/bulan
- Ringkasan per cabang (Super Admin)

**Filter:** Pilih bulan, tahun, dan cabang → klik **Tampilkan**

---

### 8.2 Laporan BPJS
**Menu:** Laporan → Laporan BPJS

Rekap semua transaksi dengan layanan BPJS.

- Filter per bulan, tahun, cabang
- Tampil detail pasien, jenis BPJS, nominal klaim
- **Export** ke Excel untuk keperluan klaim

---

### 8.3 Laporan Laba Rugi
**Menu:** Laporan → Laporan Laba Rugi  
**Akses:** Super Admin saja

Laporan keuangan lengkap per bulan:

```
PENDAPATAN
  + Omset Penjualan
  + Pemasukan Lain-lain
= Total Pendapatan

HPP (Harga Pokok Penjualan)
  - HPP Frame
  - HPP Lensa
  - HPP Aksesoris
= Total HPP

LABA KOTOR = Total Pendapatan - Total HPP

BEBAN OPERASIONAL
  - Beban Gaji Karyawan
  - Pengeluaran Operasional
= Total Beban

LABA / RUGI BERSIH = Laba Kotor - Total Beban
```

> **Agar laporan akurat:** Input gaji karyawan tiap bulan di menu **Manajemen Karyawan** dan catat pengeluaran di menu **Manajemen Keuangan**.

**Filter:** Pilih bulan, tahun, cabang → klik **Tampilkan**

---

## 9. MANAJEMEN KARYAWAN

**Akses:** Super Admin saja  
**Menu:** Manajemen → Manajemen Karyawan

### 9.1 Tambah Karyawan
1. Klik **Tambah Karyawan**
2. Isi data:
   - Nama, Jabatan, Cabang
   - No HP, Email
   - Tanggal Masuk
   - Gaji Pokok
   - Status (Aktif / Non Aktif)
3. Klik **Simpan**

### 9.2 Input Gaji & Bonus
1. Klik tombol **Gaji** pada baris karyawan
2. Pilih **Bulan** dan **Tahun**
3. Isi komponen gaji:
   | Komponen | Keterangan |
   |---|---|
   | Gaji Pokok | Gaji dasar (otomatis terisi dari data karyawan) |
   | Bonus | Bonus kinerja, THR, dll. |
   | Tunjangan | Tunjangan transport, makan, dll. |
   | Potongan | Potongan ketidakhadiran, dll. |
4. **Total Gaji** = Gaji Pokok + Bonus + Tunjangan − Potongan
5. Klik **Simpan Gaji**

Riwayat gaji semua periode tersimpan dan dapat dilihat di bawah form input.

---

## 10. MANAJEMEN KEUANGAN

**Akses:** Super Admin saja  
**Menu:** Manajemen → Manajemen Keuangan

Catat semua pemasukan dan pengeluaran operasional di luar penjualan.

### 10.1 Tambah Catatan Keuangan
1. Klik **Tambah**
2. Isi:
   | Field | Keterangan |
   |---|---|
   | Tanggal | Tanggal transaksi |
   | Jenis | Pemasukan atau Pengeluaran |
   | Kategori | Misal: Sewa Tempat, Listrik, Pembelian Stok |
   | Jumlah | Nominal (Rupiah) |
   | Cabang | Cabang terkait |
   | Keterangan | Catatan tambahan |
3. Klik **Simpan**

### 10.2 Summary Keuangan
Di bagian atas halaman tampil ringkasan otomatis:
- **Total Pemasukan** — semua catatan berjenis pemasukan periode terpilih
- **Total Pengeluaran** — semua catatan berjenis pengeluaran
- **Saldo** — Pemasukan − Pengeluaran (merah jika minus)

**Filter:** Bulan, tahun, cabang, jenis, rentang tanggal

---

## 11. SETTINGS

**Akses:** Admin, Super Admin

### 11.1 Data Sales
**Menu:** Settings → Data Sales

Kelola data tenaga penjual/sales yang menangani stok lensa.

### 11.2 Barcode
**Menu:** Settings → Barcode

Kelola dan cetak barcode untuk nota transaksi.

- **Generate Barcode:** Klik barcode lalu cetak
- **Bulk Generate:** Buat barcode massal (Admin & Super Admin)

---

## 12. HAK AKSES PER ROLE

| Menu | Super Admin | Admin | Kasir | Passet Bantu |
|---|:---:|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Open/Close Day | ✅ | ✅ | ❌ | ❌ |
| Master Data | ✅ | ✅ | ❌ | ❌ |
| Inventory | ✅ | ✅ | ❌ | ❌ |
| Transfer Stok | ✅ | ✅ | ✅ | ❌ |
| Pengerjaan (Passet) | ✅ | ✅ | ❌ | ✅ |
| Data Pasien | ✅ | ✅ | ✅ | ❌ |
| Data Penjualan | ✅ | ✅ | ✅ | ❌ |
| Scan Barcode/QR | ✅ | ✅ | ✅ | ✅ |
| Laporan POS | ✅ | ✅ | ❌ | ❌ |
| Laporan BPJS | ✅ | ✅ | ❌ | ❌ |
| Laporan Laba Rugi | ✅ | ❌ | ❌ | ❌ |
| Manajemen Karyawan | ✅ | ❌ | ❌ | ❌ |
| Manajemen Keuangan | ✅ | ❌ | ❌ | ❌ |
| Settings | ✅ | ✅ | ❌ | ❌ |

---

## ALUR KERJA HARIAN

```
1. Admin buka aplikasi → Open Day (buka toko)
       ↓
2. Kasir login → pilih pasien / tambah pasien baru
       ↓
3. Buat transaksi penjualan → pilih produk → bayar
       ↓
4. Passet Bantu proses pengerjaan → scan QR Code → update status Selesai
       ↓
5. Pelanggan ambil pesanan → update status Diambil
       ↓
6. Admin tutup toko → Close Day
       ↓
7. Admin/Super Admin lihat Laporan POS harian
```

---

## INFORMASI TEKNIS

| Item | Detail |
|---|---|
| Framework | Laravel (PHP) |
| Database | MySQL |
| Antarmuka | AdminLTE 2 (Bootstrap 3) |
| URL Lokal | `http://localhost/posoptik` |
| URL Produksi | Sesuai konfigurasi server |

---

*Panduan ini mencakup semua fitur per tanggal 17 Juli 2026.*
