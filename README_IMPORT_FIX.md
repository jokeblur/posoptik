# Perbaikan Import Data Lensa - Stok Menjadi 0

## Masalah

Saat import data lensa dari file Excel, nilai stok selalu menjadi 0 meskipun di file Excel ada nilai stok yang valid.

## Penyebab Masalah

1. Method `parseNumeric()` terlalu agresif dalam mengembalikan nilai 0
2. Header mapping untuk kolom stok tidak tepat
3. Konversi tipe data tidak sesuai dengan skema database (integer)
4. Kurangnya debugging untuk melacak proses parsing nilai stok

## Perbaikan yang Dilakukan

### 1. Model Lensa (`app/Models/Lensa.php`)

-   Menambahkan cast untuk field `stok` sebagai integer:

```php
protected $casts = [
    'is_custom_order' => 'boolean',
    'stok' => 'integer',
];
```

### 2. LensaImport (`app/Imports/LensaImport.php`)

-   Memperbaiki method `parseNumeric()` untuk mengembalikan integer
-   Menambahkan debugging yang lebih detail
-   Memperbaiki header mapping untuk kolom stok
-   Menambahkan fallback mechanism untuk mencari kolom stok
-   Menambahkan pengecekan khusus untuk nilai stok

### 3. SimpleLensaImport (`app/Imports/SimpleLensaImport.php`)

-   Memperbaiki method `parseNumeric()` untuk mengembalikan integer
-   Menambahkan debugging yang lebih detail
-   Menambahkan fallback mechanism
-   Menambahkan pengecekan khusus untuk nilai stok

### 4. LensaController (`app/Http/Controllers/LensaController.php`)

-   Menambahkan logging untuk melacak nilai stok setelah import
-   Menambahkan method `testImport()` untuk debugging

### 5. Routes (`routes/web.php`)

-   Menambahkan route untuk test import: `/lensa/test-import`

### 6. View (`resources/views/lensa/index.blade.php`)

-   Menambahkan tombol "Test Import Debug" untuk membantu debugging

## Cara Menggunakan

### 1. Import Normal

1. Klik tombol "Import Excel"
2. Pilih file Excel dengan format yang sesuai
3. Klik "Import Data"

### 2. Test Import Debug

1. Pilih file Excel yang akan diimport
2. Klik tombol "Test Import Debug"
3. Lihat preview data yang akan diimport termasuk nilai stok

### 3. Format Excel yang Diharapkan

Header yang diharapkan:

-   Kode Lensa (opsional, akan dibuat otomatis)
-   Merk Lensa
-   Type
-   Index
-   Coating
-   Harga Beli
-   Harga Jual
-   Stok (harus berisi angka)
-   Cabang
-   Sales
-   Tipe Stok
-   Catatan

## Debugging

Untuk melihat log debugging, cek file log Laravel di `storage/logs/laravel.log`.
Log akan menampilkan:

-   Nilai stok asli dari Excel
-   Proses parsing nilai stok
-   Nilai stok setelah parsing
-   Tipe data nilai stok

## Testing

1. Download template Excel dari tombol "Download Template Excel"
2. Isi data dengan nilai stok yang valid (misal: 10, 20, 30)
3. Gunakan "Test Import Debug" untuk melihat preview
4. Jika preview sudah benar, lakukan import normal

## Catatan Penting

-   Nilai stok harus berupa angka (integer)
-   Jika kolom stok kosong, akan default ke 0
-   Header kolom stok bisa berupa: "Stok", "Stock", "stok", "stock"
-   Sistem akan mencoba mencari kolom stok dengan berbagai variasi nama
