<?php
// ebook.php (Halaman untuk menampilkan DAFTAR E-Book dengan Kartu Hover Reveal)
session_start();
$title = "Daftar E-Book - Libtera";

require __DIR__ . '/../../../connect.php';

// Ambil data kategori untuk filter
$kategoriList = [];
$stmtKategori = $connect->prepare("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
if ($stmtKategori) {
    $stmtKategori->execute();
    $kategoriEbookResult = $stmtKategori->get_result();
    while ($row = $kategoriEbookResult->fetch_assoc()) {
        $kategoriList[] = $row;
    }
    $stmtKategori->close();
} else {
    error_log("Gagal menyiapkan statement untuk kategori: " . $connect->error);
}

// Ambil parameter dari URL
$kategoriId = isset($_GET['kategori']) && is_numeric($_GET['kategori']) ? (int)$_GET['kategori'] : null;
$searchTerm = $_GET['search'] ?? '';

// Bangun query SQL (termasuk cover_ebook karena akan dipakai di first-content)
$sqlEbooks = "SELECT e.id_ebook, e.judul, e.penulis, e.deskripsi, e.file_path, e.cover_ebook, k.nama_kategori 
              FROM ebooks e 
              LEFT JOIN kategori k ON e.id_kategori = k.id_kategori";

$conditions = [];
$params = [];
$types = '';

if ($kategoriId) {
    $conditions[] = "e.id_kategori = ?";
    $params[] = $kategoriId;
    $types .= 'i';
}

if (!empty($searchTerm)) {
    $conditions[] = "(e.judul LIKE ? OR e.penulis LIKE ?)";
    $likeTerm = "%" . $searchTerm . "%";
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $types .= 'ss';
}

if (!empty($conditions)) {
    $sqlEbooks .= " WHERE " . implode(" AND ", $conditions);
}
$sqlEbooks .= " ORDER BY e.judul ASC";

$resultEbooks = null;
$stmtEbooks = $connect->prepare($sqlEbooks);
if (!$stmtEbooks) {
    error_log("Gagal menyiapkan statement untuk ebooks: " . $connect->error);
} else {
    if (!empty($params)) {
        $stmtEbooks->bind_param($types, ...$params);
    }
    $stmtEbooks->execute();
    $resultEbooks = $stmtEbooks->get_result();
}

include_once __DIR__ . '/../../../layout/header.php'; // Panggil header setelah semua logika PHP
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Daftar E-Book</h1>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="category-scroll-container">
            <div class="category-list">
                <a href="ebook.php" class="btn btn-<?= !$kategoriId && empty($searchTerm) ? 'primary' : 'outline-primary' ?> mb-1">Semua</a>
                <?php if (!empty($kategoriList)): ?>
                    <?php foreach($kategoriList as $kategori): ?>
                        <a href="ebook.php?kategori=<?= $kategori['id_kategori'] ?><?= !empty($searchTerm) ? '&search='.urlencode($searchTerm) : '' ?>" 
                           class="btn btn-<?= ($kategoriId == $kategori['id_kategori']) ? 'primary' : 'outline-primary' ?> mb-1">
                            <?= htmlspecialchars($kategori['nama_kategori']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="input-container" style="min-width: 250px;">
            <form method="GET" action="ebook.php" class="d-flex">
                <?php if ($kategoriId): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategoriId) ?>">
                <?php endif; ?>
                <input class="form-control me-2" 
                       type="search" name="search" placeholder="Cari Judul atau Penulis..." 
                       value="<?= htmlspecialchars($searchTerm) ?>" aria-label="Cari E-Book">
                <button class="btn btn-outline-success" type="submit">Cari</button>
            </form>
        </div>
    </div>
    
    <?php if(!empty($searchTerm)): ?>
        <h4 class="mb-4">Hasil pencarian untuk: "<?= htmlspecialchars($searchTerm) ?>"</h4>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4">
        <?php if($resultEbooks && $resultEbooks->num_rows > 0): ?>
            <?php while($ebook = $resultEbooks->fetch_assoc()): ?>
                <div class="col d-flex align-items-stretch"> <?php
                    $coverUrl = '/libtera/assets/default_ebook_cover.png'; // Gambar default
                    if (!empty($ebook['cover_ebook'])) {
                        $coverUrl = '/libtera/uploads/ebook/assets/cover/' . htmlspecialchars($ebook['cover_ebook']);
                    }
                    $ebookFileLink = 'flipbook.php?file=' . urlencode($ebook['file_path']);
                    ?>

                    <div class="card uiverse-reveal-card"> <div class="first-content">
                        <img src="<?= $coverUrl ?>" alt="Cover: <?= htmlspecialchars($ebook['judul']) ?>" class="uiverse-reveal-card-image">
                      </div>
                      <div class="second-content">
                        <div class="uiverse-reveal-card-details">
                            <h6 class="uiverse-reveal-title text-xs " title="<?= htmlspecialchars($ebook['judul']) ?>">
                                <?= htmlspecialchars($ebook['judul']) ?>
                            </h6>
                            <p class="uiverse-reveal-author small">
                                Oleh: <?= htmlspecialchars($ebook['penulis'] ?? 'N/A') ?>
                            </p>
                            <a href="<?= $ebookFileLink ?>" class="btn btn-light btn-sm mt-2">
                                <i class="fas fa-book-open me-1"></i> Baca
                            </a>
                        </div>
                      </div>
                    </div>
                    </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <?php
                    $notFoundPath = __DIR__ . '/../_not_found_animation.php';
                    if (file_exists($notFoundPath)) {
                        include $notFoundPath;
                    } else {
                        echo "<p class='text-muted'>(Animasi buku tidak ditemukan)</p>";
                    }
                    ?>
                    <h4 class="fw-bold mt-3" style="color: #555;">Oops! E-Book Tidak Ditemukan</h4>
                    <p class="text-muted">Belum ada e-book yang sesuai dengan pencarian atau filter Anda.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
if (isset($stmtEbooks) && $stmtEbooks instanceof mysqli_stmt) $stmtEbooks->close();
include_once __DIR__ . '/../../../layout/footer.php';
?>