/**
 * Real-time Helper untuk Optik Melati
 * Menggunakan Server-Sent Events (SSE) untuk real-time updates
 */

class RealtimeManager {
    constructor() {
        this.eventSources = {};
        this.reconnectAttempts = {};
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000; // 3 seconds
        this.isPageVisible = true;
        
        this.setupVisibilityChange();
    }
    
    /**
     * Setup page visibility change detection
     */
    setupVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            this.isPageVisible = !document.hidden;
            
            if (this.isPageVisible) {
                // Page became visible, reconnect if needed
                this.reconnectAll();
            } else {
                // Page became hidden, optionally close connections to save resources
                // this.closeAll();
            }
        });
    }
    
    /**
     * Connect to real-time dashboard updates
     */
    connectDashboard(callbacks = {}) {
        const url = window.APP_BASE_URL + '/realtime/dashboard';
        return this.connect('dashboard', url, {
            onData: callbacks.onData || this.defaultDashboardHandler,
            onError: callbacks.onError,
            onOpen: callbacks.onOpen
        });
    }
    
    /**
     * Connect to real-time omset kasir updates
     */
    connectOmsetKasir(callbacks = {}) {
        const url = window.APP_BASE_URL + '/realtime/omset-kasir';
        return this.connect('omset-kasir', url, {
            onData: callbacks.onData || this.defaultOmsetHandler,
            onError: callbacks.onError,
            onOpen: callbacks.onOpen
        });
    }
    
    /**
     * Connect to real-time notifications
     */
    connectNotifications(callbacks = {}) {
        const url = window.APP_BASE_URL + '/realtime/notifications';
        return this.connect('notifications', url, {
            onData: callbacks.onData || this.defaultNotificationHandler,
            onError: callbacks.onError,
            onOpen: callbacks.onOpen
        });
    }
    
    /**
     * Connect to real-time stock updates
     */
    connectStockUpdates(callbacks = {}) {
        const url = window.APP_BASE_URL + '/realtime/stock-updates';
        return this.connect('stock-updates', url, {
            onData: callbacks.onData || this.defaultStockUpdateHandler,
            onError: callbacks.onError,
            onOpen: callbacks.onOpen
        });
    }
    
    /**
     * Generic connection method
     */
    connect(name, url, callbacks) {
        // Close existing connection if any
        this.disconnect(name);
        
        const eventSource = new EventSource(url);
        this.eventSources[name] = eventSource;
        this.reconnectAttempts[name] = 0;
        
        eventSource.onopen = (event) => {
            console.log(`Real-time connection opened: ${name}`);
            this.reconnectAttempts[name] = 0;
            if (callbacks.onOpen) callbacks.onOpen(event);
        };
        
        eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                if (callbacks.onData) callbacks.onData(data);
            } catch (error) {
                console.error(`Error parsing real-time data for ${name}:`, error);
            }
        };
        
        eventSource.onerror = (event) => {
            console.error(`Real-time connection error: ${name}`, event);
            
            if (callbacks.onError) callbacks.onError(event);
            
            // Attempt to reconnect
            this.scheduleReconnect(name, url, callbacks);
        };
        
        return eventSource;
    }
    
    /**
     * Schedule reconnection
     */
    scheduleReconnect(name, url, callbacks) {
        this.reconnectAttempts[name] = (this.reconnectAttempts[name] || 0) + 1;
        
        if (this.reconnectAttempts[name] <= this.maxReconnectAttempts) {
            setTimeout(() => {
                if (this.isPageVisible) {
                    console.log(`Attempting to reconnect ${name} (attempt ${this.reconnectAttempts[name]})`);
                    this.connect(name, url, callbacks);
                }
            }, this.reconnectDelay * this.reconnectAttempts[name]);
        } else {
            console.error(`Max reconnection attempts reached for ${name}`);
        }
    }
    
    /**
     * Disconnect a specific connection
     */
    disconnect(name) {
        if (this.eventSources[name]) {
            this.eventSources[name].close();
            delete this.eventSources[name];
            delete this.reconnectAttempts[name];
        }
    }
    
    /**
     * Reconnect all active connections
     */
    reconnectAll() {
        // This would require storing the original connection parameters
        // For now, let existing error handlers handle reconnection
    }
    
    /**
     * Close all connections
     */
    closeAll() {
        Object.keys(this.eventSources).forEach(name => {
            this.disconnect(name);
        });
    }
    
    /**
     * Default dashboard data handler
     */
    defaultDashboardHandler(data) {
        console.log('Dashboard update received:', data);
        
        // Update open day status
        if (data.open_day_status !== undefined) {
            this.updateOpenDayStatus(data.open_day_status, data.open_time);
        }
        
        // Update omset for kasir
        if (data.omset_kasir !== undefined) {
            this.updateOmsetDisplay(data);
        }
        
        // Update admin data
        if (data.total_omset_hari_ini !== undefined) {
            this.updateAdminData(data);
        }
    }
    
    /**
     * Default omset data handler
     */
    defaultOmsetHandler(data) {
        console.log('Omset update received:', data);
        this.updateOmsetDisplay(data);
        this.updateTransaksiTerbaru(data.transaksi_terbaru);
    }
    
    /**
     * Default notification handler
     */
    defaultNotificationHandler(data) {
        console.log('Notification received:', data);
        this.showNotification(data);
    }
    
    /**
     * Default stock update handler
     */
    defaultStockUpdateHandler(data) {
        console.log('Stock update received:', data);
        this.handleStockUpdate(data);
    }
    
    /**
     * Update open day status display
     */
    updateOpenDayStatus(isOpen, openTime) {
        const statusElement = document.getElementById('kasir-status-info');
        if (statusElement) {
            if (isOpen) {
                statusElement.innerHTML = `<div class="alert alert-success text-center" style="font-size:16px; margin-bottom:10px;"><b>KASIR BUKA</b> &mdash; Kasir cabang sudah dibuka (${openTime || '-'})</div>`;
            } else {
                statusElement.innerHTML = `<div class="alert alert-danger text-center" style="font-size:16px; margin-bottom:10px;"><b>KASIR TUTUP</b> &mdash; Kasir cabang belum dibuka atau sudah ditutup</div>`;
            }
        }
    }
    
    /**
     * Update omset display
     */
    updateOmsetDisplay(data) {
        // Update omset total
        this.updateElementText('.omset-total h3', 'Rp ' + this.formatNumber(data.omset_kasir || 0));
        
        // Update omset BPJS
        this.updateElementText('.omset-bpjs h3', 'Rp ' + this.formatNumber(data.omset_bpjs || 0));
        
        // Update omset umum
        this.updateElementText('.omset-umum h3', 'Rp ' + this.formatNumber(data.omset_umum || 0));
        
        // Update jumlah transaksi
        this.updateElementText('.jumlah-transaksi-badge', data.jumlah_transaksi || 0);
    }
    
    /**
     * Update admin data display
     */
    updateAdminData(data) {
        this.updateElementText('.total-omset-admin h3', 'Rp ' + this.formatNumber(data.total_omset_hari_ini || 0));
        this.updateElementText('.total-transaksi-admin h3', data.total_transaksi_hari_ini || 0);
        
        // Update rekap omset kasir if element exists
        if (data.rekap_omset_kasir && document.getElementById('rekap-omset-table')) {
            this.updateRekapOmsetTable(data.rekap_omset_kasir);
        }
    }
    
    /**
     * Update transaksi terbaru table
     */
    updateTransaksiTerbaru(transaksiList) {
        const tableBody = document.querySelector('#transaksi-terbaru-table tbody');
        if (!tableBody || !transaksiList) return;
        
        tableBody.innerHTML = '';
        
        transaksiList.forEach((transaksi, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${transaksi.waktu}</td>
                <td>${transaksi.no_transaksi}</td>
                <td>${transaksi.nama_pasien}</td>
                <td><span class="label label-info">${transaksi.service_type}</span></td>
                <td>Rp ${this.formatNumber(transaksi.total)}</td>
                <td><span class="label ${transaksi.status === 'Sudah Diambil' ? 'label-success' : 'label-warning'}">${transaksi.status}</span></td>
            `;
            tableBody.appendChild(row);
        });
    }
    
    /**
     * Show notification
     */
    showNotification(notification) {
        // Use SweetAlert2 if available
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: notification.type === 'new_transaction' ? 'success' : 'info',
                title: notification.title,
                text: notification.message
            });
        } else {
            // Fallback to browser notification if permission granted
            if (Notification.permission === 'granted') {
                new Notification(notification.title, {
                    body: notification.message,
                    icon: '/favicon.ico'
                });
            }
        }
    }
    
    /**
     * Helper: Update element text by selector
     */
    updateElementText(selector, text) {
        const element = document.querySelector(selector);
        if (element) {
            element.textContent = text;
        }
    }
    
    /**
     * Helper: Format number with thousands separator
     */
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    
    /**
     * Handle stock update
     */
    handleStockUpdate(data) {
        // Update stock displays in any open modals or tables
        this.updateStockInTables(data);
        
        // Show low stock alerts
        if (data.low_stock_alerts > 0) {
            this.showLowStockAlert(data);
        }
        
        // Update stock counters if they exist
        this.updateStockCounters(data);
        
        // Show toast notification for stock changes
        this.showStockNotification(data);
    }
    
    /**
     * Update stock in visible tables
     */
    updateStockInTables(data) {
        if (!data.updates) return;
        
        data.updates.forEach(update => {
            // Update stock in frame table
            if (update.type === 'frame_stock_update') {
                const selector = `tr[data-frame-id="${update.product_id}"] .stock-cell`;
                this.updateElementText(selector, update.new_stock);
            }
            
            // Update stock in lensa table
            if (update.type === 'lensa_stock_update') {
                const selector = `tr[data-lensa-id="${update.product_id}"] .stock-cell`;
                this.updateElementText(selector, update.new_stock);
            }
            
            // Update stock in aksesoris table
            if (update.type === 'aksesoris_stock_update') {
                const selector = `tr[data-aksesoris-id="${update.product_id}"] .stock-cell`;
                this.updateElementText(selector, update.new_stock);
            }
            
            // Add visual feedback
            const row = document.querySelector(`tr[data-${update.product_type.toLowerCase()}-id="${update.product_id}"]`);
            if (row) {
                row.classList.add('stock-updated');
                setTimeout(() => {
                    row.classList.remove('stock-updated');
                }, 2000);
            }
        });
    }
    
    /**
     * Show low stock alert
     */
    showLowStockAlert(data) {
        const lowStockItems = data.updates.filter(u => u.alert_level === 'low');
        
        if (lowStockItems.length > 0 && typeof Swal !== 'undefined') {
            let message = `${lowStockItems.length} produk memiliki stok rendah (≤ 5):\n\n`;
            lowStockItems.forEach(item => {
                message += `• ${item.product_name}: ${item.new_stock} stok\n`;
            });
            
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan Stok Rendah!',
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                timer: 10000
            });
        }
    }
    
    /**
     * Update stock counters
     */
    updateStockCounters(data) {
        // Update total stock counters if they exist on dashboard
        if (data.summary) {
            this.updateElementText('.total-frame-updates', data.summary.frames_updated);
            this.updateElementText('.total-lensa-updates', data.summary.lensas_updated);
            this.updateElementText('.total-aksesoris-updates', data.summary.aksesoris_updated);
        }
    }
    
    /**
     * Show stock notification
     */
    showStockNotification(data) {
        if (data.total_updates > 0 && typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            let message = `${data.total_updates} produk diupdate`;
            if (data.low_stock_alerts > 0) {
                message += ` (${data.low_stock_alerts} stok rendah!)`;
            }
            
            Toast.fire({
                icon: data.low_stock_alerts > 0 ? 'warning' : 'info',
                title: 'Stock Update',
                text: message
            });
        }
    }
    
    /**
     * Request notification permission
     */
    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

// Global instance
window.RealtimeManager = new RealtimeManager();

// Auto-start real-time features when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Request notification permission
    window.RealtimeManager.requestNotificationPermission();
    
    // Start real-time dashboard if on dashboard page
    if (window.location.pathname === '/dashboard' || window.location.pathname === '/') {
        window.RealtimeManager.connectDashboard();
        window.RealtimeManager.connectNotifications();
        
        // If user is kasir, also connect omset updates
        if (typeof KASIR_BRANCH_ID !== 'undefined') {
            window.RealtimeManager.connectOmsetKasir();
        }
        
        // Connect to stock updates for all authenticated users
        window.RealtimeManager.connectStockUpdates();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    window.RealtimeManager.closeAll();
});