<?php
if (!isset($_SESSION["login"]) || !isset($_SESSION["role"])) {
  header("Location: /libtera/login.php");
  exit;
}
$role = $_SESSION["role"];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $title ?? 'Libtera' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="/libtera/layout/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar bg-body-tertiary fixed-top shadow-sm d-lg-none navbar-mobile">
  <div class="container-fluid">
    <a class="navbar-brand" href="/libtera/index.php">
      <img src="/libtera/assets/logoFooter.png" alt="logo" style="max-height: 40px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title"><?php echo $role === 'admin' ? 'Menu Admin' : 'Menu Siswa'; ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <?php include '_menu_content.php'; ?>
      </div>
    </div>
  </div>
</nav>

<div class="sidebar d-none d-lg-block shadow-sm">
    <div class="text-center mb-4">
        <a href="/libtera/index.php">
            <img src="/libtera/assets/logoFooter.png" alt="logo" style="max-height: 50px;">
        </a>
    </div>
    <?php include '_menu_content.php'; ?>
</div>

<main class="main-content p-4">