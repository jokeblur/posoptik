# Fitur Transfer Stok Antar Cabang

## Deskripsi
Fitur Transfer Stok Antar Cabang memungkinkan pengguna untuk memindahkan stok produk (Frame dan Lensa) dari satu cabang ke cabang lainnya dengan sistem approval yang terstruktur.

## Fitur Utama

### 1. Dashboard Transfer Stok
- **Lokasi**: `/stock-transfer/dashboard`
- **Fitur**:
  - Statistik transfer (total, pending, selesai, ditolak)
  - Transfer terbaru
  - Statistik berdasarkan cabang
  - Aksi cepat untuk membuat transfer dan melihat data

### 2. Manajemen Transfer
- **Lokasi**: `/stock-transfer`
- **Fitur**:
  - Daftar semua transfer stok
  - Filter berdasarkan status
  - Aksi untuk approve, reject, complete, dan cancel
  - Export data ke Excel

### 3. Pembuatan Transfer
- **Lokasi**: `/stock-transfer/create`
- **Fitur**:
  - Form untuk membuat permintaan transfer
  - Pemilihan cabang tujuan
  - Penambahan produk (Frame/Lensa) dengan quantity
  - Validasi stok tersedia
  - Catatan transfer

### 4. Detail Transfer
- **Lokasi**: `/stock-transfer/{id}`
- **Fitur**:
  - Informasi lengkap transfer
  - Daftar produk yang ditransfer
  - Status dan timeline
  - Aksi berdasarkan role dan status

### 5. Riwayat Cabang
- **Lokasi**: `/stock-transfer/branch/{branchId}/history`
- **Fitur**:
  - Riwayat transfer untuk cabang tertentu
  - Statistik cabang
  - Filter dan pencarian

### 6. Export Data
- **Lokasi**: `/stock-transfer/export`
- **Fitur**:
  - Export ke Excel dengan format yang rapi
  - Styling dan formatting otomatis
  - Filter berdasarkan status dan cabang

## Role dan Permission

### Kasir
- ✅ Membuat permintaan transfer
- ✅ Melihat transfer yang dibuat
- ✅ Cancel transfer yang masih pending
- ✅ Melihat detail transfer

### Admin
- ✅ Semua permission Kasir
- ✅ Approve/reject transfer
- ✅ Export data
- ✅ Akses ke semua cabang

### Super Admin
- ✅ Semua permission Admin
- ✅ Akses penuh ke semua fitur
- ✅ Manajemen sistem

## Status Transfer

| Status | Deskripsi | Warna |
|--------|-----------|-------|
| Pending | Menunggu persetujuan admin | Warning (Kuning) |
| Approved | Disetujui, siap untuk dikirim | Success (Hijau) |
| Rejected | Ditolak dengan alasan | Danger (Merah) |
| Completed | Transfer selesai, stok dipindahkan | Info (Biru) |
| Cancelled | Dibatalkan oleh pembuat | Default (Abu-abu) |

## Alur Kerja

### 1. Pembuatan Transfer
```
Kasir → Buat Permintaan → Pilih Cabang Tujuan → Tambah Produk → Submit
```

### 2. Approval Process
```
Admin → Review Transfer → Approve/Reject → Jika Reject: Tambah Alasan
```

### 3. Completion Process
```
Kasir/Admin → Complete Transfer → Stok Dipindahkan → Status Updated
```

## Database Schema

### Tabel `stock_transfers`
- `id` - Primary key
- `kode_transfer` - Kode unik transfer
- `from_branch_id` - ID cabang asal
- `to_branch_id` - ID cabang tujuan
- `requested_by` - ID user yang meminta
- `approved_by` - ID user yang approve
- `status` - Status transfer
- `notes` - Catatan transfer
- `rejection_reason` - Alasan penolakan
- `approved_at` - Waktu approval
- `completed_at` - Waktu completion
- `created_at`, `updated_at` - Timestamps

### Tabel `stock_transfer_details`
- `id` - Primary key
- `stock_transfer_id` - Foreign key ke stock_transfers
- `itemable_type` - Polymorphic type (Frame/Lensa)
- `itemable_id` - ID produk
- `quantity` - Jumlah yang ditransfer
- `unit_price` - Harga per unit
- `total_price` - Total harga

## API Endpoints

### GET `/stock-transfer/dashboard`
Dashboard dengan statistik transfer

### GET `/stock-transfer`
Daftar semua transfer dengan pagination

### POST `/stock-transfer`
Buat transfer baru

### GET `/stock-transfer/{id}`
Detail transfer tertentu

### POST `/stock-transfer/{id}/approve`
Approve transfer (Admin only)

### POST `/stock-transfer/{id}/reject`
Reject transfer dengan alasan (Admin only)

### POST `/stock-transfer/{id}/complete`
Complete transfer (pindahkan stok)

### POST `/stock-transfer/{id}/cancel`
Cancel transfer (Pending only)

### GET `/stock-transfer/products`
Get products untuk form (AJAX)

### GET `/stock-transfer/stats`
Get statistik untuk AJAX update

### GET `/stock-transfer/export`
Export data ke Excel

### GET `/stock-transfer/branch/{branchId}/history`
Riwayat transfer untuk cabang tertentu

## Fitur Tambahan

### 1. Notifikasi Real-time
- Badge notifikasi untuk transfer pending
- Dropdown dengan daftar transfer yang perlu perhatian
- Auto-refresh setiap 30 detik

### 2. Validasi
- Stok tersedia di cabang asal
- Cabang tujuan berbeda dengan asal
- Quantity minimal 1
- Role-based access control

### 3. Logging
- Log semua aksi transfer
- Audit trail untuk compliance
- Error logging untuk debugging

### 4. Performance
- Eager loading untuk relationships
- Database indexing
- Caching untuk statistik

## File Structure

```
app/
├── Http/Controllers/
│   └── StockTransferController.php
├── Models/
│   ├── StockTransfer.php
│   └── StockTransferDetail.php
└── Exports/
    └── StockTransferExport.php

resources/views/stock-transfer/
├── dashboard.blade.php
├── index.blade.php
├── create.blade.php
├── show.blade.php
└── branch-history.blade.php

resources/views/partials/
└── stock-transfer-notifications.blade.php

public/js/
└── stock-transfer-dashboard.js

database/migrations/
├── create_stock_transfers_table.php
└── create_stock_transfer_details_table.php
```

## Cara Penggunaan

### 1. Akses Dashboard
1. Login ke sistem
2. Klik menu "Dashboard Transfer Stok" di sidebar
3. Lihat statistik dan transfer terbaru

### 2. Buat Transfer
1. Klik "Buat Transfer Baru"
2. Pilih cabang tujuan
3. Tambah produk yang akan ditransfer
4. Isi quantity dan catatan
5. Submit permintaan

### 3. Approve Transfer (Admin)
1. Lihat daftar transfer pending
2. Klik "Setujui" atau "Tolak"
3. Jika tolak, isi alasan penolakan

### 4. Complete Transfer
1. Setelah approved, klik "Selesai"
2. Sistem akan memindahkan stok otomatis
3. Status berubah menjadi "Completed"

### 5. Export Data
1. Klik tombol "Export" (Admin only)
2. Data akan di-download dalam format Excel
3. File berisi semua informasi transfer

## Troubleshooting

### Error: "Stok tidak mencukupi"
- Pastikan stok di cabang asal cukup
- Cek apakah ada transfer lain yang menggunakan stok yang sama

### Error: "Cabang tujuan tidak valid"
- Pastikan cabang tujuan ada dan aktif
- Cek permission user untuk cabang tersebut

### Error: "Tidak dapat transfer ke cabang yang sama"
- Sistem tidak mengizinkan transfer ke cabang yang sama
- Pilih cabang tujuan yang berbeda

### Export tidak berfungsi
- Pastikan Laravel Excel package terinstall
- Cek permission folder storage
- Pastikan user memiliki role admin

## Maintenance

### Backup Data
- Backup tabel `stock_transfers` dan `stock_transfer_details` secara berkala
- Export data penting ke Excel sebagai backup manual

### Performance Monitoring
- Monitor query performance untuk transfer dengan banyak produk
- Optimize database indexes jika diperlukan

### Security Updates
- Update Laravel framework secara berkala
- Monitor access logs untuk aktivitas mencurigakan
- Review dan update role permissions sesuai kebutuhan

## Future Enhancements

### 1. Mobile App
- Notifikasi push untuk transfer pending
- QR Code scanning untuk verifikasi produk
- Foto bukti transfer

### 2. Advanced Analytics
- Chart dan grafik transfer trends
- Prediksi kebutuhan stok
- Report performance cabang

### 3. Integration
- Integrasi dengan sistem inventory
- API untuk third-party applications
- Webhook notifications

### 4. Automation
- Auto-approval untuk transfer kecil
- Scheduled transfers
- Email notifications
