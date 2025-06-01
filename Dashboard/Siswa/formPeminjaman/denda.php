<?php
ob_start(); // Mulai output buffering di awal
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
    header("Location: /libtera/login.php");
    exit;
}
require "../../../connect.php"; // Sesuaikan path jika perlu

// --- KONFIGURASI ATURAN DENDA (Untuk Estimasi) ---
define('DURASI_PINJAM_HARI', 7); // Durasi peminjaman dalam hari
define('DENDA_PER_PERIODE', 5000); // Nominal denda
define('PERIODE_DENDA_HARI', 7); // Denda dihitung per berapa hari (misal 7 untuk per minggu)
// --- END KONFIGURASI ---

$title = "Informasi Denda Peminjaman";
$peminjamanAktif = [];
$dendaTercatatBelumLunas = []; // Untuk menyimpan denda resmi yang belum lunas
$akunMemberNisn = $_SESSION["siswa"]["nisn"] ?? null;

if (!$akunMemberNisn) {
    $_SESSION['swal_params'] = ['title' => 'Error Sesi', 'text' => 'Informasi pengguna tidak lengkap di sesi. Silakan login kembali.', 'icon' => 'error'];
    header("Location: /libtera/login.php");
    exit;
}

$id_siswa = null; // Inisialisasi
try {
    // Ambil data siswa (id_siswa)
    $querySiswa = "SELECT id_siswa FROM siswa WHERE nisn = ?";
    $stmtSiswa = $connect->prepare($querySiswa);
    if (!$stmtSiswa) {
        throw new Exception("Gagal menyiapkan statement siswa: " . $connect->error);
    }
    // Asumsi NISN bisa berupa string jika ada leading zero atau panjang. Jika di DB INT murni, 'i' lebih tepat.
    $stmtSiswa->bind_param("s", $akunMemberNisn); 
    $stmtSiswa->execute();
    $resultSiswa = $stmtSiswa->get_result();
    $siswaData = $resultSiswa->fetch_assoc();
    $stmtSiswa->close();

    if (!$siswaData) {
        $_SESSION['swal_params'] = ['title' => 'Error Pengguna', 'text' => 'Data siswa tidak ditemukan.', 'icon' => 'error'];
        header("Location: ../dashboard.php"); // Arahkan ke dashboard siswa
        exit;
    }
    $id_siswa = $siswaData['id_siswa'];

    // 1. Ambil peminjaman yang statusnya 'PINJAM' untuk perhitungan estimasi denda
    $sqlEstimasi = "SELECT
                        p.id_peminjaman, b.judul, p.tgl_pinjam 
                    FROM peminjaman p
                    JOIN buku b ON p.id_buku = b.id_buku
                    WHERE p.id_siswa = ? AND p.status = 'PINJAM'";
    $stmtEstimasi = $connect->prepare($sqlEstimasi);
    if (!$stmtEstimasi) {
        throw new Exception("Gagal menyiapkan statement peminjaman aktif: " . $connect->error);
    }
    $stmtEstimasi->bind_param("i", $id_siswa);
    $stmtEstimasi->execute();
    $resultEstimasi = $stmtEstimasi->get_result();
    $peminjamanAktif = $resultEstimasi->fetch_all(MYSQLI_ASSOC);
    $stmtEstimasi->close();

    // 2. Ambil denda yang sudah tercatat di tabel Denda dan statusnya 'Belum Lunas'
    // Menggunakan nama kolom dari tabel Denda sederhana: id_admin_pencatat, tgl_transaksi_denda
    $sqlDendaResmi = "SELECT 
                            d.id_denda,
                            p.id_peminjaman,
                            b.judul AS judul_buku,
                            d.jumlah_denda_dikenakan,
                            d.jumlah_telah_dibayar,
                            d.tgl_transaksi_denda,
                            d.keterangan
                        FROM Denda d
                        JOIN Peminjaman p ON d.id_peminjaman = p.id_peminjaman
                        JOIN Buku b ON p.id_buku = b.id_buku
                        WHERE p.id_siswa = ? AND d.status_denda = 'Belum Lunas'
                        ORDER BY d.tgl_transaksi_denda DESC";
    $stmtDendaResmi = $connect->prepare($sqlDendaResmi);
    if (!$stmtDendaResmi) {
        throw new Exception("Gagal menyiapkan statement denda tercatat: " . $connect->error);
    }
    $stmtDendaResmi->bind_param("i", $id_siswa);
    $stmtDendaResmi->execute();
    $resultDendaResmi = $stmtDendaResmi->get_result();
    $dendaTercatatBelumLunas = $resultDendaResmi->fetch_all(MYSQLI_ASSOC);
    $stmtDendaResmi->close();

} catch (mysqli_sql_exception $e) {
    error_log("MySQLi Exception on Info Denda Siswa Page (siswa_id: $id_siswa, nisn: $akunMemberNisn): " . $e->getMessage());
    $_SESSION['page_error_message'] = "Terjadi kesalahan database saat memuat informasi denda Anda.";
} catch (Exception $e) {
    error_log("General Exception on Info Denda Siswa Page (siswa_id: $id_siswa, nisn: $akunMemberNisn): " . $e->getMessage());
    $_SESSION['page_error_message'] = "Terjadi kesalahan umum saat memuat informasi denda Anda.";
}

include_once '../../../layout/header.php';
?>

<div class="container mt-5 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-2xl sm:text-3xl font-extrabold bg-gradient-to-r from-blue-400 via-blue-600 to-pink-500 bg-clip-text text-transparent">
            Informasi Denda Peminjaman
        </h2>
    </div>

    <?php
    if (isset($_SESSION['page_error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' .
             htmlspecialchars($_SESSION['page_error_message']) .
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['page_error_message']);
    }
    ?>

    <div class="card shadow-sm rounded mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Estimasi Denda untuk Buku yang Sedang Dipinjam</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 5%;">No.</th>
                            <th scope="col">Judul Buku</th>
                            <th scope="col">Tanggal Pinjam</th>
                            <th scope="col">Jatuh Tempo</th>
                            <th scope="col">Keterlambatan</th>
                            <th scope="col">Estimasi Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counterEstimasi = 1;
                        $totalEstimasiDendaKeseluruhan = 0;

                        if (empty($peminjamanAktif) && !isset($_SESSION['page_error_message'])) {
                            echo '<tr><td colspan="6" class="py-4"><em>Tidak ada buku yang sedang Anda pinjam saat ini.</em></td></tr>';
                        } else {
                            foreach ($peminjamanAktif as $item) {
                                if (empty($item['tgl_pinjam'])) {
                                    echo '<tr>';
                                    echo '<td>' . $counterEstimasi++ . '.</td>';
                                    echo '<td class="text-start">' . htmlspecialchars($item["judul"]) . '</td>';
                                    echo '<td colspan="4" class="text-danger"><em>Data tanggal pinjam tidak valid.</em></td>';
                                    echo '</tr>';
                                    continue;
                                }
                                try {
                                    $tgl_pinjam_obj = new DateTime($item['tgl_pinjam']);
                                    $tgl_pinjam_obj->setTime(0, 0, 0);
                                    $tgl_tempo_obj = clone $tgl_pinjam_obj;
                                    $tgl_tempo_obj->modify('+' . DURASI_PINJAM_HARI . ' days');
                                    $now_obj = new DateTime();
                                    $now_obj->setTime(0, 0, 0);

                                    $keterlambatan_hari = 0;
                                    $denda_item = 0;
                                    $status_keterlambatan_text = "<em>Tidak terlambat</em>";
                                    $row_class = '';

                                    if ($now_obj > $tgl_tempo_obj) {
                                        $interval = $now_obj->diff($tgl_tempo_obj);
                                        $keterlambatan_hari = $interval->days;
                                        if ($keterlambatan_hari >= 1) {
                                            $periode_efektif_terlambat = ceil($keterlambatan_hari / PERIODE_DENDA_HARI);
                                            $denda_item = $periode_efektif_terlambat * DENDA_PER_PERIODE;
                                            $status_keterlambatan_text = "<span class='badge bg-danger'>" . $keterlambatan_hari . " hari</span>";
                                            if (PERIODE_DENDA_HARI === 7 && $periode_efektif_terlambat > 0) {
                                                $status_keterlambatan_text .= " <small>(Nunggak " . $periode_efektif_terlambat . " minggu)</small>";
                                            }
                                            $totalEstimasiDendaKeseluruhan += $denda_item;
                                            $row_class = 'table-warning';
                                        }
                                    }
                        ?>
                                    <tr class="<?= $row_class ?>">
                                        <td><?= $counterEstimasi++; ?>.</td>
                                        <td class="text-start"><?= htmlspecialchars($item["judul"]); ?></td>
                                        <td><?= htmlspecialchars($tgl_pinjam_obj->format('d M Y')); ?></td>
                                        <td><?= htmlspecialchars($tgl_tempo_obj->format('d M Y')); ?></td>
                                        <td><?= $status_keterlambatan_text; ?></td>
                                        <td class="fw-bold <?= ($denda_item > 0) ? 'text-danger' : '' ?>">Rp <?= number_format($denda_item, 0, ',', '.'); ?></td>
                                    </tr>
                        <?php
                                } catch (Exception $dateEx) {
                                    error_log("Error parsing date (Estimasi) for Peminjaman ID " . ($item['id_peminjaman'] ?? 'N/A') . ": " . $dateEx->getMessage());
                                    echo '<tr><td>' . $counterEstimasi++ . '.</td><td class="text-start">' . htmlspecialchars($item["judul"] ?? 'Error Buku') . '</td><td colspan="4" class="text-danger"><em>Error data tanggal.</em></td></tr>';
                                }
                            }
                            if (!empty($peminjamanAktif) && $totalEstimasiDendaKeseluruhan == 0 && !isset($_SESSION['page_error_message'])) {
                                 echo '<tr><td colspan="6" class="py-4 text-success"><em>Tidak ada peminjaman aktif Anda yang terlambat saat ini. Bagus!</em></td></tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="card shadow-sm rounded mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Denda Resmi yang Tercatat & Belum Lunas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 5%;">No.</th>
                            <th scope="col">Judul Buku</th>
                            <th scope="col">Tgl Denda Dicatat</th>
                            <th scope="col">Jumlah Dikenakan</th>
                            <th scope="col">Sudah Dibayar</th>
                            <th scope="col">Sisa Denda</th>
                            <th scope="col">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counterDendaResmi = 1;
                        if (empty($dendaTercatatBelumLunas) && !isset($_SESSION['page_error_message'])) {
                            echo '<tr><td colspan="7" class="py-4"><em>Tidak ada denda resmi yang belum lunas tercatat untuk Anda saat ini.</em></td></tr>';
                        } else {
                            foreach ($dendaTercatatBelumLunas as $denda) {
                                $sisa_denda = $denda['jumlah_denda_dikenakan'] - $denda['jumlah_telah_dibayar'];
                        ?>
                                <tr>
                                    <td><?= $counterDendaResmi++; ?>.</td>
                                    <td class="text-start"><?= htmlspecialchars($denda["judul_buku"]); ?></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($denda['tgl_transaksi_denda']))); ?></td>
                                    <td class="text-end">Rp <?= number_format($denda['jumlah_denda_dikenakan'], 0, ',', '.'); ?></td>
                                    <td class="text-end">Rp <?= number_format($denda['jumlah_telah_dibayar'], 0, ',', '.'); ?></td>
                                    <td class="text-end fw-bold text-danger">Rp <?= number_format($sisa_denda, 0, ',', '.'); ?></td>
                                    <td><?= htmlspecialchars($denda['keterangan'] ?? '-'); ?></td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="mt-4 p-3 bg-light rounded border">
        <p class="fw-bold mb-2">Catatan Penting:</p>
        <ul class="small text-muted ps-3 mb-0">
            <li>Batas waktu standar peminjaman buku adalah <strong><?= DURASI_PINJAM_HARI ?> hari</strong>.</li>
            <li>Estimasi denda untuk buku yang sedang dipinjam dihitung sebesar <strong>Rp <?= number_format(DENDA_PER_PERIODE, 0, ',', '.') ?> per <?= (PERIODE_DENDA_HARI == 7) ? 'minggu' : PERIODE_DENDA_HARI . ' hari keterlambatan' ?></strong> atau bagian darinya.</li>
            <li>Denda resmi yang tercatat adalah denda yang telah ditetapkan oleh petugas perpustakaan, biasanya saat pengembalian buku terlambat.</li>
            <li>Mohon segera kembalikan buku yang telah melewati tanggal jatuh tempo dan lunasi denda yang tercatat.</li>
            <li>Untuk pembayaran denda, silakan hubungi petugas perpustakaan.</li>
        </ul>
    </div>
</div>

<?php 
include_once '../../../layout/footer.php'; 
ob_end_flush();
?>