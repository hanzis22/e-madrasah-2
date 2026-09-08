<?php
// Kode ini untuk mendeteksi halaman apa yang sedang dibuka
 $halaman_sekarang = basename($_SERVER['PHP_SELF']);
?>

<div class="menu-samping">
    <a href="index.php" class="<?= ($halaman_sekarang == 'index.php') ? 'aktif' : '' ?>"><i class="bi bi-house-door-fill me-2"></i>Dashboard</a>
    <a href="guru.php" class="<?= ($halaman_sekarang == 'guru.php') ? 'aktif' : '' ?>"><i class="bi bi-person-workspace me-2"></i>Data Guru</a>
    <a href="kelas.php" class="<?= ($halaman_sekarang == 'kelas.php') ? 'aktif' : '' ?>"><i class="bi bi-diagram-3 me-2"></i>Data Kelas</a>
    <a href="siswa.php" class="<?= ($halaman_sekarang == 'siswa.php') ? 'aktif' : '' ?>"><i class="bi bi-mortarboard-fill me-2"></i>Data Siswa</a>
    <a href="mapel.php" class="<?= ($halaman_sekarang == 'mapel.php') ? 'aktif' : '' ?>"><i class="bi bi-book-half me-2"></i>Data Mapel</a>
    <a href="ortu.php" class="<?= ($halaman_sekarang == 'ortu.php') ? 'aktif' : '' ?>"><i class="bi bi-people-fill me-2"></i>Data Orang Tua</a>
    <a href="mengajar.php" class="<?= ($halaman_sekarang == 'mengajar.php') ? 'aktif' : '' ?>"><i class="bi bi-calendar4-week me-2"></i>Kelola Mengajar</a>
    <a href="ortu_siswa.php" class="<?= ($halaman_sekarang == 'ortu_siswa.php') ? 'aktif' : '' ?>"><i class="bi bi-link-45deg me-2"></i>Manajemen Orang Tua</a>
    <a href="pembayaran.php" class="<?= ($halaman_sekarang == 'pembayaran.php') ? 'aktif' : '' ?>"><i class="bi bi-cash-coin me-2"></i>Kelola Pembayaran</a>
</div>