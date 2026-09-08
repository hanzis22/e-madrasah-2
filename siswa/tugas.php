<?php require '../templates/header_siswa.php'; ?>
<?php require '../templates/sidebar_siswa.php'; ?>
<?php
/** @var mysqli $conn */
 $id_siswa_login = $_SESSION['id'];
 $siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id_siswa_login'"));

// ==========================================
// 1. LOGIKA HAPUS PENGUMPULAN
// ==========================================
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    $cek = mysqli_query($conn, "SELECT * FROM pengumpulan_tugas WHERE id_tugas='$id_hapus' AND id_siswa='$id_siswa_login'");
    if (mysqli_num_rows($cek) > 0) {
        $data_lama = mysqli_fetch_assoc($cek);
        if (!empty($data_lama['file_jawaban']) && file_exists('../uploads/tugas/' . $data_lama['file_jawaban'])) {
            unlink('../uploads/tugas/' . $data_lama['file_jawaban']);
        }
        mysqli_query($conn, "DELETE FROM pengumpulan_tugas WHERE id_tugas='$id_hapus' AND id_siswa='$id_siswa_login'");
        echo "<script>alert('Pengumpulan tugas berhasil dihapus.'); location.replace('tugas.php');</script>";
    } else {
        echo "<script>alert('Data tidak ditemukan.'); location.replace('tugas.php');</script>";
    }
    exit();
}

// ==========================================
// 2. LOGIKA UPLOAD BARU & EDIT FILE
// ==========================================
if (isset($_POST['kumpulan'])) {
    $id_tugas = $_POST['id_tugas'];
    $aksi_type = $_POST['aksi_type']; 
    
    if ($aksi_type == 'edit') {
        // LOGIKA EDIT FILE
        $cek = mysqli_query($conn, "SELECT * FROM pengumpulan_tugas WHERE id_tugas='$id_tugas' AND id_siswa='$id_siswa_login'");
        if (mysqli_num_rows($cek) == 0) {
            echo "<script>alert('Data tidak ditemukan.'); history.back();</script>";
            exit();
        }
        
        if (!empty($_FILES['file_jawaban']['name'])) {
            $data_lama = mysqli_fetch_assoc($cek);
            if (!empty($data_lama['file_jawaban']) && file_exists('../uploads/tugas/' . $data_lama['file_jawaban'])) {
                unlink('../uploads/tugas/' . $data_lama['file_jawaban']);
            }
            $nama_file = $_FILES['file_jawaban']['name'];
            $tmp_file = $_FILES['file_jawaban']['tmp_name'];
            $nama_baru = 'tugas_' . time() . '_' . rand(1000, 9999) . '.' . pathinfo($nama_file, PATHINFO_EXTENSION);
            move_uploaded_file($tmp_file, '../uploads/tugas/' . $nama_baru);
            mysqli_query($conn, "UPDATE pengumpulan_tugas SET file_jawaban='$nama_baru' WHERE id_tugas='$id_tugas' AND id_siswa='$id_siswa_login'");
        } else {
            echo "<script>alert('Pilih file baru terlebih dahulu untuk mengganti.'); history.back();</script>";
            exit();
        }
        
    } else {
        // LOGIKA UPLOAD BARU
        $cek = mysqli_query($conn, "SELECT * FROM pengumpulan_tugas WHERE id_tugas='$id_tugas' AND id_siswa='$id_siswa_login'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Anda sudah mengumpulkan tugas ini! Gunakan tombol Edit jika ingin mengganti file.'); history.back();</script>";
            exit();
        }
        
        $data_tugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT deadline FROM tugas WHERE id='$id_tugas'"));
        $status_keterlambatan = (time() > strtotime($data_tugas['deadline'])) ? 'Terlambat' : 'Tepat Waktu';
        
        if (!empty($_FILES['file_jawaban']['name'])) {
            $nama_file = $_FILES['file_jawaban']['name'];
            $tmp_file = $_FILES['file_jawaban']['tmp_name'];
            $nama_baru = 'tugas_' . time() . '_' . rand(1000, 9999) . '.' . pathinfo($nama_file, PATHINFO_EXTENSION);
            move_uploaded_file($tmp_file, '../uploads/tugas/' . $nama_baru);
            
            mysqli_query($conn, "INSERT INTO pengumpulan_tugas (id_tugas, id_siswa, file_jawaban, status_keterlambatan) 
                                 VALUES ('$id_tugas', '$id_siswa_login', '$nama_baru', '$status_keterlambatan')");
        } else {
            echo "<script>alert('Anda harus memilih file jawaban!'); history.back();</script>";
            exit();
        }
    }
    
    echo "<script>alert('Berhasil!'); location.replace('tugas.php');</script>";
}
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Daftar Tugas</h4>
        <p style="margin:0; color:#666; font-size:14px;">Kerjakan dan kumpulkan tugas sebelum deadline.</p>
    </div>

    <?php if (empty($siswa['id_kelas'])): ?>
        <div class="box-putih" style="text-align:center; padding: 50px;">
            <h5 style="color:#999;">Anda belum ditugaskan ke kelas manapun.</h5>
        </div>
    <?php else: ?>
        <div class="box-putih" style="padding: 0; overflow: hidden;">
            <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th width="50px" style="text-align:center;">No</th>
                        <th>Judul Tugas</th>
                        <th width="150px">Deadline</th>
                        <th width="170px" style="text-align:center;">Status</th>
                        <th width="90px" style="text-align:center;">Nilai</th>
                        <th width="100px" style="text-align:center;">File</th>
                        <th width="150px" style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $data = mysqli_query($conn, "SELECT t.*, mp.nama_mapel, 
                                                (SELECT COUNT(*) FROM pengumpulan_tugas WHERE id_tugas = t.id AND id_siswa = '$id_siswa_login') as sudah_kumpul,
                                                (SELECT status_keterlambatan FROM pengumpulan_tugas WHERE id_tugas = t.id AND id_siswa = '$id_siswa_login') as status_keterlambatan,
                                                (SELECT nilai FROM pengumpulan_tugas WHERE id_tugas = t.id AND id_siswa = '$id_siswa_login') as nilai_saya,
                                                (SELECT file_jawaban FROM pengumpulan_tugas WHERE id_tugas = t.id AND id_siswa = '$id_siswa_login') as file_saya
                                                FROM tugas t
                                                JOIN mapel mp ON t.id_mapel = mp.id
                                                WHERE t.id_kelas = '{$siswa['id_kelas']}'
                                                ORDER BY t.deadline ASC");
                    
                    if (mysqli_num_rows($data) > 0) {
                        while ($row = mysqli_fetch_assoc($data)) {
                            $is_late = strtotime($row['deadline']) < time();
                            
                            // 1. LOGIKA STATUS
                            if ($row['sudah_kumpul'] > 0) {
                                if ($row['status_keterlambatan'] == 'Terlambat') {
                                    $status = "<span style='color: #ed8b12; font-size: 13px; font-weight: 400;'>ⓘ Terlambat</span>";
                                } else {
                                    $status = "<span style='color: #27ae60; font-size: 13px; font-weight: 400;'>✔ Selesai</span>";
                                }
                            } elseif ($is_late) {
                                $status = "<span style='color: #e74c3c; font-size: 13px; font-weight: 400;'>‼️ Deadline Lewat</span>";
                            } else {
                                $status = "<span style='color: #464848; font-size: 13px; font-weight: 400;'>⚠︎ Belum Mengumpulkan</span>";
                            }

                            // 2. LOGIKA FILE
                            if (!empty($row['file_saya'])) {
                                $file_text = "<a href='../uploads/tugas/".$row['file_saya']."' target='_blank' class='btn btn-blue btn-sm' style='font-size:11px; padding: 3px 8px;'><i class='bi bi-eye'></i></a>";
                            } else {
                                $file_text = "-";
                            }

                            // 3. LOGIKA AKSI (MENGGUNAKAN ICON)
                            if ($row['sudah_kumpul'] > 0) {
                                $aksi = "<button onclick='editTugas(".$row['id'].")' class='btn btn-sage btn-sm' title='Ganti File' style='font-size:11px; padding: 4px 8px; margin-right:3px;'><i class='bi bi-pencil-square'></i></button> 
                                         <a href='?hapus_id=".$row['id']."' onclick=\"return confirm('Yakin ingin menghapus hasil pengumpulan ini?')\" class='btn btn-red btn-sm' title='Hapus Pengumpulan' style='font-size:11px; padding: 4px 8px;'><i class='bi bi-trash3'></i></a>";
                            } else {
                                $aksi = "<button onclick='kumpulTugas({$row['id']})' class='btn btn-cari btn-sm' style='font-size:13px; padding: 4px 12px;'><i class='bi bi-cloud-arrow-up me-1'></i> Upload</button>";
                            }
                            
                            $nilai = $row['nilai_saya'] !== NULL ? "<strong>{$row['nilai_saya']}</strong>" : '-';
                            $tgl_deadline = date('d M Y, H:i', strtotime($row['deadline']));
                            ?>
                            <tr>
                                <td style="text-align:center;"><?= $no++ ?></td>
                                <td><?= $row['judul_tugas'] ?><br><small style="color:#888;"><?= $row['nama_mapel'] ?></small></td>
                                <td><?= $tgl_deadline ?></td>
                                <td style="text-align:left;"><?= $status ?></td>
                                <td style="text-align:center;"><?= $nilai ?></td>
                                <td style="text-align:center;"><?= $file_text ?></td>
                                <td style="text-align:center; white-space: nowrap;"><?= $aksi ?></td>
                            </tr>
                            <?php 
                        }
                    } else { ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#999; padding:20px;">Belum ada tugas.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalKumpul" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModalKumpul">Kumpulkan Tugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <div class="modal-body">
            <input type="hidden" name="id_tugas" id="id_tugas_kumpul">
            <input type="hidden" name="aksi_type" id="aksi_type" value="baru">
            <div class="mb-3">
                <label class="form-label">Upload File Jawaban (PDF/Word/Gambar)</label>
                <input type="file" name="file_jawaban" id="input_file" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.jpg,.png" required>
                <small id="hintFile" class="text-muted">Wajib upload file tugas Anda.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="kumpulan" id="tombolSubmit" class="btn btn-cari btn-sm">Kumpulkan Sekarang</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function kumpulTugas(idTugas) {
    document.getElementById('id_tugas_kumpul').value = idTugas;
    document.getElementById('aksi_type').value = 'baru';
    document.getElementById('judulModalKumpul').innerText = 'Kumpulkan Tugas';
    document.getElementById('tombolSubmit').innerText = 'Kumpulkan Sekarang';
    document.getElementById('hintFile').innerText = 'Wajib upload file tugas Anda.';
    document.getElementById('input_file').value = '';
    
    var myModal = new bootstrap.Modal(document.getElementById('modalKumpul'));
    myModal.show();
}

function editTugas(idTugas) {
    document.getElementById('id_tugas_kumpul').value = idTugas;
    document.getElementById('aksi_type').value = 'edit';
    document.getElementById('judulModalKumpul').innerText = 'Ganti File Tugas';
    document.getElementById('tombolSubmit').innerText = 'Update File';
    document.getElementById('hintFile').innerText = 'Pilih file baru untuk mengganti file lama.';
    document.getElementById('input_file').value = '';
    
    var myModal = new bootstrap.Modal(document.getElementById('modalKumpul'));
    myModal.show();
}

// Reset modal saat ditutup
document.getElementById('modalKumpul').addEventListener('hidden.bs.modal', function () {
    document.getElementById('id_tugas_kumpul').value = '';
    document.getElementById('aksi_type').value = 'baru';
    document.getElementById('input_file').value = '';
    document.getElementById('judulModalKumpul').innerText = 'Kumpulkan Tugas';
    document.getElementById('tombolSubmit').innerText = 'Kumpulkan Sekarang';
    document.getElementById('hintFile').innerText = 'Wajib upload file tugas Anda.';
});
</script>

<?php require '../templates/footer_siswa.php'; ?>