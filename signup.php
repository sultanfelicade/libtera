<?php
require_once 'connect.php'; // koneksi mysqli

session_start();

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

// Ambil data kelas
$kelasList = [];
$result = mysqli_query($connect, "SELECT id_kelas, nama_kelas FROM kelas ORDER BY id_kelas ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kelasList[] = $row;
    }
} else {
    die("Gagal mengambil data kelas: " . mysqli_error($connect));
}

// Ambil data jurusan
$jurusanList = [];
$result2 = mysqli_query($connect, "SELECT id_jurusan, nama_jurusan FROM jurusan ORDER BY id_jurusan ASC");
if ($result2) {
    while ($row = mysqli_fetch_assoc($result2)) {
        $jurusanList[] = $row;
    }
} else {
    die("Gagal mengambil data jurusan: " . mysqli_error($connect));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? ''); // Ambil data email
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $no_tlp = trim($_POST['no_tlp'] ?? '');
    $id_kelas = $_POST['id_kelas'] ?? '';
    $id_jurusan = $_POST['id_jurusan'] ?? '';

    // Validasi input
    if (empty($nisn) || empty($nama) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)
        || empty($jenis_kelamin) || empty($no_tlp) || empty($id_kelas) || empty($id_jurusan)) {
        $error = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password !== $confirmPassword) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'])) {
        $error = "Pilih jenis kelamin dengan benar.";
    } else {
        // Cek nisn, username, dan email sudah ada atau belum
        $stmt = mysqli_prepare($connect, "SELECT id_siswa FROM siswa WHERE nisn = ? OR username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "sss", $nisn, $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "NISN, Username, atau Email sudah terdaftar.";
        } else {
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert data
            $stmtInsert = mysqli_prepare($connect, "INSERT INTO siswa (nisn, nama, username, email, password, jenis_kelamin, no_tlp, id_kelas, id_jurusan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmtInsert, "sssssssis", $nisn, $nama, $username, $email, $passwordHash, $jenis_kelamin, $no_tlp, $id_kelas, $id_jurusan);
            $saved = mysqli_stmt_execute($stmtInsert);


            if ($saved) {
                $_SESSION['success'] = "Pendaftaran berhasil! Silakan login.";
                header('Location: index.php');
                exit;
            } else {
                $error = "Terjadi kesalahan saat menyimpan data: " . mysqli_error($connect);
            }
            mysqli_stmt_close($stmtInsert);
        }
        mysqli_stmt_close($stmt);
    }
    $_SESSION['error'] = $error;
    // Simpan inputan form ke session agar tidak hilang saat refresh
    $_SESSION['form_input'] = $_POST;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil data form dari session jika ada
$formInput = $_SESSION['form_input'] ?? [];
unset($_SESSION['form_input']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar Akun Siswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
  <div class="card p-4 shadow register-card" style="max-width:450px; margin: 80px auto; border-radius:12px;">
    <div class="text-center mb-3">
      <h3 class="fw-bold mt-2">Daftar Akun Siswa</h3>
      <p class="text-muted">Isi data untuk membuat akun</p>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="nisn" class="form-label">NISN</label>
        <input type="text" name="nisn" id="nisn" class="form-control" required value="<?= htmlspecialchars($formInput['nisn'] ?? '') ?>" />
        <div class="invalid-feedback">Masukkan NISN!</div>
      </div>

      <div class="mb-3">
        <label for="nama" class="form-label">Nama Lengkap</label>
        <input type="text" name="nama" id="nama" class="form-control" required value="<?= htmlspecialchars($formInput['nama'] ?? '') ?>" />
        <div class="invalid-feedback">Masukkan Nama Lengkap!</div>
      </div>

      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($formInput['username'] ?? '') ?>" />
        <div class="invalid-feedback">Masukkan Username!</div>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($formInput['email'] ?? '') ?>" />
        <div class="invalid-feedback">Masukkan alamat email yang valid!</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select" required>
            <option value="" disabled <?= !isset($formInput['jenis_kelamin']) ? 'selected' : '' ?>>Pilih Jenis Kelamin</option>
            <option value="L" <?= (($formInput['jenis_kelamin'] ?? '') == 'L') ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= (($formInput['jenis_kelamin'] ?? '') == 'P') ? 'selected' : '' ?>>Perempuan</option>
        </select>
        <div class="invalid-feedback">Pilih Jenis Kelamin!</div>
      </div>

      <div class="mb-3">
        <label for="no_tlp" class="form-label">No. Telepon</label>
        <input type="text" name="no_tlp" id="no_tlp" class="form-control" required value="<?= htmlspecialchars($formInput['no_tlp'] ?? '') ?>" />
        <div class="invalid-feedback">Masukkan No. Telepon!</div>
      </div>

      <div class="mb-3">
        <label for="id_kelas" class="form-label">Kelas</label>
        <select name="id_kelas" id="id_kelas" class="form-select" required>
            <option value="" disabled <?= !isset($formInput['id_kelas']) ? 'selected' : '' ?>>Pilih Kelas</option>
            <?php foreach ($kelasList as $kelas): ?>
            <option value="<?= htmlspecialchars($kelas['id_kelas']) ?>" <?= (($formInput['id_kelas'] ?? '') == $kelas['id_kelas']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($kelas['nama_kelas']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">Pilih Kelas!</div>
      </div>

      <div class="mb-3">
        <label for="id_jurusan" class="form-label">Jurusan</label>
        <select name="id_jurusan" id="id_jurusan" class="form-select" required>
            <option value="" disabled <?= !isset($formInput['id_jurusan']) ? 'selected' : '' ?>>Pilih Jurusan</option>
            <?php foreach ($jurusanList as $jurusan): ?>
            <option value="<?= htmlspecialchars($jurusan['id_jurusan']) ?>" <?= (($formInput['id_jurusan'] ?? '') == $jurusan['id_jurusan']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">Pilih Jurusan!</div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required />
        <div class="invalid-feedback">Masukkan Password!</div>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required />
        <div class="invalid-feedback">Konfirmasi Password harus sama!</div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Daftar</button>
      <a href="index.php" class="btn btn-secondary w-100 mt-2">Kembali ke Login</a>

    </form>
  </div>
</div>

<script>
// Bootstrap form validation
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>