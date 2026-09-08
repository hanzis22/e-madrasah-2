<?php
 $halaman_sekarang = basename($_SERVER['PHP_SELF']);
?>
<div class="menu-samping">
    <a href="index.php" class="<?= ($halaman_sekarang == 'index.php') ? 'aktif' : '' ?>"><i class="bi bi-house-door-fill me-2"></i>Dashboard</a>
    <a href="materi.php" class="<?= ($halaman_sekarang == 'materi.php') ? 'aktif' : '' ?>"><i class="bi bi-file-earmark-text me-2"></i>Kelola Materi</a>
    <a href="tugas.php" class="<?= ($halaman_sekarang == 'tugas.php') ? 'aktif' : '' ?>"><i class="bi bi-journal-check me-2"></i>Kelola Tugas</a>
    <a href="penilaian.php" class="<?= ($halaman_sekarang == 'penilaian.php') ? 'aktif' : '' ?>"><i class="bi bi-star me-2"></i>Penilaian</a>
    <a href="rapor.php" class="<?= ($halaman_sekarang == 'rapor.php') ? 'aktif' : '' ?>"><i class="bi bi-file-earmark me-2"></i></i>Rapor</a>
    <a href="profil.php" class="<?= ($halaman_sekarang == 'profil.php') ? 'aktif' : '' ?>"><i class="bi bi-person-circle me-2"></i>Profil Saya</a>
</div>