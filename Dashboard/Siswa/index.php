<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
  header("Location: /libtera/login.php");
  exit;
}

include_once __DIR__ . '/../../layout/header.php';
require "../../connect.php";

// Ambil kategori untuk tombol filter dinamis
$kategoriQuery = mysqli_query($connect, "SELECT * FROM kategori");

// Ambil kategori dari URL jika ada
$kategoriId = isset($_GET['kategori']) ? intval($_GET['kategori']) : null;

// Query buku + rating
$sql = "
  SELECT b.*, COALESCE(AVG(r.nilai_rating), 0) AS rata_rating
  FROM buku b
  LEFT JOIN rating r ON b.id_buku = r.id_buku
";

if ($kategoriId) {
  $sql .= " WHERE b.id_kategori = $kategoriId";
}

$sql .= " GROUP BY b.id_buku ORDER BY rata_rating DESC LIMIT 10";
$bukuQuery = mysqli_query($connect, $sql);
?>

<div class="container pt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group">
      <a href="?kategori=" class="btn rounded btn-<?= !$kategoriId ? 'primary' : 'outline-primary' ?> btn-sm">Semua</a>
      <?php while($kategori = mysqli_fetch_assoc($kategoriQuery)): ?>
        <a href="?kategori=<?= $kategori['id_kategori'] ?>"
           class="btn rounded ms-2 btn-<?= ($kategoriId == $kategori['id_kategori']) ? 'primary' : 'outline-primary' ?> btn-sm">
          <?= htmlspecialchars($kategori['nama_kategori']) ?>
        </a>
      <?php endwhile; ?>
    </div>
    <form class="d-flex" role="search">
      <input class="form-control" type="search" placeholder="Cari buku" aria-label="Search">
    </form>
  </div>

  <h4 class="fw-semibold mb-3">Populer</h4>
  <div class="row row-cols-2 row-cols-md-5 g-3">
    <?php while($buku = mysqli_fetch_assoc($bukuQuery)): ?>
      <div class="col">
        <div class="card h-100 border-0 shadow-sm">
          <a href="detailBuku.php?id=<?= $buku['id_buku'] ?>">
            <img src="/libtera/uploads/cover/<?= $buku['cover'] ?>" class="card-img-top" alt="cover buku">
          </a>
          <div class="card-body px-2 py-3">
            <h6 class="card-title text-truncate"><?= $buku['judul'] ?></h6>
            <p class="card-text text-muted small"><?= $buku['pengarang'] ?></p>
            <div class="text-warning small mb-2"> <i class="fa-solid fa-star" style="color: #FFD43B;"></i> <?= number_format($buku['rata_rating'], 1) ?>/5</div>
            <a href="detailBuku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary btn-sm w-100">Pinjam Buku</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php include_once __DIR__ . '/../../layout/footer.php'; ?>
