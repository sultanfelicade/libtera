<?php
ob_start(); // Mulai output buffering
session_start();

// --- Autentikasi Admin ---
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['id'])) { 
    $_SESSION['error_message'] = "Sesi admin tidak ditemukan. Silakan login kembali.";
    header("Location: /libtera/login_admin.php"); // Sesuaikan path login admin Anda
    exit;
}
$id_admin_logged_in = (int)$_SESSION['admin']['id'];
// --- End Autentikasi Admin ---

$title = "Kelola Denda & Pengembalian - Libtera Admin";
require __DIR__ . '/../../../connect.php'; // Koneksi ke database

// --- KONFIGURASI ATURAN DENDA & PEMINJAMAN ---
define('DURASI_PINJAM_HARI_DEFAULT', 7);
define('DENDA_PER_PERIODE_DEFAULT', 5000);
define('PERIODE_DENDA_HARI_DEFAULT', 7); // 7 untuk per minggu
// --- END KONFIGURASI ---

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

// --- BAGIAN PROSES AKSI FORM (POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Proses Catat Denda Baru & Pengembalian Buku
    if (isset($_POST['catat_denda_dan_pengembalian_submit'])) {
        $id_peminjaman_catat = isset($_POST['id_peminjaman_catat_modal']) ? (int)$_POST['id_peminjaman_catat_modal'] : 0;
        $jumlah_denda_dikenakan_catat_str = str_replace('.', '', $_POST['jumlah_denda_dikenakan_catat_modal'] ?? '0');
        $jumlah_denda_dikenakan_catat = (float)str_replace(',', '.', $jumlah_denda_dikenakan_catat_str);
        
        $jumlah_dibayar_sekarang_catat_str = str_replace('.', '', $_POST['jumlah_dibayar_sekarang_catat_modal'] ?? '0');
        $jumlah_dibayar_sekarang_catat = (float)str_replace(',', '.', $jumlah_dibayar_sekarang_catat_str);

        $tgl_pengembalian_catat = $_POST['tgl_pengembalian_catat_modal'] ?? date('Y-m-d');
        $keterangan_catat = trim($_POST['keterangan_catat_modal'] ?? '');

        if ($id_peminjaman_catat > 0 && $jumlah_denda_dikenakan_catat >= 0 && $jumlah_dibayar_sekarang_catat >= 0) {
            $connect->begin_transaction();
            try {
                $stmt_update_peminjaman = $connect->prepare("UPDATE Peminjaman SET status = 'KEMBALI', tgl_kembali = ? WHERE id_peminjaman = ? AND status = 'PINJAM'");
                if (!$stmt_update_peminjaman) throw new Exception("Gagal menyiapkan statement update peminjaman: " . $connect->error);
                $stmt_update_peminjaman->bind_param("si", $tgl_pengembalian_catat, $id_peminjaman_catat);
                $stmt_update_peminjaman->execute();
                $peminjaman_updated = $stmt_update_peminjaman->affected_rows;
                $stmt_update_peminjaman->close();

                if ($peminjaman_updated > 0) {
                    if ($jumlah_denda_dikenakan_catat > 0) {
                        $status_denda_catat = 'Belum Lunas';
                        // Menggunakan nama kolom dari Denda table SQL yang disepakati untuk manual:
                        // tgl_transaksi_denda, id_admin_pencatat
                        // ENUM('Lunas', 'Belum Lunas', 'Dihapuskan')
                        $tgl_transaksi_denda_val = $tgl_pengembalian_catat; 

                        if ($jumlah_dibayar_sekarang_catat >= $jumlah_denda_dikenakan_catat) {
                            $status_denda_catat = 'Lunas';
                            $jumlah_dibayar_sekarang_catat = $jumlah_denda_dikenakan_catat;
                        }
                        // Tidak ada status 'Dibayar Sebagian' di ENUM tabel Denda sederhana yang terakhir kita buat
                        // Jika ingin ada, ENUM perlu diubah. Untuk sekarang, jika bayar > 0 tapi < dikenakan, tetap 'Belum Lunas'.

                        // Sesuaikan dengan kolom tabel Denda yang sederhana:
                        // `id_admin_pencatat`, `tgl_transaksi_denda`
                        // `jumlah_telah_dibayar`
                        $stmt_insert_denda = $connect->prepare(
                            "INSERT INTO Denda (id_peminjaman, jumlah_denda_dikenakan, jumlah_telah_dibayar, tgl_transaksi_denda, status_denda, id_admin_pencatat, keterangan) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)"
                        );
                        if (!$stmt_insert_denda) throw new Exception("Gagal menyiapkan statement insert denda: " . $connect->error);
                        
                        $stmt_insert_denda->bind_param("iddssis", $id_peminjaman_catat, $jumlah_denda_dikenakan_catat, $jumlah_dibayar_sekarang_catat, $tgl_transaksi_denda_val, $status_denda_catat, $id_admin_logged_in, $keterangan_catat);
                        
                        if (!$stmt_insert_denda->execute()) {
                            throw new Exception("Gagal mencatat denda: " . $stmt_insert_denda->error);
                        }
                        $stmt_insert_denda->close();
                        $_SESSION['success_message'] = "Pengembalian & pencatatan denda (ID Peminjaman #$id_peminjaman_catat) berhasil.";
                    } else {
                         $_SESSION['success_message'] = "Pengembalian buku (ID Peminjaman #$id_peminjaman_catat) berhasil (tidak ada denda).";
                    }
                    $connect->commit();
                } else {
                    throw new Exception("Gagal memproses pengembalian. Peminjaman ID #$id_peminjaman_catat mungkin sudah dikembalikan atau tidak ditemukan dengan status 'PINJAM'.");
                }
            } catch (Exception $e) {
                $connect->rollback();
                $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "Data untuk pencatatan denda dan pengembalian tidak valid.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
        exit;
    }

    // Proses Pembayaran Denda Lanjutan (yang sudah tercatat)
    if (isset($_POST['proses_pembayaran_submit'])) {
        $id_denda_bayar = isset($_POST['id_denda_bayar_modal_hidden_lanjutan']) ? (int)$_POST['id_denda_bayar_modal_hidden_lanjutan'] : 0;
        $jumlah_bayar_sekarang_str = str_replace('.', '', $_POST['jumlah_bayar_sekarang_modal_input_lanjutan'] ?? '0');
        $jumlah_bayar_sekarang = (float)str_replace(',', '.', $jumlah_bayar_sekarang_str);
        $tgl_pembayaran = $_POST['tgl_pembayaran_modal_input_lanjutan'] ?? date('Y-m-d');
        $keterangan_pembayaran = trim($_POST['keterangan_pembayaran_modal_input_lanjutan'] ?? '');

        if ($id_denda_bayar > 0 && $jumlah_bayar_sekarang > 0) {
            $connect->begin_transaction();
            try {
                // Menggunakan nama kolom dari tabel Denda sederhana: jumlah_telah_dibayar
                $stmt_curr = $connect->prepare("SELECT jumlah_denda_dikenakan, jumlah_telah_dibayar, keterangan, status_denda FROM Denda WHERE id_denda = ? FOR UPDATE");
                if (!$stmt_curr) throw new Exception("Gagal menyiapkan statement (ambil data denda): " . $connect->error);
                $stmt_curr->bind_param("i", $id_denda_bayar);
                $stmt_curr->execute();
                $res_curr = $stmt_curr->get_result();
                $denda_curr = $res_curr->fetch_assoc();
                $stmt_curr->close();

                if ($denda_curr) {
                    if ($denda_curr['status_denda'] == 'Lunas' || $denda_curr['status_denda'] == 'Dihapuskan') {
                         throw new Exception("Denda ID #$id_denda_bayar sudah lunas atau dihapuskan.");
                    }

                    $total_sudah_dibayar_baru = $denda_curr['jumlah_telah_dibayar'] + $jumlah_bayar_sekarang;
                    $status_denda_baru = 'Belum Lunas'; // Default, akan diubah jika lunas

                    if ($total_sudah_dibayar_baru >= $denda_curr['jumlah_denda_dikenakan']) {
                        $status_denda_baru = 'Lunas';
                        $total_sudah_dibayar_baru = $denda_curr['jumlah_denda_dikenakan']; 
                    }
                    // Tidak ada status 'Dibayar Sebagian' di ENUM tabel Denda sederhana yang terakhir,
                    // jadi jika belum lunas, tetap 'Belum Lunas'.
                    
                    $keterangan_log = "Pembayaran Lanjutan: Rp " . number_format($jumlah_bayar_sekarang, 0, ',', '.') . " pada " . date('d M Y', strtotime($tgl_pembayaran)) . " oleh Admin ID: " . $id_admin_logged_in . ".";
                    if(!empty($keterangan_pembayaran)) $keterangan_log .= " Ket: " . $keterangan_pembayaran;
                    $keterangan_final = $denda_curr['keterangan'] . (!empty($denda_curr['keterangan']) ? "\n" : "") . $keterangan_log;

                    // Sesuaikan dengan kolom tabel Denda yang sederhana: id_admin_pencatat, tgl_transaksi_denda
                    $stmt_update = $connect->prepare("UPDATE Denda SET jumlah_telah_dibayar = ?, tgl_transaksi_denda = ?, status_denda = ?, id_admin_pencatat = ?, keterangan = ? WHERE id_denda = ?");
                    if (!$stmt_update) throw new Exception("Gagal menyiapkan statement (update denda): " . $connect->error);
                    
                    $stmt_update->bind_param("dssisi", $total_sudah_dibayar_baru, $tgl_pembayaran, $status_denda_baru, $id_admin_logged_in, $keterangan_final, $id_denda_bayar);
                    
                    if ($stmt_update->execute()) {
                        $connect->commit();
                        $_SESSION['success_message'] = "Pembayaran lanjutan untuk denda ID #$id_denda_bayar berhasil diproses.";
                    } else {
                        throw new Exception("Gagal mengupdate denda: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                } else {
                    throw new Exception("Denda dengan ID #$id_denda_bayar tidak ditemukan.");
                }
            } catch (Exception $e) {
                $connect->rollback();
                $_SESSION['error_message'] = "Terjadi kesalahan saat proses pembayaran: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "Data pembayaran tidak valid. Pastikan ID denda valid dan jumlah bayar lebih dari 0.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET)); 
        exit;
    }

    // Proses Hapuskan Denda (yang sudah tercatat)
    if (isset($_POST['hapuskan_denda_submit'])) {
        $id_denda_hapus = isset($_POST['id_denda_hapus_modal_hidden_confirm']) ? (int)$_POST['id_denda_hapus_modal_hidden_confirm'] : 0;
        $alasan_hapus = trim($_POST['keterangan_hapus_modal_input_confirm'] ?? '');

        if (empty($alasan_hapus)) {
             $_SESSION['error_message'] = "Alasan penghapusan denda wajib diisi.";
        } elseif ($id_denda_hapus > 0) {
            $stmt_get_data = $connect->prepare("SELECT keterangan, jumlah_denda_dikenakan FROM Denda WHERE id_denda = ?");
            $current_keterangan = '';
            $denda_dikenakan_saat_hapus = 0;
            if($stmt_get_data){
                $stmt_get_data->bind_param("i", $id_denda_hapus);
                $stmt_get_data->execute();
                $res_data = $stmt_get_data->get_result();
                if($row_data = $res_data->fetch_assoc()){
                    $current_keterangan = $row_data['keterangan'];
                    $denda_dikenakan_saat_hapus = $row_data['jumlah_denda_dikenakan'];
                }
                $stmt_get_data->close();
            }
            $keterangan_final_hapus = $current_keterangan . (!empty($current_keterangan) ? "\n" : "") . "Dihapuskan oleh Admin ID: " . $id_admin_logged_in . " pada " . date('Y-m-d H:i:s') . ". Alasan: " . $alasan_hapus;

            // Sesuaikan dengan kolom tabel Denda yang sederhana
            // Saat dihapuskan, status jadi 'Dihapuskan', jumlah_telah_dibayar diisi sama dengan jumlah_denda_dikenakan
            $stmt = $connect->prepare("UPDATE Denda SET status_denda = 'Dihapuskan', id_admin_pencatat = ?, keterangan = ?, jumlah_telah_dibayar = ?, tgl_transaksi_denda = CURDATE() WHERE id_denda = ?");
            if ($stmt) {
                $stmt->bind_param("isdi", $id_admin_logged_in, $keterangan_final_hapus, $denda_dikenakan_saat_hapus, $id_denda_hapus);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Denda ID #$id_denda_hapus berhasil ditandai sebagai 'Dihapuskan'.";
                } else {
                    $_SESSION['error_message'] = "Gagal menghapuskan denda: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['error_message'] = "Gagal menyiapkan statement (hapus denda): " . $connect->error;
            }
        } else {
            $_SESSION['error_message'] = "ID denda tidak valid untuk dihapuskan.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
        exit;
    }
}

// --- PENGAMBILAN DATA UNTUK DITAMPILKAN ---
$search_term_pinjam = $_GET['search_pinjam'] ?? ''; 
$filter_status_denda = $_GET['filter_status_denda'] ?? '';
$search_term_denda = $_GET['search_denda'] ?? '';

// 1. Ambil Peminjaman Aktif yang Berpotensi Denda
$sql_pinjaman_aktif = "SELECT 
                            p.id_peminjaman, p.tgl_pinjam,
                            s.nama AS nama_siswa, s.nisn AS nisn_siswa,
                            b.judul AS judul_buku,
                            (SELECT COUNT(*) FROM Denda d_check WHERE d_check.id_peminjaman = p.id_peminjaman AND d_check.status_denda IN ('Belum Lunas')) AS denda_belum_lunas_count
                        FROM Peminjaman p
                        JOIN Siswa s ON p.id_siswa = s.id_siswa
                        JOIN Buku b ON p.id_buku = b.id_buku
                        WHERE p.status = 'PINJAM'";
$conditions_pinjam_aktif = [];
$params_pinjam_aktif = [];
$types_pinjam_aktif = '';
if (!empty($search_term_pinjam)) {
    $conditions_pinjam_aktif[] = "(s.nama LIKE ? OR s.nisn LIKE ? OR b.judul LIKE ? OR CAST(p.id_peminjaman AS CHAR) LIKE ?)";
    $like_search_pinjam = "%" . $search_term_pinjam . "%";
    for ($i = 0; $i < 4; $i++) {
        $params_pinjam_aktif[] = $like_search_pinjam;
        $types_pinjam_aktif .= 's';
    }
}
if (!empty($conditions_pinjam_aktif)) {
    $sql_pinjaman_aktif .= " AND (" . implode(" AND ", $conditions_pinjam_aktif) . ")"; // Menggunakan AND karena WHERE p.status sudah ada
}
// Filter tambahan: hanya tampilkan yang BELUM memiliki denda 'Belum Lunas'
$sql_pinjaman_aktif .= " HAVING denda_belum_lunas_count = 0";
$sql_pinjaman_aktif .= " ORDER BY p.tgl_pinjam ASC";

$stmt_pinjaman_aktif = $connect->prepare($sql_pinjaman_aktif);
$result_pinjaman_aktif = null;
if($stmt_pinjaman_aktif){
    if(!empty($params_pinjam_aktif)){
        $stmt_pinjaman_aktif->bind_param($types_pinjam_aktif, ...$params_pinjaman_aktif);
    }
    $stmt_pinjaman_aktif->execute();
    $result_pinjaman_aktif = $stmt_pinjaman_aktif->get_result();
} else {
    $error_message .= '<div class="alert alert-danger">Gagal menyiapkan daftar peminjaman aktif: ' . $connect->error . '</div>';
}

// 2. Ambil Daftar Denda yang Sudah Tercatat
// Menggunakan id_admin_pencatat dan tgl_transaksi_denda dari tabel Denda sederhana
$sql_denda_tercatat = "SELECT 
                        d.id_denda, 
                        p.id_peminjaman, 
                        s.nama AS nama_siswa, s.nisn AS nisn_siswa,
                        b.judul AS judul_buku, 
                        d.jumlah_denda_dikenakan, 
                        d.jumlah_telah_dibayar,
                        d.status_denda, 
                        d.tgl_transaksi_denda, 
                        adm.nama_admin AS nama_admin_pencatat,
                        d.keterangan
                    FROM Denda d
                    JOIN Peminjaman p ON d.id_peminjaman = p.id_peminjaman
                    JOIN Siswa s ON p.id_siswa = s.id_siswa
                    JOIN Buku b ON p.id_buku = b.id_buku
                    LEFT JOIN admin adm ON d.id_admin_pencatat = adm.id 
                    ";
$conditions_denda = [];
$params_denda = [];
$types_denda = '';
if (!empty($filter_status_denda)) {
    $conditions_denda[] = "d.status_denda = ?";
    $params_denda[] = $filter_status_denda;
    $types_denda .= 's';
}
if (!empty($search_term_denda)) {
    $conditions_denda[] = "(s.nama LIKE ? OR s.nisn LIKE ? OR b.judul LIKE ? OR CAST(p.id_peminjaman AS CHAR) LIKE ? OR CAST(d.id_denda AS CHAR) LIKE ?)";
    $like_search_denda = "%" . $search_term_denda . "%";
    for ($i = 0; $i < 5; $i++) {
        $params_denda[] = $like_search_denda;
        $types_denda .= 's';
    }
}
if (!empty($conditions_denda)) {
    $sql_denda_tercatat .= " WHERE " . implode(" AND ", $conditions_denda);
}
$sql_denda_tercatat .= " ORDER BY CASE d.status_denda 
                        WHEN 'Belum Lunas' THEN 1
                        WHEN 'Lunas' THEN 2      -- Tidak ada 'Dibayar Sebagian' di ENUM sederhana
                        WHEN 'Dihapuskan' THEN 3
                        ELSE 4 END, d.tgl_transaksi_denda DESC, d.id_denda DESC";

$stmt_denda_tercatat = $connect->prepare($sql_denda_tercatat);
$result_denda_tercatat = null;
if ($stmt_denda_tercatat) {
    if (!empty($params_denda)) {
        $stmt_denda_tercatat->bind_param($types_denda, ...$params_denda);
    }
    $stmt_denda_tercatat->execute();
    $result_denda_tercatat = $stmt_denda_tercatat->get_result();
} else {
    $error_message .= '<div class="alert alert-danger">Gagal menyiapkan daftar denda tercatat: ' . $connect->error . '</div>';
}

include_once __DIR__ . '/../../../layout/header.php';
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="fas fa-file-invoice-dollar me-2"></i> Kelola Denda & Pengembalian Buku</h2>

    <?= $message ?>
    <?= $error_message ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-book-reader me-1"></i> Peminjaman Aktif Berpotensi Denda</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="#peminjamanAktifAnchor" class="row g-3 mb-3"> <a id="peminjamanAktifAnchor"></a>
                <div class="col-md-10">
                    <input type="text" name="search_pinjam" class="form-control form-control-sm" value="<?= htmlspecialchars($search_term_pinjam) ?>" placeholder="Cari Peminjaman Aktif (Nama/NISN Siswa, Judul, ID Pinjam)...">
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-info btn-sm w-100 me-1"><i class="fas fa-search"></i> Cari</button>
                    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>#peminjamanAktifAnchor" class="btn btn-outline-secondary btn-sm w-100" title="Reset Cari Peminjaman"><i class="fas fa-undo"></i></a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID Pinjam</th>
                            <th>Siswa (NISN)</th>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Keterlambatan</th>
                            <th class="text-end">Estimasi Denda (Rp)</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $ada_peminjaman_aktif_berpotensi_denda = false;
                    if ($result_pinjaman_aktif && $result_pinjaman_aktif->num_rows > 0):
                        while($p_aktif = $result_pinjaman_aktif->fetch_assoc()):
                            $estimasi_denda_item = 0;
                            $hari_terlambat_item = 0;
                            $status_terlambat_text_item = "<em>Belum jatuh tempo</em>";
                            $tgl_jatuh_tempo_item_obj = null;
                            $badge_estimasi = "bg-secondary";
                            try {
                                $tgl_pinjam_item_obj = new DateTime($p_aktif['tgl_pinjam']);
                                $tgl_pinjam_item_obj->setTime(0,0,0);
                                $tgl_jatuh_tempo_item_obj = clone $tgl_pinjam_item_obj;
                                $tgl_jatuh_tempo_item_obj->modify('+' . DURASI_PINJAM_HARI_DEFAULT . ' days');
                                $now_obj_item = new DateTime();
                                $now_obj_item->setTime(0,0,0);
                                if ($now_obj_item > $tgl_jatuh_tempo_item_obj) {
                                    $interval_item = $now_obj_item->diff($tgl_jatuh_tempo_item_obj);
                                    $hari_terlambat_item = $interval_item->days;
                                    if ($hari_terlambat_item >= 1) {
                                        $periode_efektif_item = ceil($hari_terlambat_item / PERIODE_DENDA_HARI_DEFAULT);
                                        $estimasi_denda_item = $periode_efektif_item * DENDA_PER_PERIODE_DEFAULT;
                                        $status_terlambat_text_item = $hari_terlambat_item . " hari";
                                        $badge_estimasi = "bg-danger";
                                        $ada_peminjaman_aktif_berpotensi_denda = true;
                                    } else {
                                        $status_terlambat_text_item = "<em>Tidak terlambat</em>";
                                        $badge_estimasi = "bg-success";
                                    }
                                } else {
                                    $badge_estimasi = "bg-primary";
                                }
                            } catch (Exception $e) {
                                $status_terlambat_text_item = "<em class='text-danger'>Error tgl</em>";
                            }
                    ?>
                        <tr>
                            <td><?= $p_aktif['id_peminjaman'] ?></td>
                            <td><?= htmlspecialchars($p_aktif['nama_siswa']) ?> (<?= htmlspecialchars($p_aktif['nisn_siswa']) ?>)</td>
                            <td><?= htmlspecialchars($p_aktif['judul_buku']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($p_aktif['tgl_pinjam']))) ?></td>
                            <td><?= $tgl_jatuh_tempo_item_obj ? htmlspecialchars($tgl_jatuh_tempo_item_obj->format('d M Y')) : '-' ?></td>
                            <td class="text-center"><span class="badge <?= $badge_estimasi ?>"><?= $status_terlambat_text_item ?></span></td>
                            <td class="text-end fw-bold"><?= number_format($estimasi_denda_item, 0, ',', '.') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalCatatDendaPengembalian"
                                        data-id-peminjaman="<?= $p_aktif['id_peminjaman'] ?>"
                                        data-nama-siswa="<?= htmlspecialchars($p_aktif['nama_siswa']) ?>"
                                        data-judul-buku="<?= htmlspecialchars($p_aktif['judul_buku']) ?>"
                                        data-tgl-pinjam="<?= htmlspecialchars(date('d M Y', strtotime($p_aktif['tgl_pinjam']))) ?>"
                                        data-tgl-jatuh-tempo="<?= $tgl_jatuh_tempo_item_obj ? htmlspecialchars($tgl_jatuh_tempo_item_obj->format('d M Y')) : '' ?>"
                                        data-hari-terlambat="<?= $hari_terlambat_item ?>"
                                        data-estimasi-denda="<?= $estimasi_denda_item ?>"
                                        title="Catat Denda & Proses Pengembalian">
                                    <i class="fas fa-clipboard-check me-1"></i> Proses
                                </button>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else: ?>
                         <tr><td colspan="8" class="text-center py-3"><em>Tidak ada peminjaman aktif yang cocok dengan pencarian Anda, atau semua sudah memiliki denda aktif.</em></td></tr>
                    <?php
                    endif;
                     if ($result_pinjaman_aktif && $result_pinjaman_aktif->num_rows > 0 && !$ada_peminjaman_aktif_berpotensi_denda && empty($search_term_pinjam) && $result_pinjaman_aktif->num_rows > 0) { // Hanya tampil jika ada data dan memang tidak ada yang telat
                        echo '<tr><td colspan="8" class="text-center py-3 text-success"><em>Tidak ada peminjaman aktif yang terlambat saat ini.</em></td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list-ul me-1"></i> Daftar Denda Tercatat</h6>
        </div>
         <div class="card-body">
            <form method="GET" action="#dendaTercatatAnchor" class="row g-3 mb-3"> <a id="dendaTercatatAnchor"></a>
                <div class="col-md-4 col-lg-3">
                    <label for="filter_status_denda" class="form-label">Status Denda:</label>
                    <select name="filter_status_denda" id="filter_status_denda" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="Belum Lunas" <?= ($filter_status_denda == 'Belum Lunas') ? 'selected' : '' ?>>Belum Lunas</option>
                        <option value="Lunas" <?= ($filter_status_denda == 'Lunas') ? 'selected' : '' ?>>Lunas</option>
                        <option value="Dihapuskan" <?= ($filter_status_denda == 'Dihapuskan') ? 'selected' : '' ?>>Dihapuskan</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-7">
                    <label for="search_denda" class="form-label">Cari Denda Tercatat (Nama/NISN Siswa, Judul, ID Pinjam/Denda):</label>
                    <input type="text" name="search_denda" id="search_denda" class="form-control form-control-sm" value="<?= htmlspecialchars($search_term_denda) ?>" placeholder="Masukkan kata kunci...">
                </div>
                <div class="col-md-2 col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 me-1"><i class="fas fa-search me-1"></i> Cari</button>
                     <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>#dendaTercatatAnchor" class="btn btn-outline-secondary btn-sm w-100" title="Reset Filter Denda"><i class="fas fa-undo"></i></a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped table-hover" id="dataTableDendaTercatat" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>ID Pinjam</th>
                            <th>Siswa (NISN)</th>
                            <th>Judul Buku</th>
                            <th class="text-end">Dikenakan (Rp)</th>
                            <th class="text-end">Dibayar (Rp)</th>
                            <th class="text-center">Status</th>
                            <th>Tgl Transaksi</th>
                            <th>Admin Pencatat</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_denda_tercatat && $result_denda_tercatat->num_rows > 0): ?>
                            <?php while($denda = $result_denda_tercatat->fetch_assoc()): 
                                $sisa_denda = $denda['jumlah_denda_dikenakan'] - $denda['jumlah_telah_dibayar'];
                            ?>
                                <tr class="<?php 
                                    if ($denda['status_denda'] == 'Belum Lunas' && $sisa_denda > 0) echo 'table-danger';
                                ?>">
                                    <td><?= $denda['id_denda'] ?></td>
                                    <td><?= $denda['id_peminjaman'] ?></td>
                                    <td><?= htmlspecialchars($denda['nama_siswa']) ?> (<?= htmlspecialchars($denda['nisn_siswa']) ?>)</td>
                                    <td><?= htmlspecialchars($denda['judul_buku']) ?></td>
                                    <td class="text-end"><?= number_format($denda['jumlah_denda_dikenakan'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($denda['jumlah_telah_dibayar'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badge_class = 'bg-secondary';
                                        if ($denda['status_denda'] == 'Belum Lunas') $badge_class = 'bg-danger';
                                        else if ($denda['status_denda'] == 'Lunas') $badge_class = 'bg-success';
                                        else if ($denda['status_denda'] == 'Dihapuskan') $badge_class = 'bg-info text-dark';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($denda['status_denda']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($denda['tgl_transaksi_denda']))) ?></td>
                                    <td><?= htmlspecialchars($denda['nama_admin_pencatat'] ?? '-') ?></td>
                                    <td title="<?= htmlspecialchars($denda['keterangan']) ?>">
                                        <?= nl2br(htmlspecialchars(mb_strimwidth($denda['keterangan'], 0, 35, "..."))) ?>
                                    </td>
                                    <td class="text-center">
                                         <div class="btn-group" role="group">
                                        <?php if ($denda['status_denda'] == 'Belum Lunas'): ?>
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#modalProsesPembayaran" 
                                                    data-id-denda="<?= $denda['id_denda'] ?>"
                                                    data-nama-siswa="<?= htmlspecialchars($denda['nama_siswa']) ?>"
                                                    data-judul-buku="<?= htmlspecialchars($denda['judul_buku']) ?>"
                                                    data-denda-dikenakan="<?= $denda['jumlah_denda_dikenakan'] ?>"
                                                    data-sudah-dibayar="<?= $denda['jumlah_telah_dibayar'] ?>"
                                                    title="Proses Pembayaran Lanjutan">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#modalHapuskanDenda"
                                                    data-id-denda-hapus="<?= $denda['id_denda'] ?>"
                                                    data-nama-siswa-hapus="<?= htmlspecialchars($denda['nama_siswa']) ?>"
                                                    data-judul-buku-hapus="<?= htmlspecialchars($denda['judul_buku']) ?>"
                                                    title="Hapuskan Denda">
                                                <i class="fas fa-eraser"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="Sudah Lunas/Dihapuskan"><i class="fas fa-check-circle"></i></button>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="text-center py-4"><em>Tidak ada data denda yang cocok dengan filter atau pencarian Anda.</em></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div> <div class="modal fade" id="modalCatatDendaPengembalian" tabindex="-1" aria-labelledby="modalCatatDendaPengembalianLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET) // Pertahankan filter/search saat submit ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCatatDendaPengembalianLabel"><i class="fas fa-book-dead me-2"></i> Catat Denda & Proses Pengembalian Buku</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_peminjaman_catat_modal" id="id_peminjaman_catat_modal_hidden">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Siswa:</strong> <span id="catat_nama_siswa_modal_text"></span></p>
                            <p><strong>Buku:</strong> <span id="catat_judul_buku_modal_text"></span></p>
                            <p><strong>Tgl Pinjam:</strong> <span id="catat_tgl_pinjam_modal_text"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Jatuh Tempo:</strong> <span id="catat_tgl_jatuh_tempo_modal_text"></span></p>
                            <p><strong>Hari Terlambat:</strong> <span id="catat_hari_terlambat_modal_text" class="fw-bold text-danger"></span></p>
                            <p><strong>Estimasi Denda Saat Ini:</strong> Rp <span id="catat_estimasi_denda_modal_text" class="fw-bold text-danger"></span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="jumlah_denda_dikenakan_catat_modal_input" class="form-label">Jumlah Denda Dikenakan (Rp) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="jumlah_denda_dikenakan_catat_modal_input" name="jumlah_denda_dikenakan_catat_modal" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="jumlah_dibayar_sekarang_catat_modal_input" class="form-label">Jumlah Dibayar Sekarang (Rp) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="jumlah_dibayar_sekarang_catat_modal_input" name="jumlah_dibayar_sekarang_catat_modal" required value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tgl_pengembalian_catat_modal_input" class="form-label">Tanggal Pengembalian & Transaksi Denda <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tgl_pengembalian_catat_modal_input" name="tgl_pengembalian_catat_modal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan_catat_modal_input" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="keterangan_catat_modal_input" name="keterangan_catat_modal" rows="2" placeholder="Misal: Diberi keringanan, dll."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="catat_denda_dan_pengembalian_submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Simpan & Proses Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProsesPembayaran" tabindex="-1" aria-labelledby="modalProsesPembayaranLanjutanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET) ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalProsesPembayaranLanjutanLabel"><i class="fas fa-cash-register me-2"></i> Proses Pembayaran Lanjutan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_denda_bayar_modal_hidden_lanjutan" id="id_denda_bayar_modal_hidden_lanjutan_val">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Siswa:</strong> <span id="nama_siswa_modal_text_lanjutan_val"></span></div>
                        <div class="col-md-6"><strong>Buku:</strong> <span id="judul_buku_modal_text_lanjutan_val"></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Total Denda:</strong> Rp <span id="denda_dikenakan_modal_text_val_lanjutan_val"></span></div>
                        <div class="col-md-4"><strong>Sudah Dibayar:</strong> Rp <span id="sudah_dibayar_modal_text_val_lanjutan_val"></span></div>
                        <div class="col-md-4"><strong class="text-danger">Sisa Pembayaran: Rp <span id="sisa_pembayaran_modal_text_val_lanjutan_val" class="fw-bold"></span></strong></div>
                    </div>
                    <div class="mb-3">
                        <label for="jumlah_bayar_sekarang_modal_input_lanjutan_val" class="form-label">Jumlah Bayar Sekarang (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="jumlah_bayar_sekarang_modal_input_lanjutan_val" name="jumlah_bayar_sekarang_modal_input_lanjutan" required>
                    </div>
                    <div class="mb-3">
                        <label for="tgl_pembayaran_modal_input_lanjutan_val" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tgl_pembayaran_modal_input_lanjutan_val" name="tgl_pembayaran_modal_input_lanjutan" value="<?= date('Y-m-d') ?>" required>
                    </div>
                     <div class="mb-3">
                        <label for="keterangan_pembayaran_modal_input_lanjutan_val" class="form-label">Keterangan Pembayaran (Opsional)</label>
                        <textarea class="form-control" id="keterangan_pembayaran_modal_input_lanjutan_val" name="keterangan_pembayaran_modal_input_lanjutan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="proses_pembayaran_submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapuskanDenda" tabindex="-1" aria-labelledby="modalHapuskanDendaLabelConfirm" aria-hidden="true">
     <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET) ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalHapuskanDendaLabelConfirm"><i class="fas fa-exclamation-triangle me-2"></i> Konfirmasi Penghapusan Denda</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_denda_hapus_modal_hidden_confirm" id="id_denda_hapus_modal_hidden_confirm_val">
                    <p>Anda yakin ingin menghapuskan denda untuk siswa <strong id="nama_siswa_hapus_modal_text_confirm"></strong> pada buku "<strong id="judul_buku_hapus_modal_text_confirm_val"></strong>"?</p>
                    <p class="text-muted small">Status denda akan diubah menjadi 'Dihapuskan' dan dianggap lunas. Tindakan ini akan dicatat.</p>
                    <div class="mb-3">
                        <label for="keterangan_hapus_modal_input_confirm_val" class="form-label">Alasan/Keterangan Penghapusan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keterangan_hapus_modal_input_confirm_val" name="keterangan_hapus_modal_input_confirm" rows="3" placeholder="Contoh: Kebijakan sekolah, buku rusak, dll." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="hapuskan_denda_submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i> Ya, Hapuskan Denda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatRupiah(angkaStr, prefix = false) {
    if (angkaStr === null || typeof angkaStr === 'undefined') angkaStr = '0';
    let number_string = angkaStr.toString().replace(/[^,\d]/g, '');
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    rupiah = split[1] != undefined ? rupiah + ',' + split[1].substr(0,2) : rupiah; // Ambil 2 desimal jika ada
    return prefix ? 'Rp ' + rupiah : rupiah;
}

function cleanNumericString(value) {
    if (typeof value !== 'string') value = String(value);
    return value.replace(/[^0-9]/g, ''); // Hanya angka
}

document.addEventListener('DOMContentLoaded', function () {
    // Modal Catat Denda & Proses Pengembalian
    var modalCatatDenda = document.getElementById('modalCatatDendaPengembalian');
    if (modalCatatDenda) {
        modalCatatDenda.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            modalCatatDenda.querySelector('#id_peminjaman_catat_modal_hidden').value = button.getAttribute('data-id-peminjaman');
            modalCatatDenda.querySelector('#catat_nama_siswa_modal_text').textContent = button.getAttribute('data-nama-siswa');
            modalCatatDenda.querySelector('#catat_judul_buku_modal_text').textContent = button.getAttribute('data-judul-buku');
            modalCatatDenda.querySelector('#catat_tgl_pinjam_modal_text').textContent = button.getAttribute('data-tgl-pinjam');
            modalCatatDenda.querySelector('#catat_tgl_jatuh_tempo_modal_text').textContent = button.getAttribute('data-tgl-jatuh-tempo');
            modalCatatDenda.querySelector('#catat_hari_terlambat_modal_text').textContent = button.getAttribute('data-hari-terlambat') + ' hari';
            
            var estimasiDenda = parseFloat(button.getAttribute('data-estimasi-denda'));
            modalCatatDenda.querySelector('#catat_estimasi_denda_modal_text').textContent = formatRupiah(estimasiDenda);
            
            var inputDendaDikenakan = modalCatatDenda.querySelector('#jumlah_denda_dikenakan_catat_modal_input');
            inputDendaDikenakan.value = formatRupiah(estimasiDenda);
            
            modalCatatDenda.querySelector('#jumlah_dibayar_sekarang_catat_modal_input').value = '0';
            modalCatatDenda.querySelector('#tgl_pengembalian_catat_modal_input').value = '<?= date('Y-m-d') ?>';
            modalCatatDenda.querySelector('#keterangan_catat_modal_input').value = '';
            inputDendaDikenakan.focus();
        });
    }
    
    ['jumlah_denda_dikenakan_catat_modal_input', 'jumlah_dibayar_sekarang_catat_modal_input'].forEach(function(id) {
        var input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function(e) {
                var cursorPosition = e.target.selectionStart;
                var oldValue = e.target.value;
                var cleanValue = cleanNumericString(this.value);
                var formattedValue = formatRupiah(cleanValue);
                e.target.value = formattedValue;

                // Menyesuaikan posisi kursor setelah format
                var diff = formattedValue.length - oldValue.length;
                // Perlu logika lebih canggih untuk posisi kursor yang tepat dengan separator ribuan
            });
        }
    });

    // Modal Proses Pembayaran Lanjutan
    var modalProsesPembayaranLanjutan = document.getElementById('modalProsesPembayaran');
    if (modalProsesPembayaranLanjutan) {
        modalProsesPembayaranLanjutan.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button.hasAttribute('data-denda-dikenakan')) return;

            var idDenda = button.getAttribute('data-id-denda');
            var namaSiswa = button.getAttribute('data-nama-siswa');
            var judulBuku = button.getAttribute('data-judul-buku');
            var dendaDikenakan = parseFloat(button.getAttribute('data-denda-dikenakan'));
            var sudahDibayar = parseFloat(button.getAttribute('data-sudah-dibayar'));
            var sisaPembayaran = dendaDikenakan - sudahDibayar;

            modalProsesPembayaranLanjutan.querySelector('.modal-title').textContent = 'Proses Pembayaran Lanjutan Denda ID #' + idDenda;
            modalProsesPembayaranLanjutan.querySelector('#id_denda_bayar_modal_hidden_lanjutan_val').value = idDenda;
            modalProsesPembayaranLanjutan.querySelector('#nama_siswa_modal_text_lanjutan_val').textContent = namaSiswa;
            modalProsesPembayaranLanjutan.querySelector('#judul_buku_modal_text_lanjutan_val').textContent = judulBuku;
            modalProsesPembayaranLanjutan.querySelector('#denda_dikenakan_modal_text_val_lanjutan_val').textContent = formatRupiah(dendaDikenakan);
            modalProsesPembayaranLanjutan.querySelector('#sudah_dibayar_modal_text_val_lanjutan_val').textContent = formatRupiah(sudahDibayar);
            modalProsesPembayaranLanjutan.querySelector('#sisa_pembayaran_modal_text_val_lanjutan_val').textContent = formatRupiah(sisaPembayaran);
            
            var jumlahBayarInputLanjutan = modalProsesPembayaranLanjutan.querySelector('#jumlah_bayar_sekarang_modal_input_lanjutan_val');
            jumlahBayarInputLanjutan.value = ''; 
            jumlahBayarInputLanjutan.placeholder = formatRupiah(sisaPembayaran > 0 ? sisaPembayaran : 0);
            jumlahBayarInputLanjutan.focus();
            modalProsesPembayaranLanjutan.querySelector('#tgl_pembayaran_modal_input_lanjutan_val').value = '<?=date('Y-m-d')?>';
            modalProsesPembayaranLanjutan.querySelector('#keterangan_pembayaran_modal_input_lanjutan_val').value = '';
        });
    }
    
    var inputJumlahBayarLanjutan = document.getElementById('jumlah_bayar_sekarang_modal_input_lanjutan_val');
    if(inputJumlahBayarLanjutan){
         inputJumlahBayarLanjutan.addEventListener('input', function (e) {
            this.value = formatRupiah(cleanNumericString(this.value));
        });
    }

    // Modal Hapuskan Denda
    var modalHapuskanDendaConfirm = document.getElementById('modalHapuskanDenda');
    if (modalHapuskanDendaConfirm) {
        modalHapuskanDendaConfirm.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button.hasAttribute('data-id-denda-hapus')) return; 

            var idDendaHapus = button.getAttribute('data-id-denda-hapus');
            var judulBukuHapus = button.getAttribute('data-judul-buku-hapus');
            var namaSiswaHapus = button.getAttribute('data-nama-siswa-hapus');
            
            modalHapuskanDendaConfirm.querySelector('#id_denda_hapus_modal_hidden_confirm_val').value = idDendaHapus;
            modalHapuskanDendaConfirm.querySelector('#nama_siswa_hapus_modal_text_confirm').textContent = namaSiswaHapus;
            modalHapuskanDendaConfirm.querySelector('#judul_buku_hapus_modal_text_confirm_val').textContent = judulBukuHapus;
            modalHapuskanDendaConfirm.querySelector('#keterangan_hapus_modal_input_confirm_val').value = ''; 
            modalHapuskanDendaConfirm.querySelector('#keterangan_hapus_modal_input_confirm_val').focus();
        });
    }
});
</script>

<?php 
if (isset($stmt_pinjaman_aktif) && $stmt_pinjaman_aktif instanceof mysqli_stmt) {
    $stmt_pinjaman_aktif->close();
}
if (isset($stmt_denda_tercatat) && $stmt_denda_tercatat instanceof mysqli_stmt) {
    $stmt_denda_tercatat->close();
}
include_once __DIR__ . '/../../../layout/footer.php'; 
ob_end_flush();
?>