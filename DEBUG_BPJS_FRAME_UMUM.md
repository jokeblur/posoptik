# 🔍 **Debug Guide: Frame Umum BPJS Pricing**

## 🎯 **Masalah yang Diperbaiki**

Logika pricing untuk frame Umum ketika pasien BPJS memilih frame dengan jenis "Umum".

## 📋 **Logika yang Benar**

```
Harga Frame Umum = Harga Asli Frame - Harga Default BPJS Pasien
```

### **Contoh Perhitungan:**

-   **Pasien**: BPJS III
-   **Frame Umum**: Rp 500.000
-   **Default BPJS III**: Rp 165.000
-   **Harga Dibayar**: Rp 500.000 - Rp 165.000 = **Rp 335.000**

## 🛠️ **File yang Diperbaiki**

### **1. `app/Services/BpjsPricingService.php`**

-   ✅ Logika `calculateFramePrice()` untuk frame Umum
-   ✅ Perhitungan: `$calculatedPrice = $framePrice - $defaultPrice`
-   ✅ Logging untuk debugging

### **2. `app/Http/Controllers/PenjualanController.php`**

-   ✅ Method `calculateBpjsPrice()` untuk API
-   ✅ Method `testBpjsPricing()` untuk debugging
-   ✅ Integrasi di method `store()`

### **3. `resources/views/penjualan/create.blade.php`**

-   ✅ JavaScript untuk memanggil API
-   ✅ Display informasi pengurangan harga
-   ✅ Fallback logic jika API gagal

## 🧪 **Cara Testing**

### **1. Test via API**

```bash
curl -X POST http://localhost/opmelati/penjualan/test-bpjs-pricing \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token();')" \
  -d '{
    "pasien_id": 1,
    "frame_id": 5
  }'
```

### **2. Test via Browser**

1. **Buka**: `http://localhost/opmelati/penjualan/create`
2. **Pilih Pasien**: BPJS III
3. **Pilih Frame**: Frame dengan jenis "Umum"
4. **Cek Console**: Lihat log API call
5. **Cek Network Tab**: Lihat response API

### **3. Test via Laravel Log**

```bash
tail -f storage/logs/laravel.log | grep "BPJS Pricing"
```

## 🔍 **Expected Results**

### **API Response untuk Frame Umum:**

```json
{
    "success": true,
    "data": {
        "pasien_service_type": "BPJS III",
        "frame_type": "Umum",
        "original_price": 500000,
        "calculated_price": 335000,
        "additional_cost": 165000,
        "reason": "BPJS III memilih frame Umum (harga frame - harga default BPJS)",
        "debug_info": {
            "is_frame_umum": true,
            "default_bpjs_price": 165000
        }
    }
}
```

### **Laravel Log Output:**

```
[2024-01-XX XX:XX:XX] local.INFO: BPJS Pricing Calculation Start: {
  "pasien_service_type": "BPJS III",
  "frame_type": "Umum",
  "frame_price": 500000
}

[2024-01-XX XX:XX:XX] local.INFO: BPJS Pricing - Frame Umum: {
  "frame_price": 500000,
  "default_price": 165000,
  "calculated_price": 335000,
  "result": {
    "price": 335000,
    "additional_cost": 165000,
    "reason": "BPJS III memilih frame Umum (harga frame - harga default BPJS)"
  }
}
```

## 🐛 **Troubleshooting**

### **Masalah: Harga tidak berkurang**

**Solusi:**

1. Cek apakah frame memiliki `jenis_frame = 'Umum'`
2. Cek apakah pasien memiliki `service_type` BPJS
3. Cek log Laravel untuk debugging info

### **Masalah: API error 500**

**Solusi:**

1. Cek log Laravel: `storage/logs/laravel.log`
2. Pastikan service class sudah dibuat
3. Cek dependency injection

### **Masalah: Frontend tidak update**

**Solusi:**

1. Cek console browser untuk error JavaScript
2. Cek Network tab untuk API response
3. Pastikan route sudah terdaftar

## 📊 **Test Cases**

| Pasien   | Frame | Harga Frame | Expected Price | Expected Total     |
| -------- | ----- | ----------- | -------------- | ------------------ |
| BPJS III | Umum  | Rp 500.000  | Rp 335.000     | Rp 335.000 + lensa |
| BPJS II  | Umum  | Rp 400.000  | Rp 180.000     | Rp 180.000 + lensa |
| BPJS I   | Umum  | Rp 600.000  | Rp 270.000     | Rp 270.000 + lensa |

## ✅ **Verification Steps**

1. **Backend Test**: Test API endpoint
2. **Frontend Test**: Test di halaman transaksi
3. **Database Test**: Cek data tersimpan dengan harga yang benar
4. **Log Test**: Cek log untuk debugging info

---

**🎯 Setelah testing selesai, logika frame Umum seharusnya bekerja dengan benar!**
