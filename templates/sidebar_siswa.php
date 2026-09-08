<?php
 $halaman_sekarang = basename($_SERVER['PHP_SELF']);
?>
<div class="menu-samping">
    <a href="index.php" class="<?= ($halaman_sekarang == 'index.php') ? 'aktif' : '' ?>"><i class="bi bi-house-door-fill me-2"></i>Dashboard</a>
    <a href="materi.php" class="<?= ($halaman_sekarang == 'materi.php') ? 'aktif' : '' ?>"><i class="bi bi-book me-2"></i>Akses Materi</a>
    <a href="tugas.php" class="<?= ($halaman_sekarang == 'tugas.php') ? 'aktif' : '' ?>"><i class="bi bi-send-check me-2"></i>Pengumpulan Tugas</a>
    <a href="nilai.php" class="<?= ($halaman_sekarang == 'nilai.php') ? 'aktif' : '' ?>"><i class="bi bi-clipboard-data me-2"></i>Nilai Saya</a>
    <a href="erapor.php" class="<?= ($halaman_sekarang == 'erapor.php') ? 'aktif' : '' ?>"><i class="bi bi-download me-2"></i>Download E-Rapor</a>
    <a href="profil.php" class="<?= ($halaman_sekarang == 'profil.php') ? 'aktif' : '' ?>"><i class="bi bi-person-circle me-2"></i>Profil Saya</a>
</div>