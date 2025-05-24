<?php
if (!isset($_SESSION)) {
  session_start();
}

if (!isset($_SESSION["login"]) || !isset($_SESSION["role"])) {
  header("Location: /libtera/login.php");
  exit;
}

$role = $_SESSION["role"];
?>

<!-- Sidebar -->
<div id="sidebar" style="
  width: 250px;
  height: calc(100% - 56px);
  position: fixed;
  top: 56px;
  left: -250px;
  background-color: #f8f9fa;
  z-index: 1040;
  transition: left 0.3s ease;
  overflow-y: auto;
">
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

<!-- Sidebar Toggle Script -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const logoToggle = document.getElementById("toggleSidebar");

    function toggleSidebar() {
      if (!sidebar) return;
      sidebar.style.left = (sidebar.style.left === "0px") ? "-250px" : "0px";
    }

    if (logoToggle) {
      logoToggle.addEventListener("click", function (e) {
        e.preventDefault();
        toggleSidebar();
      });
    }

    // Responsive auto-show/hide on resize
    function handleResize() {
      if (window.innerWidth >= 768) {
        sidebar.style.left = "0px";
      } else {
        sidebar.style.left = "-250px";
      }
    }

    window.addEventListener("resize", handleResize);
    handleResize(); // run on load
  });
</script>
