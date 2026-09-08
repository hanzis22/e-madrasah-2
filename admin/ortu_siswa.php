<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>
<?php
/** @var mysqli $conn */
?>

<!-- TAMBAHAN CSS SELECT2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="isi-halaman">
    <!-- PESAN NOTIFIKASI -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_GET['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Hubungkan Orang Tua & Siswa</h4>
        <p style="margin:0; color:#666; font-size:14px;">Tautkan akun wali murid dengan data anaknya agar ortu bisa melihat nilai, rapor, dan SPP.</p>
    </div>

    <!-- FORM HUBUNGKAN -->
    <div class="box-putih" style="border-left: 5px solid #2e7d32;">
        <h5 style="margin:0 0 15px 0;"><i class="bi bi-people-fill me-2"></i>Tambah Hubungan Baru</h5>
        <form method="post" action="">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label fw-bold">Pilih Orang Tua</label>
                    <select name="id_ortu" class="form-select form-select-sm select2-custom" required>
                        <option value="">-- Pilih Orang Tua --</option>
                        <?php 
                        $data_ortu = mysqli_query($conn, "SELECT id, nama FROM users WHERE role = 'ortu' ORDER BY nama ASC");
                        while($o = mysqli_fetch_assoc($data_ortu)) { ?>
                            <option value="<?= $o['id'] ?>"><?= $o['nama'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end justify-content-center">
                    <h5 class="mb-0 text-muted">+</h5>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Pilih Anak (Siswa)</label>
                    <select name="id_siswa" class="form-select form-select-sm select2-custom" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php 
                        $data_siswa = mysqli_query($conn, "SELECT u.id, u.nama, k.nama_kelas FROM users u LEFT JOIN kelas k ON u.id_kelas = k.id WHERE u.role = 'siswa' ORDER BY k.nama_kelas ASC, u.nama ASC");
                        while($s = mysqli_fetch_assoc($data_siswa)) { ?>
                            <option value="<?= $s['id'] ?>"><?= $s['nama'] ?> <?= $s['nama_kelas'] ? '('.$s['nama_kelas'].')' : '' ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" name="tambah_relasi" class="btn btn-cari btn-sm w-100"><i class="bi bi-person-add me-2"></i></i>Hubungkan</button>
                </div>
            </div>
        </form>
    </div>

        <!-- BOX PENCARIAN -->
        <div class="box-putih">

            <form method="get" action="" style="display:flex; gap:10px; align-items:center;">
                <input type="text" name="cari" class="form-control form-control-sm" style="max-width:300px;" placeholder="Cari ortu / siswa / kelas..." value="<?= isset($_GET['cari']) ? $_GET['cari'] : '' ?>">
                <button type="submit" class="btn btn-cari btn-sm">Cari<i class="bi bi-search ms-2"></i></button>

                <?php if(isset($_GET['cari'])): ?>

                <a href="ortu_siswa.php" class="btn btn-cari btn-sm" style="background-color:#777; border-color:#777;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <table class="table table-hover" style="margin:0; font-size: 14px;">
            <thead>
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Orang Tua</th>
                    <th>Nama Anak (Siswa)</th>
                    <th>Kelas Anak</th>
                    <th width="150px" style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $relasi = mysqli_query($conn, "SELECT os.id, o.nama AS nama_ortu, s.nama AS nama_siswa, k.nama_kelas 
                                              FROM ortu_siswa os
                                              JOIN users o ON os.id_ortu = o.id
                                              JOIN users s ON os.id_siswa = s.id
                                              LEFT JOIN kelas k ON s.id_kelas = k.id
                                              ORDER BY o.nama ASC");
                
                if (mysqli_num_rows($relasi) > 0) {
                    while ($r = mysqli_fetch_assoc($relasi)) {
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $r['nama_ortu'] ?></td>
                            <td><?= $r['nama_siswa'] ?></td>
                            <td><?= $r['nama_kelas'] ?: '-' ?></td>
                            <td style="text-align:center;">
                                <a href="?hapus_id=<?= $r['id'] ?>" 
                                   onclick="return confirm('Yakin ingin memutuskan hubungan ortu dan siswa ini?')" 
                                   class="btn btn-red btn-sm">
                                   <i class="bi bi-backspace-reverse me-2"></i></i>Batalkan
                                </a>
                            </td>
                        </tr>
                        <?php 
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#aab7c4; padding:20px;">
                            Belum ada data yang terhubung.
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
// LOGIKA HUBUNGKAN
if (isset($_POST['tambah_relasi'])) {
    $id_ortu = $_POST['id_ortu'];
    $id_siswa = $_POST['id_siswa'];

    $cek = mysqli_query($conn, "SELECT id FROM ortu_siswa WHERE id_ortu = '$id_ortu' AND id_siswa = '$id_siswa'");
    
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Orang tua dan siswa ini sudah terhubung!'); location.replace('ortu_siswa.php');</script>";
    } else {
        $simpan = mysqli_query($conn, "INSERT INTO ortu_siswa (id_ortu, id_siswa) VALUES ('$id_ortu', '$id_siswa')");
        if ($simpan) {
            echo "<script>location.replace('ortu_siswa.php?msg=Berhasil menghubungkan orang tua dengan siswa.');</script>";
        } else {
            echo "<script>alert('Gagal menyimpan ke database.'); location.replace('ortu_siswa.php');</script>";
        }
    }
}

// LOGIKA PUTUSKAN HUBUNGAN
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    mysqli_query($conn, "DELETE FROM ortu_siswa WHERE id = '$id_hapus'");
    echo "<script>location.replace('ortu_siswa.php?msg=Berhasil memutuskan hubungan.'); </script>";
}
?>

<!-- JS SELECT2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-custom').select2({
            placeholder: '-- Pilih Data --',
            allowClear: true
        });
    });
</script>

<?php require '../templates/footer.php'; ?>