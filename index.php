<?php
session_start();
require_once "connect.php";

if (isset($_POST['signIn'])) {
  $username = strtolower(trim($_POST['username']));
  $password = $_POST['password'];

// Cek siswa
$querySiswa = mysqli_query($connect, "SELECT * FROM siswa WHERE username = '$username'");
if (mysqli_num_rows($querySiswa) === 1) {
  $data = mysqli_fetch_assoc($querySiswa);
  if (password_verify($password, $data['password'])) {  // Gunakan password_verify
    $_SESSION['login'] = true;
    $_SESSION['role'] = 'siswa';
    $_SESSION['siswa'] = $data;
    $_SESSION['id_siswa'] = $data['id_siswa'];
    header("Location: /libtera/Dashboard/Siswa/index.php");
    exit;
  }
}


  // Cek admin
  $queryAdmin = mysqli_query($connect, "SELECT * FROM admin WHERE username = '$username'");
  if (mysqli_num_rows($queryAdmin) === 1) {
    $data = mysqli_fetch_assoc($queryAdmin);
    if ($password === $data['password']) {
      $_SESSION['login'] = true;
      $_SESSION['role'] = 'admin';
      $_SESSION['admin'] = $data;
      header("Location: /libtera/Dashboard/Admin/index.php");
      exit;
    }
  }

  // Jika gagal login
  $_SESSION['error'] = "Username atau Password salah!";
  header("Location: login.php");
  exit;
}

// Redirect jika sudah login dan akses langsung ke index.php
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
  if ($_SESSION['role'] === 'siswa') {
    header("Location: /libtera/Dashboard/Siswa/index.php");
    exit;
  } elseif ($_SESSION['role'] === 'admin') {
    header("Location: /libtera/Dashboard/Admin/index.php");
    exit;
  }
}

// Jika belum login dan tidak submit, redirect ke login form
header("Location: login.php");
exit;
