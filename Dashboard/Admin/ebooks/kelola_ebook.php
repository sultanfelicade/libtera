<?php
ob_start(); // Mulai output buffering untuk mencegah error "headers already sent"
session_start();

// --- Simulasi Autentikasi Admin ---
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     $_SESSION['error_message'] = "Silakan login sebagai admin terlebih dahulu.";
//     header("Location: /libtera/admin/login.php"); // Sesuaikan dengan halaman login admin Anda
//     exit;
// }

$title = "Kelola E-Book - Libtera Admin";

// Sesuaikan path jika perlu, asumsi file ini ada di Dashboard/Admin/ebooks/
require __DIR__ . '/../../../connect.php';
include_once __DIR__ . '/../../../layout/header.php';

$message = ''; // Untuk notifikasi sukses atau error

// Direktori untuk upload
$ebookCoverUploadDir = __DIR__ . '/../../../uploads/ebook/assets/cover/';
$ebookFileUploadDir = __DIR__ . '/../../../uploads/ebook/assets/books/';

// Buat direktori jika belum ada
if (!is_dir($ebookCoverUploadDir)) {
    mkdir($ebookCoverUploadDir, 0777, true);
}
if (!is_dir($ebookFileUploadDir)) {
    mkdir($ebookFileUploadDir, 0777, true);
}

// --- Fungsi untuk mengambil daftar kategori ---
function getKategoriList($connect) {
    $kategoriResult = $connect->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
    $kategoriList = [];
    if ($kategoriResult) {
        while ($kategori = $kategoriResult->fetch_assoc()) {
            $kategoriList[] = $kategori;
        }
    } else {
        error_log("Gagal mengambil daftar kategori: " . $connect->error);
    }
    return $kategoriList;
}
$kategoriOptions = getKategoriList($connect);

// --- Fungsi untuk menghapus file ---
function deleteFile($filepath) {
    if (!empty($filepath) && file_exists($filepath)) {
        unlink($filepath);
    }
}

// --- BAGIAN PENGELOLAAN DATA (CREATE, UPDATE, DELETE) ---

// Proses Tambah E-Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ebook'])) {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    
    $cover_ebook_path = '';
    $file_ebook_path = '';

    // Validasi dasar
    if (empty($judul) || empty($penulis) || empty($id_kategori)) {
        $message = '<div class="alert alert-danger">Judul, Penulis, dan Kategori wajib diisi.</div>';
    } elseif (empty($_FILES['file_ebook']) || $_FILES['file_ebook']['error'] != UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">File E-Book (PDF) wajib diupload.</div>';
    } else {
        // Handle upload cover e-book (opsional)
        if (isset($_FILES['cover_ebook']) && $_FILES['cover_ebook']['error'] == UPLOAD_ERR_OK) {
            $coverTmpName = $_FILES['cover_ebook']['tmp_name'];
            $coverName = time() . '_' . basename($_FILES['cover_ebook']['name']);
            $coverAllowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['cover_ebook']['type'], $coverAllowedTypes)) {
                if (move_uploaded_file($coverTmpName, $ebookCoverUploadDir . $coverName)) {
                    $cover_ebook_path = $coverName;
                } else {
                    $message .= '<div class="alert alert-warning">Gagal mengupload file cover. Cover tidak akan disimpan.</div>';
                }
            } else {
                 $message .= '<div class="alert alert-warning">Tipe file cover tidak valid. Hanya JPEG, PNG, GIF, WEBP yang diizinkan. Cover tidak akan disimpan.</div>';
            }
        }

        // Handle upload file e-book (PDF)
        $ebookFileTmpName = $_FILES['file_ebook']['tmp_name'];
        $ebookFileName = time() . '_' . basename($_FILES['file_ebook']['name']);
        if ($_FILES['file_ebook']['type'] == 'application/pdf') {
            if (move_uploaded_file($ebookFileTmpName, $ebookFileUploadDir . $ebookFileName)) {
                $file_ebook_path = $ebookFileName; // Simpan nama file saja
            } else {
                $message .= '<div class="alert alert-danger">Gagal mengupload file e-book (PDF).</div>';
            }
        } else {
            $message .= '<div class="alert alert-danger">File E-Book harus berformat PDF.</div>';
        }
        

        if (!empty($file_ebook_path)) { // Hanya lanjut jika file PDF berhasil diupload
            $stmt = $connect->prepare("INSERT INTO ebooks (judul, penulis, id_kategori, deskripsi, cover_ebook, file_path) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssisss", $judul, $penulis, $id_kategori, $deskripsi, $cover_ebook_path, $file_ebook_path);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "E-Book \"".htmlspecialchars($judul)."\" berhasil ditambahkan!";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $message .= '<div class="alert alert-danger">Gagal menambahkan e-book: ' . $stmt->error . '</div>';
                    deleteFile($ebookCoverUploadDir . $cover_ebook_path); // Hapus cover jika gagal insert DB
                    deleteFile($ebookFileUploadDir . $file_ebook_path);   // Hapus file PDF jika gagal insert DB
                }
                $stmt->close();
            } else {
                 $message .= '<div class="alert alert-danger">Gagal menyiapkan statement: ' . $connect->error . '</div>';
                 deleteFile($ebookCoverUploadDir . $cover_ebook_path); 
                 deleteFile($ebookFileUploadDir . $file_ebook_path);
            }
        } else {
             // Pesan error upload file PDF sudah ditangani di atas
             if (!empty($cover_ebook_path)) deleteFile($ebookCoverUploadDir . $cover_ebook_path); // Hapus cover jika file PDF gagal dan cover terlanjur diupload
        }
    }
}

// Proses Edit E-Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ebook'])) {
    $id_ebook_edit = (int)$_POST['id_ebook'];
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    
    $current_cover = $_POST['current_cover_ebook'];
    $current_file = $_POST['current_file_ebook'];

    $new_cover_path = $current_cover;
    $new_file_path = $current_file;

    if (empty($judul) || empty($penulis) || empty($id_kategori)) {
        $message = '<div class="alert alert-danger">Judul, Penulis, dan Kategori wajib diisi.</div>';
    } else {
        // Handle upload cover e-book baru jika ada
        if (isset($_FILES['cover_ebook']) && $_FILES['cover_ebook']['error'] == UPLOAD_ERR_OK) {
            $coverTmpName = $_FILES['cover_ebook']['tmp_name'];
            $coverName = time() . '_' . basename($_FILES['cover_ebook']['name']);
            $coverAllowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (in_array($_FILES['cover_ebook']['type'], $coverAllowedTypes)) {
                if (move_uploaded_file($coverTmpName, $ebookCoverUploadDir . $coverName)) {
                    deleteFile($ebookCoverUploadDir . $current_cover); // Hapus cover lama
                    $new_cover_path = $coverName;
                } else {
                    $message .= '<div class="alert alert-warning">Gagal mengupload file cover baru. Cover lama tetap digunakan.</div>';
                }
            } else {
                $message .= '<div class="alert alert-warning">Tipe file cover baru tidak valid. Cover lama tetap digunakan.</div>';
            }
        }

        // Handle upload file e-book (PDF) baru jika ada
        if (isset($_FILES['file_ebook']) && $_FILES['file_ebook']['error'] == UPLOAD_ERR_OK) {
            $ebookFileTmpName = $_FILES['file_ebook']['tmp_name'];
            $ebookFileName = time() . '_' . basename($_FILES['file_ebook']['name']);

            if ($_FILES['file_ebook']['type'] == 'application/pdf') {
                if (move_uploaded_file($ebookFileTmpName, $ebookFileUploadDir . $ebookFileName)) {
                    deleteFile($ebookFileUploadDir . $current_file); // Hapus file PDF lama
                    $new_file_path = $ebookFileName;
                } else {
                    $message .= '<div class="alert alert-warning">Gagal mengupload file e-book (PDF) baru. File PDF lama tetap digunakan.</div>';
                }
            } else {
                 $message .= '<div class="alert alert-warning">File E-Book baru harus berformat PDF. File PDF lama tetap digunakan.</div>';
            }
        }
        
        // Update database
        $stmt = $connect->prepare("UPDATE ebooks SET judul=?, penulis=?, id_kategori=?, deskripsi=?, cover_ebook=?, file_path=? WHERE id_ebook=?");
        if ($stmt) {
            $stmt->bind_param("ssisssi", $judul, $penulis, $id_kategori, $deskripsi, $new_cover_path, $new_file_path, $id_ebook_edit);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "E-Book \"".htmlspecialchars($judul)."\" berhasil diperbarui!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $message .= '<div class="alert alert-danger">Gagal memperbarui e-book: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        } else {
             $message .= '<div class="alert alert-danger">Gagal menyiapkan statement update: ' . $connect->error . '</div>';
        }
    }
}

// Proses Hapus E-Book
$action = $_GET['action'] ?? 'view';
$id_ebook_url = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if ($action === 'delete' && $id_ebook_url) {
    $stmt_get_files = $connect->prepare("SELECT judul, cover_ebook, file_path FROM ebooks WHERE id_ebook = ?");
    if ($stmt_get_files) {
        $stmt_get_files->bind_param("i", $id_ebook_url);
        $stmt_get_files->execute();
        $result_files = $stmt_get_files->get_result();
        $ebook_to_delete = $result_files->fetch_assoc();
        $stmt_get_files->close();

        if ($ebook_to_delete) {
            $judul_deleted = $ebook_to_delete['judul'];
            $stmt_delete = $connect->prepare("DELETE FROM ebooks WHERE id_ebook = ?");
            if ($stmt_delete) {
                $stmt_delete->bind_param("i", $id_ebook_url);
                if ($stmt_delete->execute()) {
                    deleteFile($ebookCoverUploadDir . $ebook_to_delete['cover_ebook']);
                    deleteFile($ebookFileUploadDir . $ebook_to_delete['file_path']);
                    $_SESSION['success_message'] = "E-Book \"".htmlspecialchars($judul_deleted)."\" dan file terkait berhasil dihapus!";
                } else {
                    $_SESSION['error_message'] = "Gagal menghapus e-book dari database: " . $stmt_delete->error;
                }
                $stmt_delete->close();
            } else {
                $_SESSION['error_message'] = "Gagal menyiapkan statement delete: " . $connect->error;
            }
        } else {
            $_SESSION['error_message'] = "E-Book tidak ditemukan untuk dihapus.";
        }
    } else {
        $_SESSION['error_message'] = "Gagal mengambil data file e-book untuk dihapus: " . $connect->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Ambil pesan dari session dan hapus
if (isset($_SESSION['success_message'])) {
    $message .= '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $message .= '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">Kelola Data E-Book</h2>

    <?= $message ?> <?php if ($action === 'add' || ($action === 'edit' && $id_ebook_url)): ?>
        <?php
        $form_title = "Tambah E-Book Baru";
        $submit_name = "add_ebook";
        $submit_text = "Tambah E-Book";
        $form_action_target = $_SERVER['PHP_SELF'];


        $e = [
            'id_ebook' => '', 'judul' => '', 'penulis' => '', 'id_kategori' => '', 
            'deskripsi' => '', 'cover_ebook' => '', 'file_path' => ''
        ];

        if ($action === 'edit' && $id_ebook_url) {
            $form_title = "Edit Data E-Book";
            $submit_name = "edit_ebook";
            $submit_text = "Simpan Perubahan";
            
            $stmt_edit = $connect->prepare("SELECT * FROM ebooks WHERE id_ebook = ?");
            if ($stmt_edit) {
                $stmt_edit->bind_param("i", $id_ebook_url);
                $stmt_edit->execute();
                $result_edit = $stmt_edit->get_result();
                $e = $result_edit->fetch_assoc();
                $stmt_edit->close();
                if (!$e) {
                    echo '<div class="alert alert-danger">Data e-book tidak ditemukan.</div>';
                    echo '<a href="'.$_SERVER['PHP_SELF'].'" class="btn btn-secondary">Kembali ke Daftar E-Book</a>';
                    include_once __DIR__ . '/../../../layout/footer.php';
                    exit;
                }
            } else {
                 echo '<div class="alert alert-danger">Gagal mengambil data e-book untuk diedit.</div>';
                 // Log error $connect->error
            }
        }
        ?>
        <h3><?= $form_title ?></h3>
        <form method="POST" action="<?= $form_action_target ?>" enctype="multipart/form-data" class="mb-5 card card-body">
             <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id_ebook" value="<?= htmlspecialchars($e['id_ebook']) ?>">
                <input type="hidden" name="current_cover_ebook" value="<?= htmlspecialchars($e['cover_ebook']) ?>">
                <input type="hidden" name="current_file_ebook" value="<?= htmlspecialchars($e['file_path']) ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul E-Book <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($e['judul']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="penulis" class="form-label">Penulis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="penulis" name="penulis" value="<?= htmlspecialchars($e['penulis']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih Kategori...</option>
                            <?php foreach ($kategoriOptions as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>" <?= ($kat['id_kategori'] == $e['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($e['deskripsi']) ?></textarea>
                    </div>
                </div>
                 <div class="col-md-5">
                    <div class="mb-3">
                        <label for="cover_ebook" class="form-label">Cover E-Book (Gambar)</label>
                        <input type="file" class="form-control" id="cover_ebook" name="cover_ebook" accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if ($action === 'edit' && !empty($e['cover_ebook'])): ?>
                            <small class="form-text text-muted d-block mt-1">Cover saat ini: 
                                <a href="/libtera/uploads/ebook/assets/cover/<?= htmlspecialchars($e['cover_ebook']) ?>" target="_blank"><?= htmlspecialchars($e['cover_ebook']) ?></a>
                            </small>
                            <img src="/libtera/uploads/ebook/assets/cover/<?= htmlspecialchars($e['cover_ebook']) ?>" alt="Cover <?= htmlspecialchars($e['judul']) ?>" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="file_ebook" class="form-label">File E-Book (PDF) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file_ebook" name="file_ebook" accept="application/pdf" <?= ($action === 'add') ? 'required' : '' ?>>
                         <?php if ($action === 'edit' && !empty($e['file_path'])): ?>
                            <small class="form-text text-muted d-block mt-1">File PDF saat ini: <?= htmlspecialchars($e['file_path']) ?>. Kosongkan jika tidak ingin mengganti.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="d-flex justify-content-end">
                <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" name="<?= $submit_name ?>" class="btn btn-primary"><?= $submit_text ?></button>
            </div>
        </form>

    <?php else: ?>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <a href="<?= $_SERVER['PHP_SELF'] ?>?action=add" class="btn btn-success"><i class="fas fa-plus me-1"></i> Tambah E-Book Baru</a>
            
            <form method="GET" action="" class="d-flex gap-2 w-100 w-md-auto">
                <select name="kategori" class="form-select" onchange="this.form.submit()" style="min-width: 150px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriOptions as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" type="search" name="search" placeholder="Judul atau penulis..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="min-width: 200px;">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                 <?php if (!empty($_GET['search']) || !empty($_GET['kategori'])): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <div class="card-header">Daftar E-Book Tersedia</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th style="width: 80px;">Cover</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>File PDF</th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $kategoriIdFilter = isset($_GET['kategori']) && is_numeric($_GET['kategori']) ? (int)$_GET['kategori'] : null;
                            $searchTermFilter = $_GET['search'] ?? '';

                            $sql_view = "SELECT e.id_ebook, e.judul, e.penulis, e.cover_ebook, e.file_path, k.nama_kategori 
                                         FROM ebooks e 
                                         LEFT JOIN kategori k ON e.id_kategori = k.id_kategori";
                            
                            $conditions_view = [];
                            $params_view = [];
                            $types_view = '';

                            if ($kategoriIdFilter) {
                                $conditions_view[] = "e.id_kategori = ?";
                                $params_view[] = $kategoriIdFilter;
                                $types_view .= 'i';
                            }

                            if (!empty($searchTermFilter)) {
                                $conditions_view[] = "(e.judul LIKE ? OR e.penulis LIKE ?)";
                                $likeTerm_view = "%" . $searchTermFilter . "%";
                                $params_view[] = $likeTerm_view;
                                $params_view[] = $likeTerm_view;
                                $types_view .= 'ss';
                            }

                            if (!empty($conditions_view)) {
                                $sql_view .= " WHERE " . implode(" AND ", $conditions_view);
                            }
                            $sql_view .= " ORDER BY e.judul ASC";

                            $stmt_list = $connect->prepare($sql_view);
                            $no = 1;
                            if ($stmt_list) {
                                if (!empty($params_view)) {
                                    $stmt_list->bind_param($types_view, ...$params_view);
                                }
                                $stmt_list->execute();
                                $result_list = $stmt_list->get_result();

                                if ($result_list->num_rows > 0):
                                    while($ebook_item = $result_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if(!empty($ebook_item['cover_ebook'])): ?>
                                        <img src="/libtera/uploads/ebook/assets/cover/<?= htmlspecialchars($ebook_item['cover_ebook']) ?>" alt="Cover" class="img-thumbnail" style="width: 70px; height: auto; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="/libtera/assets/default_ebook_cover.png" alt="No Cover" class="img-thumbnail" style="width: 70px; height: auto; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($ebook_item['judul']) ?></td>
                                <td><?= htmlspecialchars($ebook_item['penulis']) ?></td>
                                <td><?= htmlspecialchars($ebook_item['nama_kategori'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if(!empty($ebook_item['file_path'])): ?>
                                        <a href="/libtera/uploads/ebook/assets/books/<?= htmlspecialchars($ebook_item['file_path']) ?>" target="_blank" title="Unduh/Lihat PDF">
                                            <?= htmlspecialchars(substr($ebook_item['file_path'], strpos($ebook_item['file_path'], '_') + 1)) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= $_SERVER['PHP_SELF'] ?>?action=edit&id=<?= $ebook_item['id_ebook'] ?>" class="btn btn-warning btn-sm m-1" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= $_SERVER['PHP_SELF'] ?>?action=delete&id=<?= $ebook_item['id_ebook'] ?>" class="btn btn-danger btn-sm m-1" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus e-book \"<?= htmlspecialchars(addslashes($ebook_item['judul'])) ?>\" beserta semua filenya?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                                    endwhile;
                                else: ?>
                                    <tr><td colspan="7" class="text-center">Tidak ada data e-book yang ditemukan.</td></tr>
                            <?php 
                                endif;
                                $stmt_list->close();
                            } else {
                                 echo '<tr><td colspan="7" class="text-center text-danger">Gagal menyiapkan daftar e-book: '.$connect->error.'</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
include_once __DIR__ . '/../../../layout/footer.php'; 
ob_end_flush(); // Kirim output buffer dan matikan
?>