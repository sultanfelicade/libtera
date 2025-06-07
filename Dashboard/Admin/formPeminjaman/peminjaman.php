<?php
// File: Dashboard/Admin/formPeminjaman/peminjaman.php (Validasi & Riwayat Peminjaman oleh Admin)
ob_start(); // Mulai output buffering
session_start();

// --- Autentikasi Admin ---
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['id'])) { 
    $_SESSION['error_message_admin_auth'] = "Sesi admin tidak ditemukan. Silakan login kembali sebagai admin.";
    header("Location: /libtera/login_admin.php"); 
    exit;
}
$id_admin_logged_in = (int)$_SESSION['admin']['id'];
// --- End Autentikasi Admin ---

$title = "Validasi & Riwayat Peminjaman - Libtera Admin";

require __DIR__ . '/../../../connect.php'; // Pastikan $connect adalah objek mysqli

// Ambil dan Hapus pesan dari aksi validasi sebelumnya
$pesan_sukses_validasi = $_SESSION['pesan_sukses_validasi'] ?? null;
$pesan_error_validasi = $_SESSION['pesan_error_validasi'] ?? null;
if (isset($_SESSION['pesan_sukses_validasi'])) unset($_SESSION['pesan_sukses_validasi']);
if (isset($_SESSION['pesan_error_validasi'])) unset($_SESSION['pesan_error_validasi']);

// --- Proses Aksi Validasi (Setujui/Tolak) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // (Logika POST untuk setujui/tolak tetap sama seperti sebelumnya)
    $id_peminjaman_aksi = filter_input(INPUT_POST, 'id_peminjaman', FILTER_VALIDATE_INT);

    if ($id_peminjaman_aksi) {
        if (isset($_POST['setujui'])) {
            $sql_approve = "UPDATE peminjaman SET status = 'PINJAM', id_admin = ?, tgl_pinjam = CURDATE() WHERE id_peminjaman = ? AND status = 'PENDING'";
            $stmt_action = $connect->prepare($sql_approve);
            if ($stmt_action) {
                $stmt_action->bind_param("ii", $id_admin_logged_in, $id_peminjaman_aksi);
                if ($stmt_action->execute() && $stmt_action->affected_rows > 0) {
                    $_SESSION['pesan_sukses_validasi'] = "Peminjaman ID " . htmlspecialchars($id_peminjaman_aksi) . " berhasil disetujui.";
                } else {
                    $_SESSION['pesan_error_validasi'] = "Gagal menyetujui: Peminjaman mungkin sudah diproses atau tidak ditemukan. " . $stmt_action->error;
                }
                $stmt_action->close();
            } else {
                $_SESSION['pesan_error_validasi'] = "Gagal menyiapkan statement persetujuan: " . $connect->error;
            }
        } elseif (isset($_POST['tolak'])) {
            $sql_reject = "UPDATE peminjaman SET status = 'DITOLAK', id_admin = ? WHERE id_peminjaman = ? AND status = 'PENDING'";
            $stmt_action = $connect->prepare($sql_reject);
            if ($stmt_action) {
                $stmt_action->bind_param("ii", $id_admin_logged_in, $id_peminjaman_aksi);
                if ($stmt_action->execute() && $stmt_action->affected_rows > 0) {
                    $_SESSION['pesan_sukses_validasi'] = "Peminjaman ID " . htmlspecialchars($id_peminjaman_aksi) . " berhasil ditolak.";
                } else {
                    $_SESSION['pesan_error_validasi'] = "Gagal menolak: Peminjaman mungkin sudah diproses atau tidak ditemukan. " . $stmt_action->error;
                }
                $stmt_action->close();
            } else {
                $_SESSION['pesan_error_validasi'] = "Gagal menyiapkan statement penolakan: " . $connect->error;
            }
        }
    } else {
        $_SESSION['pesan_error_validasi'] = "ID Peminjaman tidak valid untuk diproses.";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET)); 
    exit;
}

// --- Ambil Data Peminjaman yang Berstatus 'PENDING' ---
$sqlDataPending = "SELECT
                        p.id_peminjaman, p.id_buku, b.judul AS judul_buku,
                        p.id_siswa, s.nisn AS nisn_siswa, s.nama AS nama_siswa,
                        p.tgl_pinjam AS tgl_pengajuan_atau_pinjam, p.status
                    FROM peminjaman p
                    INNER JOIN buku b ON p.id_buku = b.id_buku
                    INNER JOIN siswa s ON p.id_siswa = s.id_siswa 
                    WHERE p.status = 'PENDING'
                    ORDER BY p.id_peminjaman ASC";
$stmtDataPending = $connect->prepare($sqlDataPending);
$dataPermintaanPeminjaman = [];
if ($stmtDataPending && $stmtDataPending->execute()) {
    $resultDataPending = $stmtDataPending->get_result();
    $dataPermintaanPeminjaman = $resultDataPending->fetch_all(MYSQLI_ASSOC);
    $stmtDataPending->close();
} else {
    $pesan_error_load_pending = "Gagal mengambil data permintaan: " . ($stmtDataPending ? $stmtDataPending->error : $connect->error);
}

// --- Hitung Total Buku yang Sedang Dipinjam (Status 'PINJAM') ---
$total_buku_dipinjam = 0;
$sqlTotalDipinjam = "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'PINJAM'";
$resultTotalDipinjam = $connect->query($sqlTotalDipinjam);
if ($resultTotalDipinjam) {
    $rowTotal = $resultTotalDipinjam->fetch_assoc();
    $total_buku_dipinjam = $rowTotal['total'];
} else {
    $pesan_error_load_total = "Gagal mengambil total buku dipinjam: " . $connect->error;
}


// --- Filter Riwayat berdasarkan Bulan, Tahun, dan Status ---
$filter_bulan = isset($_GET['filter_bulan']) ? (int)$_GET['filter_bulan'] : 0; // Default 0 (Semua Bulan)
// PERUBAHAN 1: Logika filter tahun tetap sama, karena (int) akan mengubah input kosong/teks menjadi 0, yang berarti 'Semua Tahun'.
$filter_tahun = isset($_GET['filter_tahun']) && is_numeric($_GET['filter_tahun']) ? (int)$_GET['filter_tahun'] : 0; // Default 0 (Semua Tahun)
$filter_status_riwayat = isset($_GET['filter_status_riwayat']) ? trim($_GET['filter_status_riwayat']) : ''; // Default '' (Semua Status Riwayat)
$filter_applied = isset($_GET['apply_filter']);

// --- Ambil Data Riwayat Peminjaman (Status BUKAN 'PENDING') ---
$sqlRiwayat = "SELECT
                    p.id_peminjaman, b.judul AS judul_buku,
                    s.nama AS nama_siswa, s.nisn AS nisn_siswa,
                    p.tgl_pinjam, p.tgl_kembali, p.status
               FROM peminjaman p
               INNER JOIN buku b ON p.id_buku = b.id_buku
               INNER JOIN siswa s ON p.id_siswa = s.id_siswa
               WHERE p.status != 'PENDING'";

$params_riwayat = [];
$types_riwayat = "";
$conditions_riwayat = [];

if ($filter_applied) {
    if ($filter_bulan > 0) {
        $conditions_riwayat[] = "MONTH(p.tgl_pinjam) = ?";
        $params_riwayat[] = $filter_bulan;
        $types_riwayat .= "i";
    }
    if ($filter_tahun > 0) {
        $conditions_riwayat[] = "YEAR(p.tgl_pinjam) = ?";
        $params_riwayat[] = $filter_tahun;
        $types_riwayat .= "i";
    }
    if (!empty($filter_status_riwayat)) {
        $conditions_riwayat[] = "p.status = ?";
        $params_riwayat[] = $filter_status_riwayat;
        $types_riwayat .= "s";
    }
}

if (!empty($conditions_riwayat)) {
    $sqlRiwayat .= " AND " . implode(" AND ", $conditions_riwayat);
}
$sqlRiwayat .= " ORDER BY p.id_peminjaman DESC";

$stmtRiwayat = $connect->prepare($sqlRiwayat);
$dataRiwayatPeminjaman = [];
if ($stmtRiwayat) {
    if (!empty($params_riwayat)) {
        $stmtRiwayat->bind_param($types_riwayat, ...$params_riwayat);
    }
    if ($stmtRiwayat->execute()) {
        $resultRiwayat = $stmtRiwayat->get_result();
        $dataRiwayatPeminjaman = $resultRiwayat->fetch_all(MYSQLI_ASSOC);
    } else {
        $pesan_error_load_riwayat = "Gagal mengambil data riwayat (filter): " . $stmtRiwayat->error;
    }
    $stmtRiwayat->close();
} else {
    $pesan_error_load_riwayat = "Gagal menyiapkan statement riwayat: " . $connect->error;
}

// Daftar bulan untuk dropdown
$daftar_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
// PERUBAHAN 2: Baris di bawah ini untuk membuat daftar tahun dropdown sudah tidak diperlukan lagi, jadi dihapus.
// $tahun_sekarang = date('Y');
// $daftar_tahun = range($tahun_sekarang, $tahun_sekarang - 5); 

// Daftar status untuk filter riwayat
$daftar_status_riwayat = ['PINJAM', 'KEMBALI', 'DITOLAK', 'DIBATALKAN'];


include_once __DIR__ . '/../../../layout/header.php'; 
?>

<div class="container p-4 mt-5">
    <div class="mb-4">
        <h2 class="text-primary fw-bold"><i class="fas fa-user-shield me-2"></i>Admin: Peminjaman Buku</h2>
        <p class="text-muted">Validasi permintaan dan lihat riwayat peminjaman.</p>
    </div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Buku Sedang Dipinjam</h5>
                            <p class="card-text fs-2 fw-bold"><?= $total_buku_dipinjam; ?></p>
                        </div>
                        <i class="fas fa-book-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Bagian notifikasi pesan tetap sama
    if ($pesan_sukses_validasi) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_sukses_validasi) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if ($pesan_error_validasi) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_error_validasi) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if (isset($pesan_error_load_pending)) {
        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>Peringatan: " . htmlspecialchars($pesan_error_load_pending) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
     if (isset($pesan_error_load_total)) {
        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>Peringatan: " . htmlspecialchars($pesan_error_load_total) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if (isset($pesan_error_load_riwayat)) {
         echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>Peringatan: " . htmlspecialchars($pesan_error_load_riwayat) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if (isset($_SESSION['error_message_admin_auth'])) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($_SESSION['error_message_admin_auth']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['error_message_admin_auth']);
    }
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-hourglass-start me-2"></i>Permintaan Menunggu Validasi</h5>
        </div>
        <div class="card-body">
            <?php if (empty($dataPermintaanPeminjaman)): ?>
                <div class="alert alert-secondary text-center">Tidak ada permintaan peminjaman yang menunggu validasi.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th>Judul Buku</th>
                                <th>Tgl Diajukan/Pinjam</th>
                                <th>Status</th>
                                <th style="min-width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataPermintaanPeminjaman as $item): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($item["id_peminjaman"]); ?></td>
                                    <td><?= htmlspecialchars($item["nama_siswa"]); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($item["nisn_siswa"]); ?></td>
                                    <td><?= htmlspecialchars($item["judul_buku"]); ?></td>
                                    <td class="text-center">
                                        <?= !empty($item["tgl_pengajuan_atau_pinjam"]) ? htmlspecialchars(date('d M Y', strtotime($item["tgl_pengajuan_atau_pinjam"]))) : "<em>Menunggu</em>"; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark fs-6">
                                            <?= htmlspecialchars(strtoupper($item["status"])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?<?= http_build_query($_GET) // Pertahankan filter GET saat POST ?>" class="d-inline-block me-1 mb-1">
                                            <input type="hidden" name="id_peminjaman" value="<?= $item["id_peminjaman"]; ?>">
                                            <button type="submit" name="setujui" class="btn btn-success btn-sm" title="Setujui Peminjaman" onclick="return confirm('Setujui peminjaman buku \'<?= htmlspecialchars(addslashes($item['judul_buku'])) ?>\' oleh <?= htmlspecialchars(addslashes($item['nama_siswa'])) ?>?')">
                                                <i class="fas fa-check me-1"></i> Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?<?= http_build_query($_GET) // Pertahankan filter GET saat POST ?>" class="d-inline-block mb-1">
                                            <input type="hidden" name="id_peminjaman" value="<?= $item["id_peminjaman"]; ?>">
                                            <button type="submit" name="tolak" class="btn btn-danger btn-sm" title="Tolak Peminjaman" onclick="return confirm('Tolak peminjaman buku \'<?= htmlspecialchars(addslashes($item['judul_buku'])) ?>\' oleh <?= htmlspecialchars(addslashes($item['nama_siswa'])) ?>?')">
                                                <i class="fas fa-times me-1"></i> Tolak
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

    <div class="card shadow-sm mt-5">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Peminjaman (Sudah Diproses)</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row g-3 mb-4 align-items-end">
                <div class="col-md-3">
                    <label for="filter_bulan" class="form-label">Bulan (Tgl. Pinjam):</label>
                    <select name="filter_bulan" id="filter_bulan" class="form-select form-select-sm">
                        <option value="0">Semua Bulan</option>
                        <?php foreach ($daftar_bulan as $angka => $nama_bulan): ?>
                            <option value="<?= $angka; ?>" <?= ($filter_applied && $filter_bulan == $angka) ? 'selected' : ''; ?>><?= $nama_bulan; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="filter_tahun" class="form-label">Tahun (Tgl. Pinjam):</label>
                    <input type="number" name="filter_tahun" id="filter_tahun" class="form-control form-control-sm" 
                           placeholder="Contoh: 2024"
                           value="<?= ($filter_applied && $filter_tahun > 0) ? htmlspecialchars($filter_tahun) : ''; ?>">
                </div>

                <div class="col-md-3">
                    <label for="filter_status_riwayat" class="form-label">Status Peminjaman:</label>
                    <select name="filter_status_riwayat" id="filter_status_riwayat" class="form-select form-select-sm">
                        <option value="">Semua Status (Riwayat)</option>
                        <?php foreach ($daftar_status_riwayat as $status_opt): ?>
                            <option value="<?= $status_opt; ?>" <?= ($filter_applied && $filter_status_riwayat == $status_opt) ? 'selected' : ''; ?>><?= ucfirst(strtolower($status_opt)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" name="apply_filter" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Terapkan</button>
                </div>
                <div class="col-md-auto">
                     <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>

            <?php if (empty($dataRiwayatPeminjaman) && $filter_applied): ?>
                 <div class="alert alert-warning text-center">
                     Tidak ada riwayat peminjaman yang cocok dengan filter
                     <?= $filter_bulan > 0 ? "bulan " . $daftar_bulan[$filter_bulan] : ''; ?>
                     <?= $filter_tahun > 0 ? "tahun " . $filter_tahun : ''; ?>
                     <?= !empty($filter_status_riwayat) ? "status " . ucfirst(strtolower($filter_status_riwayat)) : ''; ?>.
                 </div>
            <?php elseif (empty($dataRiwayatPeminjaman)): ?>
                <div class="alert alert-secondary text-center">Belum ada riwayat peminjaman yang sudah diproses.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th>Judul Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataRiwayatPeminjaman as $riwayat): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($riwayat["id_peminjaman"]); ?></td>
                                    <td><?= htmlspecialchars($riwayat["nama_siswa"]); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($riwayat["nisn_siswa"]); ?></td>
                                    <td><?= htmlspecialchars($riwayat["judul_buku"]); ?></td>
                                    <td class="text-center"><?= !empty($riwayat["tgl_pinjam"]) ? htmlspecialchars(date('d M Y', strtotime($riwayat["tgl_pinjam"]))) : '-'; ?></td>
                                    <td class="text-center"><?= !empty($riwayat["tgl_kembali"]) ? htmlspecialchars(date('d M Y', strtotime($riwayat["tgl_kembali"]))) : '-'; ?></td>
                                    <td class="text-center">
                                        <?php
                                        $status = $riwayat["status"];
                                        $badgeClass = 'bg-dark'; 
                                        $statusText = htmlspecialchars(ucfirst(strtolower($status)));
                                        switch ($status) {
                                            case 'PINJAM': $badgeClass = 'bg-primary'; $statusText = 'Dipinjam'; break;
                                            case 'KEMBALI': $badgeClass = 'bg-success'; $statusText = 'Dikembalikan'; break;
                                            case 'DITOLAK': $badgeClass = 'bg-danger'; $statusText = 'Ditolak'; break;
                                            case 'DIBATALKAN': $badgeClass = 'bg-secondary'; $statusText = 'Dibatalkan'; break;
                                        }
                                        echo "<span class=\"badge $badgeClass\">$statusText</span>";
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div> <?php
include_once __DIR__ . '/../../../layout/footer.php'; 
ob_end_flush();
?>