<?php require '../templates/header_siswa.php'; ?>
<?php require '../templates/sidebar_siswa.php'; ?>
<?php
/** @var mysqli $conn */
 $id_siswa_login = $_SESSION['id'];
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Nilai Tugas Saya</h4>
        <p style="margin:0; color:#666; font-size:14px;">Berikut adalah nilai yang sudah diberikan oleh guru.</p>
    </div>

    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Judul Tugas</th>
                    <th width="300px">Mata Pelajaran</th>
                    <th width="150px">Status Waktu</th>
                    <th width="100px" style="text-align:center;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $total_nilai = 0;
                $jumlah_dinilai = 0;
                
                // QUERY SUDAH DIPERBAIKI: Mengurutkan berdasarkan ID, tanpa tanggal_kumpul
                $data = mysqli_query($conn, "SELECT p.*, t.judul_tugas, mp.nama_mapel 
                                            FROM pengumpulan_tugas p
                                            JOIN tugas t ON p.id_tugas = t.id
                                            JOIN mapel mp ON t.id_mapel = mp.id
                                            WHERE p.id_siswa = '$id_siswa_login' AND p.nilai IS NOT NULL
                                            ORDER BY p.id DESC");
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) {
                        $total_nilai += $row['nilai'];
                        $jumlah_dinilai++;
                        
                        // LOGIKA STATUS (Hanya Font Warna)
                        if ($row['status_keterlambatan'] == 'Terlambat') {
                            $status_text = "<span style='color: #ff7d03; font-size: 13px; font-weight: 400;'>ⓘ Terlambat</span>";
                        } else {
                            $status_text = "<span style='color: #27ae60; font-size: 13px; font-weight: 400;'>✔ Tepat Waktu</span>";
                        }
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['judul_tugas'], ENT_QUOTES) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_mapel'], ENT_QUOTES) ?></td>
                            <td style="text-align:left;"><?= $status_text ?></td>
                            <td style="text-align:center;"><strong style="font-size: 16px;"><?= $row['nilai'] ?></strong></td>
                        </tr>
                        <?php 
                    } 
                    
                    $rata_rata = round($total_nilai / $jumlah_dinilai);
                    ?>
                    <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
                        <td colspan="4" style="text-align: right; font-weight: 600; color: #333;">Rata-rata Nilai</td>
                        <td style="text-align:center;">
                            <strong style="font-size: 18px; color: #2e7d32;"><?= $rata_rata ?></strong>
                        </td>
                    </tr>
                    <?php
                } else { ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding:20px;">Belum ada nilai yang diberikan guru.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../templates/footer_siswa.php'; ?>