# APK Kasir Optik Melati (WebView)

Project Android WebView untuk halaman kasir mobile (`/kasir-mobile`) aplikasi POS Optik Melati.

## Cara Build APK

1. **Install Android Studio** (versi terbaru, minimal Hedgehog).
2. **Buka folder ini** (`android-kasir/`) lewat Android Studio → `File > Open`.
3. **Ganti URL server** di `app/src/main/java/com/optikmelati/kasir/MainActivity.java`:
   ```java
   private static final String BASE_URL = "https://domain-vps-lo.com";
   ```
   Ganti `https://domain-vps-lo.com` dengan domain VPS yang asli (WAJIB HTTPS — Android memblokir HTTP cleartext secara default).
4. **Ganti ikon aplikasi** (opsional):
   - Klik kanan folder `app/src/main/res` → `New > Image Asset`
   - Pilih gambar logo: `public/image/logoapp.png` dari project Laravel
   - Finish — ikon `ic_launcher` akan digenerate otomatis di semua folder mipmap.
5. **Build APK debug (untuk tes langsung ke HP):**
   - Menu `Build > Build App Bundle(s)/APK(s) > Build APK(s)`
   - Hasilnya di `app/build/outputs/apk/debug/app-debug.apk`
   - Kirim ke HP kasir via WhatsApp/USB, install (izinkan "Install from unknown sources").
6. **Build APK release (untuk dibagikan resmi):**
   - Menu `Build > Generate Signed App Bundle/APK > APK`
   - Buat keystore baru (simpan baik-baik file `.jks` + passwordnya — jangan sampai hilang!)
   - Hasilnya `app-release.apk`

## Fitur APK

- Langsung buka halaman `/kasir-mobile` (halaman kasir khusus HP/tablet)
- Session login tersimpan (cookie) — kasir cukup login sekali
- Pull-to-refresh (tarik layar ke bawah buat reload)
- Tombol back Android = back di halaman web
- Progress bar saat loading halaman

## Catatan Penting

- **Server harus bisa diakses dari HP**: kalau masih localhost XAMPP, HP & komputer harus di WiFi yang sama, dan `BASE_URL` diisi IP komputer (misal `http://192.168.1.10/posoptikmelati/public`). Kalau pakai HTTP (bukan HTTPS), tambahkan `android:usesCleartextTraffic="true"` di `AndroidManifest.xml` (sudah diset `false` untuk keamanan).
- Semua logic transaksi tetap di server Laravel — APK ini cuma "jendela" ke web. Jadi kalau update fitur di web, APK otomatis ikut update tanpa perlu build ulang.
- Kalau nanti mau upload ke Google Play Store, build **AAB** (`Build > Generate Signed App Bundle/APK > App Bundle`) bukan APK.
