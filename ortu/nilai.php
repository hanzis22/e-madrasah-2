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

    $nilai = mysqli_query($conn, "SELECT p.*, t.judul_tugas, mp.nama_mapel, u.nama as nama_anak 
                                   FROM pengumpulan_tugas p
                                   JOIN tugas t ON p.id_tugas = t.id
                                   JOIN mapel mp ON t.id_mapel = mp.id
                                   JOIN users u ON p.id_siswa = u.id
                                   WHERE p.id_siswa IN ($list_ids) AND p.nilai IS NOT NULL
                                   ORDER BY p.id DESC");
}
?>

<div class="isi-halaman">

    <div style="margin-bottom:20px;">
        <h4 style="margin:0 0 5px 0;">Nilai Anak</h4>
        <p style="margin:0; color:#666; font-size:14px;">
            Lihat hasil penilaian tugas yang telah diberikan oleh guru.
        </p>
    </div>


    <?php if (empty($ids_anak)): ?>

        <div class="box-putih" style="text-align:center; padding:50px;">
            <h5 style="color:#999;">Data anak belum terhubung dengan akun Anda.</h5>
            <p style="color:#999;">Silakan hubungi Administrator.</p>
        </div>


    <?php elseif (mysqli_num_rows($nilai) > 0): ?>


    <div class="box-putih" style="padding:0; overflow:hidden;">

        <table class="table table-bordered table-hover" style="margin:0; font-size:14px;">

            <thead style="background:#f8f9fa;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Anak</th>
                    <th>Tugas (Mapel)</th>
                    <th width="130px">Status Waktu</th>
                    <th width="100px" style="text-align:center;">Nilai</th>
                </tr>
            </thead>


            <tbody>

            <?php

            $no = 1;
            $total_nilai = 0;
            $jumlah_dinilai = 0;


            while ($row = mysqli_fetch_assoc($nilai)) {


                $total_nilai += $row['nilai'];
                $jumlah_dinilai++;

                // LOGIKA STATUS (sama seperti halaman siswa)
                if ($row['status_keterlambatan'] == 'Terlambat') {

                    $status_text = "<span style='color:#e67e22; font-size: 13px; font-weight:400;'>ⓘ Terlambat</span>";

                } else {

                    $status_text = "<span style='color:#27ae60; font-size: 13px; font-weight:400;'>✔ Tepat Waktu</span>";

                }

            ?>

                <tr>

                    <td style="text-align:center;">
                        <?= $no++ ?>
                    </td>


                    <td>
                        <?= $row['nama_anak'] ?>
                    </td>


                    <td>
                        <?= $row['judul_tugas'] ?>
                        <br>
                        <small class="text-muted">
                            (<?= $row['nama_mapel'] ?>)
                        </small>
                    </td>


                    <td>
                        <?= $status_text ?>
                    </td>


                    <td style="text-align:center;">
                        <strong style="font-size:16px;">
                            <?= $row['nilai'] ?>
                        </strong>
                    </td>


                </tr>


            <?php } ?>


            <?php

            $rata_rata = round($total_nilai / $jumlah_dinilai);

            ?>


            <tr style="background:#f8f9fa; border-top:2px solid #dee2e6;">

                <td colspan="4" 
                    style="text-align:right; font-weight:600; color:#333;">
                    Rata-rata Nilai Anak Anda
                </td>


                <td style="text-align:center;">
                    <strong style="font-size:18px; color:#2e7d32;">
                        <?= $rata_rata ?>
                    </strong>
                </td>

            </tr>


            </tbody>


        </table>

    </div>


    <?php else: ?>


        <div class="box-putih" style="text-align:center; padding:50px;">
            <h5 style="color:#999;">
                Belum ada nilai yang diberikan guru untuk anak Anda.
            </h5>
        </div>


    <?php endif; ?>


</div>


<?php require '../templates/footer_ortu.php'; ?>