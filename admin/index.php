<?php require '../templates/header.php'; ?>
<?php require '../templates/sidebar.php'; ?>
<?php
/** @var mysqli $conn */
?>

<style>
    .card-statistik {
        border-left: 4px solid #ccc;
        border-radius: 4px;
    }
    .sc-guru { 
        border-left-color: #2e7d32; 
        background-color: #f1f8e9; 
    }
    .sc-siswa { 
        border-left-color: #1976d2; 
        background-color: #e3f2fd; 
    }
    .sc-kelas { 
        border-left-color: #f57c00; 
        background-color: #fff3e0; 
    }

    /* Warna angka */
    .sc-guru .stat-angka { color: #2e7d32; }
    .sc-siswa .stat-angka { color: #1976d2; }
    .sc-kelas .stat-angka { color: #f57c00; }

    /* Warnai teks kecil agar tetap gelap di atas background pastel */
    .sc-guru small, .sc-siswa small, .sc-kelas small { color: #555 !important; }

    /* 2. TABEL LOG */
    .log-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
        position: relative;
        top: -1px;
    }
    .dot-siswa { background-color: #1976d2; }
    .dot-tugas { background-color: #2e7d32; }
    .dot-materi { background-color: #f57c00; }
</style>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Dashboard Admin</h4>
        <p style="margin:0; color:#666; font-size:14px;">Selamat datang! Berikut adalah ringkasan data E-Madrasah Anda hari ini.</p>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card-statistik stat-flex sc-guru">
                <div>
                    <h5 style="margin:0 0 5px 0;">Data Guru</h5>
                    <small>Total Guru Terdaftar</small>
                </div>
                <h2 class="stat-angka"><?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='guru'")) ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik stat-flex sc-siswa">
                <div>
                    <h5 style="margin:0 0 5px 0;">Data Siswa</h5>
                    <small>Total Siswa Terdaftar</small>
                </div>
                <h2 class="stat-angka"><?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='siswa'")) ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik stat-flex sc-kelas">
                <div>
                    <h5 style="margin:0 0 5px 0;">Data Kelas</h5>
                    <small>Total Ruangan Kelas</small>
                </div>
                <h2 class="stat-angka"><?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kelas")) ?></h2>
            </div>
        </div>
    </div>

    <!-- LOG AKTIVITAS TERBARU -->
        <div class="box-putih" style="padding: 0; overflow: hidden; border-radius: 8px;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h5 style="margin:0; font-size: 16px;"><i class="bi bi-clock-history me-2"></i>Log Aktivitas Terbaru</h5>
        </div>
        <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f9f9f9;">
                <tr>
                    <th width="200px">Waktu</th>
                    <th>Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $ada_aktivitas = false;

                $log_siswa = mysqli_query($conn, "SELECT nama, tanggal_daftar FROM users WHERE role='siswa' ORDER BY id DESC LIMIT 3");
                while ($ls = mysqli_fetch_assoc($log_siswa)) {
                    $ada_aktivitas = true;
                    $tgl = !empty($ls['tanggal_daftar']) ? date('d M Y, H:i', strtotime($ls['tanggal_daftar'])) : 'Waktu lalu';
                    echo "<tr><td>$tgl</td><td>Siswa baru <b>".htmlspecialchars($ls['nama'])."</b> berhasil didaftarkan.</td></tr>";
                }

                $log_tugas = mysqli_query($conn, "SELECT tugas.judul_tugas, users.nama, tugas.tanggal_dibuat FROM tugas JOIN users ON tugas.id_guru = users.id ORDER BY tugas.id DESC LIMIT 3");
                while ($lt = mysqli_fetch_assoc($log_tugas)) {
                    $ada_aktivitas = true;
                    $tgl = date('d M Y, H:i', strtotime($lt['tanggal_dibuat']));
                    echo "<tr><td>$tgl</td><td>Guru <b>".htmlspecialchars($lt['nama'])."</b> membuat tugas: <i>".htmlspecialchars($lt['judul_tugas'])."</i></td></tr>";
                }

                $log_materi = mysqli_query($conn, "SELECT materi.judul_materi, users.nama, materi.tanggal_upload FROM materi JOIN users ON materi.id_guru = users.id ORDER BY materi.id DESC LIMIT 2");
                while ($lm = mysqli_fetch_assoc($log_materi)) {
                    $ada_aktivitas = true;
                    $tgl = date('d M Y, H:i', strtotime($lm['tanggal_upload']));
                    echo "<tr><td>$tgl</td><td>Guru <b>".htmlspecialchars($lm['nama'])."</b> mengupload materi: <i>".htmlspecialchars($lm['judul_materi'])."</i></td></tr>";
                }

                if(!$ada_aktivitas) {
                    echo "<tr><td colspan='2' style='text-align:center; color:#999; padding:20px;'>Belum ada aktivitas sistem.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

<?php require '../templates/footer.php'; ?>