/**
 * Stock Transfer Dashboard JavaScript
 * Handles real-time updates and interactions
 */

$(document).ready(function() {
    // Auto-refresh dashboard statistics every 30 seconds
    setInterval(refreshStats, 30000);
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
});

/**
 * Refresh dashboard statistics via AJAX
 */
function refreshStats() {
    $.ajax({
        url: '/stock-transfer/stats',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            updateDashboardStats(data);
        },
        error: function(xhr, status, error) {
            console.error('Failed to refresh stats:', error);
        }
    });
}

/**
 * Update dashboard statistics display
 */
function updateDashboardStats(stats) {
    // Update statistics cards
    $('.small-box.bg-blue .inner h3').text(stats.total);
    $('.small-box.bg-yellow .inner h3').text(stats.pending);
    $('.small-box.bg-green .inner h3').text(stats.completed);
    $('.small-box.bg-red .inner h3').text(stats.rejected);
    
    // Update notification badge if exists
    const notificationBadge = $('.notifications-menu .label');
    if (notificationBadge.length > 0) {
        if (stats.pending > 0) {
            notificationBadge.text(stats.pending).show();
        } else {
            notificationBadge.hide();
        }
    }
}

/**
 * Confirm transfer action with custom message
 */
function confirmTransferAction(action, transferId, message) {
    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('stock-transfer') }}/${transferId}/${action}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Show transfer details in modal
 */
function showTransferDetails(transferId) {
    $.ajax({
        url: `/stock-transfer/${transferId}`,
        method: 'GET',
        success: function(response) {
            // Extract the content from the response
            const content = $(response).find('.box-body').html();
            
            // Show in modal
            $('#transferDetailsModal .modal-body').html(content);
            $('#transferDetailsModal').modal('show');
        },
        error: function(xhr, status, error) {
            alert('Gagal memuat detail transfer: ' + error);
        }
    });
}

/**
 * Filter transfers by status
 */
function filterTransfers(status) {
    const currentUrl = new URL(window.location);
    
    if (status === 'all') {
        currentUrl.searchParams.delete('status');
    } else {
        currentUrl.searchParams.set('status', status);
    }
    
    window.location.href = currentUrl.toString();
}

/**
 * Export transfers with filters
 */
function exportTransfers() {
    const currentUrl = new URL(window.location);
    const status = currentUrl.searchParams.get('status');
    const branch = currentUrl.searchParams.get('branch');
    
    let exportUrl = '/stock-transfer/export?';
    
    if (status) {
        exportUrl += `status=${status}&`;
    }
    
    if (branch) {
        exportUrl += `branch=${branch}&`;
    }
    
    window.location.href = exportUrl;
}

/**
 * Initialize dashboard charts if Chart.js is available
 */
function initializeCharts() {
    if (typeof Chart !== 'undefined') {
        // Transfer status chart
        const statusCtx = document.getElementById('transferStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Approved', 'Completed', 'Rejected', 'Cancelled'],
                    datasets: [{
                        data: [12, 19, 3, 5, 2],
                        backgroundColor: [
                            '#f39c12',
                            '#00c0ef',
                            '#00a65a',
                            '#dd4b39',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Monthly transfer trend chart
        const trendCtx = document.getElementById('transferTrendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Transfer Stok',
                        data: [65, 59, 80, 81, 56, 55],
                        borderColor: '#3c8dbc',
                        backgroundColor: 'rgba(60, 141, 188, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
}

// Initialize charts when document is ready
$(document).ready(function() {
    initializeCharts();
});
