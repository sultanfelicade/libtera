// Pastikan skrip ini dijalankan setelah DOM siap
// dan SweetAlert2 library sudah dimuat sebelumnya.
document.addEventListener('DOMContentLoaded', function() {

    // Fungsi untuk menampilkan SweetAlert Peminjaman Sukses
    function tampilkanPeminjamanSukses(pesan) {
        let timerIntervalSwalPeminjaman;
        Swal.fire({
            position: "top-end",
            icon: "success",
            title: "Peminjaman Berhasil!",
            html: pesan,
            timer: 3500,
            timerProgressBar: true,
            showConfirmButton: false,
            didOpen: () => {
                const timerElement = Swal.getPopup().querySelector("b");
                if (timerElement) {
                    timerIntervalSwalPeminjaman = setInterval(() => {
                        const timeLeft = Swal.getTimerLeft();
                        if (typeof timeLeft === 'number') {
                            timerElement.textContent = `${Math.ceil(timeLeft)}`;
                        }
                    }, 100);
                }
            },
            willClose: () => {
                clearInterval(timerIntervalSwalPeminjaman);
            }
        });
    }

    // Fungsi untuk menampilkan SweetAlert Peminjaman Error
    function tampilkanPeminjamanError(pesan) {
        Swal.fire({
            position: "top-end",
            icon: "error",
            title: "Gagal Meminjam",
            html: pesan,
            showConfirmButton: false,
            timer: 4000
        });
    }

    // --- FUNGSI BARU UNTUK NOTIFIKASI INFO (lebih kecil/toast) ---
    function tampilkanPeminjamanInfo(pesan) {
        Swal.fire({
            toast: true, // Membuat alert menjadi kecil seperti toast
            position: "top-end",
            icon: "info",
            title: pesan, // Untuk toast, pesan utama biasanya cukup di title
            showConfirmButton: false,
            timer: 5000, // Durasi bisa lebih lama untuk info
            timerProgressBar: true // Opsional untuk toast, tapi bisa berguna
        });
    }

    // Cek variabel global yang sudah diset oleh PHP
    // dan panggil fungsi SweetAlert yang sesuai

    // Untuk Peminjaman Sukses
    if (typeof globalPeminjamanSuksesMsg !== 'undefined' && globalPeminjamanSuksesMsg) {
        tampilkanPeminjamanSukses(globalPeminjamanSuksesMsg);
        globalPeminjamanSuksesMsg = null; // Reset setelah tampil
    }

    // Untuk Peminjaman Error
    if (typeof globalPeminjamanErrorMsg !== 'undefined' && globalPeminjamanErrorMsg) {
        tampilkanPeminjamanError(globalPeminjamanErrorMsg);
        globalPeminjamanErrorMsg = null; // Reset setelah tampil
    }

    // --- UNTUK PEMINJAMAN INFO (BATAS MAKSIMAL) ---
    if (typeof globalPeminjamanInfoMsg !== 'undefined' && globalPeminjamanInfoMsg) {
        tampilkanPeminjamanInfo(globalPeminjamanInfoMsg);
        globalPeminjamanInfoMsg = null; // Reset setelah tampil
    }

});