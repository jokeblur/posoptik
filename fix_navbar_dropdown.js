// Fix untuk navbar dropdown yang tidak bisa diklik di VPS
// Pastikan JavaScript ini di-load setelah jQuery dan Bootstrap

$(document).ready(function() {
    console.log('Navbar fix script loaded');
    
    // Fix dropdown toggle jika Bootstrap tidak ter-load
    $('.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $dropdown = $(this).next('.dropdown-menu');
        var $parent = $(this).parent();
        
        // Close other dropdowns
        $('.dropdown-menu').not($dropdown).removeClass('show');
        $('.dropdown').not($parent).removeClass('open');
        
        // Toggle current dropdown
        $dropdown.toggleClass('show');
        $parent.toggleClass('open');
        
        console.log('Dropdown toggled:', $dropdown.hasClass('show'));
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
            $('.dropdown').removeClass('open');
        }
    });
    
    // Fix logout function jika SweetAlert tidak ter-load
    window.confirmLogout = function() {
        if (typeof Swal !== 'undefined') {
            // SweetAlert tersedia, gunakan konfirmasi
            Swal.fire({
                title: "Keluar dari aplikasi?",
                text: "Anda akan keluar dari sesi saat ini.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, keluar!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("logout-form").submit();
                }
            });
        } else {
            // SweetAlert tidak tersedia, gunakan confirm native
            if (confirm("Apakah Anda yakin ingin keluar dari aplikasi?")) {
                document.getElementById("logout-form").submit();
            }
        }
    };
    
    // Debug: Cek apakah elemen navbar ada
    if ($('.user.user-menu').length > 0) {
        console.log('User menu found');
    } else {
        console.log('User menu NOT found');
    }
    
    // Debug: Cek apakah dropdown toggle ada
    if ($('.dropdown-toggle').length > 0) {
        console.log('Dropdown toggle found');
    } else {
        console.log('Dropdown toggle NOT found');
    }
});
