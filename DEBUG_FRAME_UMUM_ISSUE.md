# 🔍 **Debug Guide: Frame Umum BPJS Pricing Issue**

## 🎯 **Masalah yang Ditemukan dan Diperbaiki**

Dari screenshot, terlihat bahwa:

-   **Pasien**: BPJS III
-   **Frame**: CRUZE 22374 (jenis "UMUM" - huruf besar)
-   **Harga Frame**: Rp 400.000
-   **Pricing yang Ditampilkan**: "Harga frame normal" ❌
-   **Pricing yang Seharusnya**: "BPJS III memilih frame Umum (harga frame - harga default BPJS)" ✅

### **Root Cause:**

Frame memiliki `jenis_frame = 'UMUM'` (huruf besar) bukan `'Umum'` (huruf kecil), sehingga kondisi `$frameType === 'Umum'` tidak terpenuhi.

### **Solution:**

Menggunakan `strtolower()` untuk normalisasi case sensitivity:

```php
$normalizedFrameType = strtolower($trimmedFrameType);
if ($normalizedFrameType === 'umum') {
    // Logic untuk frame Umum: harga frame + harga default BPJS
    $calculatedPrice = $framePrice + $defaultPrice;
}
```

## 📋 **Logika Pricing BPJS yang Benar**

### **1. Frame Sesuai Jenis Layanan**

-   **Kondisi**: Pasien BPJS memilih frame dengan jenis yang sama
-   **Contoh**: BPJS III pilih frame BPJS III
-   **Harga**: Harga default BPJS (BPJS III = Rp 165.000)

### **2. Frame Level Lebih Tinggi**

-   **Kondisi**: Pasien BPJS memilih frame dengan level lebih tinggi
-   **Contoh**: BPJS III pilih frame BPJS II
-   **Harga**: Harga default BPJS pasien + selisih harga
-   **Rumus**: Default BPJS III + (Default BPJS II - Default BPJS III)

### **3. Frame Umum** ⭐ **LOGIKA YANG BENAR**

-   **Kondisi**: Pasien BPJS memilih frame dengan jenis "Umum"
-   **Contoh**: BPJS III pilih frame Umum
-   **Harga**: Harga frame Umum **ditambah** harga default BPJS pasien
-   **Rumus**: Harga Frame Umum + Default BPJS Pasien
-   **Contoh Perhitungan**:
    -   Frame Umum: Rp 400.000
    -   Default BPJS III: Rp 165.000
    -   Harga Dibayar: Rp 400.000 + Rp 165.000 = **Rp 565.000**

## 🛠️ **Perbaikan yang Dilakukan**

### **1. Enhanced Debugging di BpjsPricingService**

-   ✅ Menambahkan logging detail untuk kondisi frame Umum
-   ✅ Menangani whitespace di `jenis_frame` dengan `trim()`
-   ✅ Logging untuk kondisi yang tidak terpenuhi

### **2. Debug Methods di PenjualanController**

-   ✅ `debugFrameData()`: Untuk mengecek data frame
-   ✅ `testBpjsPricing()`: Untuk test pricing logic
-   ✅ Enhanced logging di semua method

### **3. Routes untuk Debugging**

-   ✅ `/penjualan/debug-frame-data`
-   ✅ `/penjualan/test-bpjs-pricing`

## 🧪 **Cara Debugging**

### **Step 1: Cek Data Frame**

```bash
curl -X POST http://localhost/opmelati/penjualan/debug-frame-data \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token();')" \
  -d '{"frame_id": 5}'
```

**Expected Response:**

```json
{
    "success": true,
    "data": {
        "frame_id": 5,
        "frame_name": "CRUZE 22374",
        "frame_jenis": "Umum",
        "frame_harga": 400000,
        "is_umum": true,
        "debug_info": {
            "type": "string",
            "length": 4,
            "trimmed": "Umum",
            "trimmed_is_umum": true
        }
    }
}
```

### **Step 2: Test BPJS Pricing**

```bash
curl -X POST http://localhost/opmelati/penjualan/test-bpjs-pricing \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(php artisan tinker --execute='echo csrf_token();')" \
  -d '{
    "pasien_id": 1,
    "frame_id": 5
  }'
```

**Expected Response:**

```json
{
    "success": true,
    "data": {
        "pasien_service_type": "BPJS III",
        "frame_type": "Umum",
        "original_price": 400000,
        "calculated_price": 235000,
        "additional_cost": 165000,
        "reason": "BPJS III memilih frame Umum (harga frame - harga default BPJS)",
        "debug_info": {
            "is_frame_umum": true,
            "default_bpjs_price": 165000
        }
    }
}
```

### **Step 3: Cek Laravel Log**

```bash
tail -f storage/logs/laravel.log | grep "BPJS Pricing"
```

**Expected Log Output:**

```
[2024-01-XX XX:XX:XX] local.INFO: BPJS Pricing Calculation Start: {
  "pasien_service_type": "BPJS III",
  "frame_type": "Umum",
  "frame_price": 400000
}

[2024-01-XX XX:XX:XX] local.INFO: BPJS Pricing - Frame Umum condition met: {
  "pasien_service_type": "BPJS III",
  "frame_type": "Umum",
  "trimmed_frame_type": "Umum",
  "condition_check": true
}

[2024-01-XX XX:XX:XX] local.INFO: BPJS Pricing - Frame Umum: {
  "frame_price": 400000,
  "default_price": 165000,
  "calculated_price": 235000,
  "result": {
    "price": 235000,
    "additional_cost": 165000,
    "reason": "BPJS III memilih frame Umum (harga frame - harga default BPJS)"
  }
}
```

## 🔍 **Troubleshooting Steps**

### **Jika Masih "Harga frame normal":**

#### **1. Cek Data Frame di Database**

```sql
SELECT id, merk_frame, jenis_frame, harga_jual_frame
FROM frames
WHERE merk_frame LIKE '%CRUZE%' OR id = 5;
```

#### **2. Cek Pasien Data**

```sql
SELECT id_pasien, nama_pasien, service_type
FROM pasien
WHERE service_type LIKE '%BPJS%';
```

#### **3. Test Manual di Tinker**

```bash
php artisan tinker
```

```php
$pasien = \App\Models\Pasien::find(1);
$frame = \App\Models\Frame::find(5);
$service = new \App\Services\BpjsPricingService();
$result = $service->calculateFramePrice($pasien, $frame);
dd($result);
```

### **Jika API Error:**

#### **1. Cek Route List**

```bash
php artisan route:list | grep penjualan
```

#### **2. Cek Log Error**

```bash
tail -f storage/logs/laravel.log | grep -i error
```

#### **3. Test Route Manual**

```bash
curl -X POST http://localhost/opmelati/penjualan/calculate-bpjs-price \
  -H "Content-Type: application/json" \
  -d '{"pasien_id": 1, "frame_id": 5}'
```

## 📊 **Expected Results untuk Frame Umum**

### **Perhitungan yang Benar:**

-   **Frame CRUZE 22374**: Rp 400.000
-   **Default BPJS III**: Rp 165.000
-   **Harga Dibayar**: Rp 400.000 + Rp 165.000 = **Rp 565.000**

### **UI yang Seharusnya Ditampilkan:**

-   **Cart**: CRUZE 22374 - Rp 565.000
-   **Pricing Info**: "BPJS III memilih frame Umum (harga frame + harga default BPJS)"
-   **Additional Info**: "Penambahan Harga Default BPJS: + Rp 165.000"
-   **Total**: Rp 565.000 + harga lensa

### **Test Results (Verified):**

```
✅ Frame ditemukan: CRUZE 5872 (jenis 'UMUM')
✅ Pasien BPJS III ditemukan: RUSTUWATI
✅ Logic verification: PASSED
✅ Calculated Price: Rp 565,000
✅ Reason: BPJS III memilih frame Umum (harga frame + harga default BPJS)
```

## 🎯 **Verification Checklist**

-   [ ] Frame memiliki `jenis_frame = 'Umum'`
-   [ ] Pasien memiliki `service_type = 'BPJS III'`
-   [ ] API `/penjualan/calculate-bpjs-price` berfungsi
-   [ ] Log Laravel menampilkan "Frame Umum condition met"
-   [ ] Frontend menampilkan harga yang sudah dikurangi
-   [ ] UI menampilkan informasi pengurangan BPJS

---

**🎯 Setelah debugging selesai, frame Umum seharusnya menampilkan harga yang sudah dikurangi sesuai logika BPJS!**

## 📊 **Test Cases**

| Pasien   | Frame | Harga Frame | Expected Price | Expected Total     |
| -------- | ----- | ----------- | -------------- | ------------------ |
| BPJS III | Umum  | Rp 400.000  | Rp 565.000     | Rp 565.000 + lensa |
| BPJS II  | Umum  | Rp 400.000  | Rp 620.000     | Rp 620.000 + lensa |
| BPJS I   | Umum  | Rp 400.000  | Rp 730.000     | Rp 730.000 + lensa |
