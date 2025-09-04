{{-- Mobile Responsive Table Wrapper --}}
<div class="table-responsive table-responsive-mobile">

{{-- Mobile Table Instructions --}}
<div class="mobile-table-instructions" style="display: none;">
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        <strong>Tips:</strong> Geser tabel ke kiri dan kanan untuk melihat semua kolom
    </div>
</div>

<script>
$(document).ready(function() {
    // Show instructions on mobile
    if ($(window).width() <= 768) {
        $('.mobile-table-instructions').show();
    }
    
    // Hide instructions after 5 seconds
    setTimeout(function() {
        $('.mobile-table-instructions').fadeOut();
    }, 5000);
});
</script>
</div>
