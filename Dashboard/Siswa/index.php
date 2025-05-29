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

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="category-scroll-container">
        <div class="category-list">
                <a href="?" class="btn btn-<?= !$kategoriId && empty($searchTerm) ? 'primary' : 'outline-primary' ?>">Semua</a>
                <?php mysqli_data_seek($kategoriQuery, 0); // Reset pointer query kategori ?>
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

    <h4 class="fw-bold h2 mb-4">
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
                                <img src="/libtera/assets/<?= htmlspecialchars($buku['cover']) ?>" alt="Cover: <?= htmlspecialchars($buku['judul']) ?>">
                            </div>
                            
                            <div class="flip-card-back">
                                <h6 class="book-title text-truncate" title="<?= htmlspecialchars($buku['judul']) ?>"><?= htmlspecialchars($buku['judul']) ?></h6>
                                <p class="book-author mb-2"><?= htmlspecialchars($buku['pengarang']) ?></p>
                                <div class="book-rating mb-3">
                                    <i class="fa-solid fa-star"></i>
                                    <span><?= number_format($buku['rata_rating'], 1) ?>/5</span>
                                </div>
                                <a href="detailBuku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-light btn-sm w-100">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 d-flex flex-column align-items-center justify-content-center mt-4">
                <?php 
                    // Panggil file animasi yang sudah kita buat
                    include '_not_found_animation.php'; 
                ?>
                <h4 class="fw-bold mt-4" style="color: #555;">Oops! Buku Tidak Ditemukan</h4>
                <p class="text-muted">Coba gunakan kata kunci atau filter kategori yang berbeda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php 
// Selalu tutup statement setelah selesai
$stmt->close();
include_once __DIR__ . '/../../layout/footer.php'; 
?>