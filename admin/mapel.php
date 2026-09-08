<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>

<div class="isi-halaman">
    <div class="box-putih">
        <h4 style="margin:0 0 10px 0;">Data Mata Pelajaran</h4>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalMapel" onclick="resetFormMapel()">
            + Tambah Mapel Baru</button>
    </div>

    <!-- BOX PENCARIAN -->
<div class="box-putih">
    <form method="get" action="" style="display:flex; gap:10px; align-items:center;">

        <input type="text" 
               name="cari" 
               class="form-control form-control-sm"
               style="max-width:300px;"
               placeholder="Cari mata pelajaran..."
               value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">

        <button type="submit" class="btn btn-cari btn-sm">
            Cari<i class="bi bi-search ms-2"></i>
        </button>

        <?php if(isset($_GET['cari'])): ?>

            <a href="mapel.php" 
               class="btn btn-cari btn-sm"
               style="background-color:#777; border-color:#777;">
                Reset
            </a>

        <?php endif; ?>

    </form>
</div>

    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Mata Pelajaran</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                /** @var mysqli $conn */
                $no = 1;
                $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";

                $where = "";

                if (!empty($cari)) {
                    $where = "WHERE nama_mapel LIKE '%$cari%'";
                }


                $data = mysqli_query($conn, 
                    "SELECT * FROM mapel 
                    $where
                    ORDER BY id DESC"
                );
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_mapel']) ?></td>
                            <td style="text-align:center;">
                                <button onclick="editData('<?= $row['id'] ?>', '<?= $row['nama_mapel'] ?>')" class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus mapel ini?')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="3" style="text-align:center; color:#999;">Belum ada data mata pelajaran.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modalMapel" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModal">Tambah Mapel Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
            <input type="hidden" name="id_mapel" id="id_mapel">
            <div class="mb-3">
                <label class="form-label">Nama Mata Pelajaran (Contoh: Matematika)</label>
                <input type="text" name="nama_mapel" id="nama_mapel" class="form-control form-control-sm" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="simpan" class="btn btn-simpan btn-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editData(id, nama) {
        document.getElementById('judulModal').innerText = 'Edit Data Mapel';
        document.getElementById('id_mapel').value = id;
        document.getElementById('nama_mapel').value = nama;
        var myModal = new bootstrap.Modal(document.getElementById('modalMapel'));
        myModal.show();
    }

    /*
    document.getElementById('modalMapel').addEventListener('show.bs.modal', function (event) {
        if (!event.relatedTarget) {
            document.getElementById('judulModal').innerText = 'Tambah Mapel Baru';
            document.getElementById('id_mapel').value = '';
            document.getElementById('nama_mapel').value = '';
        }
    })
    */

        function resetFormMapel() {
        document.getElementById('judulModal').innerText = 'Tambah Mapel Baru';
        document.getElementById('id_mapel').value = '';
        document.getElementById('nama_mapel').value = '';
    }

</script>

<?php 
// LOGIKA SIMPAN
if (isset($_POST['simpan'])) {
    $id = $_POST['id_mapel'];
    $nama = $_POST['nama_mapel'];

    if (empty($id)) {
        mysqli_query($conn, "INSERT INTO mapel (nama_mapel) VALUES ('$nama')");
    } else {
        mysqli_query($conn, "UPDATE mapel SET nama_mapel='$nama' WHERE id='$id'");
    }
    echo "<script>location.replace('mapel.php');</script>";
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // 1. Hapus semua materi yang terkait dengan mapel ini
    mysqli_query($conn, "DELETE FROM materi WHERE id_mapel = '$id_hapus'");
    
    // 2. Hapus semua tugas yang terkait dengan mapel ini (kalau ada)
    mysqli_query($conn, "DELETE FROM tugas WHERE id_mapel = '$id_hapus'");
    
    // 3. Hapus data penugasan mengajar guru untuk mapel ini (kalau ada)
    mysqli_query($conn, "DELETE FROM mengajar WHERE id_mapel = '$id_hapus'");
    
    // 4. BARU setelah bersih, hapus mapel-nya dari tabel utama
    $hapus_mapel = mysqli_query($conn, "DELETE FROM mapel WHERE id = '$id_hapus'");
    
    if ($hapus_mapel) {
        echo "<script>location.replace('mapel.php');</script>";
    } else {
        echo "<script>alert('Gagal menghapus mapel. Mungkin masih ada data terkait di tabel lain.'); location.replace('mapel.php');</script>";
    }
}
?>

<?php require '../templates/footer.php'; ?>