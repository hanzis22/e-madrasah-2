<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>
<?php
/** @var mysqli $conn */ 
?>

<div class="isi-halaman">
    <div class="box-putih">
        <h4 style="margin:0 0 10px 0;">Data Kelas</h4>
        <!-- Tombol Tambah Data -->
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalKelas" onclick="resetFormKelas()">+ Tambah Kelas Baru</button>
    </div>

    <!-- BOX PENCARIAN -->
<div class="box-putih">
    <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
        <input type="text" 
               name="cari" 
               class="form-control form-control-sm" 
               style="max-width:300px;" 
               placeholder="Cari nama kelas..."
               value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">

        <button type="submit" class="btn btn-cari btn-sm">
            Cari<i class="bi bi-search ms-2"></i>
        </button>

        <?php if(isset($_GET['cari'])): ?>
            <a href="kelas.php" class="btn btn-cari btn-sm"
               style="background-color:#777; border-color:#777;">
                Reset
            </a>
        <?php endif; ?>

    </form>
</div>

    <!-- Tabel Data -->
    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Kelas</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";

                $where = "";

                if (!empty($cari)) {
                    $where = "WHERE nama_kelas LIKE '%$cari%'";
                }


                $data = mysqli_query($conn, 
                    "SELECT * FROM kelas 
                    $where 
                    ORDER BY id DESC"
                );
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                            <td style="text-align:center;">
                                <button onclick="editData('<?= $row['id'] ?>', '<?= $row['nama_kelas'] ?>')" class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus kelas ini?')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="3" style="text-align:center; color:#999;">Belum ada data kelas.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Tambah/Edit -->
<div class="modal fade" id="modalKelas" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModal">Tambah Kelas Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="">
        <div class="modal-body">
            <input type="hidden" name="id_kelas" id="id_kelas">
            <div class="mb-3">
                <label class="form-label">Nama Kelas (Contoh: Kelas 7A)</label>
                <input type="text" name="nama_kelas" id="nama_kelas" class="form-control form-control-sm" required>
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
    // Fungsi untuk mengisi form modal saat tombol Edit diklik
    function editData(id, nama) {
        document.getElementById('judulModal').innerText = 'Edit Data Kelas';
        document.getElementById('id_kelas').value = id;
        document.getElementById('nama_kelas').value = nama;
        var myModal = new bootstrap.Modal(document.getElementById('modalKelas'));
        myModal.show();
    }
    /*
    // Reset form kalau klik tombol Tambah (biar nggak nempel data sebelumnya)
    document.getElementById('modalKelas').addEventListener('show.bs.modal', function (event) {
        if (!event.relatedTarget) {
            document.getElementById('judulModal').innerText = 'Tambah Kelas Baru';
            document.getElementById('id_kelas').value = '';
            document.getElementById('nama_kelas').value = '';
        }
    })
    */

    // Fungsi khusus untuk mereset form saat tombol Tambah diklik
    function resetFormKelas() {
        document.getElementById('judulModal').innerText = 'Tambah Kelas Baru';
        document.getElementById('id_kelas').value = '';
        document.getElementById('nama_kelas').value = '';
    }
</script>

<?php 
// LOGIKA PROSES SIMPAN (TAMBAH & EDIT)
if (isset($_POST['simpan'])) {
    $id = $_POST['id_kelas'];
    $nama = $_POST['nama_kelas'];

    if (empty($id)) {
        // Jika ID kosong, berarti ini proses TAMBAH
        mysqli_query($conn, "INSERT INTO kelas (nama_kelas) VALUES ('$nama')");
    } else {
        // Jika ID ada isinya, berarti ini proses EDIT
        mysqli_query($conn, "UPDATE kelas SET nama_kelas='$nama' WHERE id='$id'");
    }
    echo "<script>location.replace('kelas.php');</script>";
}

// LOGIKA PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    // Lepaskan siswa dari kelas ini sebelum kelas dihapus
    mysqli_query($conn, "UPDATE users SET id_kelas = NULL WHERE id_kelas = '$id_hapus'");
    // Baru hapus kelasnya
    mysqli_query($conn, "DELETE FROM kelas WHERE id='$id_hapus'");
    echo "<script>location.replace('kelas.php');</script>";
}
?>

<?php require '../templates/footer.php'; ?>