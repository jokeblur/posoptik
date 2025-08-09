# 🧪 **Testing Guide: Implementasi BPJS Pricing**

## 🎯 **Tujuan Testing**

Memverifikasi bahwa logika pricing BPJS berfungsi dengan benar di halaman transaksi penjualan.

## 🚀 **Cara Testing**

### **1. Persiapan Testing**

1. **Buka Browser**: Akses `http://localhost/opmelati/penjualan/create`
2. **Buka Developer Tools**: Tekan F12 untuk membuka console
3. **Login**: Pastikan sudah login sebagai kasir

### **2. Test Skenario**

#### **Skenario 1: BPJS III Pilih Frame BPJS II**

1. **Pilih Pasien**: Pilih pasien dengan jenis layanan "BPJS III"
2. **Pilih Frame**: Pilih frame dengan jenis "BPJS II"
3. **Pilih Lensa**: Pilih 1 lensa
4. **Verifikasi**:
    - Harga frame seharusnya: Rp 220.000 (bukan harga asli frame)
    - Total seharusnya: Rp 220.000 + harga lensa
    - Console menampilkan: "BPJS pricing API response"

#### **Skenario 2: BPJS III Pilih Frame BPJS I**

1. **Pilih Pasien**: Pilih pasien dengan jenis layanan "BPJS III"
2. **Pilih Frame**: Pilih frame dengan jenis "BPJS I"
3. **Pilih Lensa**: Pilih 1 lensa
4. **Verifikasi**:
    - Harga frame seharusnya: Rp 330.000
    - Total seharusnya: Rp 330.000 + harga lensa

#### **Skenario 3: BPJS II Pilih Frame BPJS I**

1. **Pilih Pasien**: Pilih pasien dengan jenis layanan "BPJS II"
2. **Pilih Frame**: Pilih frame dengan jenis "BPJS I"
3. **Pilih Lensa**: Pilih 1 lensa
4. **Verifikasi**:
    - Harga frame seharusnya: Rp 330.000
    - Total seharusnya: Rp 330.000 + harga lensa

#### **Skenario 4: BPJS Pilih Frame Umum**

1. **Pilih Pasien**: Pilih pasien dengan jenis layanan "BPJS III"
2. **Pilih Frame**: Pilih frame dengan jenis "Umum"
3. **Pilih Lensa**: Pilih 1 lensa
4. **Verifikasi**:
    - Harga frame seharusnya: harga asli frame - Rp 165.000 (default BPJS III)
    - Total seharusnya: (harga frame - Rp 165.000) + harga lensa
    - Console menampilkan: "Pengurangan Harga Default BPJS: - Rp 165.000"

#### **Skenario 5: BPJS II Pilih Frame Umum**

1. **Pilih Pasien**: Pilih pasien dengan jenis layanan "BPJS II"
2. **Pilih Frame**: Pilih frame dengan jenis "Umum"
3. **Pilih Lensa**: Pilih 1 lensa
4. **Verifikasi**:
    - Harga frame seharusnya: harga asli frame - Rp 220.000 (default BPJS II)
    - Total seharusnya: (harga frame - Rp 220.000) + harga lensa
    - Console menampilkan: "Pengurangan Harga Default BPJS: - Rp 220.000"

#### **Skenario 6: Frame Sama dengan Layanan**

1. **Pilih Pasien**: Pilih pasien dengan jenis layanan "BPJS I"
2. **Pilih Frame**: Pilih frame dengan jenis "BPJS I"
3. **Pilih Lensa**: Pilih 1 lensa
4. **Verifikasi**:
    - Harga frame seharusnya: Rp 330.000 (harga default)
    - Total seharusnya: Rp 330.000 + harga lensa

### **3. Debugging**

#### **Cek Console Browser**

```javascript
// Console seharusnya menampilkan:
"Calling BPJS pricing API: {pasien_id: 1, frame_id: 5}";
"BPJS pricing API response: {success: true, data: {...}}";
```

#### **Cek Network Tab**

1. Buka Developer Tools → Network
2. Pilih frame dan lensa
3. Cari request ke `/penjualan/calculate-bpjs-price`
4. Verifikasi response JSON

#### **Cek Laravel Log**

```bash
# Cek log Laravel
tail -f storage/logs/laravel.log
```

### **4. Expected Results**

#### **API Response Format**

```json
{
    "success": true,
    "data": {
        "pasien_service_type": "BPJS III",
        "frame_type": "BPJS II",
        "original_price": 250000,
        "calculated_price": 220000,
        "additional_cost": 0,
        "reason": "BPJS III memilih frame BPJS II (+0)"
    }
}
```

#### **UI Display**

-   **Cart Table**: Menampilkan informasi pricing BPJS
-   **Total Amount**: Harga yang sudah dikalkulasi
-   **BPJS Summary**: Ringkasan perhitungan

### **5. Troubleshooting**

#### **Masalah: API tidak dipanggil**

**Solusi:**

1. Cek console untuk error JavaScript
2. Pastikan pasien_id dan frame_id tersedia
3. Cek route sudah terdaftar: `php artisan route:list | grep calculate`

#### **Masalah: API error 500**

**Solusi:**

1. Cek log Laravel: `storage/logs/laravel.log`
2. Pastikan service class sudah dibuat
3. Cek dependency injection di controller

#### **Masalah: Harga tidak berubah**

**Solusi:**

1. Cek apakah pasien memiliki `service_type` BPJS
2. Cek apakah frame memiliki `jenis_frame` yang benar
3. Cek response API di console browser

### **6. Test Cases**

| Pasien   | Frame    | Expected Price        | Expected Total                  |
| -------- | -------- | --------------------- | ------------------------------- |
| BPJS I   | BPJS I   | Rp 330.000            | Rp 330.000 + lensa              |
| BPJS I   | BPJS II  | Rp 220.000            | Rp 220.000 + lensa              |
| BPJS I   | BPJS III | Rp 165.000            | Rp 165.000 + lensa              |
| BPJS I   | Umum     | Harga frame - 330.000 | (Harga frame - 330.000) + lensa |
| BPJS II  | BPJS I   | Rp 330.000            | Rp 330.000 + lensa              |
| BPJS II  | BPJS II  | Rp 220.000            | Rp 220.000 + lensa              |
| BPJS II  | Umum     | Harga frame - 220.000 | (Harga frame - 220.000) + lensa |
| BPJS III | BPJS I   | Rp 330.000            | Rp 330.000 + lensa              |
| BPJS III | BPJS II  | Rp 220.000            | Rp 220.000 + lensa              |
| BPJS III | BPJS III | Rp 165.000            | Rp 165.000 + lensa              |
| BPJS III | Umum     | Harga frame - 165.000 | (Harga frame - 165.000) + lensa |

### **7. Contoh Perhitungan Frame Umum**

#### **Skenario: BPJS III Pilih Frame Umum**

-   **Harga Frame Umum**: Rp 500.000
-   **Harga Default BPJS III**: Rp 165.000
-   **Harga yang Dibayar**: Rp 500.000 - Rp 165.000 = **Rp 335.000**

#### **Skenario: BPJS II Pilih Frame Umum**

-   **Harga Frame Umum**: Rp 400.000
-   **Harga Default BPJS II**: Rp 220.000
-   **Harga yang Dibayar**: Rp 400.000 - Rp 220.000 = **Rp 180.000**

#### **Skenario: BPJS I Pilih Frame Umum**

-   **Harga Frame Umum**: Rp 600.000
-   **Harga Default BPJS I**: Rp 330.000
-   **Harga yang Dibayar**: Rp 600.000 - Rp 330.000 = **Rp 270.000**

### **7. Performance Testing**

#### **Load Testing**

```bash
# Test API endpoint dengan curl
curl -X POST http://localhost/opmelati/penjualan/calculate-bpjs-price \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token();')" \
  -d '{"pasien_id": 1, "frame_id": 5}'
```

#### **Response Time**

-   API response time < 500ms
-   UI update time < 200ms
-   No JavaScript errors

---

**🎯 Setelah testing selesai, sistem BPJS pricing seharusnya berfungsi dengan sempurna!**
