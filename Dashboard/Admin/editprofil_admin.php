<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5" style="max-width: 600px;">
    <h3 class="mb-4">Edit Profil Admin</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['nama_admin']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">No. Telepon</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['no_tlp']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Username Baru</label>
            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($admin['username']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password Lama</label>
            <input type="password" name="old_password" class="form-control" placeholder="Wajib diisi jika ganti password">
        </div>
        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak ganti">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>
</html>
