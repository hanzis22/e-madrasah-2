<?php
session_start();
require '../config/db.php';

/** @var mysqli $conn */

if (isset($_POST['login'])) {
    // 1. Ambil input dan amankan dari SQL Injection
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password']; // JANGAN di-md5 atau di-hash di sini

    // 2. Cari user BERDASARKAN USERNAME SAJA (jangan cek password di SQL)
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    $data = mysqli_fetch_assoc($query);

    // 3. Cek apakah user ditemukan DAN password_verify cocok
    if ($data && password_verify($pass, $data['password'])) {
        
        // Simpan data ke session
        $_SESSION['login'] = true;
        $_SESSION['id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['foto'] = $data['foto']; 

        // Arahkan berdasarkan role
        switch ($data['role']) {
            case 'admin':
                header("Location: ../admin/index.php");
                break;
            case 'guru':
                header("Location: ../guru/index.php");
                break;
            case 'siswa':
                header("Location: ../siswa/index.php");
                break;
            case 'ortu':
                header("Location: ../ortu/index.php");
                break;
            default:
                header("Location: ../index.php"); 
                break;
        }
        exit(); 
        
    } else {
        // Jika username salah ATAU password salah
        header("Location: login.php?msg=gagal");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>