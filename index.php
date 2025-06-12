<?php
session_start();
require_once "connect.php";

if (isset($_POST['signIn'])) {
    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];

    // Cek siswa menggunakan prepared statement
    $stmtSiswa = mysqli_prepare($connect, "SELECT * FROM siswa WHERE username = ?");
    mysqli_stmt_bind_param($stmtSiswa, "s", $username);
    mysqli_stmt_execute($stmtSiswa);
    $resultSiswa = mysqli_stmt_get_result($stmtSiswa);

    if (mysqli_num_rows($resultSiswa) === 1) {
        $data = mysqli_fetch_assoc($resultSiswa);
        if (password_verify($password, $data['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'siswa';
            $_SESSION['siswa'] = $data;
            $_SESSION['id_siswa'] = $data['id_siswa'];
            header("Location: /libtera/Dashboard/Siswa/index.php");
            exit;
        }
    }

    // Cek admin menggunakan prepared statement
    $stmtAdmin = mysqli_prepare($connect, "SELECT * FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($stmtAdmin, "s", $username);
    mysqli_stmt_execute($stmtAdmin);
    $resultAdmin = mysqli_stmt_get_result($stmtAdmin);

    if (mysqli_num_rows($resultAdmin) === 1) {
        $data = mysqli_fetch_assoc($resultAdmin);
        if (password_verify($password, $data['password'])) { // Diperbaiki: Gunakan password_verify
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

// Redirect jika sudah login
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