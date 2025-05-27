<?php
session_start();

// Validasi login dan role siswa
if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
  header("Location: /libtera/login.php");
  exit;
}

require "../../connect.php";
include_once __DIR__ . '/../../layout/header.php';

$id_buku = $_GET['id'];
$id_siswa = $_SESSION['siswa']['id_siswa'];

// Ambil data buku
$result = mysqli_query($connect, "SELECT * FROM buku WHERE id_buku = '$id_buku'");
$buku = mysqli_fetch_assoc($result);

// Cek apakah siswa sudah memberi rating
$cek = mysqli_query($connect, "SELECT * FROM rating WHERE id_siswa = $id_siswa AND id_buku = '$id_buku'");
$existingRating = mysqli_fetch_assoc($cek);

// Proses insert / update rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai_rating'])) {
  $rating = (int) $_POST['nilai_rating'];
  if ($rating >= 1 && $rating <= 5) {
    if ($existingRating) {
      // Ubah rating lama
      $query = "UPDATE rating SET nilai_rating = $rating WHERE id_siswa = $id_siswa AND id_buku = '$id_buku'";
    } else {
      // Kasih rating baru
      $query = "INSERT INTO rating (id_siswa, id_buku, nilai_rating) VALUES ($id_siswa, '$id_buku', $rating)";
    }

    if (!mysqli_query($connect, $query)) {
      die("Query Gagal: " . mysqli_error($connect));
    }

    header("Location: detailBuku.php?id=$id_buku");
    exit;
  }
}

// Ambil rata-rata rating
$avgRatingQuery = mysqli_query($connect, "SELECT ROUND(AVG(nilai_rating),1) as avg_rating FROM rating WHERE id_buku = '$id_buku'");
$avg = mysqli_fetch_assoc($avgRatingQuery)['avg_rating'] ?? 0;

// Cek apakah siswa sedang meminjam buku ini (status PINJAM)
$cekPinjam = mysqli_query($connect, "SELECT * FROM peminjaman WHERE id_siswa = $id_siswa AND id_buku = '$id_buku' AND status = 'PINJAM'");
$sudahDipinjam = mysqli_num_rows($cekPinjam) > 0;
?>

<div class="container mt-5 pt-5">
  <div class="row">
    <div class="col-md-4 text-center">
      <img src="/libtera/uploads/cover/<?= htmlspecialchars($buku['cover']) ?>" class="img-fluid rounded" alt="cover buku">
    </div>
    <div class="col-md-8">
      <h2 class="fw-bold"><?= htmlspecialchars($buku['judul']) ?></h2>
      <p class="text-muted"><?= htmlspecialchars($buku['pengarang']) ?></p>
      <p class="mb-2"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> <?= $avg ?> &nbsp; | &nbsp; <?= htmlspecialchars($buku['penerbit']) ?>, <?= htmlspecialchars($buku['tahun_terbit']) ?></p>
      <h5 class="mt-4">Sinopsis</h5>
      <p class="text-secondary"><?= htmlspecialchars($buku['deskripsi']) ?></p>

      <!-- Tombol Peminjaman -->
      <?php if ($sudahDipinjam): ?>
        <button class="btn btn-secondary mt-3" disabled>Sudah Dipinjam</button>
      <?php else: ?>
        <a href="/libtera/Dashboard/siswa/formPeminjaman/prosesPeminjaman.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary mt-3">Pinjam Sekarang</a>
      <?php endif; ?>

      <!-- Bagian Rating -->
      <div class="mt-4">
        <h5>Rating Buku</h5>
        <p class="mb-1"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> <?= $avg ?> / 5</p>

        <form method="post" id="ratingForm" class="mt-2">
          <div class="flex gap-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button
                type="submit"
                name="nilai_rating"
                value="<?= $i ?>"
                class="star-btn relative <?= $existingRating && $i <= $existingRating['nilai_rating'] ? 'text-yellow-400' : 'text-gray-400' ?> hover:text-yellow-400 focus:outline-none text-3xl transition-colors duration-300"
                aria-label="Beri rating <?= $i ?> bintang"
              >
                ★
              </button>
            <?php endfor; ?>
          </div>
        </form>

        <?php if ($existingRating): ?>
          <p class="text-success fw-semibold mt-3">
            Kamu sudah memberi rating: <span class="text-yellow-400"><?= $existingRating['nilai_rating'] ?> <i class="fa-solid fa-star" style="color: #FFD43B;"></i></span>
          </p>
        <?php else: ?>
          <p class="text-muted mt-3">Klik bintang untuk memberi rating.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
  .star-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    user-select: none;
  }
</style>

<?php include_once __DIR__ . '/../../layout/footer.php'; ?>
