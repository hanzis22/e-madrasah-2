<?php require '../templates/header_ortu.php'; ?>
<?php require '../templates/sidebar_ortu.php'; ?>
<?php
/** @var mysqli $conn */
 $id_ortu = $_SESSION['id'];

// Ambil ID semua anak yang diwali oleh orang tua ini
 $data_anak = mysqli_query($conn, "SELECT id_siswa FROM ortu_siswa WHERE id_ortu = '$id_ortu'");
 $ids_anak = [];
while($a = mysqli_fetch_assoc($data_anak)) {
    $ids_anak[] = $a['id_siswa'];
}

// Jika orang tua sudah memiliki anak yang dihubungkan
if (!empty($ids_anak)) {
    $list_ids = implode(',', $ids_anak);
    
    // Query mengambil data rapor berdasarkan anak-anak tersebut
    $rapor = mysqli_query($conn, "SELECT r.*, u.nama as nama_anak, k.nama_kelas 
                                FROM rapor r
                                JOIN users u ON r.id_siswa = u.id
                                LEFT JOIN kelas k ON u.id_kelas = k.id
                                WHERE r.id_siswa IN ($list_ids)
                                ORDER BY r.id DESC");
}
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">E-Rapor Anak</h4>
        <p style="margin:0; color:#666; font-size:14px;">Download dokumen rapor semester yang telah diupload oleh guru.</p>
    </div>

    <?php if (empty($ids_anak)): ?>
        <div class="box-putih" style="text-align: center; padding: 50px;">
            <h5 style="color:#999;">Data anak belum terhubung dengan akun Anda.</h5>
            <p style="color:#999;">Silakan hubungi Administrator.</p>
        </div>
    <?php elseif (mysqli_num_rows($rapor) > 0): ?>
        <div class="box-putih" style="padding: 0; overflow: hidden;">
            <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th width="50px" style="text-align:center;">No</th>
                        <th>Nama Anak</th>
                        <th>Kelas</th>
                        <th>Periode / Semester</th>
                        <th width="150px">Tanggal Upload</th>
                        <th width="120px" style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($rapor)) { 
                        $tgl_upload = date('d M Y', strtotime($row['tanggal_upload']));
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $row['nama_anak'] ?></td>
                            <td><?= $row['nama_kelas'] ?: '-' ?></td>
                            <td><?= $row['semester'] ?></td>
                            <td><?= $tgl_upload ?></td>
                            <td style="text-align:center;">
                                <a href="../uploads/<?= $row['file_pdf'] ?>" target="_blank" class="btn btn-cari btn-sm"><i class="bi bi-download me-2"></i>Download</a>
                            </td>
                        </tr>
                        <?php 
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="box-putih" style="text-align: center; padding: 50px;">
            <h5 style="color:#999;">Belum ada E-Rapor yang diupload oleh guru.</h5>
            <p style="color:#999;">Hubungi wali kelas atau guru mata pelajaran terkait.</p>
        </div>
    <?php endif; ?>
</div>

<?php require '../templates/footer_ortu.php'; ?>