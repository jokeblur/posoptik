// SweetAlert Configuration for Optik Melati
// Notifikasi sukses/gagal, delete confirmation, dan logout

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
            icon: "success",
            title: "Berhasil!",
            text: successMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: "error",
            title: "Gagal!",
            text: errorMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }

    if (warningMessage) {
        Swal.fire({
            icon: "warning",
            title: "Peringatan!",
            text: warningMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }

    if (infoMessage) {
        Swal.fire({
            icon: "info",
            title: "Informasi",
            text: infoMessage,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }
});

// Konfirmasi delete
function confirmDelete(formId, itemName = "data ini") {
    Swal.fire({
        title: "Apakah Anda yakin?",
        text: `Data ${itemName} akan dihapus secara permanen!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Ya, hapus!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

// Konfirmasi logout
function confirmLogout() {
    Swal.fire({
        title: "Keluar dari aplikasi?",
        text: "Anda akan keluar dari sesi saat ini.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Ya, keluar!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById("logout-form").submit();
        }
    });
}

// Notifikasi AJAX success
function showSuccess(message) {
    Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
    });
}

// Notifikasi AJAX error
function showError(message) {
    Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
    });
}

// Notifikasi AJAX warning
function showWarning(message) {
    Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
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
