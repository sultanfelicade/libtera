<?php
// File: Dashboard/Admin/kelola_user.php (atau path yang kamu inginkan)
ob_start();
session_start();

// --- Autentikasi Admin ---
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['id'])) {
    $_SESSION['error_message_admin_auth'] = "Sesi admin tidak ditemukan. Silakan login kembali sebagai admin.";
    header("Location: /libtera/login_admin.php"); // Sesuaikan path login admin Anda
    exit;
}
$id_admin_logged_in = (int)$_SESSION['admin']['id'];
// --- End Autentikasi Admin ---

$title = "Kelola Pengguna Siswa - Libtera Admin";
require __DIR__ . '/../../connect.php'; // Koneksi ke database

// Inisialisasi variabel
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$edit_id_siswa = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$siswa_to_edit = null;

$pesan_sukses = $_SESSION['pesan_sukses_user'] ?? null;
$pesan_error = $_SESSION['pesan_error_user'] ?? null;
if (isset($_SESSION['pesan_sukses_user'])) unset($_SESSION['pesan_sukses_user']);
if (isset($_SESSION['pesan_error_user'])) unset($_SESSION['pesan_error_user']);

// Ambil data kelas dan jurusan untuk dropdown
$daftar_kelas = [];
$result_kelas = $connect->query("SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
if ($result_kelas) {
    $daftar_kelas = $result_kelas->fetch_all(MYSQLI_ASSOC);
}

$daftar_jurusan = [];
$result_jurusan = $connect->query("SELECT id_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan ASC");
if ($result_jurusan) {
    $daftar_jurusan = $result_jurusan->fetch_all(MYSQLI_ASSOC);
}


// --- Handle Aksi POST (Edit atau Hapus) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dianjurkan menggunakan CSRF token di sini
    if (isset($_POST['update_siswa'])) {
        $id_siswa_update = filter_input(INPUT_POST, 'id_siswa_update', FILTER_VALIDATE_INT);
        $nisn = filter_input(INPUT_POST, 'nisn', FILTER_SANITIZE_NUMBER_INT);
        $nama = trim(filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING));
        $username = strtolower(trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING)));
        $password_baru = $_POST['password_baru']; // Jangan trim password
        $jenis_kelamin = filter_input(INPUT_POST, 'jenis_kelamin', FILTER_SANITIZE_STRING);
        $id_kelas = filter_input(INPUT_POST, 'id_kelas', FILTER_VALIDATE_INT);
        $id_jurusan = filter_input(INPUT_POST, 'id_jurusan', FILTER_VALIDATE_INT);
        $no_tlp = trim(filter_input(INPUT_POST, 'no_tlp', FILTER_SANITIZE_STRING));

        if ($id_siswa_update && $nisn && $nama && $username && $jenis_kelamin && $id_kelas && $id_jurusan) {
            $sql_update = "UPDATE siswa SET nisn = ?, nama = ?, username = ?, jenis_kelamin = ?, id_kelas = ?, id_jurusan = ?, no_tlp = ?";
            $types_update = "isssiiss";
            $params_update = [$nisn, $nama, $username, $jenis_kelamin, $id_kelas, $id_jurusan, $no_tlp];

            if (!empty($password_baru)) {
                // PENTING: HASH PASSWORD BARU!
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                if ($hashed_password === false) {
                    $_SESSION['pesan_error_user'] = "Gagal melakukan hashing password.";
                } else {
                    $sql_update .= ", password = ?";
                    $types_update .= "s";
                    $params_update[] = $hashed_password;
                }
            }
            $sql_update .= " WHERE id_siswa = ?";
            $types_update .= "i";
            $params_update[] = $id_siswa_update;

            if (!isset($_SESSION['pesan_error_user'])) { // Lanjut jika tidak ada error hashing
                $stmt_update = $connect->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param($types_update, ...$params_update);
                    if ($stmt_update->execute()) {
                        $_SESSION['pesan_sukses_user'] = "Data siswa berhasil diperbarui.";
                    } else {
                        $_SESSION['pesan_error_user'] = "Gagal memperbarui data siswa: " . $stmt_update->error;
                    }
                    $stmt_update->close();
                } else {
                    $_SESSION['pesan_error_user'] = "Gagal menyiapkan statement update: " . $connect->error;
                }
            }
        } else {
            $_SESSION['pesan_error_user'] = "Semua field yang wajib diisi (kecuali password baru) harus diisi dengan benar.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search_term)); // Redirect kembali ke halaman dengan search term jika ada
        exit;

    } elseif (isset($_POST['hapus_siswa'])) {
        $id_siswa_hapus = filter_input(INPUT_POST, 'id_siswa_hapus', FILTER_VALIDATE_INT);
        if ($id_siswa_hapus) {
            // PERHATIAN: Pertimbangkan konsekuensi menghapus siswa jika ada data terkait (peminjaman, dll.)
            // Anda mungkin perlu menonaktifkan akun daripada menghapus, atau menangani foreign key constraints.
            $sql_delete = "DELETE FROM siswa WHERE id_siswa = ?";
            $stmt_delete = $connect->prepare($sql_delete);
            if ($stmt_delete) {
                $stmt_delete->bind_param("i", $id_siswa_hapus);
                if ($stmt_delete->execute()) {
                    $_SESSION['pesan_sukses_user'] = "Data siswa berhasil dihapus.";
                } else {
                    $_SESSION['pesan_error_user'] = "Gagal menghapus data siswa: " . $stmt_delete->error . " (Mungkin ada data terkait seperti peminjaman aktif).";
                }
                $stmt_delete->close();
            } else {
                $_SESSION['pesan_error_user'] = "Gagal menyiapkan statement hapus: " . $connect->error;
            }
        } else {
            $_SESSION['pesan_error_user'] = "ID Siswa tidak valid untuk dihapus.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?search=" . urlencode($search_term));
        exit;
    }
}

// --- Jika action=edit, ambil data siswa yang akan diedit ---
if ($action === 'edit' && $edit_id_siswa > 0) {
    $sql_edit = "SELECT id_siswa, nisn, nama, username, jenis_kelamin, id_kelas, id_jurusan, no_tlp 
                 FROM siswa WHERE id_siswa = ?";
    $stmt_edit = $connect->prepare($sql_edit);
    if ($stmt_edit) {
        $stmt_edit->bind_param("i", $edit_id_siswa);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        $siswa_to_edit = $result_edit->fetch_assoc();
        $stmt_edit->close();
        if (!$siswa_to_edit) {
            $_SESSION['pesan_error_user'] = "Data siswa dengan ID $edit_id_siswa tidak ditemukan.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
         $_SESSION['pesan_error_user'] = "Gagal menyiapkan data untuk edit: " . $connect->error;
    }
}


// --- Ambil Daftar Siswa (dengan filter pencarian jika ada) ---
$sql_list_siswa = "SELECT s.id_siswa, s.nisn, s.nama, s.username, s.jenis_kelamin, 
                          k.nama_kelas, j.nama_jurusan, s.no_tlp, s.tgl_daftar
                   FROM siswa s
                   LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                   LEFT JOIN jurusan j ON s.id_jurusan = j.id_jurusan";
$params_list = [];
$types_list = "";
$conditions_list = [];

if (!empty($search_term)) {
    $like_search = "%" . $search_term . "%";
    // Perhatikan tipe data NISN, jika INT, LIKE pada angka mungkin tidak optimal.
    // Lebih baik search by NISN eksak jika memungkinkan atau pastikan kolom NISN di DB adalah VARCHAR jika mau LIKE.
    // Untuk sekarang, kita cast NISN ke CHAR untuk LIKE.
    $conditions_list[] = "(s.nama LIKE ? OR CAST(s.nisn AS CHAR) LIKE ? OR s.username LIKE ?)";
    $params_list[] = $like_search; $types_list .= "s";
    $params_list[] = $like_search; $types_list .= "s";
    $params_list[] = $like_search; $types_list .= "s";
}

if (!empty($conditions_list)) {
    $sql_list_siswa .= " WHERE " . implode(" AND ", $conditions_list);
}
$sql_list_siswa .= " ORDER BY s.nama ASC";
// Tambahkan LIMIT dan OFFSET di sini untuk pagination jika datanya banyak

$stmt_list_siswa = $connect->prepare($sql_list_siswa);
$data_siswa_list = [];
if ($stmt_list_siswa) {
    if (!empty($params_list)) {
        $stmt_list_siswa->bind_param($types_list, ...$params_list);
    }
    if ($stmt_list_siswa->execute()) {
        $result_list_siswa = $stmt_list_siswa->get_result();
        $data_siswa_list = $result_list_siswa->fetch_all(MYSQLI_ASSOC);
    } else {
        $pesan_error = (isset($pesan_error) ? $pesan_error . " | " : "") . "Gagal mengambil daftar siswa: " . $stmt_list_siswa->error;
    }
    $stmt_list_siswa->close();
} else {
    $pesan_error = (isset($pesan_error) ? $pesan_error . " | " : "") . "Gagal menyiapkan statement daftar siswa: " . $connect->error;
}


include_once __DIR__ . '/../../layout/header.php';
?>

<div class="container p-4 mt-5">
    <div class="mb-4">
        <h2 class="text-primary fw-bold"><i class="fas fa-users-cog me-2"></i>Kelola Pengguna Siswa</h2>
        <p class="text-muted">Cari, edit, atau hapus data siswa.</p>
    </div>

    <?php
    if ($pesan_sukses) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_sukses) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if ($pesan_error) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_error) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if (isset($_SESSION['error_message_admin_auth'])) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($_SESSION['error_message_admin_auth']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['error_message_admin_auth']);
    }
    ?>

    <?php if ($action === 'edit' && $siswa_to_edit): ?>
    <div class="card shadow-sm mb-4" id="formEditSiswa">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Data Siswa: <?= htmlspecialchars($siswa_to_edit['nama']) ?> (NISN: <?= htmlspecialchars($siswa_to_edit['nisn']) ?>)</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?search=<?= urlencode($search_term) ?>">
                <input type="hidden" name="id_siswa_update" value="<?= $siswa_to_edit['id_siswa'] ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_nisn" name="nisn" value="<?= htmlspecialchars($siswa_to_edit['nisn']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" value="<?= htmlspecialchars($siswa_to_edit['nama']) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_username" name="username" value="<?= htmlspecialchars($siswa_to_edit['username']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_password_baru" class="form-label">Password Baru (Kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" id="edit_password_baru" name="password_baru" placeholder="Masukkan password baru">
                        <small class="form-text text-muted">Password akan di-hash. Kosongkan jika tidak ingin mengubah password.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_jenis_kelamin" name="jenis_kelamin" required>
                            <option value="L" <?= ($siswa_to_edit['jenis_kelamin'] == 'L') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= ($siswa_to_edit['jenis_kelamin'] == 'P') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_id_kelas" class="form-label">Kelas <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_id_kelas" name="id_kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($daftar_kelas as $kelas): ?>
                            <option value="<?= $kelas['id_kelas'] ?>" <?= ($siswa_to_edit['id_kelas'] == $kelas['id_kelas']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kelas['nama_kelas']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_id_jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_id_jurusan" name="id_jurusan" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <?php foreach ($daftar_jurusan as $jurusan): ?>
                            <option value="<?= $jurusan['id_jurusan'] ?>" <?= ($siswa_to_edit['id_jurusan'] == $jurusan['id_jurusan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_no_tlp" class="form-label">No. Telepon</label>
                    <input type="text" class="form-control" id="edit_no_tlp" name="no_tlp" value="<?= htmlspecialchars($siswa_to_edit['no_tlp']) ?>">
                </div>
                <div class="d-flex justify-content-end">
                    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?search=<?= urlencode($search_term) ?>" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" name="update_siswa" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>


    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Siswa</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan Nama, NISN, atau Username Siswa..." value="<?= htmlspecialchars($search_term) ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i> Cari</button>
                    <?php if(!empty($search_term)): ?>
                        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary" title="Reset Pencarian"><i class="fas fa-undo"></i> Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if (empty($data_siswa_list) && !empty($search_term)): ?>
                <div class="alert alert-warning text-center">Tidak ada siswa yang cocok dengan kata kunci pencarian "<?= htmlspecialchars($search_term) ?>".</div>
            <?php elseif (empty($data_siswa_list)): ?>
                <div class="alert alert-info text-center">Belum ada data siswa.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>ID</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Gender</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>No. Telp</th>
                                <th>Tgl. Daftar</th>
                                <th style="min-width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_siswa_list as $siswa): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($siswa['id_siswa']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($siswa['nisn']) ?></td>
                                <td><?= htmlspecialchars($siswa['nama']) ?></td>
                                <td><?= htmlspecialchars($siswa['username']) ?></td>
                                <td class="text-center"><?= ($siswa['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
                                <td><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($siswa['no_tlp'] ?? '-') ?></td>
                                <td class="text-center"><?= htmlspecialchars(date('d M Y H:i', strtotime($siswa['tgl_daftar']))) ?></td>
                                <td class="text-center">
                                    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?action=edit&id=<?= $siswa['id_siswa'] ?>&search=<?= urlencode($search_term) ?>#formEditSiswa" class="btn btn-warning btn-sm me-1 mb-1" title="Edit Siswa">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?search=<?= urlencode($search_term) ?>" class="d-inline-block mb-1" onsubmit="return confirm('Anda yakin ingin menghapus siswa <?= htmlspecialchars(addslashes($siswa['nama'])) ?> (NISN: <?= htmlspecialchars($siswa['nisn']) ?>)? Tindakan ini tidak dapat diurungkan.');">
                                        <input type="hidden" name="id_siswa_hapus" value="<?= $siswa['id_siswa'] ?>">
                                        <button type="submit" name="hapus_siswa" class="btn btn-danger btn-sm" title="Hapus Siswa">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/../../layout/footer.php';
ob_end_flush();
?>