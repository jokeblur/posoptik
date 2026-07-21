// Mobile QR Code Scanner Optimization for POS Optik Melati
// Enhanced version with better mobile support and camera handling

class MobileQRScanner {
    constructor() {
        this.scanner = null;
        this.isScanning = false;
        this.availableCameras = [];
        this.currentCameraIndex = 0;
        this.retryCount = 0;
        this.maxRetries = 3;
        
        this.init();
    }
    
    init() {
        console.log('Mobile QR Scanner initializing...');
        this.checkPermissions();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Event listener utama di-bind dari halaman view.
    }

    hasCameraSupport() {
        return !!(
            (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ||
            navigator.getUserMedia ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia ||
            navigator.msGetUserMedia
        );
    }

    isSecureCameraContext() {
        return !!(
            window.isSecureContext ||
            location.hostname === 'localhost' ||
            location.hostname === '127.0.0.1' ||
            location.protocol === 'https:' ||
            location.hostname.endsWith('.local')
        );
    }

    async requestCameraStream(constraints) {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            return navigator.mediaDevices.getUserMedia(constraints);
        }

        const legacyGetUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;

        if (!legacyGetUserMedia) {
            throw new Error('GET_USER_MEDIA_UNSUPPORTED');
        }

        return new Promise((resolve, reject) => {
            legacyGetUserMedia.call(navigator, constraints, resolve, reject);
        });
    }
    
    async checkPermissions() {
        try {
            if (!this.hasCameraSupport()) {
                this.showError('Browser atau aplikasi yang digunakan tidak mendukung akses kamera. Coba gunakan Chrome, Edge, atau Safari terbaru.');
                return false;
            }

            if (!this.isSecureCameraContext()) {
                this.showError('Akses kamera membutuhkan HTTPS. Buka aplikasi dari domain HTTPS atau localhost.');
                return false;
            }
            
            // Request camera permission
            const stream = await this.requestCameraStream({ 
                video: { 
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            });
            
            // Stop the stream immediately (we just needed permission)
            stream.getTracks().forEach(track => track.stop());
            
            console.log('Camera permission granted');
            this.updateStatus('Permission kamera diberikan');
            return true;
            
        } catch (error) {
            console.error('Camera permission error:', error);
            this.handlePermissionError(error);
            return false;
        }
    }
    
    handlePermissionError(error) {
        let message = 'Akses kamera ditolak';
        
        if (error.name === 'NotAllowedError') {
            message = 'Akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
        } else if (error.name === 'NotFoundError') {
            message = 'Kamera tidak ditemukan. Pastikan kamera terhubung.';
        } else if (error.name === 'NotSupportedError') {
            message = 'Browser tidak mendukung akses kamera.';
        } else if (error.name === 'NotReadableError') {
            message = 'Kamera sedang digunakan oleh aplikasi lain.';
        } else if (error.name === 'SecurityError') {
            message = 'Akses kamera diblokir karena halaman tidak berjalan di HTTPS atau dibatasi browser.';
        } else if (error.message === 'GET_USER_MEDIA_UNSUPPORTED') {
            message = 'Browser atau aplikasi ini belum mendukung API kamera.';
        }
        
        this.showError(message);
    }
    
    async getAvailableCameras() {
        try {
            if (!(navigator.mediaDevices && navigator.mediaDevices.enumerateDevices)) {
                this.updateStatus('Browser tidak mendukung daftar kamera, memakai kamera default');
                this.availableCameras = [];
                return [];
            }

            const devices = await navigator.mediaDevices.enumerateDevices();
            const cameras = devices.filter(device => device.kind === 'videoinput');
            
            console.log('Available cameras:', cameras);
            this.availableCameras = cameras;
            
            // Prioritize back camera
            const backCameraIndex = cameras.findIndex(camera => 
                camera.label && (
                    camera.label.toLowerCase().includes('back') ||
                    camera.label.toLowerCase().includes('rear') ||
                    camera.label.toLowerCase().includes('environment') ||
                    camera.label.toLowerCase().includes('0')
                )
            );
            
            if (backCameraIndex !== -1) {
                this.currentCameraIndex = backCameraIndex;
                console.log('Back camera found at index:', backCameraIndex);
            } else {
                // Use last camera (usually back camera on mobile)
                this.currentCameraIndex = cameras.length - 1;
                console.log('Using last camera as fallback:', this.currentCameraIndex);
            }
            
            this.updateStatus(`Ditemukan ${cameras.length} kamera`);
            return cameras;
            
        } catch (error) {
            console.error('Error getting cameras:', error);
            this.updateStatus('Error: Tidak dapat mengakses kamera');
            return [];
        }
    }
    
    async startScanning() {
        if (this.isScanning) {
            console.log('Scanner already running');
            return;
        }
        
        try {
            this.updateStatus('Memulai scanner...');
            
            // Get cameras first
            const cameras = await this.getAvailableCameras();
            
            if (cameras.length === 0) {
                await this.tryStartWithoutCameraId();
                return;
            }
            
            // Try multiple camera configurations
            await this.tryStartWithCamera(cameras);
            
        } catch (error) {
            console.error('Error starting scanner:', error);
            this.handleScanError(error);
        }
    }

    async tryStartWithoutCameraId() {
        const configs = [
            // Config 1: Prefer back camera, high quality
            {
                fps: 10,
                qrbox: {
                    width: Math.min(250, window.innerWidth * 0.6),
                    height: Math.min(250, window.innerWidth * 0.6)
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 }
                }
            },
            // Config 2: Back camera, medium quality
            {
                fps: 10,
                qrbox: {
                    width: Math.min(200, window.innerWidth * 0.5),
                    height: Math.min(200, window.innerWidth * 0.5)
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    facingMode: 'environment',
                    width: { ideal: 640, max: 1280 },
                    height: { ideal: 480, max: 720 }
                }
            },
            // Config 3: Any camera, fallback
            {
                fps: 10,
                qrbox: {
                    width: Math.min(200, window.innerWidth * 0.5),
                    height: Math.min(200, window.innerWidth * 0.5)
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            }
        ];

        for (let i = 0; i < configs.length; i++) {
            try {
                this.updateStatus(`Mencoba kamera default ${i + 1}...`);
                this.scanner = new Html5Qrcode('reader');
                await this.scanner.start(
                    { facingMode: 'environment' },
                    configs[i],
                    this.onScanSuccess.bind(this),
                    this.onScanFailure.bind(this)
                );

                this.isScanning = true;
                this.updateUI();
                this.updateStatus('Scanner aktif - Arahkan kamera ke QR Code');
                return;
            } catch (error) {
                console.error(`Default camera config ${i + 1} failed:`, error);
                if (this.scanner) {
                    try {
                        await this.scanner.stop();
                    } catch (e) {
                        console.log('Error stopping failed default scanner:', e);
                    }
                    this.scanner = null;
                }

                if (i === configs.length - 1) {
                    throw error;
                }
            }
        }
    }
    
    async tryStartWithCamera(cameras) {
        const cameraConfigs = [
            // Config 1: Specific camera with high quality
            {
                fps: 10,
                qrbox: { 
                    width: Math.min(250, window.innerWidth * 0.6), 
                    height: Math.min(250, window.innerWidth * 0.6) 
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    deviceId: { exact: cameras[this.currentCameraIndex].id },
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 }
                }
            },
            // Config 2: Specific camera with medium quality
            {
                fps: 10,
                qrbox: { 
                    width: Math.min(200, window.innerWidth * 0.5), 
                    height: Math.min(200, window.innerWidth * 0.5) 
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    deviceId: { exact: cameras[this.currentCameraIndex].id },
                    facingMode: { ideal: "environment" },
                    width: { ideal: 640, max: 1280 },
                    height: { ideal: 480, max: 720 }
                }
            },
            // Config 3: Specific camera with low quality
            {
                fps: 10,
                qrbox: { 
                    width: Math.min(150, window.innerWidth * 0.4), 
                    height: Math.min(150, window.innerWidth * 0.4) 
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    deviceId: { exact: cameras[this.currentCameraIndex].id },
                    facingMode: { ideal: "environment" },
                    width: { ideal: 320, max: 640 },
                    height: { ideal: 240, max: 480 }
                }
            },
            // Config 4: Fallback to facingMode only
            {
                fps: 10,
                qrbox: { 
                    width: Math.min(200, window.innerWidth * 0.5), 
                    height: Math.min(200, window.innerWidth * 0.5) 
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    facingMode: { ideal: "environment" }
                }
            }
        ];
        
        for (let i = 0; i < cameraConfigs.length; i++) {
            try {
                console.log(`Trying camera config ${i + 1}...`);
                this.updateStatus(`Mencoba konfigurasi kamera ${i + 1}...`);
                
                // Create scanner instance
                this.scanner = new Html5Qrcode("reader");
                
                // Start scanning with current config
                await this.scanner.start(
                    cameras[this.currentCameraIndex].id,
                    cameraConfigs[i],
                    this.onScanSuccess.bind(this),
                    this.onScanFailure.bind(this)
                );
                
                this.isScanning = true;
                this.updateUI();
                this.updateStatus('Scanner aktif - Arahkan kamera ke QR Code');
                
                console.log(`Scanner started successfully with config ${i + 1}`);
                return;
                
            } catch (error) {
                console.error(`Config ${i + 1} failed:`, error);
                
                // Clean up failed scanner
                if (this.scanner) {
                    try {
                        await this.scanner.stop();
                    } catch (e) {
                        console.log('Error stopping failed scanner:', e);
                    }
                    this.scanner = null;
                }
                
                // If this is the last config, throw the error
                if (i === cameraConfigs.length - 1) {
                    throw error;
                }
            }
        }
    }
    
    async stopScanning() {
        if (!this.isScanning || !this.scanner) {
            return;
        }
        
        try {
            await this.scanner.stop();
            this.scanner.clear();
            this.scanner = null;
            this.isScanning = false;
            this.updateUI();
            this.updateStatus('Scanner dihentikan');
            
            console.log('Scanner stopped successfully');
            
        } catch (error) {
            console.error('Error stopping scanner:', error);
            this.cleanup();
        }
    }
    
    async switchCamera() {
        if (!this.isScanning || this.availableCameras.length <= 1) {
            return;
        }
        
        try {
            await this.stopScanning();
            
            // Switch to next camera
            this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;
            
            // Small delay before starting with new camera
            setTimeout(() => {
                this.startScanning();
            }, 500);
            
        } catch (error) {
            console.error('Error switching camera:', error);
            this.showError('Gagal mengganti kamera');
        }
    }
    
    onScanSuccess(decodedText, decodedResult) {
        console.log('QR Code detected:', decodedText);
        
        // Process the scanned result
        this.processScannedCode(decodedText);
        
        // Show success feedback
        this.showSuccess('QR Code berhasil dibaca!');
    }
    
    onScanFailure(error) {
        // Don't log every scan failure (too noisy)
        // console.log('Scan failed:', error);
    }
    
    processScannedCode(code) {
        // This will be handled by the parent page
        if (window.onQRCodeScanned) {
            window.onQRCodeScanned(code);
        } else {
            // Fallback: show the scanned code
            this.showScannedCode(code);
        }
    }
    
    showScannedCode(code) {
        Swal.fire({
            title: 'QR Code Terbaca',
            text: `Kode: ${code}`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Scan Lagi',
            cancelButtonText: 'Tutup'
        }).then((result) => {
            if (result.isConfirmed) {
                this.startScanning();
            }
        });
    }
    
    handleScanError(error) {
        console.error('Scan error:', error);
        
        if (this.retryCount < this.maxRetries) {
            this.retryCount++;
            this.updateStatus(`Error, mencoba lagi (${this.retryCount}/${this.maxRetries})...`);
            
            setTimeout(() => {
                this.startScanning();
            }, 2000);
        } else {
            this.showError('Gagal memulai scanner. Silakan refresh halaman.');
        }
    }
    
    updateUI() {
        const startBtn = document.getElementById('startScan');
        const stopBtn = document.getElementById('stopScan');
        const switchBtn = document.getElementById('switchCamera');
        const cameraInfo = document.getElementById('cameraInfo');
        
        if (this.isScanning) {
            if (startBtn) startBtn.style.display = 'none';
            if (stopBtn) stopBtn.style.display = 'inline-block';
            if (switchBtn && this.availableCameras.length > 1) switchBtn.style.display = 'inline-block';
            if (cameraInfo) cameraInfo.style.display = 'block';
        } else {
            if (startBtn) startBtn.style.display = 'inline-block';
            if (stopBtn) stopBtn.style.display = 'none';
            if (switchBtn) switchBtn.style.display = 'none';
            if (cameraInfo) cameraInfo.style.display = 'none';
        }
        
        this.updateCameraInfo();
    }
    
    updateCameraInfo() {
        const cameraLabel = document.getElementById('cameraLabel');
        if (cameraLabel && this.availableCameras.length > 0) {
            const camera = this.availableCameras[this.currentCameraIndex];
            let cameraName = camera.label || `Kamera ${this.currentCameraIndex + 1}`;
            
            // Detect camera type
            if (cameraName.toLowerCase().includes('back') || 
                cameraName.toLowerCase().includes('rear') || 
                cameraName.toLowerCase().includes('environment')) {
                cameraName += ' (Belakang)';
            } else if (cameraName.toLowerCase().includes('front') || 
                       cameraName.toLowerCase().includes('user')) {
                cameraName += ' (Depan)';
            }
            
            cameraLabel.textContent = `Kamera: ${cameraName}`;
        }
    }
    
    updateStatus(message) {
        const statusElement = document.getElementById('debugStatus');
        if (statusElement) {
            statusElement.textContent = message;
        }
        console.log('Status:', message);
    }
    
    showError(message) {
        console.error('Error:', message);
        this.updateStatus(`Error: ${message}`);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`Error: ${message}`);
        }
    }
    
    showSuccess(message) {
        console.log('Success:', message);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Berhasil',
                text: message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
    
    cleanup() {
        this.isScanning = false;
        this.scanner = null;
        this.updateUI();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the scan page
    if (document.getElementById('reader')) {
        console.log('Initializing Mobile QR Scanner...');
        
        // Wait for Html5Qrcode library to load with longer timeout
        let attempts = 0;
        const maxAttempts = 100; // 10 seconds max
        
        const checkLibrary = setInterval(() => {
            attempts++;
            console.log(`Checking for Html5Qrcode library... attempt ${attempts}`);
            
            if (typeof Html5Qrcode !== 'undefined') {
                clearInterval(checkLibrary);
                console.log('Html5Qrcode library loaded successfully');
                
                // Initialize scanner
                window.mobileQRScanner = new MobileQRScanner();
                console.log('Mobile QR Scanner initialized');
                
                // Setup button event listeners - only if not already set by jQuery
                const startBtn = document.getElementById('startScan');
                const stopBtn = document.getElementById('stopScan');
                const switchBtn = document.getElementById('switchCamera');
                
                if (startBtn && !startBtn.hasListener) {
                    startBtn.addEventListener('click', () => {
                        console.log('Start scan clicked');
                        window.mobileQRScanner.startScanning();
                    });
                    startBtn.hasListener = true;
                    console.log('Start button listener attached');
                }
                
                if (stopBtn && !stopBtn.hasListener) {
                    stopBtn.addEventListener('click', () => {
                        console.log('Stop scan clicked');
                        window.mobileQRScanner.stopScanning();
                    });
                    stopBtn.hasListener = true;
                    console.log('Stop button listener attached');
                }
                
                if (switchBtn && !switchBtn.hasListener) {
                    switchBtn.addEventListener('click', () => {
                        console.log('Switch camera clicked');
                        window.mobileQRScanner.switchCamera();
                    });
                    switchBtn.hasListener = true;
                    console.log('Switch button listener attached');
                }
                
            } else if (attempts >= maxAttempts) {
                clearInterval(checkLibrary);
                console.error('Html5Qrcode library failed to load after 10 seconds');
                const debugStatus = document.getElementById('debugStatus');
                if (debugStatus) {
                    debugStatus.textContent = 'Error: Library tidak berhasil dimuat';
                }
            }
        }, 100);
    }
});
