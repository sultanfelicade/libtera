<?php
ob_start();
session_start();

if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['id'])) {
    $_SESSION['error_message'] = "Sesi admin tidak ditemukan. Silakan login kembali.";
    header("Location: /libtera/login_admin.php"); 
    exit;
}

$title = "Kelola Kategori - Libtera Admin";
require __DIR__ . '/../../connect.php'; 

$message = '';
$error_message = '';

if (isset($_SESSION['success_message'])) {
    $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['error_message']);
}

if (isset($_POST['tambah_kategori_submit'])) {
    $nama_kategori_baru = trim($_POST['nama_kategori_tambah']);
    if (!empty($nama_kategori_baru)) {
        $stmt_cek = $connect->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
        $stmt_cek->bind_param("s", $nama_kategori_baru);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        if ($result_cek->num_rows == 0) {
            $stmt_insert = $connect->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("s", $nama_kategori_baru);
                if ($stmt_insert->execute()) {
                    $_SESSION['success_message'] = "Kategori '" . htmlspecialchars($nama_kategori_baru) . "' berhasil ditambahkan.";
                } else {
                    $_SESSION['error_message'] = "Gagal menambahkan kategori: " . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                $_SESSION['error_message'] = "Gagal menyiapkan statement insert: " . $connect->error;
            }
        } else {
            $_SESSION['error_message'] = "Nama kategori '" . htmlspecialchars($nama_kategori_baru) . "' sudah ada.";
        }
        $stmt_cek->close();
    } else {
        $_SESSION['error_message'] = "Nama kategori tidak boleh kosong.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['edit_kategori_submit'])) {
    $id_kategori_edit = (int)$_POST['id_kategori_edit_modal'];
    $nama_kategori_edit = trim($_POST['nama_kategori_edit_modal']);

    if ($id_kategori_edit > 0 && !empty($nama_kategori_edit)) {
        $stmt_cek = $connect->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ? AND id_kategori != ?");
        $stmt_cek->bind_param("si", $nama_kategori_edit, $id_kategori_edit);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();

        if ($result_cek->num_rows == 0) {
            $stmt_update = $connect->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
            if ($stmt_update) {
                $stmt_update->bind_param("si", $nama_kategori_edit, $id_kategori_edit);
                if ($stmt_update->execute()) {
                    $_SESSION['success_message'] = "Kategori berhasil diperbarui menjadi '" . htmlspecialchars($nama_kategori_edit) . "'.";
                } else {
                    $_SESSION['error_message'] = "Gagal memperbarui kategori: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $_SESSION['error_message'] = "Gagal menyiapkan statement update: " . $connect->error;
            }
        } else {
            $_SESSION['error_message'] = "Nama kategori '" . htmlspecialchars($nama_kategori_edit) . "' sudah ada untuk kategori lain.";
        }
        $stmt_cek->close();
    } else {
        $_SESSION['error_message'] = "ID kategori tidak valid atau nama kategori tidak boleh kosong.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['hapus_kategori_submit'])) {
    $id_kategori_hapus = (int)$_POST['id_kategori_hapus_modal_confirm'];
    $nama_kategori_untuk_pesan = ""; 

    if ($id_kategori_hapus > 0) {
        $stmt_get_nama = $connect->prepare("SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
        if ($stmt_get_nama) {
            $stmt_get_nama->bind_param("i", $id_kategori_hapus);
            $stmt_get_nama->execute();
            $result_get_nama = $stmt_get_nama->get_result();
            if ($row_nama = $result_get_nama->fetch_assoc()) {
                $nama_kategori_untuk_pesan = $row_nama['nama_kategori'];
            }
            $stmt_get_nama->close();
        }

        $can_delete = true;
        $tables_to_check = ['buku', 'ebook']; 
        $nama_tabel_pengguna = '';

        foreach ($tables_to_check as $table) {
            $stmt_check_table = $connect->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = 'id_kategori' LIMIT 1");
            if ($stmt_check_table) {
                $stmt_check_table->bind_param("s", $table);
                $stmt_check_table->execute();
                $result_check_table = $stmt_check_table->get_result();
                $table_has_kategori = $result_check_table->num_rows > 0;
                $stmt_check_table->close();

                if ($table_has_kategori) {
                    $stmt_check_usage = $connect->prepare("SELECT COUNT(*) as count FROM `$table` WHERE id_kategori = ?");
                    if ($stmt_check_usage) {
                        $stmt_check_usage->bind_param("i", $id_kategori_hapus);
                        $stmt_check_usage->execute();
                        $result_usage = $stmt_check_usage->get_result()->fetch_assoc();
                        $stmt_check_usage->close();
                        if ($result_usage['count'] > 0) {
                            $can_delete = false;
                            $nama_tabel_pengguna = $table;
                            $_SESSION['error_message'] = "Gagal menghapus kategori ".htmlspecialchars($nama_kategori_untuk_pesan).". Pastikan tidak ada buku atau e-book yang menggunakan kategori ini.";
                            break; 
                        }
                    }
                }
            }
        }

        if ($can_delete) {
            $stmt_delete = $connect->prepare("DELETE FROM kategori WHERE id_kategori = ?");
            if ($stmt_delete) {
                $stmt_delete->bind_param("i", $id_kategori_hapus);
                if ($stmt_delete->execute()) {
                    $_SESSION['success_message'] = "Kategori '".htmlspecialchars($nama_kategori_untuk_pesan)."' berhasil dihapus.";
                } else {
                    if ($connect->errno == 1451) { 
                         $_SESSION['error_message'] = "Gagal menghapus kategori '".htmlspecialchars($nama_kategori_untuk_pesan)."'. Kategori masih digunakan oleh data lain (constraint database).";
                    } else {
                        $_SESSION['error_message'] = "Gagal menghapus kategori '".htmlspecialchars($nama_kategori_untuk_pesan)."': " . $stmt_delete->error;
                    }
                }
                $stmt_delete->close();
            } else {
                $_SESSION['error_message'] = "Gagal menyiapkan statement delete: " . $connect->error;
            }
        }
    } else {
        $_SESSION['error_message'] = "ID kategori tidak valid untuk dihapus.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$search_term_kategori = $_GET['search_kategori'] ?? '';
$sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori";
$params_kategori = [];
$types_kategori = '';

if (!empty($search_term_kategori)) {
    $sql_kategori .= " WHERE nama_kategori LIKE ?";
    $like_search_kategori = "%" . $search_term_kategori . "%";
    $params_kategori[] = $like_search_kategori;
    $types_kategori .= 's';
}
$sql_kategori .= " ORDER BY nama_kategori ASC";

$stmt_kategori = $connect->prepare($sql_kategori);
$result_kategori = null;
if ($stmt_kategori) {
    if (!empty($params_kategori)) {
        $stmt_kategori->bind_param($types_kategori, ...$params_kategori);
    }
    $stmt_kategori->execute();
    $result_kategori = $stmt_kategori->get_result();
} else {
    $error_message .= '<div class="alert alert-danger">Gagal menyiapkan daftar kategori: ' . $connect->error . '</div>';
}

include_once __DIR__ . '/../../layout/header.php';
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="fas fa-tags me-2"></i> Kelola Kategori</h2>

    <?= $message ?>
    <?= $error_message ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list-ul me-1"></i> Daftar Kategori</h6>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                <i class="fas fa-plus me-1"></i> Tambah Kategori Baru
            </button>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row g-3 mb-3">
                <div class="col-md-10">
                    <input type="text" name="search_kategori" class="form-control form-control-sm" value="<?= htmlspecialchars($search_term_kategori) ?>" placeholder="Cari Nama Kategori...">
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary btn-sm w-100 me-1"><i class="fas fa-search"></i> Cari</button>
                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-outline-secondary btn-sm w-100" title="Reset Cari"><i class="fas fa-undo"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th>Nama Kategori</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_kategori && $result_kategori->num_rows > 0): ?>
                            <?php while($kategori = $result_kategori->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $kategori['id_kategori'] ?></td>
                                    <td><?= htmlspecialchars($kategori['nama_kategori']) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm me-1"
                                                data-bs-toggle="modal" data-bs-target="#modalEditKategori"
                                                data-id-kategori="<?= $kategori['id_kategori'] ?>"
                                                data-nama-kategori="<?= htmlspecialchars($kategori['nama_kategori']) ?>"
                                                title="Edit Kategori">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalHapusKategori"
                                                data-id-kategori-hapus="<?= $kategori['id_kategori'] ?>"
                                                data-nama-kategori-hapus="<?= htmlspecialchars($kategori['nama_kategori']) ?>"
                                                title="Hapus Kategori">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <em><?= empty($search_term_kategori) ? "Belum ada data kategori." : "Tidak ada kategori yang cocok dengan pencarian Anda." ?></em>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahKategoriLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalTambahKategoriLabel"><i class="fas fa-plus-circle me-2"></i> Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kategori_tambah_input" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori_tambah_input" name="nama_kategori_tambah" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_kategori_submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalEditKategoriLabel"><i class="fas fa-edit me-2"></i> Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_kategori_edit_modal" id="id_kategori_edit_modal_hidden">
                    <div class="mb-3">
                        <label for="nama_kategori_edit_modal_input" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori_edit_modal_input" name="nama_kategori_edit_modal" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_kategori_submit" class="btn btn-warning text-dark"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapusKategori" tabindex="-1" aria-labelledby="modalHapusKategoriLabelConfirm" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalHapusKategoriLabelConfirm"><i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Penghapusan Kategori</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_kategori_hapus_modal_confirm" id="id_kategori_hapus_modal_hidden_confirm_val">
                    <p>Anda yakin ingin menghapus kategori <strong id="nama_kategori_hapus_modal_text_confirm_val"></strong>?</p>
                    <p class="text-danger small"><strong>Peringatan:</strong> Kategori tidak bisa dihapus jika ada buku yang masih termasuk ke ketegori tersebut!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="hapus_kategori_submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i> Ya, Hapus Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEditKategori = document.getElementById('modalEditKategori');
    if (modalEditKategori) {
        modalEditKategori.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var idKategori = button.getAttribute('data-id-kategori');
            var namaKategori = button.getAttribute('data-nama-kategori');

            modalEditKategori.querySelector('#id_kategori_edit_modal_hidden').value = idKategori;
            modalEditKategori.querySelector('#nama_kategori_edit_modal_input').value = namaKategori;
            modalEditKategori.querySelector('#nama_kategori_edit_modal_input').focus();
        });
    }

    var modalHapusKategori = document.getElementById('modalHapusKategori');
    if (modalHapusKategori) {
        modalHapusKategori.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var idKategoriHapus = button.getAttribute('data-id-kategori-hapus');
            var namaKategoriHapus = button.getAttribute('data-nama-kategori-hapus');
            
            modalHapusKategori.querySelector('#id_kategori_hapus_modal_hidden_confirm_val').value = idKategoriHapus;
            modalHapusKategori.querySelector('#nama_kategori_hapus_modal_text_confirm_val').textContent = namaKategoriHapus;
        });
    }

    var modalTambahKategori = document.getElementById('modalTambahKategori');
    if(modalTambahKategori) {
        modalTambahKategori.addEventListener('shown.bs.modal', function() {
            modalTambahKategori.querySelector('#nama_kategori_tambah_input').focus();
        });
    }
});
</script>

<?php
if (isset($stmt_kategori) && $stmt_kategori instanceof mysqli_stmt) {
    $stmt_kategori->close();
}
include_once __DIR__ . '/../../layout/footer.php';
ob_end_flush();
?>