<?php
session_start();



require "../../connect.php"; // Pastikan file ini menggunakan MySQLi Object-Oriented ($connect = new mysqli(...))

// --- PERBAIKAN DIMULAI DI SINI ---

// 1. Inisialisasi variabel untuk menghindari error jika buku tidak ditemukan
$buku = null;
$title = "Buku Tidak Ditemukan"; 
$id_buku = $_GET['id'] ?? null; // Gunakan null coalescing operator untuk keamanan
$id_siswa = $_SESSION['siswa']['id_siswa'];

if ($id_buku) {
    // 2. Ambil data buku menggunakan PREPARED STATEMENT
    // Ini memperbaiki keamanan (mencegah SQL Injection)
    $stmt = $connect->prepare("SELECT * FROM buku WHERE id_buku = ?");
    $stmt->bind_param("s", $id_buku); // 's' karena ID dari GET selalu string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $buku = $result->fetch_assoc();
        // 3. Set judul SETELAH mendapatkan data buku
        // Ganti 'judul' dengan nama kolom judul buku Anda jika berbeda
        $title = "Detail Buku: " . htmlspecialchars($buku['judul']);
    }
    $stmt->close();
}

// Jika buku tidak ditemukan setelah query, hentikan eksekusi.
if (!$buku) {
    // Sertakan header sebelum menampilkan pesan error
    include_once __DIR__ . '/../../layout/header.php';
    echo "<div class='container alert alert-danger'>Buku dengan ID tersebut tidak ditemukan.</div>";
    // Sertakan footer jika ada
    // include_once __DIR__ . '/../../layout/footer.php';
    exit; // Hentikan script
}


// Cek apakah siswa sudah memberi rating (Prepared Statement)
$stmt_cek = $connect->prepare("SELECT * FROM rating WHERE id_siswa = ? AND id_buku = ?");
$stmt_cek->bind_param("is", $id_siswa, $id_buku);
$stmt_cek->execute();
$existingRating = $stmt_cek->get_result()->fetch_assoc();
$stmt_cek->close();

// Proses insert / update rating (Prepared Statement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai_rating'])) {
    $rating = (int) $_POST['nilai_rating'];
    if ($rating >= 1 && $rating <= 5) {
        if ($existingRating) {
            // Ubah rating lama
            $query = "UPDATE rating SET nilai_rating = ? WHERE id_siswa = ? AND id_buku = ?";
            $stmt_update = $connect->prepare($query);
            $stmt_update->bind_param("iis", $rating, $id_siswa, $id_buku);
        } else {
            // Kasih rating baru
            $query = "INSERT INTO rating (id_siswa, id_buku, nilai_rating) VALUES (?, ?, ?)";
            $stmt_update = $connect->prepare($query);
            $stmt_update->bind_param("isi", $id_siswa, $id_buku, $rating);
        }

        if (!$stmt_update->execute()) {
            die("Query Gagal: " . $stmt_update->error);
        }
        $stmt_update->close();

        header("Location: detailBuku.php?id=$id_buku");
        exit;
    }
}

// Ambil rata-rata rating (Prepared Statement)
$stmt_avg = $connect->prepare("SELECT ROUND(AVG(nilai_rating), 1) as avg_rating FROM rating WHERE id_buku = ?");
$stmt_avg->bind_param("s", $id_buku);
$stmt_avg->execute();
$avg = $stmt_avg->get_result()->fetch_assoc()['avg_rating'] ?? 0;
$stmt_avg->close();

// Cek apakah siswa sedang meminjam buku ini (Prepared Statement)
$stmt_pinjam = $connect->prepare("SELECT * FROM peminjaman WHERE id_siswa = ? AND id_buku = ? AND status = 'PINJAM'");
$stmt_pinjam->bind_param("is", $id_siswa, $id_buku);
$stmt_pinjam->execute();
$sudahDipinjam = $stmt_pinjam->get_result()->num_rows > 0;
$stmt_pinjam->close();

// Sekarang panggil header, di mana file header.php akan menggunakan variabel $title
include_once __DIR__ . '/../../layout/header.php';
?>

<div class="container mt-5 pt-5">
  <div class="row">
    <div class="col-md-4 text-center">
      <img src="/libtera/uploads/cover/<?= htmlspecialchars($buku['cover']) ?>" class="img-fluid rounded" alt="cover buku">
    </div>
    <div class="col-md-8">
      <h2 class="fw-bold"><?= htmlspecialchars($buku['judul']) ?></h2>
      <p class="text-muted"><?= htmlspecialchars($buku['pengarang']) ?></p>
      <p class="mb-2"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> <?= $avg ?> &nbsp; | &nbsp; <?= htmlspecialchars($buku['penerbit']) ?>, <?= htmlspecialchars($buku['tahun_terbit']) ?></p>
      <h5 class="mt-4">Sinopsis</h5>
      <p class="text-secondary"><?= htmlspecialchars($buku['deskripsi']) ?></p>

      <!-- Tombol Peminjaman -->
      <?php if ($sudahDipinjam): ?>
        <button class="btn btn-secondary mt-3" disabled>Sudah Dipinjam</button>
      <?php else: ?>
        <a href="/libtera/Dashboard/siswa/formPeminjaman/prosesPeminjaman.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary mt-3">Pinjam Sekarang</a>
      <?php endif; ?>

      <!-- Bagian Rating -->
      <div class="mt-4">
        <h5>Rating Buku</h5>
        <p class="mb-1"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> <?= $avg ?> / 5</p>

        <form method="post" id="ratingForm" class="mt-2">
          <div class="flex gap-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button
                type="submit"
                name="nilai_rating"
                value="<?= $i ?>"
                class="star-btn relative <?= $existingRating && $i <= $existingRating['nilai_rating'] ? 'text-yellow-400' : 'text-gray-400' ?> hover:text-yellow-400 focus:outline-none text-3xl transition-colors duration-300"
                aria-label="Beri rating <?= $i ?> bintang"
              >
                ★
              </button>
            <?php endfor; ?>
          </div>
        </form>

        <?php if ($existingRating): ?>
          <p class="text-success fw-semibold mt-3">
            Kamu sudah memberi rating: <span class="text-yellow-400"><?= $existingRating['nilai_rating'] ?> <i class="fa-solid fa-star" style="color: #FFD43B;"></i></span>
          </p>
        <?php else: ?>
          <p class="text-muted mt-3">Klik bintang untuk memberi rating.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
  .star-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    user-select: none;
  }
</style>

<?php include_once __DIR__ . '/../../layout/footer.php'; ?>
