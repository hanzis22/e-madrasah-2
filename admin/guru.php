<?php require '../templates/header.php';
require '../templates/sidebar.php'; 
/** @var mysqli $conn */
?>

<div class="isi-halaman">
    <div class="box-putih">
        <h4 style="margin:0 0 10px 0;">Data Guru</h4>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalGuru" onclick="resetFormGuru()">
            + Tambah Guru Baru
        </button>
    </div>

    <!-- BOX PENCARIAN -->
<div class="box-putih">
    <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
        <input type="text" 
               name="cari" 
               class="form-control form-control-sm" 
               style="max-width:300px;" 
               placeholder="Cari nama guru..."
               value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">

        <button type="submit" class="btn btn-cari btn-sm">
            Cari<i class="bi bi-search ms-2"></i>
        </button>

        <?php if(isset($_GET['cari'])): ?>
            <a href="guru.php" class="btn btn-cari btn-sm" 
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
                    <th width="50" class="text-center">No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";

                $where = "";

                if (!empty($cari)) {
                    $where = "AND nama LIKE '%$cari%'";
                }


                $data = mysqli_query($conn, 
                    "SELECT * FROM users 
                    WHERE role='guru' 
                    $where
                    ORDER BY id DESC"
                );
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td class="text-center">
                                <!-- ═══════════════════════════════════════════ -->
                                <!-- PERBAIKAN: Hanya 3 parameter, sesuai fungsi JS -->
                                <!-- ═══════════════════════════════════════════ -->
                                <button onclick="editData('<?= $row['id'] ?>', '<?= clean(addslashes($row['nama'])) ?>', '<?= clean($row['username']) ?>')" class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus guru ini?')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted p-4">Belum ada data guru.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalGuru" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModal">Tambah Guru Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
            <input type="hidden" name="id_guru" id="id_guru">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label class="form-label" id="labelPassword">Password</label>
                <input type="password" name="password" id="password" class="form-control form-control-sm" required>
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
// ═══════════════════════════════════════════
// PERBAIKAN: Fungsi dengan 3 parameter
// ═══════════════════════════════════════════
function editData(id, nama, username) {
    document.getElementById('judulModal').innerText = 'Edit Data Guru';
    document.getElementById('labelPassword').innerText = 'Password (Kosongkan jika tidak diganti)';
    document.getElementById('id_guru').value = id;
    document.getElementById('nama').value = nama;
    document.getElementById('username').value = username;
    document.getElementById('password').value = '';
    document.getElementById('password').removeAttribute('required');
    
    var myModal = new bootstrap.Modal(document.getElementById('modalGuru'));
    myModal.show();
}

/*
document.getElementById('modalGuru').addEventListener('show.bs.modal', function (event) {
    if (!event.relatedTarget) {
        document.getElementById('judulModal').innerText = 'Tambah Guru Baru';
        document.getElementById('labelPassword').innerText = 'Password';
        document.getElementById('id_guru').value = '';
        document.getElementById('nama').value = '';
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password').setAttribute('required', 'true');
    }
});
*/

function resetFormGuru() {
    document.getElementById('judulModal').innerText = 'Tambah Guru Baru';
    document.getElementById('labelPassword').innerText = 'Password';
    document.getElementById('id_guru').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').setAttribute('required', 'true');
}

</script>

<?php 
if (isset($_POST['simpan'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_guru']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    if (empty($id)) {
        // Cek duplikat username
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$user'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username $user sudah dipakai!'); history.back();</script>";
            exit();
        }
        // GANTI MD5 MENJADI PASSWORD_HASH
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$user', '$pass_hash', 'guru')");
    } else {
        if (empty($pass)) {
            mysqli_query($conn, "UPDATE users SET nama='$nama', username='$user' WHERE id='$id'");
        } else {
            // GANTI MD5 MENJADI PASSWORD_HASH
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET nama='$nama', username='$user', password='$pass_hash' WHERE id='$id'");
        }
    }
    echo "<script>location.replace('guru.php');</script>";
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // 1. Hapus pengumpulan_tugas dulu (anak dari tugas)
    $tugas = mysqli_query($conn, "SELECT id FROM tugas WHERE id_guru='$id_hapus'");
    while ($row = mysqli_fetch_assoc($tugas)) {
        mysqli_query($conn, "DELETE FROM pengumpulan_tugas WHERE id_tugas='" . $row['id'] . "'");
    }
    
    // 2. Hapus tugas
    mysqli_query($conn, "DELETE FROM tugas WHERE id_guru='$id_hapus'");
    
    // 3. Hapus materi
    mysqli_query($conn, "DELETE FROM materi WHERE id_guru='$id_hapus'");
    
    // 4. Hapus mengajar
    mysqli_query($conn, "DELETE FROM mengajar WHERE id_guru='$id_hapus'");
    
    // 5. Hapus user guru
    mysqli_query($conn, "DELETE FROM users WHERE id='$id_hapus'");
    
    echo "<script>location.replace('guru.php');</script>";
}
?>

<?php require '../templates/footer.php'; ?>