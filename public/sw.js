const CACHE_NAME = "optik-melati-v1.0.0";
const urlsToCache = [
    "/",
    "/css/app.css",
    "/js/app.js",
    "/image/optik-melati.png",
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

            // If request fails and it's a navigation request, show offline page
            return fetch(event.request).catch(() => {
                if (event.request.mode === "navigate") {
                    return caches.match("/offline.html");
                }
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
    // Add your sync logic here
}
