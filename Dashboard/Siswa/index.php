<?php
session_start();
$title = "Dasbor Siswa - Libtera";
include_once __DIR__ . '/../../layout/header.php';
require "../../connect.php";

// Ambil data kategori untuk tombol filter dinamis
$kategoriQuery = mysqli_query($connect, "SELECT * FROM kategori");

// 1. Ambil parameter dari URL dengan aman
$kategoriId = isset($_GET['kategori']) && is_numeric($_GET['kategori']) ? (int)$_GET['kategori'] : null;
$searchTerm = $_GET['search'] ?? '';

// 2. Bangun query SQL dasar
$sql = "
    SELECT b.*, COALESCE(ROUND(AVG(r.nilai_rating), 1), 0) AS rata_rating
    FROM buku b
    LEFT JOIN rating r ON b.id_buku = r.id_buku
";

// 3. Tambahkan kondisi WHERE secara dinamis untuk filter dan pencarian
$conditions = [];
$params = [];
$types = '';

if ($kategoriId) {
    $conditions[] = "b.id_kategori = ?";
    $params[] = $kategoriId;
    $types .= 'i';
}

if (!empty($searchTerm)) {
    $conditions[] = "(b.judul LIKE ? OR b.pengarang LIKE ?)";
    $likeTerm = "%" . $searchTerm . "%";
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $types .= 'ss';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// 4. Tambahkan GROUP BY dan ORDER BY berdasarkan rating tertinggi
$sql .= " GROUP BY b.id_buku ORDER BY rata_rating DESC, b.judul ASC";

// 5. Eksekusi query dengan PREPARED STATEMENT untuk keamanan
$stmt = $connect->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bukuQuery = $stmt->get_result();
?>

<style>
    .tulisan {
        display: flex;
        flex-direction: column;
        align-items: center; /* membuat isinya (h1 dan p) berada di tengah horizontal */
        justify-content: center;
        text-align: center; /* kalau mau teksnya juga rata tengah */
        margin: 300px;
        margin-top: 32px;
    }
    .tulisan h1{
        width: 500px;
        font-size: 32px;
    }
    .tulisan p{
        width: 500px;
    }
</style>
<h1 class="h1 mb-2 ms-4">Daftar Buku</h1>

<div class="container-fluid ps-4"> <!-- padding kiri -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-5">
        <div class="category-scroll-container w-70">
            <div class="category-list">
                <a href="?" class="btn btn-<?= !$kategoriId && empty($searchTerm) ? 'primary' : 'outline-primary' ?>">Semua</a>
                <?php mysqli_data_seek($kategoriQuery, 0); ?>
                <?php while($kategori = mysqli_fetch_assoc($kategoriQuery)): ?>
                    <a href="?kategori=<?= $kategori['id_kategori'] ?>" class="btn btn-<?= ($kategoriId == $kategori['id_kategori']) ? 'primary' : 'outline-primary' ?>">
                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="input-container">
            <form method="GET" action="">
                <input class="input" type="search" name="search" placeholder="Judul atau pengarang..." value="<?= htmlspecialchars($searchTerm) ?>">
            </form>
        </div>
    </div>

    <h4 class="fw-bold h2 mt-3 mb-3 text-start">
        <?php 
            if(!empty($searchTerm)) {
                echo 'Hasil Pencarian untuk: "' . htmlspecialchars($searchTerm) . '"';
            } else {
                echo 'Buku Paling Populer';
            }
        ?>
    </h4>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
        <?php if($bukuQuery->num_rows > 0): ?>
            <?php while($buku = $bukuQuery->fetch_assoc()): ?>
                <div class="col">
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <img src="/libtera/uploads/books/<?= htmlspecialchars($buku['cover']) ?>" alt="Cover: <?= htmlspecialchars($buku['judul']) ?>">
                            </div>
                            <div class="flip-card-back text-start">
                                <h6 class="book-title text-xs" title="<?= htmlspecialchars($buku['judul']) ?>"><?= htmlspecialchars($buku['judul']) ?></h6>
                                <p class="book-author mb-2"><?= htmlspecialchars($buku['pengarang']) ?></p>
                                <div class="book-rating mb-3">
                                    <i class="fa-solid fa-star"></i>
                                    <span><?= number_format($buku['rata_rating'], 1) ?>/5</span>
                                </div>
                                <a href="detailBuku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary btn-sm h-15">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="tulisan text-start ps-1 w-100" style="max-width: 500px; margin: 0 auto;">
                <?php include '_not_found_animation.php'; ?>
                <h1 class="fw-bold mt-4" style="color: #555; font-size: 2rem;">Oops! Buku Tidak Ditemukan</h1>
                <p class="text-muted">Coba gunakan kata kunci atau filter kategori yang berbeda.</p>
            </div>
            <style>
                @media (max-width: 600px) {
                    .tulisan {
                        margin: 24px 0 !important;
                        max-width: 95vw !important;
                    }
                    .tulisan h1, .tulisan p {
                        width: 100% !important;
                        font-size: 1.2rem !important;
                    }
                }
            </style>
        <?php endif; ?>
    </div>
</div>

<?php 
// Selalu tutup statement setelah selesai
$stmt->close();
include_once __DIR__ . '/../../layout/footer.php'; 
?>