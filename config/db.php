<?php
date_default_timezone_set('Asia/Jakarta');

 $host = "localhost";
 $user = "root";
 $pass = "root"; 
 $db   = "e_madrasah";

 $conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// ==========================================
// TAMBAHAN: Fungsi Helper agar tidak error
// ==========================================

// Fungsi untuk amankan input ke database
if (!function_exists('escape')) {
    function escape($data) {
        global $conn;
        return mysqli_real_escape_string($conn, $data);
    }
}

// Fungsi untuk amankan tampilan di HTML (mengganti clean())
if (!function_exists('clean')) {
    function clean($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

// Matikan warning deprecated di PHP 8+ (Error Passing Null)
error_reporting(E_ALL & ~E_DEPRECATED);
?>