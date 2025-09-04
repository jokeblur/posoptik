const CACHE_NAME = "optik-melati-v1.1.0";
const urlsToCache = [
    "/",
    "/css/app.css",
    "/js/app.js",
    "/image/logoapp.png",
    "/AdminLTE2/bower_components/bootstrap/dist/css/bootstrap.min.css",
    "/AdminLTE2/bower_components/jquery/dist/jquery.min.js",
    "/AdminLTE2/bower_components/bootstrap/dist/js/bootstrap.min.js",
];

// Install event - cache resources
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Opened cache");
            return cache.addAll(urlsToCache);
        })
    );
});

// Fetch event - serve from cache when offline
self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            // Return cached version or fetch from network
            if (response) {
                return response;
            }

            // For navigation requests, try network first, then offline page
            if (event.request.mode === "navigate") {
                return fetch(event.request).catch(() => {
                    return caches.match("/offline.html");
                });
            }

            // For other requests, try network first, then cache
            return fetch(event.request).catch(() => {
                return caches.match(event.request);
            });
        })
    );
});

// Activate event - clean up old caches
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log("Deleting old cache:", cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Background sync for offline data
self.addEventListener("sync", (event) => {
    if (event.tag === "background-sync") {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Handle offline data sync when connection is restored
    console.log("Background sync triggered");
    
    // Sync offline data to server
    return syncOfflineData();
}

async function syncOfflineData() {
    try {
        // Get offline data from IndexedDB or localStorage
        const offlineData = await getOfflineData();
        
        if (offlineData && offlineData.length > 0) {
            // Send data to server
            for (const data of offlineData) {
                await fetch('/api/sync-offline-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
            }
            
            // Clear offline data after successful sync
            await clearOfflineData();
            console.log('Offline data synced successfully');
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

async function getOfflineData() {
    // Implementation to get offline data
    return [];
}

async function clearOfflineData() {
    // Implementation to clear offline data
    return true;
}

// Push notification handling
self.addEventListener("push", (event) => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: "/image/logoapp.png",
            badge: "/image/logoapp.png",
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: data.primaryKey
            },
            actions: [
                {
                    action: "explore",
                    title: "Lihat Detail",
                    icon: "/image/logoapp.png"
                },
                {
                    action: "close",
                    title: "Tutup",
                    icon: "/image/logoapp.png"
                }
            ]
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click handling
self.addEventListener("notificationclick", (event) => {
    event.notification.close();
    
    if (event.action === "explore") {
        event.waitUntil(
            clients.openWindow("/dashboard")
        );
    }
});
