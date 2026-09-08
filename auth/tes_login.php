<?php
require '../config/db.php';
/** @var mysqli $conn */

// SESUAIKAN DENGAN USERNAME ADMIN ANDA
 $username_admin = 'admin'; 
 $password_tes = '123456';

 $cek = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username_admin'");
 $data = mysqli_fetch_assoc($cek);

if (!$data) {
    echo "ERROR: Username '<b>$username_admin</b>' TIDAK DITEMUKAN di database!";
} else {
    echo "Username ditemukan: " . $data['username'] . "<br>";
    echo "Role di database: " . $data['role'] . "<br>";
    echo "Password di database: <b>" . $data['password'] . "</b><br><hr>";
    
    if (password_verify($password_tes, $data['password'])) {
        echo "<h2 style='color:green;'>✅ BERHASIL! Sistem bisa membaca passwordnya.</h2>";
        echo "Artinya masalah ada di form login Anda (mungkin arah action-nya salah).";
    } else {
        echo "<h2 style='color:red;'>❌ GAGAL! Password tidak cocok.</h2>";
        echo "Artinya isi database memang masih salah (bukan hash yang benar).";
    }
}
?>