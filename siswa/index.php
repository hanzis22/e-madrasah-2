<?php require '../templates/header_siswa.php'; ?>
<?php require '../templates/sidebar_siswa.php'; ?>
<?php
/** @var mysqli $conn */
 $id_siswa_login = $_SESSION['id'];
 $siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT u.*, k.nama_kelas FROM users u LEFT JOIN kelas k ON u.id_kelas = k.id WHERE u.id='$id_siswa_login'"));

  // PENGAMAN: Kalau akun siswa ini ternyata tidak ada di database (misal dihapus admin)
 if (!$siswa) {
     echo "<script>alert('Akun siswa tidak ditemukan di database. Silakan login ulang.'); window.location='../auth/logout.php';</script>";
     exit();
 }

  $id_kelas_siswa = $siswa['id_kelas'];

// HITUNG TUGAS BELUM DIKERJAKAN (SUDAH DIPERBAIKI: MENJADI pengumpulan_tugas)
 $belum_dikerjakan = 0;
if (!empty($siswa['id_kelas'])) {
    $hitung = mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas 
                                    LEFT JOIN pengumpulan_tugas ON tugas.id = pengumpulan_tugas.id_tugas AND pengumpulan_tugas.id_siswa = '$id_siswa_login'
                                    WHERE tugas.id_kelas = '{$siswa['id_kelas']}' AND pengumpulan_tugas.id IS NULL");
    $data_hitung = mysqli_fetch_assoc($hitung);
    $belum_dikerjakan = $data_hitung['total'];
}
?>

<style>
.card-statistik {
        border-left: 4px solid #ccc;
        border-radius: 4px;
    }
    .sc-tugas { 
        border-left-color: #f57c00; 
        background-color: #fff3e0; 
    }
    .sc-dikumpulkan { 
        border-left-color: #2e7d32; 
        background-color: #f1f8e9; 
    }
    .sc-materi { 
        border-left-color: #1976d2; 
        background-color: #e3f2fd; 
    }

    /* Warna angka */
    .sc-tugas h2 { color: #f57c00; }
    .sc-dikumpulkan h2 { color: #2e7d32; }
    .sc-materi h2 { color: #1976d2; }
    
    /* Warna teks kecil */
    .sc-tugas small, .sc-dikumpulkan small, .sc-materi small { 
        color: #555 !important; 
    }
</style>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Dashboard Siswa</h4>
        <p style="margin:0; color:#666; font-size:14px;">Selamat datang, <?= $siswa['nama'] ?>. Kelas: <?= $siswa['nama_kelas'] ?: 'Belum ada kelas' ?></p>
    </div>
    
    <!-- NOTIFIKASI JIKA ADA TUGAS BELUM DIKERJAKAN -->
    <?php if ($belum_dikerjakan > 0): ?>
    <div class="alert alert-warning d-flex align-items-center" role="alert" style="margin-bottom: 20px;">
        <span class="me-2">⚠️</span>
        <div>
            Kamu punya <strong><?= $belum_dikerjakan ?> tugas</strong> yang belum dikumpulkan! 
            <a href="tugas.php" class="alert-link">Lihat sekarang</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- STATISTIK DIATAS -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card-statistik stat-flex sc-tugas">
                <div>
                    <h5 style="margin:0 0 5px 0;">Tugas Aktif</h5>
                    <small style="color:#777;">Belum lewat deadline</small>
                </div>
                <?php 
                // DITAMBAH PENGAMAN: Jika tidak ada kelas, angkanya dijadikan 0
                $jml_tugas_aktif = 0;
                if (!empty($siswa['id_kelas'])) {
                    $ta = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tugas WHERE id_kelas = '{$siswa['id_kelas']}' AND deadline >= CURDATE()"));
                    $jml_tugas_aktif = $ta['total'];
                }
                ?>
                <h2 class="stat-angka"><?= $jml_tugas_aktif ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik stat-flex sc-dikumpulkan">
                <div>
                    <h5 style="margin:0 0 5px 0;">Sudah Dikumpulkan</h5>
                    <small style="color:#777;">Total pengumpulan</small>
                </div>
                <?php 
                // DITAMBAH PENGAMAN
                $jml_dikumpulkan = 0;
                $sk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengumpulan_tugas WHERE id_siswa = '$id_siswa_login'")); 
                if($sk) { // Cek apakah hasilnya tidak null
                    $jml_dikumpulkan = $sk['total'];
                }
                ?>
                <h2 class="stat-angka"><?= $jml_dikumpulkan ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik stat-flex sc-materi">
                <div>
                    <h5 style="margin:0 0 5px 0;">Materi Tersedia</h5>
                    <small style="color:#777;">Dari semua guru</small>
                </div>
                <?php 
                // DITAMBAH PENGAMAN
                $jml_materi = 0;
                if (!empty($siswa['id_kelas'])) {
                    $m = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM materi WHERE id_kelas = '{$siswa['id_kelas']}'")); 
                    if($m) {
                        $jml_materi = $m['total'];
                    }
                }
                ?>
                <h2 class="stat-angka"><?= $jml_materi ?></h2>
            </div>
        </div>
    </div>

    <!-- TABEL TUGAS TERBARU -->
    <div class="box-putih" style="padding: 0; overflow: hidden; border-radius: 8px;">
        <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
            <h5 style="margin:0; font-size: 16px;">Tugas Terbaru untuk Kelas Anda</h5>
        </div>
        <table class="table table-bordered table-hover" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th>Judul Tugas (Mapel)</th>
                    <th width="180px">Deadline</th>
                    <th width="180px">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($siswa['id_kelas'])) {
                    // QUERY BARU: Menggunakan LEFT JOIN untuk mengambil status keterlambatan
                    $data = mysqli_query($conn, "SELECT t.*, mp.nama_mapel, pt.status_keterlambatan
                                                FROM tugas t
                                                JOIN mapel mp ON t.id_mapel = mp.id
                                                LEFT JOIN pengumpulan_tugas pt ON t.id = pt.id_tugas AND pt.id_siswa = '$id_siswa_login'
                                                WHERE t.id_kelas = '{$siswa['id_kelas']}'
                                                ORDER BY t.deadline ASC LIMIT 5");
                    
                    if (mysqli_num_rows($data) > 0) {
                        while ($t = mysqli_fetch_assoc($data)) {
                            $tgl = date('d M Y, H:i', strtotime($t['deadline']));
                            
                            // LOGIKA STATUS WARNA TANPA BACKGROUND
                            if ($t['status_keterlambatan']) {
                                if ($t['status_keterlambatan'] == 'Terlambat') {
                                    $status = "<span style='color: #ef8902; font-size: 13px; font-weight: 400;'>ⓘ Terlambat</span>";
                                } else {
                                    $status = "<span style='color: #27ae60; font-size:13px; font-weight: 400;'>✓ Tepat Waktu</span>";
                                }
                            } else {
                                $status = "<span style='color: #e74c3c; font-size: 13px; font-weight: 400;'>⚠ Belum Dikerjakan</span>";
                            }
                            ?>
                            <tr>
                                <td><?= $t['judul_tugas'] ?> <i style="color:#888; font-size:12px;">(<?= $t['nama_mapel'] ?>)</i></td>
                                <td><?= $tgl ?></td>
                                <td style="text-align:left;"><?= $status ?></td>
                            </tr>
                            <?php 
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; color:#999; padding:20px;'>Belum ada tugas.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align:center; color:#999; padding:20px;'>Anda belum ditugaskan ke kelas.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../templates/footer_siswa.php'; ?>