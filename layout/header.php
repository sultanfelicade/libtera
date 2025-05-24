<?php
session_start();
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/de8de52639.js" crossorigin="anonymous"></script>
  <style>
    body {
      padding-top: 56px;
    }

    #sidebar {
      width: 250px;
      height: calc(100% - 56px);
      position: fixed;
      top: 56px;
      left: -250px;
      background-color: #f8f9fa;
      transition: left 0.3s ease;
      z-index: 1040;
    }

    #sidebar.active {
      left: 0;
    }

    #main-content {
      padding: 1rem;
      transition: margin-left 0.3s ease;
    }

    @media (min-width: 768px) {
      #sidebar {
        left: 0;
      }

      #main-content {
        margin-left: 250px;
      }
    }

    .sidebar-link {
      text-decoration: none;
      color: #333;
      display: block;
      padding: 0.5rem 1rem;
    }

    .sidebar-link:hover {
      background-color: #e9ecef;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar fixed-top bg-body-tertiary shadow-sm">
  <div class="container-fluid p-2 d-flex align-items-center justify-content-between">

    <!-- Logo as toggle button -->
    <a class="navbar-brand m-0" href="#" id="toggleSidebar">
      <img src="/libtera/assets/logoFooter.png" alt="logo" width="120px">
    </a>

    <!-- User Dropdown -->
    <div class="dropdown">
      <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <img src="<?php echo $role === 'admin' ? '/libtera/assets/adminLogo.png' : '/libtera/assets/memberLogo.png'; ?>" alt="userLogo" width="40px" />
      </button>
      <ul class="dropdown-menu dropdown-menu-end mt-2 p-2">
        <li class="text-center">
          <img src="<?php echo $role === 'admin' ? '/libtera/assets/adminLogo.png' : '/libtera/assets/memberLogo.png'; ?>" alt="logo" width="30px">
        </li>
        <li>
          <a class="dropdown-item text-center text-secondary" href="#">
            <span class="text-capitalize fw-bold">
              <?php echo $role === "admin" ? $_SESSION["admin"]["nama_admin"] : $_SESSION["siswa"]["nama"]; ?>
            </span>
          </a>
          <?php if ($role === "siswa"): ?>
            <a class="dropdown-item text-center mb-2" href="#">Siswa</a>
          <?php endif; ?>
        </li>
        <?php if ($role === "admin"): ?>
          <hr>
          <li>
            <a class="dropdown-item text-center mb-2" href="#">
              Akun Terverifikasi <span class="text-primary"><i class="fa-solid fa-circle-check"></i></span>
            </a>
          </li>
        <?php endif; ?>
        <li>
          <a class="dropdown-item text-center p-2 bg-danger text-light rounded" href="/libtera/signOut.php">
            Sign Out <i class="fa-solid fa-right-to-bracket"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar -->
<div id="sidebar">
  <ul class="list-group list-group-flush">
    <?php if ($role === "siswa"): ?>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/siswa/buku.php">📚 Buku</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/siswa/ebook.php">📘 Ebook</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/siswa/peminjaman.php">📄 Peminjaman</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/siswa/denda.php">💸 Denda</a></li>
    <?php elseif ($role === "admin"): ?>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/admin/kelola_buku.php">📚 Kelola Buku</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/admin/kelola_ebook.php">📘 Kelola Ebook</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/admin/peminjaman.php">📄 Peminjaman</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/admin/denda.php">💸 Denda</a></li>
      <li class="list-group-item"><a class="sidebar-link" href="/libtera/admin/kelola_user.php">👤 Kelola User</a></li>
    <?php endif; ?>
  </ul>
</div>

<!-- Main Content -->
<div id="main-content" class="container mt-5">
  <h1>Selamat Datang di Dashboard</h1>
</div>

<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const logoBtn = document.getElementById("toggleSidebar");

    function toggleSidebar() {
      sidebar.classList.toggle("active");
    }

    logoBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      toggleSidebar();
    });

    function handleResize() {
      if (window.innerWidth >= 768) {
        sidebar.classList.add("active");
      } else {
        sidebar.classList.remove("active");
      }
    }

    window.addEventListener("resize", handleResize);
    handleResize();
  });
</script>
</body>
</html>
