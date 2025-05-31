<?php
session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "siswa") {
    header("Location: /libtera/login.php");
    exit;
}
require "../../../connect.php";

// Aktifkan pelaporan error MySQLi sebagai exceptions (opsional, bisa di connect.php)
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$title = "Informasi Denda Peminjaman"; // Judul halaman lebih spesifik
$peminjamanAktif = []; // Inisialisasi array
$akunMemberNisn = $_SESSION["siswa"]["nisn"] ?? null;

if (!$akunMemberNisn) {
    // Jika NISN tidak ada di session, ini masalah serius
    $_SESSION['swal_params'] = ['title' => 'Error Sesi', 'text' => 'Informasi pengguna tidak lengkap di sesi. Silakan login kembali.', 'icon' => 'error'];
    header("Location: /libtera/login.php");
    exit;
}

try {
    // Ambil data siswa (id_siswa)
    $querySiswa = "SELECT id_siswa FROM siswa WHERE nisn = ?";
    $stmtSiswa = $connect->prepare($querySiswa);
    if (!$stmtSiswa) {
        throw new Exception("Gagal menyiapkan statement untuk mengambil data siswa.");
    }
    $stmtSiswa->bind_param("s", $akunMemberNisn); // NISN biasanya string
    $stmtSiswa->execute();
    $resultSiswa = $stmtSiswa->get_result();
    $siswaData = $resultSiswa->fetch_assoc();
    $stmtSiswa->close();

    if (!$siswaData) {
        // Data siswa tidak ditemukan berdasarkan NISN
        $_SESSION['swal_params'] = ['title' => 'Error Pengguna', 'text' => 'Data siswa tidak ditemukan. Pastikan NISN Anda benar.', 'icon' => 'error'];
        // Arahkan ke halaman yang sesuai, mungkin dashboard atau logout
        header("Location: ../dashboard.php"); // Sesuaikan
        exit;
    }
    $id_siswa = $siswaData['id_siswa'];

    // Ambil peminjaman yang statusnya 'PINJAM' untuk perhitungan denda
    $sqlDenda = "SELECT
                    p.id_peminjaman, b.judul, p.tgl_pinjam
                 FROM peminjaman p
                 JOIN buku b ON p.id_buku = b.id_buku
                 WHERE p.id_siswa = ? AND p.status = 'PINJAM'"; // Filter hanya status 'PINJAM'

    $stmtDenda = $connect->prepare($sqlDenda);
    if (!$stmtDenda) {
        throw new Exception("Gagal menyiapkan statement untuk mengambil data peminjaman.");
    }
    $stmtDenda->bind_param("i", $id_siswa);
    $stmtDenda->execute();
    $resultDenda = $stmtDenda->get_result();
    $peminjamanAktif = $resultDenda->fetch_all(MYSQLI_ASSOC);
    $stmtDenda->close();

} catch (mysqli_sql_exception $e) {
    error_log("MySQLi Exception on Denda Page (siswa: $akunMemberNisn): " . $e->getMessage());
    $_SESSION['page_error_message'] = "Terjadi kesalahan database saat memuat data denda. Silakan coba lagi nanti.";
} catch (Exception $e) {
    error_log("General Exception on Denda Page (siswa: $akunMemberNisn): " . $e->getMessage());
    $_SESSION['page_error_message'] = "Terjadi kesalahan umum saat memuat data denda. Silakan coba lagi nanti.";
}

// Panggil header SETELAH pengambilan data agar bisa menampilkan pesan error jika ada
include_once '../../../layout/header.php';
?>

<div class="container mt-5 pt-4"> <?php // pt-4 jika header fixed-top ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-2xl sm:text-3xl font-extrabold bg-gradient-to-r from-blue-400 via-blue-600 to-pink-500 bg-clip-text text-transparent">
            Informasi Denda Peminjaman
        </h2>
    </div>

    <?php
    // Tampilkan pesan error umum dari sesi jika ada
    if (isset($_SESSION['page_error_message'])) {
        // Jika Anda sudah integrasi SweetAlert untuk notifikasi halaman via window.swalInitParams
        // Anda bisa set $_SESSION['swal_params'] di blok catch di atas, dan biarkan JS di footer menampilkannya.
        // Untuk sekarang, kita tampilkan sebagai alert Bootstrap biasa.
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' .
             htmlspecialchars($_SESSION['page_error_message']) .
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['page_error_message']);
    }
    ?>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-primary">
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
                $counter = 1;
                $adaYangTerlambat = false;

                if (empty($peminjamanAktif) && !isset($_SESSION['page_error_message'])) { // Jika tidak ada error dan memang kosong
                    echo '<tr><td colspan="6" class="py-4"><em>Tidak ada buku yang sedang Anda pinjam saat ini.</em></td></tr>';
                } else {
                    foreach ($peminjamanAktif as $item) {
                        // Pastikan tgl_pinjam tidak null dan valid sebelum diproses
                        if (empty($item['tgl_pinjam'])) {
                            // Ini seharusnya tidak terjadi untuk peminjaman berstatus 'PINJAM'
                            // Jika terjadi, berarti ada masalah data atau proses admin
                            echo '<tr>';
                            echo '<td>' . $counter++ . '.</td>';
                            echo '<td class="text-start">' . htmlspecialchars($item["judul"]) . '</td>';
                            echo '<td colspan="4" class="text-danger"><em>Data tanggal pinjam tidak valid.</em></td>';
                            echo '</tr>';
                            continue;
                        }

                        try {
                            $tgl_pinjam = new DateTime($item['tgl_pinjam']);
                            // Normalisasi waktu ke awal hari untuk perbandingan yang konsisten
                            $tgl_pinjam->setTime(0, 0, 0);

                            $tgl_tempo = clone $tgl_pinjam;
                            $tgl_tempo->modify('+7 days'); // Jatuh tempo 7 hari dari tanggal pinjam

                            $now = new DateTime(); // Waktu saat ini
                            $now->setTime(0, 0, 0); // Normalisasi waktu saat ini juga

                            $keterlambatan_hari = 0;
                            $denda = 0;
                            $status_keterlambatan_text = "<em>Tidak terlambat</em>";

                            // Cek apakah hari ini sudah melewati tanggal jatuh tempo
                            if ($now > $tgl_tempo) {
                                $interval = $now->diff($tgl_tempo);
                                $keterlambatan_hari = $interval->days; // Jumlah hari keterlambatan absolut

                                if ($keterlambatan_hari >= 1) { // Terlambat minimal 1 hari
                                    $minggu_efektif_terlambat = ceil($keterlambatan_hari / 7); // Pembulatan ke atas untuk minggu denda
                                    $denda = $minggu_efektif_terlambat * 5000;
                                    $status_keterlambatan_text = $keterlambatan_hari . " hari";
                                    if ($minggu_efektif_terlambat > 0) {
                                        $status_keterlambatan_text .= " (" . $minggu_efektif_terlambat . " minggu denda)";
                                    }
                                    $adaYangTerlambat = true;
                                }
                            }
                ?>
                            <tr>
                                <td><?= $counter++; ?>.</td>
                                <td class="text-start"><?= htmlspecialchars($item["judul"]); ?></td>
                                <td><?= htmlspecialchars($tgl_pinjam->format('d M Y')); ?></td>
                                <td><?= htmlspecialchars($tgl_tempo->format('d M Y')); ?></td>
                                <td><?= $status_keterlambatan_text; ?></td>
                                <td class="fw-bold <?= ($denda > 0) ? 'text-danger' : '' ?>">Rp <?= number_format($denda, 0, ',', '.'); ?></td>
                            </tr>
                <?php
                        } catch (Exception $dateEx) {
                            // Menangani error jika format tanggal pinjam dari database tidak valid
                            error_log("Error parsing date for Peminjaman ID " . ($item['id_peminjaman'] ?? 'N/A') . " (Siswa: $akunMemberNisn): " . $dateEx->getMessage());
                            echo '<tr>';
                            echo '<td>' . $counter++ . '.</td>';
                            echo '<td class="text-start">' . htmlspecialchars($item["judul"] ?? 'Data Buku Error') . '</td>';
                            echo '<td colspan="4" class="text-danger"><em>Terjadi masalah dengan data tanggal peminjaman buku ini.</em></td>';
                            echo '</tr>';
                        }
                    } // End foreach

                    // Jika loop selesai tapi tidak ada yang terlambat (dan ada peminjaman aktif)
                    if ($counter > 1 && !$adaYangTerlambat && !isset($_SESSION['page_error_message'])) {
                         echo '<tr><td colspan="6" class="py-4"><em>Tidak ada peminjaman Anda yang terlambat saat ini. Selamat!</em></td></tr>';
                    } elseif ($counter == 1 && !empty($peminjamanAktif) && !$adaYangTerlambat && !isset($_SESSION['page_error_message'])) {
                        // Kasus jika hanya 1 buku dipinjam dan tidak terlambat
                        echo '<tr><td colspan="6" class="py-4"><em>Tidak ada peminjaman Anda yang terlambat saat ini. Selamat!</em></td></tr>';
                    }

                } // End else (jika $peminjamanAktif tidak kosong atau ada error)
                ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded border">
        <p class="fw-bold mb-2">Catatan Penting:</p>
        <ul class="small text-muted ps-3 mb-0">
            <li>Batas waktu standar peminjaman buku adalah <strong>7 hari</strong> sejak tanggal buku dipinjam.</li>
            <li>Denda keterlambatan dikenakan sebesar <strong>Rp 5.000 per minggu</strong> atau bagian dari minggu.</li>
            <li>Contoh: Terlambat 1 hari hingga 7 hari dihitung sebagai 1 minggu denda. Terlambat 8 hari dihitung sebagai 2 minggu denda.</li>
            <li>Mohon segera kembalikan buku yang telah melewati tanggal jatuh tempo untuk menghindari akumulasi denda.</li>
            <li>Informasi denda ini adalah estimasi berdasarkan tanggal saat ini. Denda final akan dihitung saat pengembalian buku.</li>
        </ul>
    </div>
</div>

<?php include_once '../../../layout/footer.php'; ?>