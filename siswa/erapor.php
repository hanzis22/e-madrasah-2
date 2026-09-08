<?php require '../templates/header_siswa.php'; ?>
<?php require '../templates/sidebar_siswa.php'; ?>
<?php
/** @var mysqli $conn */
 $id_siswa_login = $_SESSION['id'];
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">E-Rapor Saya</h4>
        <p style="margin:0; color:#666; font-size:14px;">Download dokumen rapor semester yang telah diupload oleh guru.</p>
    </div>

    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <!-- KOLOM TAHUN AJARAN DIHAPUS, GANTI JADI PERIODE/SEMESTER -->
                    <th>Periode / Semester</th>
                    <th width="150px">Tanggal Upload</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $data = mysqli_query($conn, "SELECT * FROM rapor WHERE id_siswa = '$id_siswa_login' ORDER BY id DESC");
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { 
                        $tgl_upload = date('d M Y', strtotime($row['tanggal_upload']));
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <!-- MENAMPILKAN ISI KOLOM SEMESTER (YANG SUDAH TERMASUK TAHUN AJARAN DI DALAMNYA) -->
                            <td><?= $row['semester'] ?></td>
                            <td><?= $tgl_upload ?></td>
                            <td style="text-align:center;">
                                <a href="../uploads/<?= $row['file_pdf'] ?>" target="_blank" class="btn btn-cari btn-sm"><i class="bi bi-download me-2"></i>Download</a>
                            </td>
                        </tr>
                        <?php 
                    } 
                } else { ?>
                    <tr>
                        <!-- COLSPAN DIUBAH JADI 4 KARENA KOLOMNYA HANYA 4 -->
                        <td colspan="4" style="text-align:center; color:#999; padding:20px;">Belum ada E-Rapor yang diupload.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../templates/footer_siswa.php'; ?>