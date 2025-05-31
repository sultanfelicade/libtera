<?php
session_start();
require "../../../connect.php"; // Sesuaikan path ke file koneksi Anda

// Keamanan: Pastikan siswa login dan memiliki peran siswa
if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa" || !isset($_SESSION['siswa']['id_siswa'])) {
    $_SESSION['swal_params'] = [
        'title' => 'Akses Ditolak!',
        'text' => 'Aksi tidak diizinkan. Silakan login kembali.',
        'icon' => 'error'
    ];
    header("Location: ../../login.php"); // Arahkan ke halaman login utama
    exit;
}

$id_peminjaman_raw = $_GET['id_peminjaman'] ?? null;
$id_siswa_session = $_SESSION['siswa']['id_siswa'];

// Validasi id_peminjaman
if (!$id_peminjaman_raw || !filter_var($id_peminjaman_raw, FILTER_VALIDATE_INT)) {
    $_SESSION['swal_params'] = [
        'title' => 'Error Input!',
        'text' => 'ID peminjaman tidak valid atau tidak disertakan.',
        'icon' => 'error'
    ];
    header("Location: TransaksiPeminjaman.php"); // Kembali ke halaman transaksi
    exit;
}
$id_peminjaman = (int)$id_peminjaman_raw;

// Cek apakah peminjaman ini milik siswa yang login dan statusnya PENDING
$stmt_cek = $connect->prepare("SELECT status FROM peminjaman WHERE id_peminjaman = ? AND id_siswa = ?");
if (!$stmt_cek) {
    error_log("Prepare statement gagal (cek peminjaman untuk batal): " . $connect->error);
    $_SESSION['swal_params'] = [
        'title' => 'Error Sistem',
        'text' => 'Terjadi kesalahan internal (cek data). Silakan coba lagi nanti.',
        'icon' => 'error'
    ];
    header("Location: TransaksiPeminjaman.php");
    exit;
}
$stmt_cek->bind_param("ii", $id_peminjaman, $id_siswa_session);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$peminjaman = $result_cek->fetch_assoc();
$stmt_cek->close();

if ($peminjaman && $peminjaman['status'] == 'PENDING') {
    // Ubah status menjadi 'DIBATALKAN'
    $stmt_update = $connect->prepare("UPDATE peminjaman SET status = 'DIBATALKAN' WHERE id_peminjaman = ? AND id_siswa = ? AND status = 'PENDING'");
    if (!$stmt_update) {
        error_log("Prepare statement gagal (update batal peminjaman): " . $connect->error);
        $_SESSION['swal_params'] = [
            'title' => 'Error Sistem',
            'text' => 'Terjadi kesalahan internal saat memproses pembatalan. Silakan coba lagi nanti.',
            'icon' => 'error'
        ];
        header("Location: TransaksiPeminjaman.php");
        exit;
    }
    $stmt_update->bind_param("ii", $id_peminjaman, $id_siswa_session);

    if ($stmt_update->execute()) {
        if ($stmt_update->affected_rows > 0) {
            $_SESSION['swal_params'] = [
                'title' => 'Berhasil Dibatalkan!',
                'text' => 'Pengajuan peminjaman telah dibatalkan.',
                'icon' => 'success'
            ];
        } else {
            // Ini bisa terjadi jika ada race condition atau status sudah berubah tepat sebelum update
            $_SESSION['swal_params'] = [
                'title' => 'Info',
                'text' => 'Tidak ada pengajuan yang dibatalkan. Kemungkinan status sudah berubah atau sudah diproses.',
                'icon' => 'info'
            ];
        }
    } else {
        $_SESSION['swal_params'] = [
            'title' => 'Gagal Membatalkan!',
            'text' => 'Proses pembatalan pengajuan peminjaman gagal.', // Pesan error detail bisa dilog
            'icon' => 'error'
        ];
        error_log("Gagal execute update batal peminjaman untuk id_peminjaman $id_peminjaman: " . $stmt_update->error);
    }
    $stmt_update->close();
} elseif ($peminjaman) {
    // Jika peminjaman ditemukan tapi statusnya bukan PENDING
    $_SESSION['swal_params'] = [
        'title' => 'Tidak Dapat Dibatalkan',
        'text' => "Pengajuan ini tidak dapat dibatalkan karena statusnya sudah " . htmlspecialchars($peminjaman['status']) . ".",
        'icon' => 'warning'
    ];
} else {
    // Jika peminjaman tidak ditemukan untuk siswa ini
    $_SESSION['swal_params'] = [
        'title' => 'Tidak Ditemukan!',
        'text' => 'Pengajuan peminjaman tidak ditemukan atau Anda tidak memiliki hak akses.',
        'icon' => 'error'
    ];
}

header("Location: TransaksiPeminjaman.php"); // Kembali ke halaman transaksi
exit;
?>