<?php require '../templates/header_siswa.php'; ?>
<?php require '../templates/sidebar_siswa.php'; ?>
<?php
/** @var mysqli $conn */
 $id_siswa_login = $_SESSION['id'];
 $siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id_siswa_login'"));

 $id_kelas_siswa = $siswa['id_kelas'];
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Akses Materi Pembelajaran</h4>
        <p style="margin:0; color:#666; font-size:14px;">Download PDF atau tonton video pembelajaran yang dibagikan oleh guru.</p>
    </div>

    <?php if (empty($siswa['id_kelas'])): ?>
        <div class="box-putih" style="text-align:center; padding: 50px;">
            <h5 style="color:#999;">Anda belum ditugaskan ke kelas manapun.</h5>
            <p style="color:#999;">Silakan hubungi administrator.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php 
            $materi = mysqli_query($conn, "SELECT m.*, mp.nama_mapel, u.nama as nama_guru 
                                          FROM materi m
                                          JOIN mapel mp ON m.id_mapel = mp.id
                                          JOIN users u ON m.id_guru = u.id
                                          WHERE m.id_kelas = '{$siswa['id_kelas']}'
                                          ORDER BY m.tanggal_upload DESC");
            
            if (mysqli_num_rows($materi) > 0) {
                while ($row = mysqli_fetch_assoc($materi)) { 
                    // Ambil jenis, kalau kosong diisi string kosong agar tidak error "Undefined array key"
                    $jenis = isset($row['jenis_materi']) ? $row['jenis_materi'] : '';
                    $ikon = ($jenis == 'pdf') ? '📄' : '🎬';

                    $has_pdf = !empty($row['file_pdf']);
                    $has_yt = !empty($row['link_youtube']);
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="box-putih" style="text-align: center; padding: 25px;">
                            <div style="font-size: 40px; margin-bottom: 15px;">
                                <?= $ikon ?>
                            </div>
                            <h6 style="margin-bottom: 10px; font-weight: bold;"><?= htmlspecialchars($row['judul_materi'], ENT_QUOTES) ?></h6>
                                                        <small style="color:#777; display:block; margin-bottom: 5px;">Mapel: <?= htmlspecialchars($row['nama_mapel'], ENT_QUOTES) ?></small>
                                                        <small style="color:#777; display:block; margin-bottom: 20px;">Guru: <?= htmlspecialchars($row['nama_guru'], ENT_QUOTES) ?></small>
                            
                            <!-- LOGIKA TOMBOL: SEJAJAR ATAU FULL -->
                            <?php if ($has_pdf && $has_yt): ?>
                                <!-- Kalau ADA 2: Pakai Grid agar sejajar (col-6 col-6) -->
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="../download.php?f=<?= rawurlencode($row['file_pdf']) ?>" target="_blank" class="btn btn-cari btn-sm w-100"><i class="bi bi-filetype-pdf me-2"></i>PDF</a>
                                        <?php /* H-04 FIX */ ?>
                                    </div>
                                    <div class="col-6">
                                        <a href="<?= $row['link_youtube'] ?>" target="_blank" class="btn btn-red btn-sm w-100"><i class="bi bi-caret-right-square-fill me-2"></i>YouTube</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Kalau CUMA 1: Langsung full width (w-100) -->
                                <?php if ($has_pdf): ?>
                                    <a href="../download.php?f=<?= rawurlencode($row['file_pdf']) ?>" target="_blank" class="btn btn-cari btn-sm w-100"><i class="bi bi-filetype-pdf me-2"></i>Buka/Download PDF</a>
                                    <?php /* H-04 FIX */ ?>
                                <?php endif; ?>
                                
                                <?php if ($has_yt): ?>
                                    <a href="<?= $row['link_youtube'] ?>" target="_blank" class="btn btn-red btn-sm w-100"><i class="bi bi-caret-right-square-fill me-2"></i>Tonton Video YouTube</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                } 
            } else { ?>
                <div class="col-12">
                    <div class="box-putih" style="text-align:center; padding: 50px;">
                        <h5 style="color:#999;">Belum ada materi untuk kelas Anda.</h5>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php endif; ?>
</div>

<?php require '../templates/footer_siswa.php'; ?>