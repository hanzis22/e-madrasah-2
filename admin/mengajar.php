<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>
<?php
/** @var mysqli $conn */ 
?>

<div class="isi-halaman">
    <!-- FORM PENUGASAN MENGAJAR -->
    <div class="box-putih">
        <h4 style="margin:0 0 15px 0;" id="judulForm">Tugaskan Mengajar</h4>
        <form method="post" action="" id="formMengajar">
            <!-- ID tersembunyi ini WAJIB ada untuk logika edit -->
            <input type="hidden" name="id_mengajar" id="id_mengajar" value="">
            
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Pilih Guru</label>
                    <!-- Tambahkan ID pada setiap select -->
                    <select name="id_guru" class="form-select form-select-sm" id="inputGuru" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php 
                        $guru = mysqli_query($conn, "SELECT * FROM users WHERE role='guru'");
                        while ($g = mysqli_fetch_assoc($guru)) {
                            echo "<option value='".$g['id']."'>".$g['nama']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Pilih Mapel</label>
                    <select name="id_mapel" class="form-select form-select-sm" id="inputMapel" required>
                        <option value="">-- Pilih Mapel --</option>
                        <?php 
                        $mapel = mysqli_query($conn, "SELECT * FROM mapel");
                        while ($m = mysqli_fetch_assoc($mapel)) {
                            echo "<option value='".$m['id']."'>".$m['nama_mapel']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Pilih Kelas</label>
                    <select name="id_kelas" class="form-select form-select-sm" id="inputKelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php 
                        $kelas = mysqli_query($conn, "SELECT * FROM kelas");
                        while ($k = mysqli_fetch_assoc($kelas)) {
                            echo "<option value='".$k['id']."'>".$k['nama_kelas']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2" style="display:flex; align-items:flex-end;">
                    <!-- Tambahkan ID pada tombol -->
                    <button type="submit" name="simpan" id="tombolSimpan" class="btn btn-cari btn-sm w-100"><i class="bi bi-save me-2"></i>Simpan Penugasan</button>
                </div>
            </div>
            <a href="mengajar.php" class="btn btn-sm mt-2" style="color:#7f8c8d; text-decoration:none; display:none;" id="btnBatal">Batal Edit</a>
        </form>
    </div>

    <!-- BOX PENCARIAN -->
    <div class="box-putih">
        <form method="get" action="" style="display:flex; gap:10px; align-items:center;">

            <input type="text"
                name="cari"
                class="form-control form-control-sm"
                style="max-width:300px;"
                placeholder="Cari guru / mapel / kelas..."
                value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">

            <button type="submit" class="btn btn-cari btn-sm">
                Cari<i class="bi bi-search ms-2"></i>
            </button>


            <?php if(isset($_GET['cari'])): ?>

            <a href="mengajar.php"
            class="btn btn-cari btn-sm"
            style="background-color:#777; border-color:#777;">
                Reset
            </a>

            <?php endif; ?>

        </form>
    </div>

    <!-- TABEL DAFTAR MENGAJAR -->
    <div class="box-putih" style="padding: 0; overflow: hidden; border-radius: 8px; margin-top: 20px;">
        <table class="table table-hover" style="margin:0; font-size: 14px;">
            <thead>
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Guru</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <!-- DIPERLEBAR JADI 150PX -->
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";


$where = "";

if (!empty($cari)) {

    $where = "WHERE 
        guru.nama LIKE '%$cari%' 
        OR mapel.nama_mapel LIKE '%$cari%' 
        OR kelas.nama_kelas LIKE '%$cari%'";

}


        $query = "SELECT mengajar.id, mengajar.id_guru, mengajar.id_mapel, mengajar.id_kelas, guru.nama AS nama_guru, mapel.nama_mapel, kelas.nama_kelas
                FROM mengajar
                JOIN users guru ON mengajar.id_guru = guru.id
                JOIN mapel ON mengajar.id_mapel = mapel.id
                JOIN kelas ON mengajar.id_kelas = kelas.id

                $where

                ORDER BY mengajar.id DESC";
                          
                $data = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_guru']) ?></td>
                            <td><?= htmlspecialchars($row['nama_mapel']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                            <td style="text-align:center;">
                                <button onclick="editData('<?= $row['id'] ?>', '<?= $row['id_guru'] ?>', '<?= $row['id_mapel'] ?>', '<?= $row['id_kelas'] ?>')" class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus penugasan ini?')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#aab7c4; padding:20px;">Belum ada data penugasan mengajar.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editData(id, idGuru, idMapel, idKelas) {
    document.getElementById('id_mengajar').value = id;
    document.getElementById('inputGuru').value = idGuru;
    document.getElementById('inputMapel').value = idMapel;
    document.getElementById('inputKelas').value = idKelas;
    
    document.getElementById('judulForm').innerText = "Edit Penugasan Mengajar";
    document.getElementById('tombolSimpan').innerText = "Update Penugasan";
    document.getElementById('btnBatal').style.display = "inline-block";
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php 
// LOGIKA SIMPAN (TAMBAH & UPDATE)
if (isset($_POST['simpan'])) {
    $id_mengajar = $_POST['id_mengajar'];
    $id_guru = $_POST['id_guru'];
    $id_mapel = $_POST['id_mapel'];
    $id_kelas = $_POST['id_kelas'];

    if (empty($id_mengajar)) {
        mysqli_query($conn, "INSERT INTO mengajar (id_guru, id_mapel, id_kelas) VALUES ('$id_guru', '$id_mapel', '$id_kelas')");
    } else {
        mysqli_query($conn, "UPDATE mengajar SET id_guru='$id_guru', id_mapel='$id_mapel', id_kelas='$id_kelas' WHERE id='$id_mengajar'");
    }
    echo "<script>location.replace('mengajar.php');</script>";
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM mengajar WHERE id='$id_hapus'");
    echo "<script>location.replace('mengajar.php');</script>";
}
?>

<?php require '../templates/footer.php'; ?>