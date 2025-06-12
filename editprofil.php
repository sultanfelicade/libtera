<?php
session_start();
require 'connect.php';

// Cek koneksi
if (!isset($connect) || mysqli_connect_errno()) {
    die("Koneksi database tidak tersedia.");
}

// Cek login
if (!isset($_SESSION['id_siswa'])) {
    header("Location: login.php");
    exit();
}

$id_siswa = $_SESSION['id_siswa'];

// Ambil data saat ini
$sql = "SELECT username FROM siswa WHERE id_siswa = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi username
    if (strlen($username) < 4) {
        $_SESSION['error'] = "Username minimal 4 karakter.";
        header("Location: editprofil.php");
        exit();
    }

    // Cek apakah username sudah digunakan oleh orang lain
    $cekUsername = $connect->prepare("SELECT id_siswa FROM siswa WHERE username = ? AND id_siswa != ?");
    $cekUsername->bind_param("si", $username, $id_siswa);
    $cekUsername->execute();
    $cekUsername->store_result();

    if ($cekUsername->num_rows > 0) {
        $_SESSION['error'] = "Username sudah digunakan oleh pengguna lain.";
        header("Location: editprofil.php");
        exit();
    }

    // Update
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $_SESSION['error'] = "Password minimal 6 karakter.";
            header("Location: editprofil.php");
            exit();
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $connect->prepare("UPDATE siswa SET username = ?, password = ? WHERE id_siswa = ?");
        $update->bind_param("ssi", $username, $password_hash, $id_siswa);
    } else {
        $update = $connect->prepare("UPDATE siswa SET username = ? WHERE id_siswa = ?");
        $update->bind_param("si", $username, $id_siswa);
    }

    if ($update->execute()) {
        $_SESSION['success'] = "Profil berhasil diperbarui!";
        header("Location: editprofil.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil.";
        header("Location: editprofil.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Profil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 500px;">
  <div class="card shadow p-4 rounded">
    <h3 class="mb-4 text-center">Edit Profil</h3>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username Baru</label>
        <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($data['username'] ?? '', ENT_QUOTES) ?>">
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password Baru (opsional)</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-link mt-2">← Kembali ke Beranda</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
