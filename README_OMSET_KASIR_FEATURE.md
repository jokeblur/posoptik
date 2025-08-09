# Fitur Omset Hari Ini untuk Dashboard Kasir

## Deskripsi
Fitur ini menambahkan tampilan omset hari ini pada dashboard kasir, termasuk:
- Total omset hari ini
- Omset BPJS hari ini
- Omset umum hari ini
- Tabel detail transaksi hari ini

## File yang Dimodifikasi

### 1. `resources/views/home.blade.php`
**Perubahan:**
- Menambahkan section khusus untuk kasir dengan box omset
- Menampilkan 3 box omset: Total, BPJS, dan Umum
- Menambahkan tabel detail transaksi hari ini

**Fitur yang Ditambahkan:**
```blade
{{-- Box Omset untuk Kasir --}}
<div class="row" style="margin-bottom: 32px;">
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>Rp {{ number_format($omsetKasir ?? 0, 0, ',', '.') }}</h3>
                <p>Omset Hari Ini</p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>Rp {{ number_format($omsetBpjs ?? 0, 0, ',', '.') }}</h3>
                <p>Omset BPJS Hari Ini</p>
            </div>
            <div class="icon"><i class="fa fa-heartbeat"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>Rp {{ number_format($omsetUmum ?? 0, 0, ',', '.') }}</h3>
                <p>Omset Umum Hari Ini</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
        </div>
    </div>
</div>
```

**Tabel Transaksi Hari Ini:**
```blade
{{-- Tabel Transaksi Hari Ini untuk Kasir --}}
@if(isset($transaksiKasir) && $transaksiKasir->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Transaksi Hari Ini</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Waktu</th>
                                <th>No. Transaksi</th>
                                <th>Nama Pasien</th>
                                <th>Jenis Layanan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksiKasir as $index => $transaksi)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $transaksi->created_at->format('H:i') }}</td>
                                <td>{{ $transaksi->no_transaksi }}</td>
                                <td>{{ $transaksi->pasien->nama ?? '-' }}</td>
                                <td>
                                    @if($transaksi->pasien && $transaksi->pasien->service_type)
                                        <span class="label label-info">{{ $transaksi->pasien->service_type }}</span>
                                    @else
                                        <span class="label label-default">UMUM</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                                <td>
                                    @if($transaksi->status_pengerjaan == 'Sudah Diambil')
                                        <span class="label label-success">{{ $transaksi->status_pengerjaan }}</span>
                                    @else
                                        <span class="label label-warning">{{ $transaksi->status_pengerjaan }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
```

## Data yang Ditampilkan

### 1. **Omset Total Hari Ini**
- Menampilkan total omset kasir hari ini
- Format: Rp dengan pemisah ribuan
- Warna: Hijau (bg-success)

### 2. **Omset BPJS Hari Ini**
- Menampilkan omset dari transaksi BPJS hari ini
- Format: Rp dengan pemisah ribuan
- Warna: Biru (bg-info)

### 3. **Omset Umum Hari Ini**
- Menampilkan omset dari transaksi umum hari ini
- Format: Rp dengan pemisah ribuan
- Warna: Kuning (bg-warning)

### 4. **Tabel Detail Transaksi**
- Menampilkan semua transaksi kasir hari ini
- Informasi: Waktu, No. Transaksi, Nama Pasien, Jenis Layanan, Total, Status
- Status ditampilkan dengan label berwarna

## Logika Perhitungan

Data omset dihitung berdasarkan:
- **Cabang**: Hanya transaksi di cabang kasir tersebut
- **User**: Hanya transaksi yang dibuat oleh kasir tersebut
- **Waktu**: Berdasarkan open day (jika ada) atau hari ini
- **Jenis Layanan**: Dipisah antara BPJS dan UMUM

## Controller Logic

Data sudah tersedia di `DashboardController` dengan variabel:
- `$omsetKasir`: Total omset kasir hari ini
- `$omsetBpjs`: Omset BPJS kasir hari ini
- `$omsetUmum`: Omset umum kasir hari ini
- `$transaksiKasir`: Detail transaksi kasir hari ini

## Tampilan

### Box Omset
- 3 box dengan warna berbeda
- Icon yang sesuai untuk setiap jenis omset
- Format angka dengan pemisah ribuan

### Tabel Transaksi
- Responsive table
- Label berwarna untuk jenis layanan dan status
- Format waktu yang mudah dibaca

## Keamanan

- Hanya kasir yang dapat melihat omset mereka sendiri
- Data dibatasi berdasarkan cabang kasir
- Tidak ada akses ke data kasir lain

## Catatan

- Fitur ini menggunakan data yang sudah ada di controller
- Tidak memerlukan perubahan pada database
- Kompatibel dengan sistem open/close day yang sudah ada 