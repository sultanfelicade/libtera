<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
  <div class="card p-4 shadow login-card" style="max-width:450px; margin: 80px auto; border-radius:12px;">
    <div class="text-center mb-3">
      <h3 class="fw-bold mt-2">Sign In</h3>
      <p class="text-muted">Silakan login untuk melanjutkan</p>
    </div>
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="post" action="index.php" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" required />
        <div class="invalid-feedback">Masukkan Username!</div>
      </div>

      <div class="mb-4">
        <label for="passwordInput" class="form-label">Password</label>
        <input type="password" name="password" id="passwordInput" class="form-control" required />
        <div class="invalid-feedback">Masukkan Password!</div>
      </div>

      <button type="submit" name="signIn" class="btn btn-primary w-100">Sign In</button>
    </form>

    <!-- Tambahan: link daftar -->
    <div class="text-center mt-3">
      <p class="">Belum punya akun? <a href="signup.php">Daftar sekarang</a></p>
    </div>
  </div>
</div>

<!-- Tombol HALAMAN DEPAN di luar card -->
<div class="text-center mt-3">
  <a href="index.html" class="btn btn-outline-secondary btn-sm">HALAMAN DEPAN</a>
</div>
<!-- Tambahan: tombol kembali ke dashboard -->
<script>
  (function () {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
