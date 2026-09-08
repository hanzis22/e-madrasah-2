<?php require '../templates/header_guru.php'; ?>
<?php require '../templates/sidebar_guru.php'; ?>
<?php
/** @var mysqli $conn */
?>

<div class="isi-halaman">
    <div class="box-putih">
        <h4 style="margin:0 0 15px 0;">Penilaian Tugas</h4>
        <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
            <label style="margin:0;">Pilih Tugas:</label>
            <select name="id_tugas" class="form-select form-select-sm" style="max-width: 400px;" onchange="this.form.submit()">
                <option value="">-- Pilih Tugas --</option>
                <?php 
                $id_guru = $_SESSION['id'];
                $tugas = mysqli_query($conn, "SELECT * FROM tugas WHERE id_guru='$id_guru' ORDER BY id DESC");
                while ($t = mysqli_fetch_assoc($tugas)) {
                    $selected = (isset($_GET['id_tugas']) && $_GET['id_tugas'] == $t['id']) ? 'selected' : '';
                    echo "<option value='".$t['id']."' $selected>".$t['judul_tugas']."</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <?php 
    // Jika dropdown tugas sudah dipilih
    if (isset($_GET['id_tugas']) && !empty($_GET['id_tugas'])) {
        $id_tugas = $_GET['id_tugas'];
        
        // Ambil info tugas (untuk mengetahui kelas mana)
        $info_tugas = mysqli_query($conn, "SELECT * FROM tugas WHERE id='$id_tugas'");
        $dt = mysqli_fetch_assoc($info_tugas);
        $id_kelas = $dt['id_kelas'];

        // Ambil semua siswa di kelas tersebut
        $siswa = mysqli_query($conn, "SELECT * FROM users WHERE id_kelas='$id_kelas' AND role='siswa' ORDER BY nama ASC");
    ?>
    
    <!-- FORM PENILAIAN -->
    <form method="post" action="">
        <input type="hidden" name="id_tugas" value="<?= $id_tugas ?>">
        
        <div class="box-putih" style="padding: 0; overflow: hidden;">
            <table class="table table-bordered table-sm" style="margin:0; font-size:13px;">
                <thead style="background-color: #f9f9f9;">
                    <tr>
                        <th width="50px" style="text-align:center;">No</th>
                        <th>Nama Siswa</th>
                        <th width="180px" style="text-align:center;">Status</th>
                        <th width="100px" style="text-align:center;">File Jawaban</th>
                        <th width="120px" style="text-align:center;">Input Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($s = mysqli_fetch_assoc($siswa)) {
                        // Cek apakah siswa ini sudah pernah mengumpulkan tugas ini
                        $cek_kumpul = mysqli_query($conn, "SELECT * FROM pengumpulan_tugas WHERE id_tugas='$id_tugas' AND id_siswa='".$s['id']."'");
                        $data_kumpul = mysqli_fetch_assoc($cek_kumpul);
                        
                        // Jika sudah mengumpul, ambil nilainya (kalau belum, nilainya kosong)
                        $nilai_lama = $data_kumpul ? $data_kumpul['nilai'] : "";
                        
                        // Tentukan status text
                        // 1. Logika Status (Hanya Font Warna Tanpa Background)
                        if ($data_kumpul) {
                            if ($data_kumpul['status_keterlambatan'] == 'Terlambat') {
                                $status_text = "<span style='color: #e67e22; font-size:13px; font-weight: 400;'>ⓘ Terlambat</span>";
                            } else {
                                $status_text = "<span style='color: #27ae60; font-size:13px; font-weight: 400;'>✔ Tepat Waktu</span>";
                            }
                        } else {
                            $status_text = "<span style='color: #e74c3c; font-size:13px; font-weight: 400;'>⚠︎ Belum Mengumpulkan</span>";
                        }

                        // 2. Logika File (Dipisah)
                        $file_text = "-";
                        if ($data_kumpul && !empty($data_kumpul['file_jawaban'])) {
                            $file_text = "<a href='../download.php?f=tugas/".rawurlencode($data_kumpul['file_jawaban'])."' target='_blank' class='btn btn-blue btn-sm' style='font-size:11px; padding: 3px 8px;'><i class='bi bi-eye'></i></a>";
                        }
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $s['nama'] ?></td>
                            <td style="text-align:left;"><?= $status_text ?></td>
                            <td style="text-align:center;"><?= $file_text ?></td>
                            <td style="text-align:center;">
                                <input type="number" name="nilai[<?= $s['id'] ?>]" value="<?= $nilai_lama ?>" class="form-control form-control-sm text-center" placeholder="0" min="0" max="100">
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="box-putih" style="text-align: right;">
            <button type="submit" name="simpan_nilai" class="btn btn-cari btn-sm"><i class="bi bi-cloud-upload me-2"></i></i>Simpan Nilai</button>
        </div>
    </form>

    <?php } else { ?>
        <div class="box-putih" style="text-align:center; color:#999; padding: 30px;">
            Silakan pilih tugas di dropdown atas untuk melihat daftar siswa dan memberikan nilai.
        </div>
    <?php } ?>
</div>

<?php 
// LOGIKA SIMPAN NILAI SEKALIGUS
if (isset($_POST['simpan_nilai'])) {
    $id_tugas = $_POST['id_tugas'];
    $nilai_array = $_POST['nilai']; // Ini berupa array (key= id_siswa, value= angka nilai)

    foreach ($nilai_array as $id_siswa => $nilai_angka) {
        // Cek dulu, apakah siswa ini sudah ada datanya di tabel pengumpulan?
        $cek = mysqli_query($conn, "SELECT id FROM pengumpulan_tugas WHERE id_tugas='$id_tugas' AND id_siswa='$id_siswa'");
        
        if (mysqli_num_rows($cek) > 0) {
            // Jika SUDAH ADA (sudah kumpul tugas), maka UPDATE nilainya saja
            $data_lama = mysqli_fetch_assoc($cek);
            $id_kumpul = $data_lama['id'];
            
            if ($nilai_angka != "") { // Hanya update kalau input nilainya tidak kosong
                mysqli_query($conn, "UPDATE pengumpulan_tugas SET nilai='$nilai_angka' WHERE id='$id_kumpul'");
            }
        } else {
            // Jika BELUM ADA (belum kumpul tapi guru tetap mau kasih nilai misalnya 0)
            if ($nilai_angka != "") {
                mysqli_query($conn, "INSERT INTO pengumpulan_tugas (id_tugas, id_siswa, nilai) VALUES ('$id_tugas', '$id_siswa', '$nilai_angka')");
            }
        }
    }
    
    // Kembalikan ke halaman yang sama dengan parameter tugas yang dipilih tadi
    echo "<script>location.replace('penilaian.php?id_tugas=$id_tugas');</script>";
}
?>

<?php require '../templates/footer.php'; ?>