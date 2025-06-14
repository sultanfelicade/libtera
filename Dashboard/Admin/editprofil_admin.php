<?php
session_start();
require_once "../../connect.php"; // Sesuaikan path ke file koneksi database Anda

// Pastikan hanya admin yang login yang bisa mengakses halaman ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: /libtera/login.php"); // Arahkan ke halaman login jika tidak sah
    exit;
}

$message = '';
$admin_id = $_SESSION['admin']['id']; // Ambil ID admin dari session

// Logika untuk memproses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $nama_admin = trim($_POST['nama_admin']);
    $no_tlp = trim($_POST['no_tlp']);

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $current_password = $_POST['current_password'];

    $update_password = !empty($new_password);
    $can_update = true;

    // Lakukan validasi hanya jika pengguna mencoba mengubah password
    if ($update_password) {
        // 1. Ambil password hash yang sekarang dari database
        $stmt_check = mysqli_prepare($connect, "SELECT password FROM admin WHERE id = ?");
        mysqli_stmt_bind_param($stmt_check, "i", $admin_id);
        mysqli_stmt_execute($stmt_check);
        $result = mysqli_stmt_get_result($stmt_check);
        $admin_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt_check);

        // 2. Verifikasi password saat ini
        if (!password_verify($current_password, $admin_data['password'])) {
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
            $sql = "UPDATE admin SET username = ?, nama_admin = ?, no_tlp = ?, password = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt_update, "ssssi", $username, $nama_admin, $no_tlp, $hashed_password, $admin_id);
        } else {
            // Jika password tidak diubah
            $sql = "UPDATE admin SET username = ?, nama_admin = ?, no_tlp = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt_update, "sssi", $username, $nama_admin, $no_tlp, $admin_id);
        }

        if (mysqli_stmt_execute($stmt_update)) {
            // Update data di session agar tampilan langsung berubah
            $_SESSION['admin']['username'] = $username;
            $_SESSION['admin']['nama_admin'] = $nama_admin;
            $_SESSION['admin']['no_tlp'] = $no_tlp;
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Profil berhasil diperbarui!'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gagal memperbarui profil.'];
        }
        mysqli_stmt_close($stmt_update);
    }

    header("Location: editprofil_admin.php"); // Refresh halaman untuk menampilkan pesan
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
    <title>Edit Profil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 mb-5">
        <h2>Edit Profil Admin</h2>
        <hr>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message['type']); ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>
        <form action="editprofil_admin.php" method="POST">
            <div class="mb-3">
                <label for="nama_admin" class="form-label">Nama Admin</label>
                <input type="text" class="form-control" id="nama_admin" name="nama_admin" value="<?php echo htmlspecialchars($_SESSION['admin']['nama_admin']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['admin']['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="no_tlp" class="form-label">Nomor Telepon</label>
                <input type="text" class="form-control" id="no_tlp" name="no_tlp" value="<?php echo htmlspecialchars($_SESSION['admin']['no_tlp']); ?>" required>
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
            <a href="/libtera/Dashboard/Admin/index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>