<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>

<div class="isi-halaman">
    <div class="box-putih">
        <h4 style="margin:0 0 10px 0;">Data Siswa</h4>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSiswa" onclick="resetFormTambah()">+ Tambah Siswa Baru</button>
    </div>

    <!-- BOX PENCARIAN -->
    <div class="box-putih">
        <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
            <input type="text" name="cari" class="form-control form-control-sm" style="max-width: 300px;" placeholder="Cari nama siswa..." value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">
            <button type="submit" class="btn btn-cari btn-sm">Cari<i class="bi bi-search ms-2"></i></button>
            <?php if(isset($_GET['cari'])): ?>
                <a href="siswa.php" class="btn btn-cari btn-sm" style="background-color:#777; border-color:#777;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Siswa</th>
                    <th>Username</th>
                    <th>Kelas</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                /** @var mysqli $conn */
                
                // LOGIKA PENCARIAN
                $cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : "";
                $where = "";
                if (!empty($cari)) {
                    $where = "AND siswa.nama LIKE '%$cari%'";
                }

                // LOGIKA PAGINATION
                $batas = 5; // Jumlah data per halaman
                $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
                $halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

                // Query menghitung total semua data (untuk info jumlah halaman)
                $hitung_data = mysqli_query($conn, "SELECT siswa.*, kelas.nama_kelas FROM users siswa LEFT JOIN kelas ON siswa.id_kelas = kelas.id WHERE siswa.role='siswa' $where");
                $total_data = mysqli_num_rows($hitung_data);
                $total_halaman = ceil($total_data / $batas);

                // Query untuk mengambil data sesuai batas halaman
                $data = mysqli_query($conn, "SELECT siswa.*, kelas.nama_kelas FROM users siswa LEFT JOIN kelas ON siswa.id_kelas = kelas.id WHERE siswa.role='siswa' $where ORDER BY siswa.id DESC LIMIT $halaman_awal, $batas");
                
                $no = $halaman_awal + 1;
                
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas'] ?: '- Belum Punya Kelas -') ?></td>
                            <td style="text-align:center;">
                                <button onclick="editData('<?= $row['id'] ?>', '<?= $row['nama'] ?>', '<?= $row['username'] ?>', '<?= $row['id_kelas'] ?>')" class="btn btn-sage btn-sm"><i class="bi bi-pencil-square"></i></button>
                                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus siswa ini?')" class="btn btn-red btn-sm"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999;">Data tidak ditemukan.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- NAVIGASI PAGINATION -->
    <?php if ($total_halaman > 1): ?>
    <div class="box-putih" style="display:flex; justify-content:space-between; align-items:center; font-size:14px;">
        <span>Menampilkan <?= $halaman_awal+1 ?> sampai <?= min($halaman_awal + $batas, $total_data) ?> dari <?= $total_data ?> data</span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <!-- Tombol Previous -->
                <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?halaman=<?= $halaman - 1 ?>&cari=<?= $cari ?>">Prev</a>
                </li>
                
                <?php for($i = 1; $i <= $total_halaman; $i++): ?>
                <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                    <a class="page-link" href="?halaman=<?= $i ?>&cari=<?= $cari ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <!-- Tombol Next -->
                <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?halaman=<?= $halaman + 1 ?>&cari=<?= $cari ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

</div>

<!-- Modal Form (Tetap Sama) -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModal">Tambah Siswa Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
            <input type="hidden" name="id_siswa" id="id_siswa">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password <?php if(isset($_GET['edit'])) echo "(Kosongkan jika tidak diganti)"; ?></label>
                <input type="password" name="password" id="password" class="form-control form-control-sm" <?= !isset($_GET['edit']) ? 'required' : '' ?>>
            </div>
            <div class="mb-3">
                <label class="form-label">Pilih Kelas</label>
                <select name="id_kelas" id="id_kelas" class="form-select form-select-sm" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php 
                    $kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                    while ($k = mysqli_fetch_assoc($kelas)) {
                        echo "<option value='".$k['id']."'>".$k['nama_kelas']."</option>";
                    }
                    ?>
                </select>
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
    function editData(id, nama, username, id_kelas) {
        document.getElementById('judulModal').innerText = 'Edit Data Siswa';
        document.getElementById('id_siswa').value = id;
        document.getElementById('nama').value = nama;
        document.getElementById('username').value = username;
        document.getElementById('password').removeAttribute('required');
        document.getElementById('id_kelas').value = id_kelas; 
        var myModal = new bootstrap.Modal(document.getElementById('modalSiswa'));
        myModal.show();
    }
    
    /*
    document.getElementById('modalSiswa').addEventListener('show.bs.modal', function (event) {
        if (!event.relatedTarget) {
            document.getElementById('judulModal').innerText = 'Tambah Siswa Baru';
            document.getElementById('id_siswa').value = '';
            document.getElementById('nama').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password').setAttribute('required', 'true');
            document.getElementById('id_kelas').value = ''; 
        }
    })
    */
    
    // Fungsi khusus untuk mereset form saat tombol Tambah diklik
    function resetFormTambah() {
        document.getElementById('judulModal').innerText = 'Tambah Siswa Baru';
        document.getElementById('id_siswa').value = '';
        document.getElementById('nama').value = '';
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password').setAttribute('required', 'true');
        document.getElementById('id_kelas').value = ''; 
    }

</script>

<?php 
// LOGIKA SIMPAN DAN HAPUS
if (isset($_POST['simpan'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_siswa']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];
    $id_kelas = mysqli_real_escape_string($conn, $_POST['id_kelas']);

    if (empty($id)) {
        // --- SAAT TAMBAH DATA BARU ---
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$user'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username $user sudah dipakai!'); history.back();</script>";
            exit();
        }
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (nama, username, password, role, id_kelas) VALUES ('$nama', '$user', '$pass_hash', 'siswa', '$id_kelas')");
    } else {
        // --- SAAT EDIT DATA ---
        // Cek apakah username baru sudah dipakai user LAIN (id != $id)
        $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$user' AND id != '$id'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username $user sudah dipakai siswa lain!'); history.back();</script>";
            exit();
        }

        if (empty($pass)) {
            mysqli_query($conn, "UPDATE users SET nama='$nama', username='$user', id_kelas='$id_kelas' WHERE id='$id'");
        } else {
            $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET nama='$nama', username='$user', password='$pass_hash', id_kelas='$id_kelas' WHERE id='$id'");
        }
    }
    echo "<script>location.replace('siswa.php');</script>";
}

// LOGIKA HAPUS SISWA
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM pengumpulan_tugas WHERE id_siswa = '$id_hapus'");
    mysqli_query($conn, "DELETE FROM ortu_siswa WHERE id_siswa = '$id_hapus'");
    mysqli_query($conn, "DELETE FROM pembayaran WHERE id_siswa = '$id_hapus'");
    mysqli_query($conn, "DELETE FROM rapor WHERE id_siswa = '$id_hapus'");
    $hapus_user = mysqli_query($conn, "DELETE FROM users WHERE id = '$id_hapus'");
    
    if ($hapus_user) {
        echo "<script>location.replace('siswa.php');</script>";
    } else {
        echo "<script>alert('Gagal menghapus siswa.'); location.replace('siswa.php');</script>";
    }
}
?>

<?php require '../templates/footer.php'; ?>