# Optik Melati - Progressive Web App (PWA)

## 🚀 Fitur PWA yang Tersedia

### 1. **Installable App**
- Dapat diinstal di home screen smartphone/desktop
- Tampil seperti aplikasi native
- Icon dan splash screen yang menarik

### 2. **Offline Functionality**
- Cache data penting untuk akses offline
- Halaman offline yang informatif
- Sync data otomatis saat online kembali

### 3. **Service Worker**
- Background sync
- Push notifications (dapat dikembangkan lebih lanjut)
- Cache management otomatis

### 4. **Responsive Design**
- Optimized untuk mobile dan desktop
- Touch-friendly interface
- Standalone mode tanpa browser UI

## 📱 Cara Install PWA

### **Android (Chrome)**
1. Buka aplikasi di Chrome
2. Tap menu (3 titik) → "Add to Home screen"
3. Pilih "Add" untuk menginstal

### **iOS (Safari)**
1. Buka aplikasi di Safari
2. Tap Share button → "Add to Home Screen"
3. Tap "Add" untuk menginstal

### **Desktop (Chrome/Edge)**
1. Buka aplikasi di browser
2. Klik icon install (📱) di address bar
3. Klik "Install" untuk menginstal

## 🔧 File PWA yang Dibuat

### **Core Files**
- `public/manifest.json` - Konfigurasi PWA
- `public/sw.js` - Service Worker
- `public/js/pwa.js` - PWA registration & logic
- `public/css/pwa.css` - PWA styling

### **Meta Files**
- `public/browserconfig.xml` - Windows tile config
- `public/offline.html` - Halaman offline

### **Updated Layouts**
- `resources/views/layouts/app.blade.php` - Main layout dengan PWA
- `resources/views/layouts/guest.blade.php` - Guest layout dengan PWA

## 🎯 Fitur PWA Spesifik

### **Install Button**
- Muncul otomatis saat app dapat diinstal
- Animasi bounce yang menarik
- Hilang otomatis setelah instalasi

### **Offline Indicator**
- Menampilkan status koneksi internet
- Animasi pulse saat offline
- Auto-hide saat online kembali

### **Update Notification**
- Notifikasi otomatis saat ada update
- Button update yang mudah diakses
- Auto-hide setelah 10 detik

### **Shortcuts**
- Quick access ke fitur utama
- Dashboard, Penjualan, Pasien
- Icon yang konsisten

## 🛠️ Konfigurasi PWA

### **Manifest.json**
```json
{
  "name": "Optik Melati",
  "short_name": "Optik Melati",
  "theme_color": "#e74c3c",
  "background_color": "#ffffff",
  "display": "standalone",
  "orientation": "portrait-primary"
}
```

### **Service Worker Cache**
- CSS dan JS files
- Images penting
- Offline page
- Auto-update cache

## 📊 Testing PWA

### **Chrome DevTools**
1. F12 → Application tab
2. Service Workers → Check registration
3. Manifest → Verify PWA config
4. Storage → Check cache

### **Lighthouse Audit**
1. F12 → Lighthouse tab
2. Run audit untuk PWA
3. Target score: 90+ untuk PWA

### **Offline Testing**
1. Chrome DevTools → Network
2. Check "Offline"
3. Refresh page → Should show offline page

## 🔄 Update PWA

### **Auto Update**
- Service worker auto-detect changes
- Notifikasi update otomatis
- One-click update process

### **Manual Update**
- Hard refresh (Ctrl+F5)
- Clear cache browser
- Re-register service worker

## 🎨 Customization

### **Colors**
- Primary: `#e74c3c` (Red)
- Secondary: `#f39c12` (Orange)
- Background: `#ffffff` (White)

### **Icons**
- Logo: `public/image/optik-melati.png`
- Sizes: 192x192, 512x512
- Format: PNG with transparency

### **Fonts**
- Primary: Poppins (Google Fonts)
- Fallback: System fonts

## 🚨 Troubleshooting

### **PWA Tidak Muncul**
1. Check manifest.json syntax
2. Verify service worker registration
3. Clear browser cache
4. Check HTTPS requirement

### **Install Button Tidak Muncul**
1. App belum memenuhi kriteria installable
2. User sudah pernah dismiss prompt
3. Browser tidak support PWA
4. Check beforeinstallprompt event

### **Offline Tidak Bekerja**
1. Service worker tidak ter-register
2. Cache tidak ter-populate
3. Check fetch event handler
4. Verify offline.html exists

## 📈 Performance Tips

### **Cache Strategy**
- Cache-first untuk static assets
- Network-first untuk API calls
- Stale-while-revalidate untuk dynamic content

### **Bundle Optimization**
- Minify CSS/JS
- Optimize images
- Use CDN for external resources
- Implement lazy loading

## 🔮 Future Enhancements

### **Push Notifications**
- Order status updates
- Appointment reminders
- Stock alerts
- Marketing campaigns

### **Background Sync**
- Offline form submissions
- Data synchronization
- File uploads
- API calls queuing

### **Advanced Caching**
- IndexedDB for large data
- Cache API optimization
- Smart cache invalidation
- Progressive loading

## 📞 Support

Untuk pertanyaan atau masalah PWA:
1. Check browser console untuk errors
2. Verify file paths dan permissions
3. Test di browser berbeda
4. Check PWA requirements compliance

---

**Optik Melati PWA** - Aplikasi manajemen optik yang modern, cepat, dan dapat diakses offline! 🎯👓
