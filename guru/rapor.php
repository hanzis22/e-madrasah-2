<?php require '../templates/header_guru.php'; ?>
<?php require '../templates/sidebar_guru.php'; ?>
<?php
/** @var mysqli $conn */
?>

<div class="isi-halaman">
    <!-- FORM UPLOAD RAPOR -->
    <div class="box-putih">
        <h4 style="margin:0 0 15px 0;">Upload E-Rapor Siswa</h4>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pilih Kelas</label>
                    <select name="id_kelas" class="form-select form-select-sm" id="pilihKelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php 
                        $id_guru = $_SESSION['id'];
                        $kelas = mysqli_query($conn, "SELECT DISTINCT kelas.id, kelas.nama_kelas FROM mengajar JOIN kelas ON mengajar.id_kelas = kelas.id WHERE mengajar.id_guru = '$id_guru'");
                        while ($k = mysqli_fetch_assoc($kelas)) {
                            echo "<option value='".$k['id']."'>".$k['nama_kelas']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pilih Siswa</label>
                    <select name="id_siswa" class="form-select form-select-sm" id="pilihSiswa" required>
                        <option value="">-- Pilih Siswa Terlebih Dahulu --</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Periode / Semester</label>
                    <select name="semester" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Semester --</option>
                        <option value="Ganjil 2024/2025">Ganjil 2024/2025</option>
                        <option value="Genap 2024/2025">Genap 2024/2025</option>
                        <option value="Ganjil 2025/2026">Ganjil 2025/2026 </option>
                        <option value="Genap 2025/2026">Genap 2025/2026</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">File Rapor (PDF)</label>
                    <input type="file" name="file_pdf" class="form-control form-control-sm" accept=".pdf" required>
                </div>
                <div class="col-md-12">
                    <button type="submit" name="upload" class="btn btn-cari btn-sm">Upload E-Rapor</button>
                </div>
            </div>
        </form>
    </div>

    <!-- TABEL RIWAYAT RAPOR -->
    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h5 style="margin:0; font-size: 16px;"><i class="bi bi-clock-fill me-2"></i>Riwayat Upload Rapor</h5>
        </div>
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th> <!-- KOLOM KELAS SUDAH DITAMBAHKAN DI SINI -->
                    <th>Semester</th>
                    <th>File Rapor</th>
                    <th width="100px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                // Query sudah ditambahkan JOIN ke tabel kelas
                $data_rapor = mysqli_query($conn, "SELECT rapor.*, users.nama, kelas.nama_kelas FROM rapor JOIN users ON rapor.id_siswa = users.id JOIN kelas ON users.id_kelas = kelas.id ORDER BY rapor.id DESC");
                
                if (mysqli_num_rows($data_rapor) > 0) {
                    while ($row = mysqli_fetch_assoc($data_rapor)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['nama_kelas'] ?></td> <!-- ISI KOLOM KELAS -->
                            <td><?= $row['semester'] ?></td>
                            <td>
                                <a href="../uploads/<?= $row['file_pdf'] ?>" target="_blank" class="btn btn-blue btn-sm"><i class="bi bi-file-pdf me-2"></i>Lihat PDF</a>
                            </td>
                            <td style="text-align:center;">
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus rapor ini?')" class="btn btn-red btn-sm" style="color:red; text-decoration:none;"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <!-- colspan diubah jadi 6 karena ada 6 kolom sekarang -->
                    <tr>
                        <td colspan="6" style="text-align:center; color:#999; padding: 20px;">Belum ada rapor diupload.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script AJAX untuk Dropdown Bertingkat -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#pilihKelas").change(function() {
            var id_kelas = $(this).val();
            $("#pilihSiswa").html('<option value="">-- Mengambil Data Siswa --</option>');
            
            if (id_kelas != "") {
                $.ajax({
                    url: "get_siswa.php",
                    type: "POST",
                    data: {id_kelas: id_kelas},
                    success: function(response) {
                        $("#pilihSiswa").html(response);
                    }
                });
            } else {
                $("#pilihSiswa").html('<option value="">-- Pilih Siswa Terlebih Dahulu --</option>');
            }
        });
    });
</script>

<?php 
// LOGIKA UPLOAD
if (isset($_POST['upload'])) {
    $id_siswa = $_POST['id_siswa'];
    $semester = $_POST['semester'];

    $nama_file = $_FILES['file_pdf']['name'];
    $tmp_file  = $_FILES['file_pdf']['tmp_name'];
    $nama_file_baru = time() . "_rapor_" . md5($nama_file) . ".pdf";
    
    move_uploaded_file($tmp_file, "../uploads/" . $nama_file_baru);

    mysqli_query($conn, "INSERT INTO rapor (id_siswa, semester, file_pdf) VALUES ('$id_siswa', '$semester', '$nama_file_baru')");
    echo "<script>location.replace('rapor.php');</script>";
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $data = mysqli_query($conn, "SELECT * FROM rapor WHERE id='$id_hapus'");
    $d = mysqli_fetch_assoc($data);
    if ($d['file_pdf']) {
        unlink("../uploads/" . $d['file_pdf']);
    }
    mysqli_query($conn, "DELETE FROM rapor WHERE id='$id_hapus'");
    echo "<script>location.replace('rapor.php');</script>";
}
?>

<?php require '../templates/footer.php'; ?>