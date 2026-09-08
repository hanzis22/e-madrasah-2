<?php require '../templates/header_guru.php'; ?>
<?php require '../templates/sidebar_guru.php'; ?>
<?php
/** @var mysqli $conn */
?>

<div class="isi-halaman">
    <!-- FORM BUAT TUGAS -->
    <div class="box-putih">
        <h4 style="margin:0 0 15px 0;" id="judulFormTugas">Bagikan Tugas Baru</h4>
        
        <!-- TAMBAHKAN ENCTYPE INI -->
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="id_tugas" id="id_tugas" value="">
            <input type="hidden" name="file_lama" id="file_lama" value="">
            <div class="row">
                <div class="col-md-4 mb-3">
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
                <div class="col-md-4 mb-3">
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
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tanggal Deadline</label>
                    <input type="date" name="deadline_tgl" id="inputDeadlineTgl" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Jam Deadline</label>
                    <input type="time" name="deadline_jam" id="inputDeadlineJam" class="form-control form-control-sm" value="23:59" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Judul Tugas</label>
                    <input type="text" name="judul" id="inputJudul" class="form-control form-control-sm" placeholder="Contoh: Tugas Bab 3 Persamaan Kuadrat" required>
                </div>
                
                <!-- ========== TAMBAHAN INPUT FILE (DIANTARA JUDUL & DESKRIPSI) ========== -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lampiran (Gambar/PDF) <small class="text-muted">- Opsional, maks 20MB</small></label>
                    <input type="file" name="file_tugas" id="inputFile" class="form-control form-control-sm" accept="image/*,.pdf">
                    <div id="previewFileLama" style="margin-top:8px; display:none;">
                        <small class="text-muted">File saat ini: </small>
                        <a href="#" id="linkFileLama" target="_blank" style="font-size:13px;">Lihat File</a>
                        <button type="button" onclick="hapusFileLama()" class="btn btn-link btn-sm text-danger p-0" style="font-size:12px; text-decoration:underline;">Hapus</button>
                    </div>
                </div>
                <!-- ========================================================================= -->
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi Tugas</label>
                    <textarea name="deskripsi" id="inputDeskripsi" class="form-control form-control-sm" rows="4" placeholder="Jelaskan apa yang harus dikerjakan siswa..." required></textarea>
                </div>
                <div class="col-md-12">
                    <button type="submit" name="bagikan" class="btn btn-cari btn-sm">Bagikan Tugas</button>
                    <button type="button" onclick="resetForm()" class="btn btn-outline-secondary btn-sm">Batal</button>
                </div>
            </div>
        </form>
    </div>

    
    <!-- TABEL DAFTAR TUGAS -->
    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h5 style="margin:0; font-size: 16px;"><i class="bi bi-clock-fill me-2"></i>Daftar Tugas Saya</h5>
        </div>
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Judul Tugas</th>
                    <th>Mapel / Kelas</th>
                    <th>Deadline</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $data_tugas = mysqli_query($conn, "SELECT tugas.*, mapel.nama_mapel, kelas.nama_kelas FROM tugas JOIN mapel ON tugas.id_mapel = mapel.id JOIN kelas ON tugas.id_kelas = kelas.id WHERE tugas.id_guru='$id_guru' ORDER BY tugas.id DESC");
                
                if (mysqli_num_rows($data_tugas) > 0) {
                    while ($row = mysqli_fetch_assoc($data_tugas)) { 
                        $deadline_tgl = date('d F Y, H:i', strtotime($row['deadline']));
                        
                        // Link pratinjau lampiran jika ada file
                        $link_lampiran = '';
                        if (!empty($row['file_tugas'])) {
                            $ext = strtolower(pathinfo($row['file_tugas'], PATHINFO_EXTENSION));
                            $path_file = '../uploads/tugas/' . $row['file_tugas'];
                            
                            if ($ext == 'pdf') {
                                $link_lampiran = '<br><a href="'.$path_file.'" target="_blank" style="font-size:12px; color:#0000FF; text-decoration:none;"><i class="bi bi-file-pdf me-2"></i>Lihat Lampiran PDF</a>';
                            } else {
                                $link_lampiran = '<br><a href="'.$path_file.'" target="_blank" style="font-size:12px; color:#1565c0; text-decoration:none;"><i class="bi bi-file-earmark-image me-2"></i>Lihat Lampiran Gambar</a>';
                            }
                        }
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td>
                                <b><?= $row['judul_tugas'] ?>
                                <br><small class="text-muted"><?= substr($row['deskripsi'], 0, 80) ?>...</small>
                                </b><?= $link_lampiran ?>
                            </td>
                            <td><?= $row['nama_mapel'] ?> - <?= $row['nama_kelas'] ?></td>
                            <td><?= $deadline_tgl ?></td>
                            <td style="text-align:center;">
                                <button onclick="editTugas(<?= $row['id'] ?>, '<?= $row['id_mapel'] ?>', '<?= $row['id_kelas'] ?>', '<?= $row['deadline'] ?>', '<?= addslashes($row['judul_tugas']) ?>', '<?= addslashes($row['deskripsi']) ?>', '<?= $row['file_tugas'] ?>')" class="btn btn-sage btn-sm"> <i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus tugas ini?')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding: 20px;">Belum ada tugas.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
// LOGIKA SIMPAN TUGAS
if (isset($_POST['bagikan'])) {
    $id_tugas  = $_POST['id_tugas'];
    $file_lama = $_POST['file_lama']; // Untuk keep file lama saat edit
    $id_mapel  = $_POST['id_mapel'];
    $id_kelas  = $_POST['id_kelas'];
    $judul     = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $deadline  = $_POST['deadline_tgl'] . ' ' . $_POST['deadline_jam'] . ':00';

    // ========== LOGIKA UPLOAD FILE ==========
    $nama_file = $file_lama; // Default pakai file lama (untuk edit)
    
    if (!empty($_FILES['file_tugas']['name'])) {
        $file       = $_FILES['file_tugas'];
        $nama_asli  = $file['name'];
        $ukuran     = $file['size'];
        $tmp_name   = $file['tmp_name'];
        $error      = $file['error'];
        
        // Ambil ekstensi file
        $ekstensi = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        
        // Ekstensi yang diizinkan
        $ekstensi_boleh = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        
        // Validasi ekstensi
        if (in_array($ekstensi, $ekstensi_boleh)) {
            // Validasi ukuran (maks 20MB = 20971520 byte)
            if ($ukuran <= 20971520) {
                // Buat nama file unik biar tidak tabrakan
                $nama_file = uniqid() . '_' . time() . '.' . $ekstensi;
                $folder_tujuan = '../uploads/tugas/';
                
                // Pindahkan file ke folder tujuan
                if (move_uploaded_file($tmp_name, $folder_tujuan . $nama_file)) {
                    // Jika ini edit dan ada file lama yang diganti, hapus file lama
                    if (!empty($id_tugas) && !empty($file_lama) && file_exists($folder_tujuan . $file_lama)) {
                        unlink($folder_tujuan . $file_lama);
                    }
                } else {
                    echo "<script>alert('Gagal mengupload file!'); history.back();</script>";
                    exit;
                }
            } else {
                echo "<script>alert('Ukuran file maksimal 20MB!'); history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Hanya file gambar (JPG, PNG, JPEG) dan PDF yang diizinkan!'); history.back();</script>";
            exit;
        }
    }
    // ========================================

    if (empty($id_tugas)) {
        // TUGAS BARU
        mysqli_query($conn, "INSERT INTO tugas (id_guru, id_mapel, id_kelas, judul_tugas, deskripsi, file_tugas, deadline) VALUES ('$id_guru', '$id_mapel', '$id_kelas', '$judul', '$deskripsi', '$nama_file', '$deadline')");
    } else {
        // UPDATE TUGAS
        mysqli_query($conn, "UPDATE tugas SET id_mapel='$id_mapel', id_kelas='$id_kelas', judul_tugas='$judul', deskripsi='$deskripsi', file_tugas='$nama_file', deadline='$deadline' WHERE id='$id_tugas'");
    }
    
    echo "<script>location.replace('tugas.php');</script>";
}

// LOGIKA HAPUS TUGAS
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    
    // 1. Ambil nama file tugas dulu (untuk dihapus dari folder)
    $data_hapus = mysqli_query($conn, "SELECT file_tugas FROM tugas WHERE id='$id_hapus'");
    $row_hapus = mysqli_fetch_assoc($data_hapus);
    if (!empty($row_hapus['file_tugas']) && file_exists('../uploads/tugas/' . $row_hapus['file_tugas'])) {
        unlink('../uploads/tugas/' . $row_hapus['file_tugas']);
    }
    
    // 2. Hapus jawaban siswa + file jawaban jika ada
    $jawaban = mysqli_query($conn, "SELECT file_jawaban FROM pengumpulan_tugas WHERE id_tugas='$id_hapus'");
    while ($j = mysqli_fetch_assoc($jawaban)) {
        if (!empty($j['file_jawaban']) && file_exists('../uploads/tugas/' . $j['file_jawaban'])) {
            unlink('../uploads/tugas/' . $j['file_jawaban']);
        }
    }
    mysqli_query($conn, "DELETE FROM pengumpulan_tugas WHERE id_tugas='$id_hapus'");
    
    // 3. Hapus data tugas
    mysqli_query($conn, "DELETE FROM tugas WHERE id='$id_hapus'");
    
    echo "<script>location.replace('tugas.php');</script>";
}
?>

<script>
function editTugas(id, idMapel, idKelas, deadline, judul, deskripsi, fileTugas) {
    document.getElementById('id_tugas').value = id;
    document.getElementById('inputMapel').value = idMapel;
    document.getElementById('inputKelas').value = idKelas;
    
    var pecahDeadline = deadline.split(' ');
    document.getElementById('inputDeadlineTgl').value = pecahDeadline[0];
    document.getElementById('inputDeadlineJam').value = pecahDeadline[1].substring(0, 5);
    
    document.getElementById('inputJudul').value = judul;
    document.getElementById('inputDeskripsi').value = deskripsi;
    
    // Handle file lama
    document.getElementById('file_lama').value = fileTugas;
    document.getElementById('inputFile').value = ''; // Kosongkan input file
    
    if (fileTugas) {
        document.getElementById('previewFileLama').style.display = 'block';
        document.getElementById('linkFileLama').href = '../uploads/tugas/' + fileTugas;
        document.getElementById('linkFileLama').textContent = fileTugas;
    } else {
        document.getElementById('previewFileLama').style.display = 'none';
    }
    
    document.getElementById('judulFormTugas').innerText = "Edit Tugas";
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hapusFileLama() {
    document.getElementById('file_lama').value = '';
    document.getElementById('previewFileLama').style.display = 'none';
}

function resetForm() {
    document.getElementById('id_tugas').value = '';
    document.getElementById('file_lama').value = '';
    document.getElementById('inputMapel').value = '';
    document.getElementById('inputKelas').value = '';
    document.getElementById('inputDeadlineTgl').value = '';
    document.getElementById('inputDeadlineJam').value = '23:59';
    document.getElementById('inputJudul').value = '';
    document.getElementById('inputDeskripsi').value = '';
    document.getElementById('inputFile').value = '';
    document.getElementById('previewFileLama').style.display = 'none';
    document.getElementById('judulFormTugas').innerText = "Bagikan Tugas Baru";
}
</script>

<?php require '../templates/footer.php'; ?>