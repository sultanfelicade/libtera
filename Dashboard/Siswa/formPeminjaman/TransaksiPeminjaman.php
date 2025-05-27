<?php 
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
  header("Location: /libtera/login.php");
  exit;
}
require "../../../connect.php";

$akunMember = $_SESSION["siswa"]["nisn"];

// Query harus menggunakan id_siswa, tapi kamu punya nisn, 
// jadi kita perlu cari id_siswa berdasarkan nisn dulu
$querySiswa = "SELECT id_siswa, nama FROM siswa WHERE nisn = ?";
$stmt = $connect->prepare($querySiswa);
$stmt->bind_param("i", $akunMember);
$stmt->execute();
$resultSiswa = $stmt->get_result();
$siswaData = $resultSiswa->fetch_assoc();

if (!$siswaData) {
    die("Data siswa tidak ditemukan");
}

$id_siswa = $siswaData['id_siswa'];
$nama_siswa = $siswaData['nama'];

// Query peminjaman sesuai id_siswa
$sql = "SELECT 
    p.id_peminjaman, p.id_buku, b.judul, s.nama, a.nama_admin, p.tgl_pinjam, p.tgl_kembali
FROM 
    peminjaman p
INNER JOIN buku b ON p.id_buku = b.id_buku
INNER JOIN siswa s ON p.id_siswa = s.id_siswa
INNER JOIN admin a ON p.id_admin = a.id
WHERE 
    p.id_siswa = ?";

$stmt2 = $connect->prepare($sql);
$stmt2->bind_param("i", $id_siswa);
$stmt2->execute();
$dataPinjam = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
include_once '../../../layout/header.php'; 
?>


<div class="container p-4 mt-5">
  <div class="text-4xl font-extrabold bg-gradient-to-r from-blue-400 via-blue-600 to-pink-500 bg-clip-text text-transparent mb-3">
    Riwayat transaksi Peminjaman Buku
  </div>

  <!-- Ini bagian responsif tabel -->
  <div class="table-responsive">
    <table class="table table-striped table-hover text-center align-middle">
      <thead class="table-primary">
        <tr>
          <th>Id Peminjaman</th>
          <th>Id Buku</th>
          <th>Judul Buku</th>
          <th>Tanggal Peminjaman</th>
          <th>Tanggal Pengembalian</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dataPinjam as $item): ?>
          <tr>
            <td><?= $item["id_peminjaman"]; ?></td>
            <td><?= $item["id_buku"]; ?></td>
            <td><?= htmlspecialchars($item["judul"]); ?></td>
            <td><?= $item["tgl_pinjam"]; ?></td>
            <td><?= $item["tgl_kembali"] ?: "<em>-</em>"; ?></td>
            <td>
              <?php if (empty($item["tgl_kembali"])): ?>
                <span class="badge bg-danger">Belum Dikembalikan</span>
              <?php elseif (empty($item['tgl_pinjam'])): ?>
                <span class="badge bg-secondary">Pending </span>
                <a href="#" class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Batalkan">
                  Batalkan
                </a>
              <?php else: ?>
                <span class="badge bg-success">Dikembalikan</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
</script>



<?php include_once '../../../layout/footer.php'; ?>