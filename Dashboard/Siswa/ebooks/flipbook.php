<?php
// flipbook.php (Viewer E-Book dengan layout standar)
session_start(); // Penting jika header.php atau logika lain memerlukannya

// Mendapatkan nama file PDF dari query string dan membersihkannya
$pdfFile = isset($_GET['file']) ? basename(urldecode($_GET['file'])) : '';

// Set judul untuk header.php
$title = "Baca: " . htmlspecialchars(str_replace(['_', '-'], ' ', pathinfo($pdfFile, PATHINFO_FILENAME))); // Judul lebih rapi

// --- PATH KONFIGURASI (Sama seperti sebelumnya) ---
$baseWebUrlToPdfDir = '/libtera/uploads/ebook/assets/books/';
$pdfUrlForDFLIP = $baseWebUrlToPdfDir . rawurlencode($pdfFile);
$baseServerPathToPdfDir = __DIR__ . '/../../../uploads/ebook/assets/books/';
$filePathOnServer = realpath($baseServerPathToPdfDir . $pdfFile);
$dflipAssetsBaseUrl = '/libtera/uploads/ebook/assets/';
// --- AKHIR PATH KONFIGURASI ---

// Periksa apakah file ada dan valid (Sama seperti sebelumnya)
if (empty($pdfFile) || !$filePathOnServer || strpos($filePathOnServer, realpath($baseServerPathToPdfDir)) !== 0) {
    // Jika error, mungkin lebih baik redirect atau tampilkan pesan dalam layout standar
    $_SESSION['error_message'] = "File E-Book tidak ditemukan atau tidak valid.";
    // header("Location: ebook.php"); // Redirect kembali ke daftar ebook
    // exit;
    // Atau, jika ingin menampilkan pesan error di halaman ini dengan layout:
    include_once __DIR__ . '/../../../layout/header.php';
    echo '<div class="container mt-5"><div class="alert alert-danger"><h1>Error</h1><p>Maaf, E-Book yang Anda cari tidak dapat ditemukan atau tidak valid.</p><p><a href="ebook.php" class="btn btn-primary">Kembali ke Daftar E-Book</a></p></div></div>';
    include_once __DIR__ . '/../../../layout/footer.php';
    exit;
}

// Sertakan header layout utama Anda
// Path dari Dashboard/Siswa/ ke layout/header.php
include_once __DIR__ . '/../../../layout/header.php';
?>

<div class="container-fluid"> <?php /* Atau ganti dengan class "container" jika ingin ada batas samping */ ?>
    <div class="d-flex justify-content-between align-items-center my-3 pt-3 pt-md-0">
        <h1 class="h4 mb-0 text-truncate" title="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></h1>
        <a href="ebook.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar E-Book
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-2 p-md-3">
            <div id="flipbook-viewer-wrapper" style="width: 100%; height: 75vh; position: relative; background-color: #f0f0f0;">
                <div
                    class="_df_book"
                    id="df_manual_book"
                    source="<?php echo htmlspecialchars($pdfUrlForDFLIP); ?>"
                    height="100%" <?php /* Mengambil 100% dari tinggi parent (flipbook-viewer-wrapper) */ ?>
                    webgl="true"
                    backgroundcolor="#f0f0f0" <?php /* Warna latar flipbook, bisa beda dari page */ ?>
                ></div>
            </div>
        </div>
    </div>

    </div> <?php /* End container-fluid */ ?>

<?php
// Sertakan JavaScript yang dibutuhkan dFlip (setelah konten utama)
// Path-path ini sudah absolut dari root web (/libtera/)
?>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/jquery.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/three.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/compatibility.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/mockup.min.js'); ?>" type="text/javascript"></script>

<script type="text/javascript">
    // PENTING: Atur path ke pdf.worker.min.js SEBELUM pdf.min.js dimuat.
    window.PDFJS_WORKER_SRC = '<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/pdf.worker.min.js'); ?>';
</script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/pdf.min.js'); ?>" type="text/javascript"></script>

<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/dflip.min.js'); ?>" type="text/javascript"></script>

<?php
// Sertakan footer layout utama Anda
// Path dari Dashboard/Siswa/ ke layout/footer.php
include_once __DIR__ . '/../../../layout/footer.php';
?>