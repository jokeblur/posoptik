# Fitur Real-time untuk Aplikasi Optik Melati

## Deskripsi

Implementasi fitur real-time menggunakan Server-Sent Events (SSE) untuk memberikan update langsung pada dashboard kasir dan admin tanpa perlu refresh halaman.

## Fitur Real-time yang Diimplementasikan

### 1. **Dashboard Omset Kasir Real-time**

-   Update omset total hari ini setiap 3 detik
-   Update omset BPJS dan umum secara terpisah
-   Update jumlah transaksi hari ini
-   Update tabel transaksi terbaru
-   Visual feedback dengan animasi pulse saat ada update

### 2. **Notifikasi Real-time**

-   Notifikasi transaksi baru (untuk admin/super admin)
-   Notifikasi pesanan siap diambil
-   Toast notification menggunakan SweetAlert2
-   Browser notification (jika user memberikan permission)

### 3. **Status Connection Indicator**

-   Indikator status koneksi real-time
-   Auto-reconnect jika koneksi terputus
-   Visual feedback untuk status connected/disconnected

## File yang Dibuat/Dimodifikasi

### 1. `app/Http/Controllers/RealtimeController.php`

**Controller baru untuk handling real-time endpoints:**

**Endpoints:**

-   `GET /realtime/dashboard` - Real-time dashboard data
-   `GET /realtime/omset-kasir` - Real-time omset kasir
-   `GET /realtime/notifications` - Real-time notifications

**Fitur:**

```php
public function omsetKasir(Request $request)
{
    // Stream real-time omset data untuk kasir
    // Update setiap 3 detik
}

public function notifications(Request $request)
{
    // Stream notifikasi real-time
    // Check for new transactions, ready orders, etc.
}
```

### 2. `public/js/realtime.js`

**JavaScript library untuk managing real-time connections:**

**Class RealtimeManager:**

```javascript
class RealtimeManager {
    connectDashboard()     // Connect to dashboard updates
    connectOmsetKasir()    // Connect to omset updates
    connectNotifications() // Connect to notifications

    // Auto-reconnection, visibility handling
}
```

**Features:**

-   Auto-reconnection dengan exponential backoff
-   Page visibility handling (pause saat tab tidak aktif)
-   Connection status monitoring
-   Multiple concurrent SSE connections

### 3. `resources/views/home.blade.php`

**Dashboard dengan real-time integration:**

**Perubahan:**

-   Added real-time status indicator
-   Added CSS classes untuk real-time updates
-   Added pulse animation untuk visual feedback
-   Added JavaScript integration dengan RealtimeManager

**Real-time Elements:**

```blade
<div class="small-box bg-success omset-total">
    <!-- Akan diupdate real-time -->
</div>

<div id="realtime-status">
    <!-- Status koneksi real-time -->
</div>

<table id="transaksi-terbaru-table">
    <!-- Tabel akan diupdate real-time -->
</table>
```

### 4. `routes/web.php`

**Routes untuk real-time endpoints:**

```php
Route::get('/realtime/dashboard', [RealtimeController::class, 'dashboard']);
Route::get('/realtime/omset-kasir', [RealtimeController::class, 'omsetKasir']);
Route::get('/realtime/notifications', [RealtimeController::class, 'notifications']);
```

## Teknologi yang Digunakan

### Server-Sent Events (SSE)

-   **Mengapa SSE?** Lebih sederhana dari WebSocket untuk one-way communication
-   **Browser Support:** Excellent (IE10+, semua modern browsers)
-   **Network Friendly:** HTTP-based, melewati firewall dengan mudah

### JavaScript EventSource API

```javascript
const eventSource = new EventSource("/realtime/dashboard");
eventSource.onmessage = function (event) {
    const data = JSON.parse(event.data);
    // Update UI
};
```

### Laravel Streaming Response

```php
return response()->stream(function () {
    while (true) {
        echo "data: " . json_encode($data) . "\n\n";
        flush();
        sleep(3);
    }
}, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
]);
```

## Performance & Optimizations

### 1. **Connection Management**

-   Maximum 5 reconnection attempts
-   Exponential backoff delay
-   Automatic cleanup on page unload

### 2. **Resource Efficiency**

-   Connections pause when tab not visible
-   Optimized query untuk real-time data
-   Minimum polling interval (3 seconds untuk omset)

### 3. **Error Handling**

-   Graceful degradation jika real-time tidak tersedia
-   User-friendly error messages
-   Automatic retry logic

## User Experience

### 1. **Visual Feedback**

-   Pulse animation saat ada update data
-   Connection status indicator
-   Smooth transitions untuk perubahan data

### 2. **Notifications**

-   Toast notifications untuk events penting
-   Browser notifications (optional)
-   Sound alerts (dapat ditambahkan)

### 3. **Responsiveness**

-   Real-time updates tanpa mengganggu user interaction
-   Non-blocking UI updates
-   Mobile-friendly

## Security

### 1. **Authentication**

-   Semua endpoint real-time memerlukan authentication
-   Data filtered berdasarkan user role dan branch

### 2. **Authorization**

-   Kasir hanya melihat data cabang mereka
-   Admin/Super admin dapat melihat data yang sesuai

### 3. **Rate Limiting**

-   Connection limits untuk prevent abuse
-   Automatic cleanup untuk abandoned connections

## Setup dan Konfigurasi

### 1. **Server Requirements**

-   PHP >= 7.4
-   Laravel >= 8.0
-   Server dengan support untuk long-running requests

### 2. **Browser Requirements**

-   Modern browsers dengan EventSource support
-   JavaScript enabled

### 3. **Optional Enhancements**

```javascript
// Request notification permission
Notification.requestPermission();

// Add sound alerts
const audio = new Audio("/sounds/notification.mp3");
audio.play();
```

## Monitoring dan Debugging

### 1. **Connection Status**

-   Visual indicator di dashboard
-   Console logging untuk development
-   Error tracking dan reporting

### 2. **Performance Monitoring**

```javascript
// Monitor connection health
setInterval(() => {
    console.log("Active connections:", Object.keys(eventSources).length);
}, 30000);
```

### 3. **Server-side Logging**

```php
Log::info('Real-time connection established', [
    'user_id' => $user->id,
    'endpoint' => 'omset-kasir'
]);
```

## Deployment Notes

### 1. **Nginx Configuration**

```nginx
location /realtime/ {
    proxy_pass http://backend;
    proxy_set_header Connection '';
    proxy_http_version 1.1;
    proxy_buffering off;
    proxy_cache off;
}
```

### 2. **PHP Configuration**

```ini
max_execution_time = 0
max_input_time = -1
```

### 3. **Laravel Configuration**

```php
// config/session.php
'lifetime' => 525600, // Longer session for real-time connections
```

## Pengembangan Lanjutan

### 1. **Possible Enhancements**

-   WebSocket upgrade untuk two-way communication
-   Redis untuk scaling multiple servers
-   Push notifications untuk mobile apps
-   Real-time charts dan analytics

### 2. **Additional Features**

-   Real-time inventory updates
-   Live customer queue management
-   Real-time collaboration features
-   Voice notifications

### 3. **Performance Scaling**

-   Redis pub/sub untuk multiple servers
-   WebSocket clustering
-   CDN untuk static assets
-   Database query optimization

## Testing

### 1. **Manual Testing**

-   Buka multiple tabs untuk test real-time sync
-   Test connection recovery saat network interrupt
-   Test dengan multiple users

### 2. **Automated Testing**

```php
// Test SSE endpoint
public function test_realtime_omset_stream()
{
    $response = $this->get('/realtime/omset-kasir');
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/event-stream');
}
```

## Troubleshooting

### 1. **Common Issues**

-   **Connection timeouts:** Check server configuration
-   **Memory issues:** Monitor PHP memory usage
-   **Browser limits:** Maximum 6 SSE connections per domain

### 2. **Debugging Steps**

1. Check browser developer tools Network tab
2. Verify server logs untuk connection issues
3. Test endpoints manually dengan curl
4. Check firewall/proxy settings

## Kesimpulan

Implementasi real-time features ini memberikan:

-   **Better User Experience:** Updates langsung tanpa refresh
-   **Improved Productivity:** Kasir dapat melihat omset real-time
-   **Better Communication:** Notifikasi instant untuk events penting
-   **Modern Feel:** Aplikasi terasa lebih responsif dan modern

Real-time features ini menggunakan teknologi yang mature dan reliable, dengan fallback mechanisms untuk memastikan aplikasi tetap berfungsi walaupun real-time connection bermasalah.
