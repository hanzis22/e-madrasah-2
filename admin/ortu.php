<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>
<?php
/** @var mysqli $conn */

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Hapus relasi ortu-siswa dulu sebelum hapus akun ortunya
    mysqli_query($conn, "DELETE FROM ortu_siswa WHERE id_ortu = '$id_hapus'");
    
    // Hapus akun ortu dari tabel users
    $hapus = mysqli_query($conn, "DELETE FROM users WHERE id = '$id_hapus' AND role='ortu'");
    
    if ($hapus) {
        echo "<script>location.replace('ortu.php?msg=Akun orang tua berhasil dihapus.');</script>";
    } else {
        echo "<script>alert('Gagal menghapus akun.'); location.replace('ortu.php');</script>";
    }
}

// LOGIKA SIMPAN (TAMBAH & UPDATE)
if (isset($_POST['simpan'])) {
    $id = $_POST['id_ortu'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    if (empty($id)) {
        // Cek username agar tidak duplikat
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username $username sudah dipakai!'); location.replace('ortu.php');</script>";
            exit();
        }
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$pass_hash', 'ortu')");
    } else {
        if (empty($pass)) {
            // Jika password dikosongkan saat edit, jangan update passwordnya
            mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username' WHERE id='$id'");
        } else {
            // Jika password diisi saat edit, update semuanya
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET nama='$nama', username='$username', password='$pass_hash' WHERE id='$id'");
        }
    }
    echo "<script>location.replace('ortu.php?msg=Data orang tua berhasil disimpan.');</script>";
}

// LOGIKA PENCARIAN
 $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";

$where = "";

if (!empty($cari)) {
    $where = "AND (nama LIKE '%$cari%' OR username LIKE '%$cari%')";
}


// Ambil data untuk ditampilkan di tabel
$data_ortu = mysqli_query($conn, "SELECT * FROM users WHERE role='ortu' $where ORDER BY id DESC");
?>

<div class="isi-halaman">
    <!-- PESAN NOTIFIKASI -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_GET['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

<div class="box-putih">
    <h4 style="margin:0 0 10px 0;">Data Akun Orang Tua</h4>
    <button type="button" class="btn btn-cari btn-sm" data-bs-toggle="modal" data-bs-target="#modalOrtu" onclick="resetFormOrtu()">+ Tambah Orang Tua Baru</button>
</div>


<!-- BOX PENCARIAN -->
<div class="box-putih">
    <form method="get" action="" 
          style="display:flex; gap:10px; align-items:center;">
        <input type="text" name="cari" class="form-control form-control-sm" style="max-width:300px;" placeholder="Cari nama / username ortu..." value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">
        <button type="submit" class="btn btn-cari btn-sm">Cari<i class="bi bi-search ms-2"></i></button>


        <?php if(isset($_GET['cari'])): ?>
        <a href="ortu.php" class="btn btn-cari btn-sm" style="background-color:#777; border-color:#777;">Reset</a>
        <?php endif; ?>
    </form>
</div>

    <!-- TABEL DATA ORTU -->
    <div class="box-putih" style="padding: 0; overflow: hidden; margin-top: 20px;">
        <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($data_ortu) > 0) {
                    while ($row = mysqli_fetch_assoc($data_ortu)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td style="text-align:center;">
                                <button onclick="editOrtu('<?= $row['id'] ?>', '<?= $row['nama'] ?>', '<?= $row['username'] ?>')" class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus akun ortu ini? Relasi dengan siswa juga akan dihapus.')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="4" style="text-align:center; color:#aab7c4; padding:20px;">
                            Belum ada data orang tua.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL FORM ORTU -->
<div class="modal fade" id="modalOrtu" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModalOrtu">Tambah Orang Tua Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
            <input type="hidden" name="id_ortu" id="id_ortu">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" id="nama_ortu" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" id="username_ortu" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password <span id="hintPass" class="text-muted">(Wajib diisi)</span></label>
                <input type="password" name="password" id="password_ortu" class="form-control form-control-sm" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="simpan" class="btn btn-cari btn-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fungsi untuk mengisi form saat tombol Edit diklik
    function editOrtu(id, nama, username) {
        document.getElementById('judulModalOrtu').innerText = 'Edit Data Orang Tua';
        document.getElementById('id_ortu').value = id;
        document.getElementById('nama_ortu').value = nama;
        document.getElementById('username_ortu').value = username;
        document.getElementById('password_ortu').value = '';
        document.getElementById('password_ortu').removeAttribute('required');
        document.getElementById('hintPass').innerText = '(Kosongkan jika tidak diganti)';
        
        var myModal = new bootstrap.Modal(document.getElementById('modalOrtu'));
        myModal.show();
    }

    // Fungsi untuk mengosongkan form saat tombol Tambah diklik
    function resetFormOrtu() {
        document.getElementById('judulModalOrtu').innerText = 'Tambah Orang Tua Baru';
        document.getElementById('id_ortu').value = '';
        document.getElementById('nama_ortu').value = '';
        document.getElementById('username_ortu').value = '';
        document.getElementById('password_ortu').value = '';
        document.getElementById('password_ortu').setAttribute('required', 'true');
        document.getElementById('hintPass').innerText = '(Wajib diisi)';
    }
</script>

<?php require '../templates/footer.php'; ?>