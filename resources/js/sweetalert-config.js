// SweetAlert Configuration for Optik Melati
// Notifikasi sukses/gagal, delete confirmation, dan logout

// Global SweetAlert configuration
const swalConfig = {
    background: '#ffffff',
    backdrop: 'rgba(0, 0, 0, 0.4)',
    confirmButtonColor: '#a4193d',
    cancelButtonColor: '#6c757d',
    customClass: {
        popup: 'swal-custom-popup',
        title: 'swal-custom-title',
        content: 'swal-custom-content',
        confirmButton: 'swal-custom-confirm',
        cancelButton: 'swal-custom-cancel'
    }
};

// Notifikasi dari session flash
document.addEventListener("DOMContentLoaded", function () {
    // Flash messages
    const successMessage = document.querySelector("[data-success-message]")
        ?.dataset.successMessage;
    const errorMessage = document.querySelector("[data-error-message]")?.dataset
        .errorMessage;
    const warningMessage = document.querySelector("[data-warning-message]")
        ?.dataset.warningMessage;
    const infoMessage = document.querySelector("[data-info-message]")?.dataset
        .infoMessage;

    if (successMessage) {
        Swal.fire({
            ...swalConfig,
            icon: "success",
            title: "Berhasil!",
            text: successMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#ffffff',
            color: '#155724',
            iconColor: '#28a745'
        });
    }

    if (errorMessage) {
        Swal.fire({
            ...swalConfig,
            icon: "error",
            title: "Gagal!",
            text: errorMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#ffffff',
            color: '#721c24',
            iconColor: '#dc3545'
        });
    }

    if (warningMessage) {
        Swal.fire({
            ...swalConfig,
            icon: "warning",
            title: "Peringatan!",
            text: warningMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#ffffff',
            color: '#856404',
            iconColor: '#ffc107'
        });
    }

    if (infoMessage) {
        Swal.fire({
            ...swalConfig,
            icon: "info",
            title: "Informasi",
            text: infoMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#ffffff',
            color: '#0c5460',
            iconColor: '#17a2b8'
        });
    }
});

// Konfirmasi delete
function confirmDelete(formId, itemName = "data ini") {
    Swal.fire({
        ...swalConfig,
        title: "Apakah Anda yakin?",
        text: `Data ${itemName} akan dihapus secara permanen!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, hapus!",
        cancelButtonText: "Batal",
        background: '#ffffff',
        color: '#212529'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

// Konfirmasi logout
function confirmLogout() {
    Swal.fire({
        ...swalConfig,
        title: "Keluar dari aplikasi?",
        text: "Anda akan keluar dari sesi saat ini.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, keluar!",
        cancelButtonText: "Batal",
        background: '#ffffff',
        color: '#212529'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("logout-form").submit();
        }
    });
}

// Notifikasi AJAX success
function showSuccess(message) {
    Swal.fire({
        ...swalConfig,
        icon: "success",
        title: "Berhasil!",
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        background: '#ffffff',
        color: '#155724',
        iconColor: '#28a745'
    });
}

// Notifikasi AJAX error
function showError(message) {
    Swal.fire({
        ...swalConfig,
        icon: "error",
        title: "Gagal!",
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        background: '#ffffff',
        color: '#721c24',
        iconColor: '#dc3545'
    });
}

// Notifikasi AJAX warning
function showWarning(message) {
    Swal.fire({
        ...swalConfig,
        icon: "warning",
        title: "Peringatan!",
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        background: '#ffffff',
        color: '#856404',
        iconColor: '#ffc107'
    });
}

// Auto-hide alert setelah 3 detik
function autoHideAlert() {
    setTimeout(() => {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach((alert) => {
            alert.style.transition = "opacity 0.5s";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        });
    }, 3000);
}

// Jalankan auto-hide saat halaman dimuat
document.addEventListener("DOMContentLoaded", autoHideAlert);
