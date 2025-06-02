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
        <div>
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#aiModal">
                <i class="fas fa-robot me-1"></i> Tanya AI
            </button>
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
                    height="100%" <?php /* Mengambil 100% dari tinggi parent (flipbook-viewer-wrapper) */ ?>
                    webgl="true"
                    backgroundcolor="#C0C0C0" <?php /* Warna latar flipbook, bisa beda dari page */ ?>
                ></div>
            </div>
        </div>
    </div>

</div> <?php /* End container-fluid */ ?>

<div class="modal fade" id="aiModal" tabindex="-1" aria-labelledby="aiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiModalLabel"><i class="fas fa-robot me-2"></i>Fitur AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="aiPrompt" class="form-label">Ajukan pertanyaan terkait E-Book ini:</label>
                    <textarea class="form-control" id="aiPrompt" rows="3" placeholder="Contoh: Jelaskan tentang bab ini"></textarea>
                </div>
                <div class="mb-3">
                    <button id="askAiButton" class="btn btn-primary"><i class="fas fa-question-circle me-1"></i> Ajukan</button>
                </div>
                <div id="aiResponse" class="mt-3 border p-3 rounded bg-light" style="white-space: pre-wrap; overflow-y: auto; max-height: 300px;">
                    </div>
                <div id="aiLoading" class="mt-2 text-center" style="display:none;">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Memproses...</span>
                </div>
                <div id="aiError" class="mt-2 text-danger" style="display:none;">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

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

<script>
    $(document).ready(function() {
        const aiModal = $('#aiModal');
        const aiPromptInput = $('#aiPrompt');
        const askAiButton = $('#askAiButton');
        const aiResponseDiv = $('#aiResponse');
        const aiLoadingDiv = $('#aiLoading');
        const aiErrorDiv = $('#aiError');

        askAiButton.on('click', function() {
            const prompt = aiPromptInput.val();
            if (prompt.trim() !== "") {
                aiResponseDiv.empty();
                aiLoadingDiv.show();
                aiErrorDiv.hide();

                // Kirim permintaan ke backend AI Anda
                $.ajax({
                    url: 'gemini_api.php', // Pastikan path ini benar
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({ prompt: prompt }),
                    success: function(data) {
                        aiLoadingDiv.hide();
                        if (data.response) {
                            aiResponseDiv.html(data.response);
                        } else if (data.error) {
                            aiErrorDiv.text('Error: ' + data.error).show();
                        } else {
                            aiErrorDiv.text('Error: Respon tidak valid dari server.').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        aiLoadingDiv.hide();
                        aiErrorDiv.text('Error: ' + error).show();
                        console.error("Error fetching AI response:", error);
                    }
                });
            } else {
                alert('Silakan masukkan pertanyaan Anda.');
            }
        });

        aiModal.on('hidden.bs.modal', function() {
            aiPromptInput.val(''); // Kosongkan input saat modal ditutup
            aiResponseDiv.empty(); // Kosongkan respon
            aiErrorDiv.hide(); // Sembunyikan error
        });
    });
</script>

<?php
// Sertakan footer layout utama Anda
// Path dari Dashboard/Siswa/ ke layout/footer.php
include_once __DIR__ . '/../../../layout/footer.php';
?>