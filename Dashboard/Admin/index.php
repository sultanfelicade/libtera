<?php
ob_start();
session_start();
// --- Simulasi Autentikasi Admin ---
// Dalam aplikasi nyata, Anda harus memiliki sistem login admin yang aman.
// Untuk contoh ini, kita asumsikan admin sudah login jika session 'admin_logged_in' ada.
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika belum login, redirect ke halaman login admin
    // header("Location: ../login_admin.php"); 
    // exit;
    $_SESSION['info_message'] = "Silakan login sebagai admin terlebih dahulu.";
    // Untuk demo, kita set admin_logged_in agar bisa langsung diakses
    // Namun, baris di atas JANGAN DIPAKAI DI PRODUKSI TANPA LOGIN NYATA
    // Jika Anda punya halaman login, hapus 2 baris di bawah ini dan uncomment 2 baris di atasnya
    // echo "Akses ditolak. Silakan login sebagai admin.";
    // exit;
}
*/
// Jika Anda ingin menguji tanpa login, Anda bisa sementara mengatur session di sini:
// $_SESSION['admin_logged_in'] = true; 


$title = "Kelola Buku - Libtera Admin";
// Sesuaikan path ke header dan footer jika file ini ada di subdirektori
// Misalnya, jika kelola_buku.php ada di admin/buku/, maka pathnya ../../layout/
include_once __DIR__ . '/../../layout/header.php'; 
require "../../connect.php"; // Koneksi ke database

$message = ''; // Untuk notifikasi sukses atau error

// Direktori untuk upload cover buku
$uploadDir = __DIR__ . '/../../uploads/books/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// --- Fungsi untuk mengambil daftar kategori ---
function getKategoriList($connect) {
    $kategoriResult = mysqli_query($connect, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
    $kategoriList = [];
    while ($kategori = mysqli_fetch_assoc($kategoriResult)) {
        $kategoriList[] = $kategori;
    }
    return $kategoriList;
}
$kategoriOptions = getKategoriList($connect);


// --- BAGIAN PENGELOLAAN DATA (CREATE, UPDATE, DELETE) ---

// Proses Tambah Buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    $isbn = trim($_POST['isbn']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = (int)$_POST['tahun_terbit'];
    $stok = (int)$_POST['stok'];
    $cover_path = '';

    // Validasi dasar
    if (empty($judul) || empty($pengarang) || empty($id_kategori) || $tahun_terbit <= 0 || $stok < 0) {
        $message = '<div class="alert alert-danger">Judul, Pengarang, Kategori, Tahun Terbit (harus > 0), dan Stok (harus >= 0) wajib diisi.</div>';
    } else {
        // Handle upload cover
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == UPLOAD_ERR_OK) {
            $coverTmpName = $_FILES['cover']['tmp_name'];
            $coverName = time() . '_' . basename($_FILES['cover']['name']);
            $cover_path = $coverName; // Simpan nama file saja di DB
            
            if (!move_uploaded_file($coverTmpName, $uploadDir . $cover_path)) {
                $message = '<div class="alert alert-danger">Gagal mengupload file cover.</div>';
                $cover_path = ''; // Reset jika gagal
            }
        }

        if (empty($message)) { // Lanjutkan jika tidak ada error upload
            $stmt = $connect->prepare("INSERT INTO buku (judul, pengarang, id_kategori, deskripsi, isbn, penerbit, tahun_terbit, stok, cover) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisssiis", $judul, $pengarang, $id_kategori, $deskripsi, $isbn, $penerbit, $tahun_terbit, $stok, $cover_path);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Buku \"".htmlspecialchars($judul)."\" berhasil ditambahkan!";
                header("Location: " . $_SERVER['PHP_SELF']); // Redirect untuk mencegah resubmit
                exit;
            } else {
                $message = '<div class="alert alert-danger">Gagal menambahkan buku: ' . $stmt->error . '</div>';
                // Jika gagal insert DB dan file sudah terupload, hapus file
                if (!empty($cover_path) && file_exists($uploadDir . $cover_path)) {
                    unlink($uploadDir . $cover_path);
                }
            }
            $stmt->close();
        }
    }
}

// Proses Edit Buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
    $id_buku_edit = (int)$_POST['id_buku'];
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    $isbn = trim($_POST['isbn']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = (int)$_POST['tahun_terbit'];
    $stok = (int)$_POST['stok'];
    $cover_lama = $_POST['cover_lama'];
    $cover_path = $cover_lama;

    // Validasi dasar
    if (empty($judul) || empty($pengarang) || empty($id_kategori) || $tahun_terbit <= 0 || $stok < 0) {
        $message = '<div class="alert alert-danger">Judul, Pengarang, Kategori, Tahun Terbit (harus > 0), dan Stok (harus >= 0) wajib diisi.</div>';
    } else {
        // Handle upload cover baru jika ada
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == UPLOAD_ERR_OK) {
            $coverTmpName = $_FILES['cover']['tmp_name'];
            $newCoverName = time() . '_' . basename($_FILES['cover']['name']);
            
            if (move_uploaded_file($coverTmpName, $uploadDir . $newCoverName)) {
                // Hapus cover lama jika ada dan berhasil upload yang baru
                if (!empty($cover_lama) && file_exists($uploadDir . $cover_lama)) {
                    unlink($uploadDir . $cover_lama);
                }
                $cover_path = $newCoverName; // Update dengan nama cover baru
            } else {
                $message = '<div class="alert alert-danger">Gagal mengupload file cover baru. Perubahan tidak disimpan untuk cover.</div>';
                // $cover_path tetap cover_lama jika upload baru gagal
            }
        }
        
        if (empty($message)) {
             $stmt = $connect->prepare("UPDATE buku SET judul=?, pengarang=?, id_kategori=?, deskripsi=?, isbn=?, penerbit=?, tahun_terbit=?, stok=?, cover=? WHERE id_buku=?");
            $stmt->bind_param("ssisssiisi", $judul, $pengarang, $id_kategori, $deskripsi, $isbn, $penerbit, $tahun_terbit, $stok, $cover_path, $id_buku_edit);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Buku \"".htmlspecialchars($judul)."\" berhasil diperbarui!";
                header("Location: " . $_SERVER['PHP_SELF']); // Redirect
                exit;
            } else {
                $message = '<div class="alert alert-danger">Gagal memperbarui buku: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }
}

// Proses Hapus Buku
$action = $_GET['action'] ?? 'view';
$id_buku_url = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if ($action === 'delete' && $id_buku_url) {
    // Ambil nama file cover untuk dihapus
    $stmt_get_cover = $connect->prepare("SELECT cover, judul FROM buku WHERE id_buku = ?");
    $stmt_get_cover->bind_param("i", $id_buku_url);
    $stmt_get_cover->execute();
    $result_cover = $stmt_get_cover->get_result();
    $book_to_delete = $result_cover->fetch_assoc();
    $stmt_get_cover->close();

    if ($book_to_delete) {
        $cover_to_delete = $book_to_delete['cover'];
        $judul_deleted = $book_to_delete['judul'];

        $stmt = $connect->prepare("DELETE FROM buku WHERE id_buku = ?");
        $stmt->bind_param("i", $id_buku_url);
        if ($stmt->execute()) {
            // Hapus file cover jika ada
            if (!empty($cover_to_delete) && file_exists($uploadDir . $cover_to_delete)) {
                unlink($uploadDir . $cover_to_delete);
            }
            $_SESSION['success_message'] = "Buku \"".htmlspecialchars($judul_deleted)."\" berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus buku: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Buku tidak ditemukan untuk dihapus.";
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect
    exit;
}

// Ambil pesan dari session dan hapus agar tidak muncul lagi
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
    <h2 class="mb-4">Kelola Data Buku</h2>

    <?= $message ?> <?php if ($action === 'add' || ($action === 'edit' && $id_buku_url)): ?>
        <?php
        $form_title = "Tambah Buku Baru";
        $form_action = $_SERVER['PHP_SELF'] . "?action=add"; // Default untuk add
        $submit_name = "add_book";
        $submit_text = "Tambah Buku";

        // Default values untuk form tambah
        $b = [
            'id_buku' => '', 'judul' => '', 'pengarang' => '', 'id_kategori' => '', 
            'deskripsi' => '', 'isbn' => '', 'penerbit' => '', 
            'tahun_terbit' => date('Y'), 'stok' => 0, 'cover' => ''
        ];

        if ($action === 'edit' && $id_buku_url) {
            $form_title = "Edit Data Buku";
            $form_action = $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id_buku_url;
            $submit_name = "edit_book";
            $submit_text = "Simpan Perubahan";

            $stmt_edit = $connect->prepare("SELECT * FROM buku WHERE id_buku = ?");
            $stmt_edit->bind_param("i", $id_buku_url);
            $stmt_edit->execute();
            $result_edit = $stmt_edit->get_result();
            $b = $result_edit->fetch_assoc();
            if (!$b) {
                echo '<div class="alert alert-danger">Data buku tidak ditemukan.</div>';
                echo '<a href="'.$_SERVER['PHP_SELF'].'" class="btn btn-secondary">Kembali ke Daftar Buku</a>';
                include_once __DIR__ . '/../../layout/footer.php';
                exit;
            }
            $stmt_edit->close();
        }
        ?>
        <h3><?= $form_title ?></h3>
        <form method="POST" action="<?= $form_action ?>" enctype="multipart/form-data" class="mb-5">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id_buku" value="<?= htmlspecialchars($b['id_buku']) ?>">
                <input type="hidden" name="cover_lama" value="<?= htmlspecialchars($b['cover']) ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($b['judul']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="pengarang" class="form-label">Pengarang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pengarang" name="pengarang" value="<?= htmlspecialchars($b['pengarang']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih Kategori...</option>
                            <?php foreach ($kategoriOptions as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>" <?= ($kat['id_kategori'] == $b['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($b['deskripsi']) ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?= htmlspecialchars($b['isbn']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="penerbit" class="form-label">Penerbit</label>
                        <input type="text" class="form-control" id="penerbit" name="penerbit" value="<?= htmlspecialchars($b['penerbit']) ?>">
                    </div>
                     <div class="mb-3">
                        <label for="tahun_terbit" class="form-label">Tahun Terbit <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" value="<?= htmlspecialchars($b['tahun_terbit']) ?>" min="1000" max="<?= date('Y') + 1 ?>" required>
                    </div>
                     <div class="mb-3">
                        <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($b['stok']) ?>" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="cover" class="form-label">Cover Buku</label>
                        <input type="file" class="form-control" id="cover" name="cover" accept="image/*">
                        <?php if ($action === 'edit' && !empty($b['cover'])): ?>
                            <small class="form-text text-muted">Cover saat ini: <a href="/libtera/uploads/books/<?= htmlspecialchars($b['cover']) ?>" target="_blank"><?= htmlspecialchars($b['cover']) ?></a>. Kosongkan jika tidak ingin mengganti.</small>
                            <img src="/libtera/uploads/books/<?= htmlspecialchars($b['cover']) ?>" alt="Cover <?= htmlspecialchars($b['judul']) ?>" class="img-thumbnail mt-2" style="max-height: 100px;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="<?= $submit_name ?>" class="btn btn-primary"><?= $submit_text ?></button>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Batal</a>
        </form>

    <?php else: ?>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <a href="<?= $_SERVER['PHP_SELF'] ?>?action=add" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Buku Baru</a>
            
            <form method="GET" action="" class="d-flex gap-2 w-100 w-md-auto">
                <select name="kategori" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriOptions as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" type="search" name="search" placeholder="Judul atau pengarang..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button class="btn btn-outline-primary" type="submit">Cari</button>
                 <?php if (!empty($_GET['search']) || !empty($_GET['kategori'])): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Tahun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil parameter dari URL dengan aman
                    $kategoriIdFilter = isset($_GET['kategori']) && is_numeric($_GET['kategori']) ? (int)$_GET['kategori'] : null;
                    $searchTermFilter = $_GET['search'] ?? '';

                    $sql_view = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori";
                    
                    $conditions = [];
                    $params = [];
                    $types = '';

                    if ($kategoriIdFilter) {
                        $conditions[] = "b.id_kategori = ?";
                        $params[] = $kategoriIdFilter;
                        $types .= 'i';
                    }

                    if (!empty($searchTermFilter)) {
                        $conditions[] = "(b.judul LIKE ? OR b.pengarang LIKE ?)";
                        $likeTerm = "%" . $searchTermFilter . "%";
                        $params[] = $likeTerm;
                        $params[] = $likeTerm;
                        $types .= 'ss';
                    }

                    if (!empty($conditions)) {
                        $sql_view .= " WHERE " . implode(" AND ", $conditions);
                    }
                    $sql_view .= " ORDER BY b.judul ASC";

                    $stmt_view = $connect->prepare($sql_view);
                    if (!empty($params)) {
                        $stmt_view->bind_param($types, ...$params);
                    }
                    $stmt_view->execute();
                    $result_view = $stmt_view->get_result();
                    $no = 1;

                    if ($result_view->num_rows > 0):
                        while($buku = $result_view->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if(!empty($buku['cover'])): ?>
                                <img src="/libtera/uploads/books/<?= htmlspecialchars($buku['cover']) ?>" alt="Cover" style="width: 50px; height: auto;">
                            <?php else: ?>
                                <small class="text-muted">Tidak ada cover</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($buku['judul']) ?></td>
                        <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                        <td><?= htmlspecialchars($buku['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($buku['stok']) ?></td>
                        <td><?= htmlspecialchars($buku['tahun_terbit']) ?></td>
                        <td>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?action=edit&id=<?= $buku['id_buku'] ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?action=delete&id=<?= $buku['id_buku'] ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus buku <?= htmlspecialchars(addslashes($buku['judul'])) ?>?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else: ?>
                        <tr><td colspan="8" class="text-center">Tidak ada data buku yang ditemukan.</td></tr>
                    <?php 
                    endif;
                    $stmt_view->close();
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php 
include_once __DIR__ . '/../../layout/footer.php'; 
?>