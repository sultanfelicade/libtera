<?php
session_start();
$title = "Riwayat Peminjaman - Libtera";

require "../../../connect.php"; // Pastikan path ini benar

// Pastikan siswa sudah login
if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa" || !isset($_SESSION["siswa"]["nisn"])) {
    $_SESSION['pesan_error_login'] = "Sesi tidak valid atau Anda belum login.";
    header("Location: /libtera/login.php"); // Sesuaikan path login Anda
    exit;
}

$akunMemberNisn = $_SESSION["siswa"]["nisn"];

// Ambil dan Hapus pesan dari aksi (misalnya pembatalan)
$pesan_sukses_riwayat = $_SESSION['pesan_sukses_riwayat'] ?? null;
$pesan_error_riwayat = $_SESSION['pesan_error_riwayat'] ?? null;
if (isset($_SESSION['pesan_sukses_riwayat'])) unset($_SESSION['pesan_sukses_riwayat']);
if (isset($_SESSION['pesan_error_riwayat'])) unset($_SESSION['pesan_error_riwayat']);

// 1. Dapatkan id_siswa dari nisn
$querySiswa = "SELECT id_siswa, nama FROM siswa WHERE nisn = ?";
$stmtSiswa = $connect->prepare($querySiswa);
if (!$stmtSiswa) {
    // Penanganan error jika prepare gagal
    error_log("Prepare statement gagal (querySiswa): " . $connect->error);
    die("Terjadi kesalahan pada sistem. Silakan coba lagi nanti.");
}
// Asumsi NISN bisa jadi string atau integer. Jika NISN selalu string, gunakan "s".
// Jika bisa juga integer panjang, "s" lebih aman. Jika pasti integer, "i".
$stmtSiswa->bind_param("s", $akunMemberNisn); // Menggunakan "s" untuk fleksibilitas NISN
$stmtSiswa->execute();
$resultSiswa = $stmtSiswa->get_result();
$siswaData = $resultSiswa->fetch_assoc();
$stmtSiswa->close();

if (!$siswaData) {
    // Handle jika data siswa tidak ditemukan berdasarkan NISN di session
    $_SESSION['pesan_error_login'] = "Data siswa tidak ditemukan. Silakan login kembali.";
    header("Location: /libtera/login.php"); // Sesuaikan path login Anda
    exit;
}

$id_siswa = $siswaData['id_siswa'];

// 2. Query peminjaman sesuai id_siswa, tambahkan p.status dan urutkan
$sql = "SELECT
            p.id_peminjaman,
            p.id_buku,
            b.judul,
            p.tgl_pinjam,
            p.tgl_kembali,
            p.status  -- Tambahkan kolom status
        FROM
            peminjaman p
        INNER JOIN buku b ON p.id_buku = b.id_buku
        WHERE
            p.id_siswa = ?
        ORDER BY
            FIELD(p.status, 'PENDING', 'PINJAM', 'KEMBALI', 'DITOLAK', 'DIBATALKAN'), -- Urutkan berdasarkan prioritas status
            p.id_peminjaman DESC"; // Kemudian berdasarkan ID Peminjaman terbaru

$stmtPeminjaman = $connect->prepare($sql);
if (!$stmtPeminjaman) {
    error_log("Prepare statement gagal (sql peminjaman): " . $connect->error);
    die("Terjadi kesalahan pada sistem saat mengambil data peminjaman.");
}
$stmtPeminjaman->bind_param("i", $id_siswa);
$stmtPeminjaman->execute();
$dataPinjam = $stmtPeminjaman->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtPeminjaman->close();

include_once '../../../layout/header.php';
?>

<div class="container p-4 mt-5">
    <div class="text-4xl font-extrabold bg-gradient-to-r from-blue-400 via-blue-600 to-pink-500 bg-clip-text text-transparent mb-3">
        Riwayat Peminjaman Buku
    </div>

    <?php
    // Tampilkan pesan notifikasi dari aksi
    if ($pesan_sukses_riwayat) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_sukses_riwayat) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if ($pesan_error_riwayat) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_error_riwayat) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    ?>

    <?php if (empty($dataPinjam)): ?>
        <div class="alert alert-info mt-3">Anda belum memiliki riwayat peminjaman buku.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Judul Buku</th>
                        <th>Tgl. Pengajuan/Pinjam</th>
                        <th>Tgl. Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataPinjam as $item): ?>
                        <tr>
                            <td><?= $item["id_peminjaman"]; ?></td>
                            <td class="text-start"><?= htmlspecialchars($item["judul"]); ?></td>
                            <td>
                                <?php
                                if ($item["status"] == 'PENDING' && empty($item["tgl_pinjam"])) {
                                    echo "<em>Menunggu Persetujuan</em>";
                                } elseif (!empty($item["tgl_pinjam"])) {
                                    echo htmlspecialchars(date('d M Y', strtotime($item["tgl_pinjam"])));
                                } else {
                                    echo "<em>-</em>";
                                }
                                ?>
                            </td>
                            <td>
                                <?= !empty($item["tgl_kembali"]) ? htmlspecialchars(date('d M Y', strtotime($item["tgl_kembali"]))) : "<em>-</em>"; ?>
                            </td>
                            <td>
                                <?php
                                $status = $item["status"];
                                $badgeClass = 'bg-secondary'; // Default
                                $statusText = htmlspecialchars(ucfirst(strtolower($status)));

                                switch ($status) {
                                    case 'PENDING':
                                        $badgeClass = 'bg-warning text-dark';
                                        $statusText = 'Menunggu Persetujuan';
                                        break;
                                    case 'PINJAM':
                                        $badgeClass = 'bg-primary';
                                        $statusText = 'Sedang Dipinjam';
                                        break;
                                    case 'KEMBALI':
                                        $badgeClass = 'bg-success';
                                        $statusText = 'Sudah Dikembalikan';
                                        break;
                                    case 'DITOLAK':
                                        $badgeClass = 'bg-danger';
                                        $statusText = 'Ditolak';
                                        break;
                                    case 'DIBATALKAN':
                                        $badgeClass = 'bg-dark';
                                        $statusText = 'Dibatalkan';
                                        break;
                                }
                                echo "<span class=\"badge $badgeClass\">$statusText</span>";
                                ?>
                            </td>
                            <td>
                                <?php if ($item["status"] == 'PENDING'): ?>
                                    <a href="batalkan_peminjaman.php?id_peminjaman=<?= $item["id_peminjaman"]; ?>"
                                       class="btn btn-danger btn-sm btn-batalkan-peminjaman"  <?php // Tambah kelas .btn-batalkan-peminjaman ?>
                                       data-id-peminjaman="<?= $item["id_peminjaman"]; ?>"
                                       data-judul-buku="<?= htmlspecialchars($item['judul']); ?>" <?php // Data judul buku ?>
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="Batalkan Pengajuan Ini">
                                        <i class="fas fa-times-circle me-1"></i>Batalkan
                                    </a>
                                <?php else: ?>
                                    <em>-</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    // Inisialisasi tooltip Bootstrap (jika menggunakan Bootstrap 5)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?php include_once '../../../layout/footer.php'; ?>