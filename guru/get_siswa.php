<?php
require '../config/db.php';
/** @var mysqli $conn */

if (isset($_POST['id_kelas'])) {
    $id_kelas = $_POST['id_kelas'];
    
    $siswa = mysqli_query($conn, "SELECT id, nama FROM users WHERE id_kelas='$id_kelas' AND role='siswa' ORDER BY nama ASC");
    
    echo "<option value=''>-- Pilih Siswa --</option>";
    while ($s = mysqli_fetch_assoc($siswa)) {
        echo "<option value='".$s['id']."'>".$s['nama']."</option>";
    }
}
?>