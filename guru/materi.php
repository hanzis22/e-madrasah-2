<?php require '../templates/header_guru.php'; ?>
<?php require '../templates/sidebar_guru.php'; ?>
<?php
/** @var mysqli $conn */
?>

<div class="isi-halaman">
    <!-- FORM UPLOAD/EDIT MATERI -->
    <div class="box-putih">
        <!-- JUDUL FORM AKAN BERUBAH DINAMIS OLEH JAVASCRIPT -->
        <h4 style="margin:0 0 15px 0;" id="judulFormMateri">Upload Materi Baru</h4>
        <form method="post" action="" enctype="multipart/form-data">
            <!-- INPUT HIDDEN WAJIB ADA UNTUK LOGIKA EDIT -->
            <input type="hidden" name="id_materi" id="id_materi" value="">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Mapel</label>
                    <select name="id_mapel" id="inputMapel" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Mapel --</option>
                        <?php 
                        $id_guru = $_SESSION['id'];
                        $mapel = mysqli_query($conn, "SELECT DISTINCT mapel.id, mapel.nama_mapel FROM mengajar JOIN mapel ON mengajar.id_mapel = mapel.id WHERE mengajar.id_guru = '$id_guru'");
                        while ($m = mysqli_fetch_assoc($mapel)) {
                            echo "<option value='".$m['id']."'>".$m['nama_mapel']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Kelas</label>
                    <select name="id_kelas" id="inputKelas" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php 
                        $kelas = mysqli_query($conn, "SELECT DISTINCT kelas.id, kelas.nama_kelas FROM mengajar JOIN kelas ON mengajar.id_kelas = kelas.id WHERE mengajar.id_guru = '$id_guru'");
                        while ($k = mysqli_fetch_assoc($kelas)) {
                            echo "<option value='".$k['id']."'>".$k['nama_kelas']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Judul Materi</label>
                    <input type="text" name="judul" id="inputJudul" class="form-control form-control-sm" placeholder="Contoh: Persamaan Kuadrat Bab 3" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Upload File PDF <span id="labelPdf" class="text-muted">(Wajib)</span></label>
                    <input type="file" name="file_pdf" id="inputPdf" class="form-control form-control-sm" accept=".pdf" required>
                    <small class="text-muted">Hanya file berekstensi .pdf</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Link YouTube (Opsional)</label>
                    <input type="text" name="link_yt" id="inputYt" class="form-control form-control-sm" placeholder="https://youtube.com/watch?v=...">
                </div>
                <div class="col-md-12">
                    <button type="submit" name="simpan_materi" id="tombolSimpan" class="btn btn-cari btn-sm">Upload Materi</button>
                </div>
            </div>
        </form>
    </div>

    <!-- TABEL DAFTAR MATERI YANG SUDAH DIUPLOAD -->
    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h5 style="margin:0; font-size: 16px;"><i class="bi bi-clock-fill me-2"></i>Materi Saya</h5>
        </div>
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Judul Materi</th>
                    <th>Mapel / Kelas</th>
                    <th>File / Link</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $data_materi = mysqli_query($conn, "SELECT materi.*, mapel.nama_mapel, kelas.nama_kelas FROM materi JOIN mapel ON materi.id_mapel = mapel.id JOIN kelas ON materi.id_kelas = kelas.id WHERE materi.id_guru='$id_guru' ORDER BY materi.id DESC");
                
                if (mysqli_num_rows($data_materi) > 0) {
                    while ($row = mysqli_fetch_assoc($data_materi)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['judul_materi'], ENT_QUOTES) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_mapel'], ENT_QUOTES) ?> - <?= htmlspecialchars($row['nama_kelas'], ENT_QUOTES) ?></td>
                            <td>
                                <?php if(!empty($row['file_pdf'])): ?>
                                    <a href="../download.php?f=<?= rawurlencode($row['file_pdf']) ?>" target="_blank" class="btn btn-blue btn-sm"><i class="bi bi-file-pdf me-2"></i>Lihat PDF</a>
                                <?php /* H-04 FIX */ ?>
                                <?php endif; ?>
                                <?php if(!empty($row['link_youtube'])): ?>
                                    <a href="<?= $row['link_youtube'] ?>" target="_blank" class="btn btn-red btn-sm" style="margin-left:5px;"><i class="bi bi-youtube me-2"></i>Youtube</a>
                                <?php endif; ?>
                            </td>
                            <!-- KOLOM AKSI DIPERLEBAR, DITAMBAH TOMBOL EDIT -->
                            <td style="text-align:center;">
                                <button onclick='editMateri(<?= (int)$row['id'] ?>, "<?= htmlspecialchars($row['id_mapel'], ENT_QUOTES) ?>", "<?= htmlspecialchars($row['id_kelas'], ENT_QUOTES) ?>", "<?= htmlspecialchars($row['judul_materi'], ENT_QUOTES) ?>", "<?= htmlspecialchars($row['link_youtube'], ENT_QUOTES) ?>")' class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus materi ini?')" class="btn btn-red btn-sm" style="color:red; text-decoration:none; margin-left:5px;"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding: 20px;">Belum ada materi.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JAVASCRIPT UNTUK LOGIKA EDIT -->
<script>
function editMateri(id, idMapel, idKelas, judul, linkYt) {
    // Isi hidden ID
    document.getElementById('id_materi').value = id;
    
    // Isi form dropdown & teks
    document.getElementById('inputMapel').value = idMapel;
    document.getElementById('inputKelas').value = idKelas;
    document.getElementById('inputJudul').value = judul;
    document.getElementById('inputYt').value = linkYt;
    
    // Ubah tampilan form agar terlihat sedang mode "Edit"
    document.getElementById('judulFormMateri').innerText = "Edit Materi";
    document.getElementById('tombolSimpan').innerText = "Update Materi";
    document.getElementById('labelPdf').innerText = "(Kosongkan jika tidak ingin mengganti file PDF)";
    
    // File PDF TIDAK WAJIB saat edit
    document.getElementById('inputPdf').removeAttribute('required');
    
    // Scroll ke atas
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php 
// LOGIKA SIMPAN (GABUNGAN UPLOAD BARU & UPDATE)
if (isset($_POST['simpan_materi'])) {
    $id_guru  = $_SESSION['id'];
    $id_materi = $_POST['id_materi']; // Tangkap ID hidden
    $id_mapel  = $_POST['id_mapel'];
    $id_kelas  = $_POST['id_kelas'];
    $judul     = $_POST['judul'];
    $link_yt   = $_POST['link_yt'];

    if (empty($id_materi)) {
        // JIKA ID KOSONG = PROSES UPLOAD BARU
        $nama_file = $_FILES['file_pdf']['name'];
        $tmp_file  = $_FILES['file_pdf']['tmp_name'];
        $nama_file_baru = time() . "_" . md5($nama_file) . ".pdf";
        move_uploaded_file($tmp_file, "../uploads/" . $nama_file_baru);

        mysqli_query($conn, "INSERT INTO materi (id_guru, id_mapel, id_kelas, judul_materi, file_pdf, link_youtube) VALUES ('$id_guru', '$id_mapel', '$id_kelas', '$judul', '$nama_file_baru', '$link_yt')");
    } else {
        // JIKA ID ADA ISINYA = PROSES UPDATE
        if (!empty($_FILES['file_pdf']['name'])) {
            // Jika guru memilih file PDF baru, hapus file lama, lalu upload yang baru
            $data_lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM materi WHERE id='$id_materi'"));
            if ($data_lama['file_pdf']) {
                unlink("../uploads/" . $data_lama['file_pdf']);
            }
            
            $nama_file = $_FILES['file_pdf']['name'];
            $tmp_file  = $_FILES['file_pdf']['tmp_name'];
            $nama_file_baru = time() . "_" . md5($nama_file) . ".pdf";
            move_uploaded_file($tmp_file, "../uploads/" . $nama_file_baru);
            
            // Update database termasuk ganti nama file PDF baru
            mysqli_query($conn, "UPDATE materi SET id_mapel='$id_mapel', id_kelas='$id_kelas', judul_materi='$judul', file_pdf='$nama_file_baru', link_youtube='$link_yt' WHERE id='$id_materi'");
        } else {
            // Jika guru TIDAK memilih file PDF baru (diisi kosong), update teks saja, file PDF-nya tetap
            mysqli_query($conn, "UPDATE materi SET id_mapel='$id_mapel', id_kelas='$id_kelas', judul_materi='$judul', link_youtube='$link_yt' WHERE id='$id_materi'");
        }
    }
    
    echo "<script>location.replace('materi.php');</script>";
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];  // FIX H-01: cast to int — blocks SQLi on the id
    $data = mysqli_query($conn, "SELECT * FROM materi WHERE id='$id_hapus'");
    $d = mysqli_fetch_assoc($data);
    if ($d['file_pdf']) {
        unlink("../uploads/" . $d['file_pdf']);
    }
    mysqli_query($conn, "DELETE FROM materi WHERE id='$id_hapus'");
    echo "<script>location.replace('materi.php');</script>";
}
?>

<?php require '../templates/footer.php'; ?>