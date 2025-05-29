<?php
// Ambil session role jika belum ada
$role = $_SESSION["role"] ?? 'siswa'; 
?>

<ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
  <?php if ($role === 'siswa'): ?>
    <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/siswa/index.php"><i class="fa-solid fa-book fa-fw me-2"></i>Buku</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/siswa/ebook.php"><i class="fa-solid fa-book-open fa-fw me-2"></i>Ebook</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/Siswa/formPeminjaman/Transaksipeminjaman.php"><i class="fas fa-handshake fa-fw me-2"></i>Peminjaman</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/Dashboard/Siswa/formPeminjaman/denda.php"><i class="fa-solid fa-money-bill-1-wave fa-fw me-2"></i>Denda</a></li>
  <?php elseif ($role === 'admin'): ?>
    <li class="nav-item"><a class="nav-link" href="/libtera/admin/kelola_buku.php"><i class="fa-solid fa-book fa-fw me-2"></i>Kelola Buku</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/admin/kelola_ebook.php"><i class="fa-solid fa-book-open fa-fw me-2"></i>Kelola Ebook</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/admin/peminjaman.php"><i class="fas fa-handshake fa-fw me-2"></i>Peminjaman</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/admin/denda.php"><i class="fa-solid fa-money-bill-1-wave fa-fw me-2"></i>Kelola Denda</a></li>
    <li class="nav-item"><a class="nav-link" href="/libtera/admin/kelola_user.php"><i class="fa-solid fa-users fa-fw me-2"></i>Kelola User</a></li>
  <?php endif; ?>
</ul>

<hr>

<div class="text-center">
  <img src="<?php echo $role === 'admin' ? '/libtera/assets/adminLogo.png' : '/libtera/assets/memberLogo.png'; ?>" alt="userLogo" width="50px" class="rounded-circle mb-2">
  <div class="fw-bold text-capitalize">
    <?php echo $role === "admin" ? ($_SESSION["admin"]["nama_admin"] ?? 'Admin') : ($_SESSION["siswa"]["nama"] ?? 'Siswa'); ?>
  </div>
  <div class="text-muted small mb-3"><?php echo ucfirst($role); ?></div>
  <a class="btn btn-danger btn-sm" href="/libtera/logout.php">
    Sign Out <i class="fa-solid fa-right-from-bracket"></i>
  </a>
</div>