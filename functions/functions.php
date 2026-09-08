<?php
// Fungsi untuk memastikan halaman ini tidak bisa diakses kalau belum login
function cek_login() {
    session_start();
    if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Fungsi untuk mengecek role, misal: cek_role('admin');
function cek_role($role_yang_diizinkan) {
    if ($_SESSION['role'] != $role_yang_diizinkan) {
        // Kalau rolenya salah, lempar ke beranda
        header("Location: ../index.php");
        exit();
    }
}
?>