<?php
session_start();
require_once "../../connect.php"; // Sesuaikan path ke file koneksi Anda

// Pastikan hanya siswa yang login yang bisa mengakses halaman ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'siswa') {
    header("Location: /libtera/login.php");
    exit;
}

$message = '';
// Asumsi nama kolom primary key adalah 'id_siswa'
$siswa_id = $_SESSION['siswa']['id_siswa']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $current_password = $_POST['current_password'];

    $update_password = !empty($new_password);
    $can_update = true;

    // Lakukan validasi hanya jika pengguna mencoba mengubah password
    if ($update_password) {
        // 1. Ambil password hash yang sekarang dari database
        $stmt_check = mysqli_prepare($connect, "SELECT password FROM siswa WHERE id_siswa = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $siswa_id);
        mysqli_stmt_execute($stmt_check);
        $result = mysqli_stmt_get_result($stmt_check);
        $siswa_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt_check);

        // 2. Verifikasi password saat ini
        if (!$siswa_data || !password_verify($current_password, $siswa_data['password'])) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Password saat ini yang Anda masukkan salah!'];
            $can_update = false;
        } 
        // 3. Verifikasi password baru dan konfirmasinya
        elseif ($new_password !== $confirm_password) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Konfirmasi password baru tidak cocok!'];
            $can_update = false;
        }
    }

    // Lanjutkan proses update hanya jika semua validasi lolos
    if ($can_update) {
        if ($update_password) {
            // Jika password diubah
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE siswa SET username = ?, password = ? WHERE id_siswa = ?";
            $stmt_update = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt_update, "ssi", $username, $hashed_password, $siswa_id);
        } else {
            // Jika password tidak diubah
            $sql = "UPDATE siswa SET username = ? WHERE id_siswa = ?";
            $stmt_update = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt_update, "si", $username, $siswa_id);
        }

        if (mysqli_stmt_execute($stmt_update)) {
            // Update data username di session agar tampilan langsung berubah
            $_SESSION['siswa']['username'] = $username;
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Profil berhasil diperbarui!'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal memperbarui profil.'];
        }
        mysqli_stmt_close($stmt_update);
    }

    header("Location: editprofil_siswa.php");
    exit;
}

// Ambil pesan dari session untuk ditampilkan
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 mb-5">
        <h2>Edit Profil</h2>
        <p class="text-muted">Halo, <?php echo htmlspecialchars($_SESSION['siswa']['nama']); ?>!</p>
        <hr>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>
        <form action="editprofil_siswa.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['siswa']['username']); ?>" required>
            </div>
            
            <h5 class="mt-4">Ubah Password</h5>
            <hr>
            <div class="mb-3">
                <label for="current_password" class="form-label">Password Saat Ini</label>
                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Masukkan password Anda saat ini untuk mengubahnya">
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Kosongkan jika tidak ingin ganti">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ketik ulang password baru Anda">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="/libtera/Dashboard/Siswa/index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>