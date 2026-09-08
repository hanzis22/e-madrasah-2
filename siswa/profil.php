<?php 
require '../templates/header_siswa.php'; 
require '../templates/sidebar_siswa.php';

/** @var mysqli $conn */
 $id_siswa = $_SESSION['id'];

// ========== LOGIKA UPLOAD & UPDATE (DIPINDAH KE ATAS) ==========
 $pesan = '';
 $pesan_tipe = '';

if (isset($_POST['simpan_profil'])) {
    $username_baru = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $pass_baru = $_POST['password_baru'];

    $cek_username = mysqli_query($conn, "SELECT id FROM users WHERE username='$username_baru' AND id != '$id_siswa'");
    if (mysqli_num_rows($cek_username) > 0) {
        $pesan = 'Username sudah digunakan orang lain!';
        $pesan_tipe = 'danger';
    } else {
        // PROSES UPLOAD FOTO
        $nama_foto = $_POST['foto_lama'];

        if (!empty($_FILES['foto']['name'])) {
            $file = $_FILES['foto'];
            $tipe = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $ukuran = $file['size'];

            if (!in_array($tipe, ['jpg', 'jpeg', 'png', 'gif'])) {
                $pesan = 'Tipe file tidak diperbolehkan! (JPG/PNG/GIF)';
                $pesan_tipe = 'danger';
            } elseif ($ukuran > 2 * 1024 * 1024) {
                $pesan = 'Ukuran file terlalu besar! Maksimal 2MB.';
                $pesan_tipe = 'danger';
            } else {
                $nama_foto_baru = 'profil_' . $id_siswa . '_' . time() . '.' . $tipe;
                $folder = '../uploads/profil/';

                // Hapus foto lama (kecuali default)
                if (!empty($_POST['foto_lama']) && $_POST['foto_lama'] != 'default.png') {
                    $file_lama = $folder . $_POST['foto_lama'];
                    if (file_exists($file_lama)) {
                        unlink($file_lama);
                    }
                }

                if (move_uploaded_file($file['tmp_name'], $folder . $nama_foto_baru)) {
                    $nama_foto = $nama_foto_baru;
                }
            }
        }

        // SIMPAN KE DATABASE
        if (empty($pesan)) {
            if (empty($pass_baru)) {
                mysqli_query($conn, "UPDATE users SET nama='$nama_baru', username='$username_baru', foto='$nama_foto' WHERE id='$id_siswa'");
            } else {
                $pass_md5 = md5($pass_baru);
                mysqli_query($conn, "UPDATE users SET nama='$nama_baru', username='$username_baru', password='$pass_md5', foto='$nama_foto' WHERE id='$id_siswa'");
            }

            // Update session
            $_SESSION['nama'] = $nama_baru;
            $_SESSION['foto'] = $nama_foto;

            $pesan = 'Profil berhasil diperbarui!';
            $pesan_tipe = 'success';
        }
    }
}

// Ambil data siswa terbaru
 $data_siswa = mysqli_query($conn, "SELECT * FROM users WHERE id='$id_siswa'");
 $siswa = mysqli_fetch_assoc($data_siswa);

// Tentukan path foto
 $folder_foto = '../uploads/profil/';
if (!empty($siswa['foto']) && file_exists($folder_foto . $siswa['foto'])) {
    $src_foto = $folder_foto . $siswa['foto'];
} else {
    $src_foto = '../assets/img/default.png';
}
?>

<!-- CSS FOTO BULET -->
<style>
.foto-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 20px auto;
    cursor: pointer;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #dee2e6;
    transition: border-color 0.3s;
}
.foto-wrapper:hover { border-color: #0d6efd; }

.foto-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.foto-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    color: #fff;
}
.foto-overlay i { font-size: 22px; margin-bottom: 2px; }
.foto-overlay small { font-size: 11px; }
.foto-wrapper:hover .foto-overlay { opacity: 1; }
</style>

<div class="isi-halaman">
    <div class="box-putih">
        <h4 style="margin:0 0 20px 0;">Profil Saya</h4>

        <!-- PESAN SUKSES/ERROR -->
        <?php if (!empty($pesan)): ?>
            <div class="alert alert-<?= $pesan_tipe ?> alert-dismissible fade show">
                <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- FORM DENGAN ENCTYPE -->
        <form method="post" action="" enctype="multipart/form-data" style="max-width: 500px;">
            
            <!-- FOTO PROFIL BULET -->
            <div class="text-center">
                <div class="foto-wrapper" id="fotoWrapper">
                    <img src="<?= $src_foto ?>" alt="Foto" class="foto-img" id="fotoPreview">
                    <div class="foto-overlay">
                        <i class="bi bi-camera-fill"></i>
                        <small>Ubah Foto</small>
                    </div>
                </div>
                <input type="file" name="foto" id="inputFoto" accept="image/*" class="d-none">
                <input type="hidden" name="foto_lama" value="<?= $siswa['foto'] ?? '' ?>">
                <small class="text-muted d-block mb-4">Klik foto untuk mengubah. Maks 2MB</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control form-control-sm" value="<?= htmlspecialchars($siswa['username']) ?>" required>
                <small class="text-muted">Username digunakan untuk login.</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control form-control-sm" value="<?= htmlspecialchars($siswa['nama']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password_baru" class="form-control form-control-sm" placeholder="Kosongkan jika tidak ingin mengubah password">
                <small class="text-muted">Isi hanya jika ingin mengganti password lama.</small>
            </div>
            
            <button type="submit" name="simpan_profil" class="btn btn-cari btn-sm">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- JAVASCRIPT PREVIEW FOTO -->
<script>
document.getElementById('fotoWrapper').addEventListener('click', function() {
    document.getElementById('inputFoto').click();
});

document.getElementById('inputFoto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('fotoPreview').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require '../templates/footer_siswa.php'; ?>