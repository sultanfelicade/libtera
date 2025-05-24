<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* coba */
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .login-card {
      max-width: 450px;
      margin: auto;
      margin-top: 80px;
      border-radius: 12px;
    }
    .logo-img {
      height: 60px;
    }
    .input-group .btn {
      padding: 0 10px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card p-4 shadow login-card">
    <div class="text-center mb-3">
      <img src="/libtera/assets/logoFooter.png" alt="Logo" class="logo-img mb-2">
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
        <input type="text" name="username" id="username" class="form-control" required>
        <div class="invalid-feedback">Masukkan Username!</div>
      </div>

      <div class="mb-4">
        <label for="passwordInput" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password" class="form-control pe-4" id="passwordInput" required>
          <span class="input-group-text bg-white border-start-0">
            <button class="btn p-0 border-0 bg-transparent" type="button" onclick="togglePassword()" tabindex="-1">
              <i class="fa fa-eye"></i>
            </button>
          </span>
        </div>
        <div class="invalid-feedback">Masukkan Password!</div>
      </div>

      <div class="d-flex justify-content-between">
        <button type="submit" name="signIn" class="btn btn-primary w-100 me-2">Sign In</button>
        <a href="/libtera-main/index.html" class="btn btn-outline-secondary w-100 ms-2">Batal</a>
      </div>

      <p class="text-center mt-3">Belum punya akun? <a href="/libtera/connect.php">Daftar</a></p>
    </form>
  </div>
</div>

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

function togglePassword() {
  const pwd = document.getElementById("passwordInput");
  pwd.type = pwd.type === "password" ? "text" : "password";
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
