<?php
// flipbook.php (Viewer E-Book dengan layout standar - TANPA MODAL AI INTERNAL)

// AKTIFKAN ERROR REPORTING UNTUK DEBUGGING (HAPUS/NONAKTIFKAN DI PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Blok PHP untuk menangani permintaan API Gemini internal SUDAH DIHAPUS.
// Logika API akan ditangani oleh gemini_api.php yang dipanggil dari halaman AI terpisah.

session_start(); // Penting jika header.php atau logika lain memerlukannya

// Mendapatkan nama file PDF dari query string dan membersihkannya
$pdfFile = isset($_GET['file']) ? basename(urldecode($_GET['file'])) : '';

// Set judul untuk header.php (akan digunakan di header.php jika $title di sana)
$title = "Baca: " . htmlspecialchars(str_replace(['_', '-'], ' ', pathinfo($pdfFile, PATHINFO_FILENAME)));

// --- PATH KONFIGURASI (SESUAIKAN DENGAN STRUKTUR FOLDER ANDA!) ---
$baseWebUrlToPdfDir = '/libtera/uploads/ebook/assets/books/';
$pdfUrlForDFLIP = $baseWebUrlToPdfDir . rawurlencode($pdfFile);

// Path server absolut ke direktori PDF.
// __DIR__ adalah direktori tempat file flipbook.php ini berada (misal: .../ebooks/)
// Sesuaikan '../..' sesuai kedalaman flipbook.php dari root '/libtera/'
// Contoh: jika flipbook.php di /libtera/Dashboard/Siswa/ebooks/, maka '../../../' akan ke /libtera/
$baseServerPathToPdfDir = realpath(__DIR__ . '/../../../uploads/ebook/assets/books/'); // PERIKSA PATH INI!
$filePathOnServer = $baseServerPathToPdfDir ? realpath($baseServerPathToPdfDir . DIRECTORY_SEPARATOR . $pdfFile) : false;

$dflipAssetsBaseUrl = '/libtera/uploads/ebook/assets/'; // Path URL ke aset dflip
// --- AKHIR PATH KONFIGURASI ---


// Periksa apakah file ada dan valid
// Path ke header dan footer untuk halaman error (jika file PDF tidak ditemukan)
$pathToHeaderForError = realpath(__DIR__ . '/../../../layout/header.php'); // PERIKSA PATH INI!
$pathToFooterForError = realpath(__DIR__ . '/../../../layout/footer.php'); // PERIKSA PATH INI!

if (empty($pdfFile) || !$filePathOnServer || !is_file($filePathOnServer) || strpos($filePathOnServer, $baseServerPathToPdfDir) !== 0) {
    if($pathToHeaderForError && file_exists($pathToHeaderForError)) include_once $pathToHeaderForError;
    else echo "Error: Header file not found for error page.";

    echo '<div class="container mt-5"><div class="alert alert-danger"><h1>Error E-Book</h1><p>Maaf, E-Book "'.htmlspecialchars($pdfFile).'" tidak dapat ditemukan atau tidak valid.</p>';
    if (!$baseServerPathToPdfDir) {
        echo "<p>Debug: Path dasar server ke direktori PDF (\$baseServerPathToPdfDir) tidak valid.</p>";
    } elseif (!$filePathOnServer || !is_file($filePathOnServer)) {
        echo "<p>Debug: File PDF tidak ditemukan di server pada path: " . htmlspecialchars($baseServerPathToPdfDir . DIRECTORY_SEPARATOR . $pdfFile) . "</p>";
    } elseif (strpos($filePathOnServer, $baseServerPathToPdfDir) !== 0) {
        echo "<p>Debug: File berada di luar direktori yang diizinkan.</p>";
    }
    echo '<p><a href="ebook.php" class="btn btn-primary">Kembali ke Daftar E-Book</a></p></div></div>';
    
    if($pathToFooterForError && file_exists($pathToFooterForError)) include_once $pathToFooterForError;
    else echo "</body></html>";
    exit;
}

// Sertakan header layout utama Anda
// Path dari Dashboard/Siswa/ebooks/ ke layout/header.php adalah '../../../layout/header.php'
$pathToHeader = realpath(__DIR__ . '/../../../layout/header.php'); // PERIKSA PATH INI!
if ($pathToHeader && file_exists($pathToHeader)) {
    include_once $pathToHeader;
} else {
    die("ERROR Kritis: File header.php tidak ditemukan. Periksa path di flipbook.php. Path yang dicari: " . htmlspecialchars(__DIR__ . '/../../../layout/header.php'));
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-3 pt-3 pt-md-0">
        <h1 class="h4 mb-0 text-truncate" title="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></h1>
        <div>
            <a href="tanya_ai.html" target="_blank" class="btn btn-primary me-2">
                <i class="fas fa-robot me-1"></i> Tanya AI
            </a>
            <a href="ebook.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar E-Book
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-2 p-md-3">
            <div id="flipbook-viewer-wrapper" style="width: 100%; height: 75vh; position: relative; background-color: #f0f0f0;">
                <div
                    class="_df_book"
                    id="df_manual_book"
                    source="<?php echo htmlspecialchars($pdfUrlForDFLIP); ?>"
                    height="100%"
                    webgl="true"
                    backgroundcolor="#C0C0C0"
                ></div>
            </div>
        </div>
    </div>
</div>

<?php
// MODAL BOOTSTRAP SUDAH DIHAPUS
?>

<?php
// Sertakan JavaScript yang dibutuhkan dFlip
?>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/jquery.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/three.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/compatibility.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/mockup.min.js'); ?>" type="text/javascript"></script>

<script type="text/javascript">
    window.PDFJS_WORKER_SRC = '<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/pdf.worker.min.js'); ?>';
</script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/libs/pdf.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'js/dflip.min.js'); ?>" type="text/javascript"></script>

<?php
// JAVASCRIPT UNTUK MODAL DAN AJAX AI SUDAH DIHAPUS DARI SINI
// Logika AJAX akan ada di tanya_ai.html
?>

<?php
// Sertakan footer layout utama Anda
$pathToFooter = realpath(__DIR__ . '/../../../layout/footer.php'); // PERIKSA PATH INI!
if ($pathToFooter && file_exists($pathToFooter)) {
    include_once $pathToFooter;
} else {
    die("ERROR Kritis: File footer.php tidak ditemukan. Periksa path di flipbook.php. Path yang dicari: " . htmlspecialchars(__DIR__ . '/../../../layout/footer.php'));
}
?>