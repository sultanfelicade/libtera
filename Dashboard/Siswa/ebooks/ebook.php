<?php
// ebook.php (Halaman untuk menampilkan DAFTAR E-Book)
session_start();
$title = "Daftar E-Book - Libtera";
include_once __DIR__ . '/../../../layout/header.php'; // Path dari Dashboard/Siswa/ ke layout/
require __DIR__ . '/../../../connect.php';         // Path dari Dashboard/Siswa/ ke connect.php

// Asumsi tabel 'ebooks' (id_ebook, judul, penulis, deskripsi, file_path, id_kategori, cover_ebook)
// Asumsi tabel 'kategori' (id_kategori, nama_kategori) digunakan juga untuk e-book

// Ambil data kategori untuk filter
$kategoriEbookQuery = mysqli_query($connect, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

// Ambil parameter dari URL
$kategoriId = isset($_GET['kategori']) && is_numeric($_GET['kategori']) ? (int)$_GET['kategori'] : null;
$searchTerm = $_GET['search'] ?? '';

// Bangun query SQL
// Pastikan 'file_path' di database HANYA berisi nama file PDF (misal: "buku_hebat.pdf")
// dan 'cover_ebook' HANYA berisi nama file gambar cover (misal: "cover_hebat.jpg")
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

$stmtEbooks = $connect->prepare($sqlEbooks);
if (!empty($params)) {
    $stmtEbooks->bind_param($types, ...$params);
}
$stmtEbooks->execute();
$resultEbooks = $stmtEbooks->get_result();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Daftar E-Book</h1>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="category-scroll-container">
            <div class="category-list">
                <a href="ebook.php" class="btn btn-<?= !$kategoriId && empty($searchTerm) ? 'primary' : 'outline-primary' ?>">Semua</a>
                <?php if ($kategoriEbookQuery && mysqli_num_rows($kategoriEbookQuery) > 0): mysqli_data_seek($kategoriEbookQuery, 0); ?>
                    <?php while($kategori = mysqli_fetch_assoc($kategoriEbookQuery)): ?>
                        <a href="ebook.php?kategori=<?= $kategori['id_kategori'] ?>" class="btn btn-<?= ($kategoriId == $kategori['id_kategori']) ? 'primary' : 'outline-primary' ?>">
                            <?= htmlspecialchars($kategori['nama_kategori']) ?>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="input-container">
            <form method="GET" action="ebook.php">
                <?php if ($kategoriId): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategoriId) ?>">
                <?php endif; ?>
                <input class="form-control" <?php /* Ganti class 'input' menjadi 'form-control' agar styling Bootstrap berlaku */ ?>
                       type="search" name="search" placeholder="Cari E-Book..." value="<?= htmlspecialchars($searchTerm) ?>">
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4">
        <?php if($resultEbooks && $resultEbooks->num_rows > 0): ?>
            <?php while($ebook = $resultEbooks->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <?php
                        $coverUrl = '/libtera/assets/default_ebook_cover.png'; // Gambar default
                        if (!empty($ebook['cover_ebook'])) {
                            // Path ke cover, asumsikan file cover ada di folder khusus untuk cover ebook
                            // Misalnya: /libtera/uploads/ebook/covers/namafilecover.jpg
                            $potentialCoverPath = '/libtera/uploads/ebook/covers/' . htmlspecialchars($ebook['cover_ebook']);
                            // Anda mungkin perlu memeriksa file_exists di sini jika ingin lebih aman
                            // if (file_exists($_SERVER['DOCUMENT_ROOT'] . $potentialCoverPath)) {
                            //    $coverUrl = $potentialCoverPath;
                            // }
                             $coverUrl = $potentialCoverPath; // Untuk sekarang, asumsikan path selalu benar jika ada isinya
                        }
                        ?>
                        <img src="<?= $coverUrl ?>" class="card-img-top" alt="Cover: <?= htmlspecialchars($ebook['judul']) ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-truncate" title="<?= htmlspecialchars($ebook['judul']) ?>"><?= htmlspecialchars($ebook['judul']) ?></h6>
                            <p class="card-text small text-muted mb-1">Penulis: <?= htmlspecialchars($ebook['penulis'] ?? 'N/A') ?></p>
                            <p class="card-text small text-muted mb-2">Kategori: <?= htmlspecialchars($ebook['nama_kategori'] ?? 'N/A') ?></p>
                            <?php /* <p class="card-text small flex-grow-1 text-truncate" title="<?= htmlspecialchars($ebook['deskripsi'] ?? '') ?>"><?= htmlspecialchars(substr($ebook['deskripsi'] ?? '', 0, 70)) . (strlen($ebook['deskripsi'] ?? '') > 70 ? '...' : '') ?></p> */ ?>
                            <a href="flipbook.php?file=<?= urlencode($ebook['file_path']) ?>" class="btn btn-primary btn-sm mt-auto w-100">
                                <i class="fas fa-book-open me-1"></i> Baca Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <?php include '../_not_found_animation.php'; ?>
                    <h4 class="fw-bold" style="color: #555;">Oops! E-Book Tidak Ditemukan</h4>
                    <p class="text-muted">Belum ada e-book yang sesuai dengan pencarian atau filter Anda.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
if (isset($stmtEbooks)) $stmtEbooks->close();
if (isset($kategoriEbookQuery)) mysqli_free_result($kategoriEbookQuery); // Bebaskan hasil query kategori
include_once __DIR__ . '/../../../layout/footer.php';
?>