<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
  header("Location: /libtera/login.php");
  exit;
}

require "../../../connect.php";

$id_buku = $_GET['id'] ?? null;
$id_siswa = $_SESSION['siswa']['id_siswa'];
$id_admin = 1; // sementara hardcode dulu
$tgl_pinjam = date('Y-m-d');
$status = 'PINJAM';

if (!$id_buku) {
  die("ID buku tidak ditemukan.");
}

// Cek apakah sudah meminjam buku yang sama dan belum dikembalikan
$cek = mysqli_query($connect, "SELECT * FROM peminjaman WHERE id_siswa = $id_siswa AND id_buku = '$id_buku' AND status = 'PINJAM'");
if (mysqli_num_rows($cek) > 0) {
  echo "<script>alert('Kamu sudah meminjam buku ini dan belum mengembalikannya.'); window.location.href = '../detailBuku.php?id=$id_buku';</script>";
  exit;
}

// Insert peminjaman
$query = "INSERT INTO peminjaman (id_siswa, id_buku, id_admin, tgl_pinjam, status)
          VALUES ($id_siswa, '$id_buku', $id_admin, '$tgl_pinjam', '$status')";

if (mysqli_query($connect, $query)) {
  echo "<script>alert('Peminjaman berhasil!'); window.location.href = '../detailBuku.php?id=$id_buku';</script>";
} else {
  echo "Gagal meminjam buku: " . mysqli_error($connect);
}
?>
