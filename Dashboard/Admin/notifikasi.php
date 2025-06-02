<?php
// File: Dashboard/Admin/notifikasi.php (SESUAIKAN PATH INI JIKA PERLU)
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

$title = "Notifikasi Denda Siswa - Libtera Admin";
// SESUAIKAN PATH INI JIKA 'notifikasi.php' ada di root atau subfolder lain relatif ke 'connect.php'
// Contoh: Jika notifikasi.php ada di C:\laragon\www\libtera\notifikasi.php, maka pathnya menjadi:
// require __DIR__ . '/../connect.php'; (jika connect.php di C:\laragon\www\connect.php)
// atau require 'connect.php'; (jika connect.php di C:\laragon\www\libtera\connect.php)
require __DIR__ . '/../../connect.php'; 

// Inisialisasi variabel pesan
$pesan_sukses_notif = $_SESSION['pesan_sukses_notif'] ?? null;
$pesan_error_notif = $_SESSION['pesan_error_notif'] ?? null;
if (isset($_SESSION['pesan_sukses_notif'])) unset($_SESSION['pesan_sukses_notif']);
if (isset($_SESSION['pesan_error_notif'])) unset($_SESSION['pesan_error_notif']);

// --- Ambil Data Denda yang Belum Lunas ---
$sql_denda = "SELECT
                d.id_denda,
                d.id_peminjaman,
                d.jumlah_denda_dikenakan,
                d.jumlah_telah_dibayar,
                d.status_denda,
                d.keterangan AS keterangan_denda,
                s.nama AS nama_siswa,
                s.nisn AS nisn_siswa,
                s.email AS email_siswa,
                p.tgl_pinjam,
                p.tgl_kembali AS tgl_pengembalian_aktual,
                b.judul AS judul_buku, 
                DATE_ADD(p.tgl_pinjam, INTERVAL 7 DAY) AS tgl_jatuh_tempo -- WAJIB SESUAIKAN LOGIKA JATUH TEMPO INI!
            FROM denda d
            JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
            JOIN siswa s ON p.id_siswa = s.id_siswa
            LEFT JOIN buku b ON p.id_buku = b.id_buku 
            WHERE d.status_denda = 'Belum Lunas'
            ORDER BY s.nama ASC";

$result_denda = $connect->query($sql_denda);
$daftar_denda_belum_lunas = [];
if ($result_denda && $result_denda->num_rows > 0) {
    while ($row = $result_denda->fetch_assoc()) {
        $row['sisa_denda'] = $row['jumlah_denda_dikenakan'] - $row['jumlah_telah_dibayar'];
        $daftar_denda_belum_lunas[] = $row;
    }
}

// --- Template Email Default ---
$subjekEmailDefaultGlobal = "Pemberitahuan Tagihan Denda Peminjaman Buku";
$templatePesanDefaultGlobal = <<<HTML
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #d9534f;">Pemberitahuan Denda Perpustakaan</h2>
    </div>
    <p>Yth. Sdr/i <strong>[NAMA_SISWA]</strong>,</p>
    <p>NISN: <strong>[NISN_SISWA]</strong></p>
    <p>Dengan hormat, kami informasikan bahwa Anda memiliki tagihan denda yang belum diselesaikan terkait peminjaman buku di perpustakaan kami. Berikut adalah rinciannya:</p>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr><td style="padding: 8px; border: 1px solid #eee; width: 40%;">Judul Buku</td><td style="padding: 8px; border: 1px solid #eee;"><strong>[JUDUL_BUKU]</strong></td></tr>
        <tr><td style="padding: 8px; border: 1px solid #eee;">Tanggal Pinjam</td><td style="padding: 8px; border: 1px solid #eee;">[TGL_PINJAM]</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #eee;">Tanggal Jatuh Tempo</td><td style="padding: 8px; border: 1px solid #eee;">[TGL_JATUH_TEMPO]</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #eee;">Total Denda Dikenakan</td><td style="padding: 8px; border: 1px solid #eee;">Rp [JUMLAH_DENDA_DIKENAKAN]</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #eee;">Jumlah Telah Dibayar</td><td style="padding: 8px; border: 1px solid #eee;">Rp [JUMLAH_TELAH_DIBAYAR]</td></tr>
        <tr style="background-color: #f2dede; color: #a94442; font-weight: bold;"><td style="padding: 8px; border: 1px solid #eee;">Sisa Denda</td><td style="padding: 8px; border: 1px solid #eee;">Rp [SISA_DENDA]</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #eee;">Keterangan Tambahan</td><td style="padding: 8px; border: 1px solid #eee;">[KETERANGAN_DENDA]</td></tr>
    </table>
    <p>Kami mohon agar Anda dapat segera menyelesaikan pembayaran denda tersebut untuk menghindari sanksi lebih lanjut sesuai dengan peraturan yang berlaku di perpustakaan.</p>
    <p>Anda dapat melakukan pembayaran langsung di meja layanan perpustakaan.</p>
    <p>Terima kasih atas perhatian dan kerjasamanya.</p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="font-size: 0.9em; color: #777; text-align: center;">Hormat kami,<br>Admin Perpustakaan Libtera<br><em>(Email ini dikirimkan secara otomatis oleh sistem)</em></p>
</div>
HTML;

// SESUAIKAN PATH INI untuk header dan footer
include_once __DIR__ . '/../../layout/header.php';
?>

<div class="container p-4 mt-5">
    <div class="mb-4">
        <h2 class="text-primary fw-bold"><i class="fas fa-envelope-open-text me-2"></i>Notifikasi Denda Siswa</h2>
        <p class="text-muted">Kirim pemberitahuan email kepada siswa mengenai denda yang belum lunas.</p>
    </div>

    <?php
    if ($pesan_sukses_notif) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_sukses_notif) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if ($pesan_error_notif) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($pesan_error_notif) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
    if (isset($_SESSION['error_message_admin_auth'])) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" . htmlspecialchars($_SESSION['error_message_admin_auth']) . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
        unset($_SESSION['error_message_admin_auth']);
    }
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Template Pesan Email (Admin Dapat Mengedit)</h5>
        </div>
        <div class="card-body">
            <p class="small text-muted"><strong>JANGAN UBAH BAGIAN</strong>: [NAMA_SISWA], [NISN_SISWA], [JUDUL_BUKU], [TGL_PINJAM], [TGL_JATUH_TEMPO], [JUMLAH_DENDA_DIKENAKAN], [JUMLAH_TELAH_DIBAYAR], [SISA_DENDA], [KETERANGAN_DENDA]</p>
            <textarea id="templateEmailAdmin" class="form-control" rows="15"><?php echo htmlspecialchars($templatePesanDefaultGlobal); ?></textarea>
            <div class="mt-2">
                <label for="subjekEmailAdmin" class="form-label small">Subjek Email:</label>
                <input type="text" id="subjekEmailAdmin" class="form-control form-control-sm" value="<?php echo htmlspecialchars($subjekEmailDefaultGlobal); ?>">
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4 mb-4" id="previewAreaContainer" style="display:none;">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Preview Email</h5>
        </div>
        <div class="card-body">
            <strong>Subjek:</strong> <span id="previewSubjek"></span><br>
            <strong>Kepada:</strong> <span id="previewKepada"></span>
            <hr>
            <div id="previewEmailContent" style="border: 1px solid #ccc; padding: 10px; min-height: 200px; background: #fff; overflow-y: auto;">
                </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Daftar Denda Belum Lunas</h5>
                <button id="kirimSemuaNotifBtn" class="btn btn-danger btn-sm" <?php echo empty($daftar_denda_belum_lunas) ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane me-1"></i> Kirim ke Semua (<?php echo count($daftar_denda_belum_lunas); ?>)
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($daftar_denda_belum_lunas)): ?>
                <div class="alert alert-info text-center">Tidak ada data denda yang belum lunas saat ini untuk dinotifikasi.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No.</th>
                                <th>Nama Siswa</th>
                                <th>Email</th>
                                <th>Judul Buku</th>
                                <th style="min-width: 120px;">Sisa Denda (Rp)</th>
                                <th style="min-width: 200px;">Aksi</th>
                                <th style="min-width: 150px;">Status Pengiriman</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftar_denda_belum_lunas as $index => $denda): ?>
                            <tr id="denda-baris-<?php echo $denda['id_denda']; ?>">
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($denda['nama_siswa'] ?? '-'); ?> <small class="text-muted d-block">NISN: <?php echo htmlspecialchars($denda['nisn_siswa'] ?? '-'); ?></small></td>
                                <td><?php echo htmlspecialchars($denda['email_siswa'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($denda['judul_buku'] ?? '-'); ?></td>
                                <td class="text-end fw-bold text-danger"><?php echo number_format($denda['sisa_denda'] ?? 0, 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm tombol-preview-notif mb-1"
                                            data-id_denda="<?php echo htmlspecialchars($denda['id_denda'] ?? ''); ?>"
                                            data-email="<?php echo htmlspecialchars($denda['email_siswa'] ?? ''); ?>"
                                            data-nama_siswa="<?php echo htmlspecialchars($denda['nama_siswa'] ?? ''); ?>"
                                            data-nisn_siswa="<?php echo htmlspecialchars($denda['nisn_siswa'] ?? ''); ?>"
                                            data-judul_buku="<?php echo htmlspecialchars($denda['judul_buku'] ?? 'Tidak diketahui'); ?>"
                                            data-tgl_pinjam="<?php echo htmlspecialchars($denda['tgl_pinjam'] ?? ''); ?>"
                                            data-tgl_jatuh_tempo="<?php echo htmlspecialchars($denda['tgl_jatuh_tempo'] ?? ''); ?>"
                                            data-jumlah_denda_dikenakan="<?php echo htmlspecialchars($denda['jumlah_denda_dikenakan'] ?? '0'); ?>"
                                            data-jumlah_telah_dibayar="<?php echo htmlspecialchars($denda['jumlah_telah_dibayar'] ?? '0'); ?>"
                                            data-sisa_denda="<?php echo htmlspecialchars($denda['sisa_denda'] ?? '0'); ?>"
                                            data-keterangan_denda="<?php echo htmlspecialchars($denda['keterangan_denda'] ?? ''); ?>"
                                            title="Preview notifikasi untuk <?php echo htmlspecialchars($denda['nama_siswa'] ?? ''); ?>">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <button class="btn btn-primary btn-sm tombol-kirim-individual-notif mb-1"
                                            title="Kirim notifikasi ke <?php echo htmlspecialchars($denda['nama_siswa'] ?? ''); ?>"
                                            data-id_denda="<?php echo htmlspecialchars($denda['id_denda'] ?? ''); ?>"
                                            data-email="<?php echo htmlspecialchars($denda['email_siswa'] ?? ''); ?>"
                                            data-nama_siswa="<?php echo htmlspecialchars($denda['nama_siswa'] ?? ''); ?>"
                                            data-nisn_siswa="<?php echo htmlspecialchars($denda['nisn_siswa'] ?? ''); ?>"
                                            data-judul_buku="<?php echo htmlspecialchars($denda['judul_buku'] ?? 'Tidak diketahui'); ?>"
                                            data-tgl_pinjam="<?php echo htmlspecialchars($denda['tgl_pinjam'] ?? ''); ?>"
                                            data-tgl_jatuh_tempo="<?php echo htmlspecialchars($denda['tgl_jatuh_tempo'] ?? ''); ?>"
                                            data-jumlah_denda_dikenakan="<?php echo htmlspecialchars($denda['jumlah_denda_dikenakan'] ?? '0'); ?>"
                                            data-jumlah_telah_dibayar="<?php echo htmlspecialchars($denda['jumlah_telah_dibayar'] ?? '0'); ?>"
                                            data-sisa_denda="<?php echo htmlspecialchars($denda['sisa_denda'] ?? '0'); ?>" 
                                            data-keterangan_denda="<?php echo htmlspecialchars($denda['keterangan_denda'] ?? ''); ?>">
                                        <i class="fas fa-paper-plane"></i> Kirim
                                    </button>
                                </td>
                                <td class="status-kirim-notif text-center" id="status-kirim-<?php echo $denda['id_denda']; ?>">
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

<script>
// PASTIKAN URL ENDPOINT INI BENAR menunjuk ke server Node.js Email_Sender Anda
const EMAIL_SENDER_NODEJS_ENDPOINT = 'http://localhost:3000/send-email'; 

function formatAngkaKeRupiah(angka, desimal = 0) {
    if (angka === null || angka === undefined || isNaN(parseFloat(angka))) return '0';
    return parseFloat(angka).toLocaleString('id-ID', {
        minimumFractionDigits: desimal,
        maximumFractionDigits: desimal
    });
}

async function kirimEmailNotifikasiKeNode(payload, statusElementId) {
    const statusEl = document.getElementById(statusElementId);
    if (statusEl) {
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Mengirim...';
        statusEl.className = 'status-kirim-notif text-center text-primary';
    }

    try {
        const response = await fetch(EMAIL_SENDER_NODEJS_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (response.ok) {
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-check-circle text-success"></i> Berhasil';
                statusEl.className = 'status-kirim-notif text-center text-success';
            }
            console.log("Email terkirim: ", result.message);
        } else {
            if (statusEl) {
                statusEl.innerHTML = `<i class="fas fa-times-circle text-danger"></i> Gagal: ${result.message || 'Error tidak diketahui'}`;
                statusEl.className = 'status-kirim-notif text-center text-danger';
            }
            console.error("Gagal kirim: ", result.message || response.statusText);
        }
    } catch (error) {
        if (statusEl) {
            statusEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> Error Koneksi';
            statusEl.className = 'status-kirim-notif text-center text-danger';
        }
        console.error("Error fetch: ", error);
    }
}

function gantiPlaceholderPesan(templatePesan, data) {
    let pesan = templatePesan;
    pesan = pesan.replace(/\[NAMA_SISWA\]/g, data.nama_siswa || '');
    pesan = pesan.replace(/\[NISN_SISWA\]/g, data.nisn_siswa || '');
    pesan = pesan.replace(/\[JUDUL_BUKU\]/g, data.judul_buku || 'Tidak diketahui');
    pesan = pesan.replace(/\[TGL_PINJAM\]/g, data.tgl_pinjam ? new Date(data.tgl_pinjam).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-');
    pesan = pesan.replace(/\[TGL_JATUH_TEMPO\]/g, data.tgl_jatuh_tempo ? new Date(data.tgl_jatuh_tempo).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-');
    pesan = pesan.replace(/\[JUMLAH_DENDA_DIKENAKAN\]/g, formatAngkaKeRupiah(data.jumlah_denda_dikenakan, 0));
    pesan = pesan.replace(/\[JUMLAH_TELAH_DIBAYAR\]/g, formatAngkaKeRupiah(data.jumlah_telah_dibayar, 0));
    pesan = pesan.replace(/\[SISA_DENDA\]/g, formatAngkaKeRupiah(data.sisa_denda, 0));
    pesan = pesan.replace(/\[KETERANGAN_DENDA\]/g, data.keterangan_denda || '-');
    return pesan;
}

document.querySelectorAll('.tombol-preview-notif').forEach(tombol => {
    tombol.addEventListener('click', function() {
        const dataSiswaDariTombol = this.dataset;
        const templatePesanDariAdmin = document.getElementById('templateEmailAdmin').value;
        const subjekEmailDariAdmin = document.getElementById('subjekEmailAdmin').value || '<?php echo addslashes($subjekEmailDefaultGlobal); ?>';

        const pesanHTMLFinalUntukSiswa = gantiPlaceholderPesan(templatePesanDariAdmin, dataSiswaDariTombol);
        const subjekFinal = subjekEmailDariAdmin.replace(/\[NAMA_SISWA\]/g, dataSiswaDariTombol.nama_siswa);

        document.getElementById('previewSubjek').textContent = subjekFinal;
        document.getElementById('previewKepada').textContent = dataSiswaDariTombol.email;
        document.getElementById('previewEmailContent').innerHTML = pesanHTMLFinalUntukSiswa;
        document.getElementById('previewAreaContainer').style.display = 'block';
        document.getElementById('previewAreaContainer').scrollIntoView({ behavior: 'smooth' });
    });
});

document.querySelectorAll('.tombol-kirim-individual-notif').forEach(tombol => {
    tombol.addEventListener('click', function() {
        const dataSiswaDariTombol = this.dataset;
        const templatePesanDariAdmin = document.getElementById('templateEmailAdmin').value;
        const subjekEmailDariAdmin = document.getElementById('subjekEmailAdmin').value || '<?php echo addslashes($subjekEmailDefaultGlobal); ?>';

        const pesanHTMLFinalUntukSiswa = gantiPlaceholderPesan(templatePesanDariAdmin, dataSiswaDariTombol);

        const payloadEmail = {
            to: dataSiswaDariTombol.email,
            subject: subjekEmailDariAdmin.replace(/\[NAMA_SISWA\]/g, dataSiswaDariTombol.nama_siswa),
            message: pesanHTMLFinalUntukSiswa,
            template: 'default' // Menggunakan wrapper 'default' dari server.js Node.js
        };
        const idStatusElement = `status-kirim-${dataSiswaDariTombol.id_denda}`;
        kirimEmailNotifikasiKeNode(payloadEmail, idStatusElement);
    });
});

document.getElementById('kirimSemuaNotifBtn').addEventListener('click', function() {
    const semuaTombolKirimIndividual = document.querySelectorAll('.tombol-kirim-individual-notif');
    if (semuaTombolKirimIndividual.length === 0) {
        alert("Tidak ada data denda untuk dikirimkan notifikasinya.");
        return;
    }
    if (confirm(`Anda akan mencoba mengirim notifikasi ke ${semuaTombolKirimIndividual.length} siswa. Lanjutkan?`)) {
        semuaTombolKirimIndividual.forEach((tombol, index) => {
            setTimeout(() => {
                tombol.click(); 
            }, index * 1500); 
        });
    }
});

</script>

<?php
// SESUAIKAN PATH INI untuk footer
include_once __DIR__ . '/../../layout/footer.php';
ob_end_flush();
?>