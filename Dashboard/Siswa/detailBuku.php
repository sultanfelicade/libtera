<?php
session_start(); // Harus ada di baris paling awal

require "../../connect.php"; // Koneksi ke database Anda

// Ambil dan Hapus pesan untuk RATING (ini untuk notifikasi HTML biasa, BUKAN SweetAlert)
$rating_pesan_sukses_html = $_SESSION['pesan_sukses'] ?? null;
$rating_pesan_error_html = $_SESSION['pesan_error'] ?? null;
// Hapus pesan dari session setelah dibaca agar tidak muncul lagi
if (isset($_SESSION['pesan_sukses'])) unset($_SESSION['pesan_sukses']);
if (isset($_SESSION['pesan_error'])) unset($_SESSION['pesan_error']);

// Ambil dan Hapus pesan untuk PEMINJAMAN (INI UNTUK ALERT JAVASCRIPT di footer.php)
$js_peminjaman_sukses_message = $_SESSION['peminjaman_sukses_message'] ?? null;
$js_peminjaman_error_message = $_SESSION['peminjaman_error_message'] ?? null;
$js_peminjaman_info_message = $_SESSION['peminjaman_info_message'] ?? null;

if (isset($_SESSION['peminjaman_sukses_message'])) unset($_SESSION['peminjaman_sukses_message']);
if (isset($_SESSION['peminjaman_error_message'])) unset($_SESSION['peminjaman_error_message']);
if (isset($_SESSION['peminjaman_info_message'])) unset($_SESSION['peminjaman_info_message']);


$buku = null;
$title = "Buku Tidak Ditemukan";
$id_buku = $_GET['id'] ?? null;
$id_siswa = $_SESSION['siswa']['id_siswa'] ?? null;

if (!$id_siswa) {
    $_SESSION['pesan_error_login'] = "Anda harus login untuk melihat detail buku dan memberi rating.";
    header("Location: /libtera/login.php");
    exit;
}

if ($id_buku) {
    $stmt = $connect->prepare("SELECT * FROM buku WHERE id_buku = ?");
    $stmt->bind_param("s", $id_buku);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $buku = $result->fetch_assoc();
        $title = "Detail Buku: " . htmlspecialchars($buku['judul']);
    }
    $stmt->close();
}


if (!$buku) {
    include_once __DIR__ . '/../../layout/header.php';
    if (isset($_SESSION['pesan_error_umum'])) {
        echo "<div class='container mt-3 pt-5'><div class='alert alert-danger'>".htmlspecialchars($_SESSION['pesan_error_umum'])."</div></div>";
        unset($_SESSION['pesan_error_umum']);
    }
    echo "<div class='container mt-3 pt-5 alert alert-danger'>Buku dengan ID tersebut tidak ditemukan.</div>";
    include_once __DIR__ . '/../../layout/footer.php';
    exit;
}

// Cek apakah siswa sudah memberi rating
$stmt_cek_rating = $connect->prepare("SELECT * FROM rating WHERE id_siswa = ? AND id_buku = ?");
$stmt_cek_rating->bind_param("is", $id_siswa, $id_buku);
$stmt_cek_rating->execute();
$existingRating = $stmt_cek_rating->get_result()->fetch_assoc();
$stmt_cek_rating->close();

// Cek apakah siswa sedang meminjam buku ini (status = 'PINJAM') - INI UNTUK LOGIKA RATING
$stmt_pinjam_untuk_rating = $connect->prepare("SELECT id_peminjaman FROM peminjaman WHERE id_siswa = ? AND id_buku = ? AND status = 'PINJAM'");
$stmt_pinjam_untuk_rating->bind_param("is", $id_siswa, $id_buku);
$stmt_pinjam_untuk_rating->execute();
$sedangDipinjamUntukRating = $stmt_pinjam_untuk_rating->get_result()->num_rows > 0;
$stmt_pinjam_untuk_rating->close();

// Cek apakah siswa pernah meminjam dan MENGEMBALIKAN buku ini - UNTUK LOGIKA RATING
$stmt_pernah_kembali = $connect->prepare("SELECT COUNT(*) as jumlah_kembali FROM peminjaman WHERE id_siswa = ? AND id_buku = ? AND status = 'KEMBALI'");
$stmt_pernah_kembali->bind_param("is", $id_siswa, $id_buku);
$stmt_pernah_kembali->execute();
$hasil_pernah_kembali = $stmt_pernah_kembali->get_result()->fetch_assoc();
$jumlahPernahKembali = $hasil_pernah_kembali ? (int)$hasil_pernah_kembali['jumlah_kembali'] : 0;
$stmt_pernah_kembali->close();

// Tentukan apakah siswa boleh memberi rating
$bolehMemberiRating = ($sedangDipinjamUntukRating || $jumlahPernahKembali > 0);

// --- BARU: Cek status peminjaman buku ini (PINJAM atau PENDING) untuk kondisi tombol ---
$statusPeminjamanUntukTombol = null; 
$stmt_cek_status_tombol = $connect->prepare(
    "SELECT status FROM peminjaman 
     WHERE id_siswa = ? AND id_buku = ? AND (status = 'PINJAM' OR status = 'PENDING') 
     LIMIT 1"
);
if ($stmt_cek_status_tombol) {
    $stmt_cek_status_tombol->bind_param("is", $id_siswa, $id_buku);
    $stmt_cek_status_tombol->execute();
    $result_status_tombol = $stmt_cek_status_tombol->get_result();
    if ($row_status_tombol = $result_status_tombol->fetch_assoc()) {
        $statusPeminjamanUntukTombol = $row_status_tombol['status'];
    }
    $stmt_cek_status_tombol->close();
} else {
    // Handle error jika prepare statement gagal, misalnya log error
    error_log("Gagal prepare statement untuk cek status tombol: " . $connect->error);
}
// --- AKHIR BLOK BARU ---


// Proses insert / update rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai_rating'])) {
    if ($bolehMemberiRating) {
        $rating = (int) $_POST['nilai_rating'];
        if ($rating >= 1 && $rating <= 5) {
            if ($existingRating) {
                $query = "UPDATE rating SET nilai_rating = ? WHERE id_siswa = ? AND id_buku = ?";
                $stmt_update = $connect->prepare($query);
                $stmt_update->bind_param("iis", $rating, $id_siswa, $id_buku);
            } else {
                $query = "INSERT INTO rating (id_siswa, id_buku, nilai_rating) VALUES (?, ?, ?)";
                $stmt_update = $connect->prepare($query);
                $stmt_update->bind_param("isi", $id_siswa, $id_buku, $rating);
            }

            if (isset($stmt_update)) {
                if (!$stmt_update->execute()) {
                    error_log("Query Gagal Update/Insert Rating: " . $stmt_update->error . " untuk siswa $id_siswa, buku $id_buku");
                    $_SESSION['pesan_error'] = "Terjadi kesalahan internal saat menyimpan rating.";
                } else {
                    $_SESSION['pesan_sukses'] = "Rating berhasil disimpan!";
                }
                $stmt_update->close();
            } else {
                $_SESSION['pesan_error'] = "Gagal menyiapkan statement untuk menyimpan rating.";
            }
            header("Location: detailBuku.php?id=$id_buku");
            exit;
        } else {
            $_SESSION['pesan_error'] = "Nilai rating yang diberikan tidak valid.";
            header("Location: detailBuku.php?id=$id_buku");
            exit;
        }
    } else {
        $_SESSION['pesan_error'] = "Anda belum memenuhi syarat untuk memberikan rating pada buku ini.";
        header("Location: detailBuku.php?id=$id_buku");
        exit;
    }
}

// Ambil rata-rata rating buku
$stmt_avg = $connect->prepare("SELECT ROUND(AVG(nilai_rating), 1) as avg_rating FROM rating WHERE id_buku = ?");
$stmt_avg->bind_param("s", $id_buku);
$stmt_avg->execute();
$avg_result = $stmt_avg->get_result()->fetch_assoc();
$avg = $avg_result && $avg_result['avg_rating'] !== null ? $avg_result['avg_rating'] : "Belum ada rating";
$stmt_avg->close();

include_once __DIR__ . '/../../layout/header.php';

if ($rating_pesan_error_html) {
    echo "<div class='container mt-3 pt-5'><div class='alert alert-danger'>".htmlspecialchars($rating_pesan_error_html)."</div></div>";
}
if ($rating_pesan_sukses_html) {
    echo "<div class='container mt-3 pt-5'><div class='alert alert-success'>".htmlspecialchars($rating_pesan_sukses_html)."</div></div>";
}
?>

<div class="container mt-5 pt-5">
  <div class="row">
    <div class="col-md-4 text-center mb-4 mb-md-0">
      <img src="/libtera/uploads/books/<?= htmlspecialchars($buku['cover']) ?>" class="img-fluid rounded shadow-sm" style="max-height: 450px; object-fit: contain;" alt="Cover Buku: <?= htmlspecialchars($buku['judul']) ?>">
    </div>
    <div class="col-md-8">
      <h2 class="fw-bold"><?= htmlspecialchars($buku['judul']) ?></h2>
      <p class="text-muted mb-1">Pengarang: <?= htmlspecialchars($buku['pengarang']) ?></p>
      <p class="text-muted mb-1">Penerbit: <?= htmlspecialchars($buku['penerbit']) ?>, <?= htmlspecialchars($buku['tahun_terbit']) ?></p>
      <p class="mb-3"><i class="fa-solid fa-star" style="color: #FFD43B;"></i> Rating: <?= $avg ?> / 5</p>

      <h5 class="mt-4">Sinopsis</h5>
      <p class="text-secondary" style="text-align: justify;"><?= nl2br(htmlspecialchars($buku['deskripsi'])) ?></p>

      <div class="mt-4">
          <?php if ($statusPeminjamanUntukTombol === 'PINJAM'): ?>
            <button class="btn btn-secondary" disabled><i class="fas fa-book-reader me-2"></i>Sedang Dipinjam</button>
          <?php elseif ($statusPeminjamanUntukTombol === 'PENDING'): ?>
            <button class="btn btn-info" disabled><i class="fas fa-clock me-2"></i>Menunggu Persetujuan</button>
          <?php else: // null, berarti tidak ada PENDING atau PINJAM untuk buku ini oleh siswa ini ?>
            <a href="/libtera/Dashboard/siswa/formPeminjaman/prosesPeminjaman.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary"><i class="fas fa-hand-holding-heart me-2"></i>Pinjam Sekarang</a>
          <?php endif; ?>
          </div>


      <div class="mt-4 border-top pt-3">
        <h5>Beri Rating Buku Ini</h5>
        <?php if ($bolehMemberiRating): ?>
            <form method="post" id="ratingForm" class="mt-2">
                <div class="d-flex gap-1 rating-buttons"> <?php
                    $nilaiSudahAda = null;
                    if (isset($existingRating) && is_array($existingRating) && isset($existingRating['nilai_rating'])) {
                        $nilaiSudahAda = (int)$existingRating['nilai_rating'];
                    }
                    for ($i = 1; $i <= 5; $i++):
                    ?>
                        <button
                            type="submit"
                            name="nilai_rating"
                            value="<?= $i ?>"
                            class="star-btn btn btn-outline-secondary border-0 p-1 <?= ($nilaiSudahAda && $i <= $nilaiSudahAda) ? 'text-warning' : 'text-muted' ?> focus:outline-none fs-4 transition-colors duration-300"
                            aria-label="Beri rating <?= $i ?> bintang"
                            data-rating-value="<?= $i ?>"
                            title="Beri <?= $i ?> bintang"
                        >
                            <i class="fa-solid fa-star"></i> </button>
                    <?php endfor; ?>
                </div>
            </form>

            <?php if ($existingRating): ?>
              <p class="text-success fw-semibold mt-2 small">
                <i class="fas fa-check-circle me-1"></i>Kamu sudah memberi rating: <?= htmlspecialchars($existingRating['nilai_rating']) ?> <i class="fa-solid fa-star text-warning"></i>
              </p>
            <?php else: ?>
              <p class="text-muted mt-2 small">Klik bintang untuk memberi rating.</p>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-muted mt-2 small">
                <?php
                // Variabel $sedangDipinjamUntukRating digunakan di sini, bukan $statusPeminjamanUntukTombol
                if (!$sedangDipinjamUntukRating && $jumlahPernahKembali == 0) {
                    echo "Anda harus meminjam buku ini terlebih dahulu untuk dapat memberikan rating.";
                } elseif ($sedangDipinjamUntukRating && !$existingRating) {
                    echo "Anda dapat memberikan rating setelah selesai membaca buku ini atau jika sudah pernah mengembalikannya.";
                } elseif ($existingRating) {
                    echo "Anda sudah memberikan rating untuk buku ini.";
                } else {
                    echo "Anda belum memenuhi syarat untuk memberikan rating pada buku ini.";
                }
                ?>
            </p>
        <?php endif; ?>
      </div> 
    </div> 
  </div> 
</div> 
<script>
    var globalPeminjamanSuksesMsg = <?= $js_peminjaman_sukses_message ? json_encode($js_peminjaman_sukses_message) : 'null' ?>;
    var globalPeminjamanErrorMsg = <?= $js_peminjaman_error_message ? json_encode($js_peminjaman_error_message) : 'null' ?>;
    var globalPeminjamanInfoMsg = <?= $js_peminjaman_info_message ? json_encode($js_peminjaman_info_message) : 'null' ?>;
</script>

<?php
include_once __DIR__ . '/../../layout/footer.php';
?>