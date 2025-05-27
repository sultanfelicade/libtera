<?php 
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
  header("Location: /libtera/login.php");
  exit;
}
require "../../../connect.php";

$akunMember = $_SESSION["siswa"]["nisn"];

// Ambil data siswa
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

// Ambil peminjaman yang belum dikembalikan
$sql = "SELECT 
    p.id_peminjaman, b.judul, p.tgl_pinjam
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
WHERE p.id_siswa = ? AND p.tgl_kembali IS NULL";

$stmt2 = $connect->prepare($sql);
$stmt2->bind_param("i", $id_siswa);
$stmt2->execute();
$result = $stmt2->get_result();
$peminjamanBelumKembali = $result->fetch_all(MYSQLI_ASSOC);

include_once '../../../layout/header.php'; 
?>

<div class="container mt-5">
  <h3>Denda Buku yang Belum Dikembalikan</h3>

  <div class="table-responsive mt-4">
    <table class="table table-bordered table-striped text-center">
      <thead class="table-dark">
        <tr>
          <th>Judul Buku</th>
          <th>Tanggal Pinjam</th>
          <th>Jatuh Tempo</th>
          <th>Keterlambatan (minggu)</th>
          <th>Estimasi Denda</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $now = new DateTime();
        foreach ($peminjamanBelumKembali as $item): 
          $tgl_pinjam = new DateTime($item['tgl_pinjam']);
          $tgl_tempo = clone $tgl_pinjam;
          $tgl_tempo->modify('+7 days');

          $keterlambatan = $tgl_tempo < $now ? $tgl_tempo->diff($now)->days : 0;
          $minggu_terlambat = floor($keterlambatan / 7);
          $denda = $minggu_terlambat * 5000;
        ?>
          <tr>
            <td><?= htmlspecialchars($item["judul"]); ?></td>
            <td><?= $item["tgl_pinjam"]; ?></td>
            <td><?= $tgl_tempo->format('Y-m-d'); ?></td>
            <td><?= $minggu_terlambat ?> minggu</td>
            <td>Rp <?= number_format($denda, 0, ',', '.'); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($peminjamanBelumKembali)): ?>
          <tr><td colspan="5"><em>Tidak ada peminjaman yang terlambat</em></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once '../../../layout/footer.php'; ?>
