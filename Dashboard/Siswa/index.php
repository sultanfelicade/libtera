<?php
session_start();

// Validasi login dan role siswa
if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
  header("Location: /libtera/login.php");
  exit;
}
?>

<?php include_once __DIR__ . '/../../layout/header.php'; ?>
<body>
  <div class="container mt-5 pt-5">
    <?php
    $day = date('l');
    $dayOfMonth = date('d');
    $month = date('F');
    $year = date('Y');
    ?>

    <div class="text-center mb-4">
      <h1 class="fw-bold">Dashboard Siswa</h1>
      <p class="text-muted fs-5"><?php echo $day . ", " . $dayOfMonth . " " . $month . " " . $year; ?></p>
      <div class="alert alert-info d-inline-block px-4 py-2 mt-2 rounded-pill shadow-sm" role="alert">
        Selamat datang, <span class="fw-bold text-capitalize"><?php echo $_SESSION['siswa']['nama']; ?></span>
      </div>
    </div>

    <div class="text-center mb-5">
      <h3 class="fw-semibold">Layanan Perpustakaan</h3>
      <p class="text-secondary">Pilih layanan yang ingin kamu gunakan</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center">
      <div class="col">
        <div class="card border-0 shadow-sm h-100 hover-scale">
          <a href="buku/daftarBuku.php">
            <img src="../../assets/dashboardCardMember/daftarBuku.png" class="card-img-top" alt="daftar buku">
          </a>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm h-100 hover-scale">
          <a href="formPeminjaman/TransaksiPeminjaman.php">
            <img src="../../assets/dashboardCardMember/peminjaman.png" class="card-img-top" alt="peminjaman">
          </a>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm h-100 hover-scale">
          <a href="formPeminjaman/TransaksiPengembalian.php">
            <img src="../../assets/dashboardCardMember/pengembalian.png" class="card-img-top" alt="pengembalian">
          </a>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm h-100 hover-scale">
          <a href="formPeminjaman/TransaksiDenda.php">
            <img src="../../assets/dashboardCardMember/denda.png" class="card-img-top" alt="denda">
          </a>
        </div>
      </div>
    </div>
  </div>

  <style>
    .hover-scale {
      transition: transform 0.3s ease;
    }

    .hover-scale:hover {
      transform: scale(1.02);
    }

    .card-img-top {
      border-radius: 10px;
    }

    @media (min-width: 768px) {
      .container {
        margin-left: 250px;
      }
    }
  </style>

<?php include_once __DIR__ . '/../../layout/footer.php'; ?>