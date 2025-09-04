// PWA Registration and Installation
class PWAInstaller {
    constructor() {
        this.deferredPrompt = null;
        this.installButton = null;
        this.init();
    }

    init() {
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.createInstallButton();
        this.setupOfflineIndicator();
        this.createSplashScreen();
        this.setupPushNotifications();
    }

    // Register Service Worker
    async registerServiceWorker() {
        if ("serviceWorker" in navigator) {
            try {
                const registration = await navigator.serviceWorker.register(
                    "/sw.js"
                );
                console.log(
                    "Service Worker registered successfully:",
                    registration
                );

                // Check for updates
                registration.addEventListener("updatefound", () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener("statechange", () => {
                        if (
                            newWorker.state === "installed" &&
                            navigator.serviceWorker.controller
                        ) {
                            this.showUpdateNotification();
                        }
                    });
                });
            } catch (error) {
                console.error("Service Worker registration failed:", error);
            }
        }
    }

    // Setup Install Prompt
    setupInstallPrompt() {
        window.addEventListener("beforeinstallprompt", (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        window.addEventListener("appinstalled", () => {
            this.hideInstallButton();
            this.deferredPrompt = null;
            this.showSuccessMessage("Aplikasi berhasil diinstal!");
        });
    }

    // Create Install Button
    createInstallButton() {
        this.installButton = document.createElement("button");
        this.installButton.innerHTML = "📱 Install App";
        this.installButton.className = "pwa-install-btn";
        this.installButton.style.cssText = `
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 9999;
      background: linear-gradient(135deg, #e74c3c, #f39c12);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 25px;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
      transition: all 0.3s ease;
      display: none;
    `;

        this.installButton.addEventListener("click", () => {
            this.installApp();
        });

        document.body.appendChild(this.installButton);
    }

    // Show Install Button
    showInstallButton() {
        if (this.installButton) {
            this.installButton.style.display = "block";
            this.installButton.addEventListener("mouseenter", () => {
                this.installButton.style.transform = "translateY(-2px)";
                this.installButton.style.boxShadow =
                    "0 6px 20px rgba(231, 76, 60, 0.4)";
            });
            this.installButton.addEventListener("mouseleave", () => {
                this.installButton.style.transform = "translateY(0)";
                this.installButton.style.boxShadow =
                    "0 4px 15px rgba(231, 76, 60, 0.3)";
            });
        }
    }

    // Hide Install Button
    hideInstallButton() {
        if (this.installButton) {
            this.installButton.style.display = "none";
        }
    }

    // Install App
    async installApp() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;

            if (outcome === "accepted") {
                console.log("User accepted the install prompt");
            } else {
                console.log("User dismissed the install prompt");
            }

            this.deferredPrompt = null;
            this.hideInstallButton();
        }
    }

    // Show Update Notification
    showUpdateNotification() {
        const notification = document.createElement("div");
        notification.innerHTML = `
      <div style="
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        z-index: 10000;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
      ">
        <strong>🔄 Update Tersedia!</strong><br>
        <small>Klik untuk memperbarui aplikasi</small>
        <button onclick="location.reload()" style="
          background: white;
          color: #28a745;
          border: none;
          padding: 5px 10px;
          border-radius: 4px;
          margin-left: 10px;
          cursor: pointer;
        ">Update</button>
      </div>
    `;

        document.body.appendChild(notification);

        // Auto hide after 10 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 10000);
    }

    // Setup Offline Indicator
    setupOfflineIndicator() {
        const indicator = document.createElement("div");
        indicator.id = "offline-indicator";
        indicator.innerHTML = "📶 Offline Mode";
        indicator.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: #dc3545;
      color: white;
      padding: 8px 15px;
      border-radius: 20px;
      font-family: 'Poppins', sans-serif;
      font-size: 12px;
      font-weight: 600;
      z-index: 9998;
      display: none;
    `;

        document.body.appendChild(indicator);

        // Check online/offline status
        window.addEventListener("online", () => {
            indicator.style.display = "none";
            this.showSuccessMessage("Koneksi internet tersambung kembali!");
        });

        window.addEventListener("offline", () => {
            indicator.style.display = "block";
            this.showInfoMessage(
                "Mode offline aktif - data akan disimpan lokal"
            );
        });
    }

    // Show Success Message
    showSuccessMessage(message) {
        this.showMessage(message, "#28a745");
    }

    // Show Info Message
    showInfoMessage(message) {
        this.showMessage(message, "#17a2b8");
    }

    // Show Message
    showMessage(message, color) {
        const msgDiv = document.createElement("div");
        msgDiv.innerHTML = message;
        msgDiv.style.cssText = `
      position: fixed;
      top: 80px;
      right: 20px;
      background: ${color};
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      z-index: 10000;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      animation: slideIn 0.3s ease;
    `;

        // Add animation CSS
        if (!document.getElementById("pwa-animations")) {
            const style = document.createElement("style");
            style.id = "pwa-animations";
            style.textContent = `
        @keyframes slideIn {
          from { transform: translateX(100%); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
        }
      `;
            document.head.appendChild(style);
        }

        document.body.appendChild(msgDiv);

        // Auto hide after 3 seconds
        setTimeout(() => {
            if (msgDiv.parentNode) {
                msgDiv.style.animation = "slideOut 0.3s ease";
                setTimeout(() => {
                    if (msgDiv.parentNode) {
                        msgDiv.parentNode.removeChild(msgDiv);
                    }
                }, 300);
            }
        }, 3000);
    }

    // Create Splash Screen
    createSplashScreen() {
        // Only show splash screen on first visit or after app install
        if (this.shouldShowSplash()) {
            const splash = document.createElement("div");
            splash.className = "pwa-splash";
            splash.innerHTML = `
                <img src="/image/logoapp.png" alt="Optik Melati" class="logo" 
                     onerror="this.src='/image/logologin.png'; this.onerror=function(){this.style.display='none'; document.getElementById('splash-fallback').style.display='block';}">
                <div id="splash-fallback" class="logo-fallback" style="display: none;">
                    <div class="fallback-icon">👓</div>
                </div>
                <div class="app-name">Optik Melati</div>
                <div class="app-description">Aplikasi Manajemen Optik</div>
                <div class="loading">
                    <div class="loading-bar">
                        <div class="loading-progress"></div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(splash);
            
            // Hide splash screen after 3 seconds
            setTimeout(() => {
                splash.classList.add("hidden");
                setTimeout(() => {
                    if (splash.parentNode) {
                        splash.parentNode.removeChild(splash);
                    }
                }, 500);
            }, 3000);
            
            // Mark splash as shown
            localStorage.setItem("pwa-splash-shown", "true");
        }
    }

    // Check if splash screen should be shown
    shouldShowSplash() {
        // Show splash if:
        // 1. First time visiting
        // 2. App was just installed
        // 3. User cleared localStorage
        return !localStorage.getItem("pwa-splash-shown") || 
               this.isAppInstalled() ||
               this.isFirstVisit();
    }

    // Check if app is installed
    isAppInstalled() {
        return window.matchMedia("(display-mode: standalone)").matches ||
               window.navigator.standalone === true;
    }

    // Check if this is first visit
    isFirstVisit() {
        return !localStorage.getItem("pwa-first-visit");
    }

    // Setup Push Notifications
    async setupPushNotifications() {
        if ("Notification" in window && "serviceWorker" in navigator) {
            // Request permission for notifications
            if (Notification.permission === "default") {
                try {
                    const permission = await Notification.requestPermission();
                    if (permission === "granted") {
                        this.showSuccessMessage("Notifikasi diaktifkan!");
                        this.subscribeToPush();
                    }
                } catch (error) {
                    console.error("Error requesting notification permission:", error);
                }
            }
        }
    }

    // Subscribe to push notifications
    async subscribeToPush() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(
                    "BEl62iUYgUivxIkv69yViEuiBIa40HI0QYlQ5U8X3hJ4vKBFxS5FHVPF8jf8WUvX4YyB0wzsN1yZp6F2G5u4BQ"
                )
            });
            
            // Send subscription to server
            await fetch("/api/push-subscribe", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(subscription)
            });
            
            console.log("Push subscription successful");
        } catch (error) {
            console.error("Push subscription failed:", error);
        }
    }

    // Convert VAPID key
    urlBase64ToUint8Array(base64String) {
        const padding = "=".repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, "+")
            .replace(/_/g, "/");
        
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}

// Initialize PWA when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    new PWAInstaller();
});

// Add to home screen functionality
if ("standalone" in window.navigator && window.navigator.standalone) {
    document.documentElement.classList.add("standalone");
}
