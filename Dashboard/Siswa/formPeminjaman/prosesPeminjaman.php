<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
    $_SESSION['pesan_error_login'] = "Anda harus login sebagai siswa untuk melakukan aksi ini.";
    header("Location: /libtera/login.php");
    exit;
}

require "../../../connect.php";

$id_buku = $_GET['id'] ?? null;
$id_siswa = $_SESSION['siswa']['id_siswa'] ?? null;
$id_admin = 1;
$tgl_pinjam = date('Y-m-d');
$status_pinjam = 'PINJAM';

if (!$id_siswa) {
    $_SESSION['peminjaman_error_message'] = "Sesi pengguna tidak valid. Silakan login kembali.";
    header("Location: /libtera/login.php");
    exit;
}

if (!$id_buku) {
    $_SESSION['peminjaman_error_message'] = "ID buku tidak valid atau tidak ditemukan.";
    header("Location: /libtera/Dashboard/siswa/katalog.php"); // Asumsi path katalog
    exit;
}

// Ambil judul buku untuk pesan notifikasi
$judulBukuDisplay = "buku ini";
$stmt_judul_buku = $connect->prepare("SELECT judul FROM buku WHERE id_buku = ?");
if ($stmt_judul_buku) {
    $stmt_judul_buku->bind_param("s", $id_buku);
    $stmt_judul_buku->execute();
    $result_judul_buku = $stmt_judul_buku->get_result();
    if($row_judul = $result_judul_buku->fetch_assoc()){
        $judulBukuDisplay = "'" . htmlspecialchars($row_judul['judul']) . "'";
    }
    $stmt_judul_buku->close();
}

// --- PENGECEKAN BATAS MAKSIMAL PEMINJAMAN ---
$stmt_hitung_pinjaman = $connect->prepare("SELECT COUNT(*) as jumlah_dipinjam FROM peminjaman WHERE id_siswa = ? AND status = 'PINJAM'");
if (!$stmt_hitung_pinjaman) {
    error_log("Prepare statement gagal (hitung pinjaman): " . $connect->error);
    $_SESSION['peminjaman_error_message'] = "Terjadi kesalahan pada sistem (hitung).";
    header("Location: ../detailBuku.php?id=$id_buku");
    exit;
}
$stmt_hitung_pinjaman->bind_param("i", $id_siswa);
$stmt_hitung_pinjaman->execute();
$hasil_hitung = $stmt_hitung_pinjaman->get_result()->fetch_assoc();
$jumlahBukuSedangDipinjam = $hasil_hitung ? (int)$hasil_hitung['jumlah_dipinjam'] : 0;
$stmt_hitung_pinjaman->close();

$batasMaksimalPeminjaman = 3; // Tentukan batas maksimal

if ($jumlahBukuSedangDipinjam >= $batasMaksimalPeminjaman) {
    $_SESSION['peminjaman_info_message'] = "Anda telah mencapai batas maksimal peminjaman ($batasMaksimalPeminjaman buku). Kembalikan buku terlebih dahulu untuk meminjam lagi.";
    header("Location: ../detailBuku.php?id=$id_buku");
    exit;
}
// --- AKHIR PENGECEKAN BATAS MAKSIMAL PEMINJAMAN ---

// Cek apakah siswa sudah meminjam buku yang sama dan statusnya masih 'PINJAM'
$stmt_cek = $connect->prepare("SELECT id_peminjaman FROM peminjaman WHERE id_siswa = ? AND id_buku = ? AND status = 'PINJAM'");
if (!$stmt_cek) {
    error_log("Prepare statement gagal (cek peminjaman): " . $connect->error);
    $_SESSION['peminjaman_error_message'] = "Terjadi kesalahan pada sistem (cek).";
    header("Location: ../detailBuku.php?id=$id_buku");
    exit;
}
$stmt_cek->bind_param("is", $id_siswa, $id_buku);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();

if ($result_cek->num_rows > 0) {
    $_SESSION['peminjaman_error_message'] = "Kamu sudah meminjam $judulBukuDisplay dan belum mengembalikannya.";
    $stmt_cek->close();
    header("Location: ../detailBuku.php?id=$id_buku");
    exit;
}
$stmt_cek->close();

// Jika lolos semua pengecekan, proses insert peminjaman baru
$stmt_insert = $connect->prepare("INSERT INTO peminjaman (id_siswa, id_buku, id_admin, tgl_pinjam, status) VALUES (?, ?, ?, ?, ?)");
if (!$stmt_insert) {
    error_log("Prepare statement gagal (insert peminjaman): " . $connect->error);
    $_SESSION['peminjaman_error_message'] = "Terjadi kesalahan pada sistem (insert).";
    header("Location: ../detailBuku.php?id=$id_buku");
    exit;
}
$stmt_insert->bind_param("isiss", $id_siswa, $id_buku, $id_admin, $tgl_pinjam, $status_pinjam);

if ($stmt_insert->execute()) {
    $_SESSION['peminjaman_sukses_message'] = "Peminjaman $judulBukuDisplay berhasil!";
} else {
    $_SESSION['peminjaman_error_message'] = "Gagal meminjam $judulBukuDisplay. Kesalahan: " . htmlspecialchars($stmt_insert->error);
    error_log("Gagal insert peminjaman untuk siswa $id_siswa, buku $id_buku: " . $stmt_insert->error);
}
$stmt_insert->close();

header("Location: ../detailBuku.php?id=$id_buku");
exit;
?>