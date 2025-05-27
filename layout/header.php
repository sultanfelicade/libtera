<?php
if (!isset($_SESSION["login"]) || !isset($_SESSION["role"])) {
  header("Location: /libtera/login.php");
  exit;
}
$role = $_SESSION["role"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <script src="https://kit.fontawesome.com/de8de52639.js" crossorigin="anonymous"></script>
  <style>
    body {
      padding-top: 70px;
    }
    .navbar-brand img {
      max-height: 40px;
    }
  </style>
</head>
<body>

<nav class="navbar bg-body-tertiary fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="/libtera/index.php">
      <img src="/libtera/assets/logoFooter.png" alt="logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">
          <?php echo $role === 'admin' ? 'Menu Admin' : 'Menu Siswa'; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
          <?php if ($role === 'siswa'): ?>
            <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/siswa/index.php"><i class="fa-solid fa-book"></i> Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/siswa/ebook.php"><i class="fa-solid fa-book-open"></i> Ebook</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/Siswa/formPeminjaman/Transaksipeminjaman.php"><i class="fas fa-handshake"></i> Peminjaman</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/Siswa/formPeminjaman/denda.php"><i class="fa-solid fa-money-bill-1-wave"></i> Denda</a></li>
          <?php elseif ($role === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/libtera/admin/kelola_buku.php"><i class="fa-solid fa-book"></i> Kelola Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/admin/kelola_ebook.php"><i class="fa-solid fa-book-open"></i> Kelola Ebook</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/admin/peminjaman.php"><i class="fas fa-handshake"></i> Peminjaman</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/admin/denda.php"><i class="fa-solid fa-money-bill-1-wave"></i> Kelola Denda</a></li>
            <li class="nav-item"><a class="nav-link" href="/libtera/admin/kelola_user.php"><i class="fa-solid fa-users"></i> Kelola User</a></li>
          <?php endif; ?>
        </ul>
        <hr>
        <div class="text-center">
          <img src="<?php echo $role === 'admin' ? '/libtera/assets/adminLogo.png' : '/libtera/assets/memberLogo.png'; ?>" alt="userLogo" width="40px">
          <div class="fw-bold text-capitalize mt-2">
            <?php echo $role === "admin" ? $_SESSION["admin"]["nama_admin"] : $_SESSION["siswa"]["nama"]; ?>
          </div>
          <div class="text-muted mb-2"><?php echo ucfirst($role); ?></div>
          <a class="btn btn-danger btn-sm" href="/libtera/signOut.php">
            Sign Out <i class="fa-solid fa-right-to-bracket"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Isi halaman lanjut di bawah ini -->
