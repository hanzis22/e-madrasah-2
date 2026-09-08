<?php require '../templates/header_guru.php'; ?>
<?php require '../templates/sidebar_guru.php'; ?>
<?php
/** @var mysqli $conn */
$id_guru_login = $_SESSION['id'];
?>

<style>
    /* 1. KARTU STATISTIK (Garis Samping + Pastel) */
    .card-statistik {
        border-left: 4px solid #ccc;
        border-radius: 4px;
    }
    .sc-kelas { 
        border-left-color: #2e7d32; 
        background-color: #f1f8e9; 
    }
    .sc-materi { 
        border-left-color: #1976d2; 
        background-color: #e3f2fd; 
    }
    .sc-tugas { 
        border-left-color: #f57c00; 
        background-color: #fff3e0; 
    }

    /* Warna angka */
    .sc-kelas h2 { color: #2e7d32; }
    .sc-materi h2 { color: #1976d2; }
    .sc-tugas h2 { color: #f57c00; }
    
    /* Warna teks kecil */
    .sc-kelas small, .sc-materi small, .sc-tugas small { 
        color: #555 !important; 
    }

    /* 2. VARIASI WARNA TABEL (Agar tidak putih polos) */
    .text-mapel {
        color: #2e7d32;
        font-weight: 600;
    }
    .text-deadline {
        color: #c62828;
        font-weight: 500;
        font-size: 13px;
    }
</style>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Dashboard Guru</h4>
        <p style="margin:0; color:#666; font-size:14px;">Selamat datang. Ini adalah ringkasan kelas, tugas, dan materi Anda.</p>
    </div>
    
    <!-- STATISTIK DIATAS -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card-statistik sc-kelas">
                <h5>Jumlah Kelas Diampu</h5>
                <?php 
                $kelasku = mysqli_query($conn, "SELECT * FROM mengajar WHERE id_guru='$id_guru_login'");
                ?>
                <h2><?= mysqli_num_rows($kelasku) ?></h2>
                <small>Kelas Aktif</small>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik sc-materi">
                <h5>Total Materi</h5>
                <?php $materi = mysqli_query($conn, "SELECT * FROM materi WHERE id_guru='$id_guru_login'"); ?>
                <h2><?= mysqli_num_rows($materi) ?></h2>
                <small>File PDF & Link YT</small>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik sc-tugas">
                <h5>Tugas Dibagikan</h5>
                <?php $tugas = mysqli_query($conn, "SELECT * FROM tugas WHERE id_guru='$id_guru_login'"); ?>
                <h2><?= mysqli_num_rows($tugas) ?></h2>
                <small>Aktif untuk Siswa</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- KOLOM KIRI: PENUGASAN MENGAJAR -->
        <div class="col-md-6 mb-3">
            <div class="box-putih" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
                    <h5 style="margin:0; font-size: 16px;"><i class="bi bi-mortarboard-fill me-2"></i></i>Penugasan Mengajar</h5>
                </div>
                <table class="table table-bordered" style="margin:0; font-size: 14px;">
                    <thead style="background-color: #f9f9f9;">
                        <tr>
                            <th>Mapel</th>
                            <th>Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query = "SELECT mengajar.id, mapel.nama_mapel, kelas.nama_kelas 
                                  FROM mengajar
                                  JOIN mapel ON mengajar.id_mapel = mapel.id
                                  JOIN kelas ON mengajar.id_kelas = kelas.id
                                  WHERE mengajar.id_guru = '$id_guru_login'
                                  ORDER BY kelas.nama_kelas ASC";
                          
                        $data_mengajar = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($data_mengajar) > 0) {
                            while ($row = mysqli_fetch_assoc($data_mengajar)) { ?>
                                <tr>
                                    <td><?= $row['nama_mapel'] ?></td>
                                    <td><?= $row['nama_kelas'] ?></td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="2" style="text-align:center; color:#999; padding: 20px;">
                                    Anda belum ditugaskan mengajar.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: TUGAS TERBARU YANG DIBAGIKAN -->
        <div class="col-md-6 mb-3">
            <div class="box-putih" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
                    <h5 style="margin:0; font-size: 16px;"><i class="bi bi-clock-history me-2"></i>Tugas Terbaru Dibagikan</h5>
                </div>
                <table class="table table-bordered" style="margin:0; font-size: 14px;">
                    <thead style="background-color: #f9f9f9;">
                        <tr>
                            <th>Judul Tugas</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $data_tugas = mysqli_query($conn, "SELECT judul_tugas, deadline FROM tugas WHERE id_guru='$id_guru_login' ORDER BY id DESC LIMIT 5");
                        
                        if (mysqli_num_rows($data_tugas) > 0) {
                            while ($t = mysqli_fetch_assoc($data_tugas)) { 
                                $deadline = date('d M Y', strtotime($t['deadline']));
                                ?>
                                <tr>
                                    <td><?= $t['judul_tugas'] ?></td>
                                    <td><?= $deadline ?></td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="2" style="text-align:center; color:#999; padding: 20px;">
                                    Belum ada tugas.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require '../templates/footer.php'; ?>