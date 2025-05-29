<?php
// flipbook.php (Viewer untuk SATU E-Book)
// session_start(); // Jika perlu cek login, header.php mungkin sudah menanganinya
// include_once __DIR__ . '/../../../layout/header.php'; // Header TIDAK PERLU di sini, ini halaman full-screen

// Mendapatkan nama file PDF dari query string dan membersihkannya
$pdfFile = isset($_GET['file']) ? basename(urldecode($_GET['file'])) : '';

// --- PATH KONFIGURASI ---
// Base URL path untuk file PDF (dari root web /libtera/)
$baseWebUrlToPdfDir = '/libtera/uploads/ebook/assets/books/';
$pdfUrlForDFLIP = $baseWebUrlToPdfDir . rawurlencode($pdfFile);

// Path file sistem untuk memeriksa keberadaan file
// flipbook.php ada di /libtera/Dashboard/Siswa/flipbook.php
$baseServerPathToPdfDir = __DIR__ . '/../../../uploads/ebook/assets/books/';
$filePathOnServer = realpath($baseServerPathToPdfDir . $pdfFile);

// Path ke aset dFlip (dari root web /libtera/)
$dflipAssetsBaseUrl = '/libtera/uploads/ebook/assets/';
// --- AKHIR PATH KONFIGURASI ---

// Periksa apakah file ada dan berada dalam direktori yang diizinkan
if (empty($pdfFile) || !$filePathOnServer || strpos($filePathOnServer, realpath($baseServerPathToPdfDir)) !== 0) {
    die('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Error</title><style>body{font-family: Arial, sans-serif; padding: 20px; text-align:center;} h1{color:#cc0000;}</style></head><body><h1>File Tidak Ditemukan</h1><p>Maaf, E-Book yang Anda cari tidak dapat ditemukan atau tidak valid.</p><p><a href="javascript:history.back()">Kembali</a> atau <a href="javascript:window.close();">Tutup Halaman</a></p></body></html>');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Baca E-Book: <?php echo htmlspecialchars(basename($pdfFile)); ?></title>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />

    <link href="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'css/dflip.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo htmlspecialchars($dflipAssetsBaseUrl . 'css/themify-icons.min.css'); ?>" rel="stylesheet" type="text/css" />

    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background-color: #333; }
        ._df_book { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div
        class="_df_book"
        id="df_manual_book" <?php /* ID jika Anda ingin target manual via JS */ ?>
        source="<?php echo htmlspecialchars($pdfUrlForDFLIP); ?>"
        height="100%" <?php /* Atau "600" jika mau tinggi tetap */ ?>
        webgl="true"
        backgroundcolor="#333" <?php /* Atau ambil dari GET: isset($_GET['bgcolor']) ? htmlspecialchars(trim($_GET['bgcolor'])) : '#333' */ ?>
    ></div>

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
</body>
</html>