# Grafik Dashboard Super Admin

## Overview
Fitur grafik penjualan yang ditambahkan pada dashboard super admin untuk memberikan visualisasi data penjualan yang komprehensif.

## Fitur Grafik

### 1. Grafik Penjualan 7 Hari Terakhir
- **Tipe**: Line Chart
- **Data**: Total penjualan dan jumlah transaksi per hari
- **Fitur**:
  - Dual Y-axis (penjualan di kiri, transaksi di kanan)
  - Responsive design
  - Tooltip interaktif
  - Format tanggal Indonesia

### 2. Grafik Perbandingan BPJS vs Umum
- **Tipe**: Doughnut Chart
- **Data**: Perbandingan total penjualan BPJS vs transaksi umum
- **Fitur**:
  - Persentase otomatis
  - Format currency Indonesia
  - Legend di bawah chart

### 3. Grafik Status Transaksi BPJS
- **Tipe**: Bar Chart
- **Data**: Jumlah transaksi berdasarkan status (Normal/Naik Kelas)
- **Fitur**:
  - Warna berbeda untuk setiap status
  - Tooltip dengan detail jumlah

### 4. Grafik Penjualan per Cabang
- **Tipe**: Bar Chart
- **Data**: Total penjualan per cabang (top 10)
- **Fitur**:
  - Hanya muncul jika tidak ada filter cabang
  - Format currency Indonesia
  - Responsive design

## Teknologi yang Digunakan

### Frontend
- **Chart.js**: Library JavaScript untuk membuat grafik
- **CDN**: https://cdn.jsdelivr.net/npm/chart.js
- **Responsive**: Grafik menyesuaikan ukuran layar

### Backend
- **Laravel**: Controller dan data processing
- **MySQL**: Query untuk aggregasi data
- **Carbon**: Manipulasi tanggal

## Struktur Data

### Data yang Disediakan
```php
[
    'daily_sales' => [
        '2024-01-01' => [
            'total_sales' => 1000000,
            'total_transactions' => 5
        ],
        // ... 7 hari terakhir
    ],
    'last_7_days' => ['2024-01-01', '2024-01-02', ...],
    'bpjs_vs_umum' => [
        ['transaction_type' => 'BPJS', 'total_sales' => 500000],
        ['transaction_type' => 'Umum', 'total_sales' => 300000]
    ],
    'branch_sales' => [
        ['branch_name' => 'Cabang A', 'total_sales' => 800000],
        // ... top 10 cabang
    ],
    'bpjs_status' => [
        ['transaction_status' => 'Normal', 'total_transactions' => 10],
        ['transaction_status' => 'Naik Kelas', 'total_transactions' => 5]
    ]
]
```

## Implementasi

### 1. DashboardController
- Method `getChartDataPrivate()`: Menyediakan data untuk grafik
- Method `getChartData()`: API endpoint untuk data grafik
- Filter berdasarkan cabang untuk admin

### 2. View (home.blade.php)
- Conditional rendering untuk super admin
- Canvas elements untuk setiap grafik
- JavaScript untuk inisialisasi Chart.js

### 3. Routes
```php
Route::get('/api/dashboard/chart-data', [DashboardController::class, 'getChartData'])
    ->name('dashboard.chart-data')
    ->middleware('role:admin,super admin');
```

## Query Database

### 1. Penjualan Harian
```sql
SELECT 
    DATE(created_at) as date, 
    SUM(total) as total_sales, 
    COUNT(*) as total_transactions
FROM penjualan 
WHERE created_at BETWEEN ? AND ?
GROUP BY DATE(created_at)
```

### 2. BPJS vs Umum
```sql
SELECT 
    CASE 
        WHEN pasien_service_type IS NOT NULL THEN "BPJS"
        ELSE "Umum"
    END as transaction_type,
    SUM(total) as total_sales,
    COUNT(*) as total_transactions
FROM penjualan 
WHERE created_at BETWEEN ? AND ?
GROUP BY transaction_type
```

### 3. Status Transaksi BPJS
```sql
SELECT 
    transaction_status,
    COUNT(*) as total_transactions,
    SUM(total) as total_sales
FROM penjualan 
WHERE pasien_service_type IS NOT NULL
    AND created_at BETWEEN ? AND ?
GROUP BY transaction_status
```

### 4. Penjualan per Cabang
```sql
SELECT 
    branches.name as branch_name, 
    SUM(penjualan.total) as total_sales, 
    COUNT(*) as total_transactions
FROM penjualan 
JOIN branches ON penjualan.branch_id = branches.id
WHERE created_at BETWEEN ? AND ?
GROUP BY branches.id, branches.name
ORDER BY total_sales DESC
LIMIT 10
```

## Keuntungan

### 1. Visualisasi Data
- Data penjualan mudah dipahami
- Trend dan pola terlihat jelas
- Perbandingan antar kategori

### 2. Analisis Bisnis
- Monitoring performa cabang
- Analisis tren penjualan
- Evaluasi strategi BPJS

### 3. Real-time Data
- Data selalu up-to-date
- Refresh otomatis saat akses dashboard
- Filter berdasarkan cabang

## Customization

### 1. Periode Data
- Saat ini: 7 hari terakhir
- Bisa diubah di method `getChartDataPrivate()`

### 2. Jenis Grafik
- Line chart untuk trend
- Doughnut untuk perbandingan
- Bar chart untuk kategorisasi

### 3. Warna dan Style
- Customizable di JavaScript
- Menggunakan Chart.js options
- Responsive design

## Troubleshooting

### 1. Grafik Tidak Muncul
- Cek console browser untuk error
- Pastikan Chart.js CDN terload
- Verifikasi data dari API

### 2. Data Kosong
- Cek query database
- Pastikan ada transaksi dalam 7 hari
- Verifikasi filter cabang

### 3. Performance
- Query sudah dioptimasi dengan index
- Data di-cache di controller
- Lazy loading untuk grafik

## Future Enhancement

### 1. Filter Tambahan
- Filter periode custom
- Filter berdasarkan produk
- Filter berdasarkan kasir

### 2. Export Grafik
- Export ke PDF
- Export ke Excel
- Screenshot grafik

### 3. Real-time Updates
- WebSocket untuk update real-time
- Auto-refresh setiap interval
- Notifikasi perubahan data 