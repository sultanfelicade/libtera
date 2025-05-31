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

// File: notifikasi.js

document.addEventListener('DOMContentLoaded', function () {

    // 1. Logika untuk konfirmasi pembatalan peminjaman
    const tombolBatalPeminjamanList = document.querySelectorAll('.btn-batalkan-peminjaman');
    tombolBatalPeminjamanList.forEach(tombol => {
        tombol.addEventListener('click', function (event) {
            event.preventDefault(); // Mencegah navigasi langsung dari link

            const judulBuku = this.dataset.judulBuku;
            const urlBatal = this.href; // URL aksi dari atribut href

            if (typeof Swal !== 'undefined') { // Cek apakah SweetAlert2 sudah terdefinisi
                Swal.fire({
                    title: 'Anda Yakin?',
                    text: `Anda akan membatalkan pengajuan untuk buku "${judulBuku}". Aksi ini tidak dapat dikembalikan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, batalkan!',
                    cancelButtonText: 'Tidak jadi'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika dikonfirmasi, lanjutkan ke URL pembatalan
                        window.location.href = urlBatal;
                    }
                });
            } else {
                // Fallback jika SweetAlert2 tidak terload, gunakan confirm bawaan
                if (confirm(`Anda akan membatalkan pengajuan untuk buku "${judulBuku}". Lanjutkan?`)) {
                    window.location.href = urlBatal;
                }
            }
        });
    });

    // 2. Logika untuk menampilkan notifikasi hasil aksi dari server
    //    yang parameternya sudah disiapkan di window.swalInitParams oleh PHP (di footer.php)
    if (typeof Swal !== 'undefined' && window.swalInitParams) {
        Swal.fire(window.swalInitParams);
        window.swalInitParams = null; // Bersihkan variabel global setelah digunakan agar tidak muncul lagi pada navigasi berikutnya
    } else if (window.swalInitParams && window.swalInitParams.text) {
        // Fallback sederhana jika Swal tidak ada tapi parameter ada (misalnya untuk debug)
        alert(window.swalInitParams.title + "\n" + window.swalInitParams.text);
        window.swalInitParams = null;
    }

    // ... (Kode JavaScript lain yang mungkin sudah ada di notifikasi.js) ...

});