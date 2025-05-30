<?php
// Ambil session role jika belum ada
$role = $_SESSION["role"] ?? 'siswa';

// Fungsi sederhana untuk menentukan apakah link aktif
// Sesuaikan logika ini jika struktur URL Anda berbeda
if (!function_exists('isNavLinkActive')) {
    // Fungsi sederhana untuk menentukan apakah link aktif
    // Sesuaikan logika ini jika struktur URL Anda berbeda
    function isNavLinkActive($href) {
        $currentPath = strtok($_SERVER["REQUEST_URI"], '?');
        // Menghapus trailing slash jika ada dari $currentPath dan $href untuk perbandingan yang lebih konsisten
        $normalizedCurrentPath = rtrim($currentPath, '/');
        $normalizedHref = rtrim($href, '/');
        return $normalizedCurrentPath === $normalizedHref;
    }
}
?>

<ul class="navbar-nav justify-content-end flex-grow-1 pe-3 custom-vertical-navbar">
    <div class="glider-indicator-container">
        <div class="glider-indicator"></div>
    </div>
    <?php if ($role === 'siswa'): ?>
        <?php
        $menuSiswa = [
            ["href" => "/libtera/Dashboard/siswa/index.php", "icon" => "fa-solid fa-book", "text" => "Buku"],
            ["href" => "/libtera/Dashboard/siswa/ebooks/ebook.php", "icon" => "fa-solid fa-book-open", "text" => "Ebook"],
            ["href" => "/libtera/Dashboard/Siswa/formPeminjaman/Transaksipeminjaman.php", "icon" => "fas fa-handshake", "text" => "Peminjaman"],
            ["href" => "/libtera/Dashboard/Siswa/formPeminjaman/denda.php", "icon" => "fa-solid fa-money-bill-1-wave", "text" => "Denda"]
        ];
        foreach ($menuSiswa as $item): ?>
            <li class="nav-item <?php echo isNavLinkActive($item['href']) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $item['href']; ?>">
                    <i class="<?php echo $item['icon']; ?> fa-fw me-2 nav-icon"></i>
                    <span class="nav-text"><?php echo $item['text']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    <?php elseif ($role === 'admin'): ?>
        <?php
        $menuAdmin = [
            ["href" => "/libtera/admin/kelola_buku.php", "icon" => "fa-solid fa-book", "text" => "Kelola Buku"],
            ["href" => "/libtera/admin/kelola_ebook.php", "icon" => "fa-solid fa-book-open", "text" => "Kelola Ebook"],
            ["href" => "/libtera/admin/peminjaman.php", "icon" => "fas fa-handshake", "text" => "Peminjaman"],
            ["href" => "/libtera/admin/denda.php", "icon" => "fa-solid fa-money-bill-1-wave", "text" => "Kelola Denda"],
            ["href" => "/libtera/admin/kelola_user.php", "icon" => "fa-solid fa-users", "text" => "Kelola User"]
        ];
        foreach ($menuAdmin as $item): ?>
            <li class="nav-item <?php echo isNavLinkActive($item['href']) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $item['href']; ?>">
                    <i class="<?php echo $item['icon']; ?> fa-fw me-2 nav-icon"></i>
                    <span class="nav-text"><?php echo $item['text']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<hr class="my-3">

<div class="text-center user-profile-widget px-3">
    <img src="<?php echo $role === 'admin' ? '/libtera/assets/adminLogo.png' : '/libtera/assets/memberLogo.png'; ?>" alt="userLogo" width="60" height="60" class="rounded-circle mb-2 img-thumbnail shadow-sm user-avatar">
    <div class="fw-bold text-capitalize profile-name">
        <?php echo htmlspecialchars($role === "admin" ? ($_SESSION["admin"]["nama_admin"] ?? 'Admin') : ($_SESSION["siswa"]["nama"] ?? 'Siswa')); ?>
    </div>
    <div class="text-muted small mb-3 profile-role"><?php echo ucfirst(htmlspecialchars($role)); ?></div>
    <a class="btn btn-danger btn-sm sign-out-btn w-100" href="/libtera/logout.php">
        Sign Out <i class="fa-solid fa-right-from-bracket ms-1"></i>
    </a>
</div>