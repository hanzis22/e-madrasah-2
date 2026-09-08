<?php $halaman_sekarang = basename($_SERVER['PHP_SELF']); ?>
<div class="menu-samping">
    <a href="index.php" class="<?= ($halaman_sekarang == 'index.php') ? 'aktif' : '' ?>"><i class="bi bi-house-door-fill me-2"></i>Dashboard</a>
    <a href="pembayaran.php" class="<?= ($halaman_sekarang == 'pembayaran.php') ? 'aktif' : '' ?>"><i class="bi bi-wallet2 me-2"></i>Pembayaran SPP</a>
    <a href="riwayat.php" class="<?= ($halaman_sekarang == 'riwayat.php') ? 'aktif' : '' ?>"><i class="bi bi-clock-history me-2"></i>Riwayat Pembayaran</a>
    <a href="nilai.php" class="<?= ($halaman_sekarang == 'nilai.php') ? 'aktif' : '' ?>"><i class="bi bi-graph-up me-2"></i>Nilai Anak</a>
    <a href="rapor.php" class="<?= ($halaman_sekarang == 'rapor.php') ? 'aktif' : '' ?>"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i></i>E-Rapor</a>
    <a href="profil.php" class="<?= ($halaman_sekarang == 'profil.php') ? 'aktif' : '' ?>"><i class="bi bi-person-circle me-2"></i>Profil Saya</a>
</div>