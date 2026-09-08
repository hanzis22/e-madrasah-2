<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

/** @var mysqli $conn */
 $id_ortu = $_SESSION['id'];

// AMBIL DATA ANAK
 $anak = mysqli_query($conn, "SELECT u.*, k.nama_kelas FROM ortu_siswa os JOIN users u ON os.id_siswa = u.id LEFT JOIN kelas k ON u.id_kelas = k.id WHERE os.id_ortu = '$id_ortu'");

 $total_tagihan = 0;
 $ada_notif = false;
 $ids_anak = [];

if ($id_ortu > 0 && mysqli_num_rows($anak) > 0) {
    while($a = mysqli_fetch_assoc($anak)) { 
        $ids_anak[] = $a['id']; 
    }
    $list_ids = implode(',', $ids_anak);
    
    // Ambil daftar bulan yang statusnya masih 'menunggu'
    $data_bayar = mysqli_query($conn, "SELECT bulan FROM pembayaran WHERE id_siswa IN ($list_ids) AND status='menunggu'");
    
    $bulan_indo = [
        'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
        'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
        'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
    ];
    
    $bulan_sekarang = date('n'); 
    $tagihan_lewat = 0;
    
    if (mysqli_num_rows($data_bayar) > 0) {
        while($b = mysqli_fetch_assoc($data_bayar)) {
            $nomor_bulan = isset($bulan_indo[$b['bulan']]) ? $bulan_indo[$b['bulan']] : 0;
            $is_lewat = false;
            
            if ($nomor_bulan < $bulan_sekarang) {
                $is_lewat = true;
            } elseif ($bulan_sekarang <= 3 && $nomor_bulan >= 10) {
                $is_lewat = true;
            }
            
            if ($is_lewat) {
                $tagihan_lewat++;
            }
        }
        
        if ($tagihan_lewat > 0) {
            $ada_notif = true;
            $total_tagihan = $tagihan_lewat;
        }
    }
}

// Inisialisasi variabel statistik default (menghindari warning)
 $jml_anak = count($ids_anak);
 $stat_lunas = 0;
 $stat_menunggu = 0;
 $riwayat = false;

// Hitung statistik HANYA JIKA ada anak
if (!empty($ids_anak)) {
    $stat_lunas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE id_siswa IN ($list_ids) AND status='lunas'"))['total'];
    $stat_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE id_siswa IN ($list_ids) AND status='menunggu'"))['total'];
    $riwayat = mysqli_query($conn, "SELECT p.*, u.nama as nama_anak FROM pembayaran p JOIN users u ON p.id_siswa = u.id WHERE p.id_siswa IN ($list_ids) ORDER BY p.tanggal_bayar DESC LIMIT 3");
}
?>
<?php require '../templates/header_ortu.php'; ?>
<?php require '../templates/sidebar_ortu.php'; ?>

<!-- CSS TAMBAHAN -->
<style>
    /* 1. KARTU STATISTIK (Dihapus stat-flex, pakai block normal) */
    .card-statistik {
        border-left: 4px solid #ccc;
        border-radius: 4px;
        padding: 20px;
    }
    .sc-anak { border-left-color: #2e7d32; background-color: #f1f8e9; }
    .sc-lunas { border-left-color: #1976d2; background-color: #e3f2fd; }
    .sc-menunggu { border-left-color: #f57c00; background-color: #fff3e0; }

    .sc-anak h2 { color: #2e7d32; }
    .sc-lunas h2 { color: #1976d2; }
    .sc-menunggu h2 { color: #f57c00; }
    
    .sc-anak small, .sc-lunas small, .sc-menunggu small { color: #555 !important; }
    
    /* Angka rata kanan agar tidak mepet */
    .stat-angka { text-align: right; margin: 10px 0 0 0; }

    /* 2. TEKS STATUS */
    .status-lunas { color: #4f7c4a; font-weight: 600; }
    .status-menunggu { color: #b08a2e; font-weight: 600; }
    .status-gagal { color: #c96b6b; font-weight: 600; }
</style>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Dashboard Wali Murid</h4>
        <p style="margin:0; color:#666; font-size:14px;">Pantau keuangan dan perkembangan belajar anak Anda.</p>
    </div>

    <!-- NOTIFIKASI TAGIHAN LEWAT -->
    <?php if ($ada_notif): ?>
    <div class="alert alert-warning d-flex align-items-center">
        <span class="me-2">⚠️</span>
        <div>
            Anda memiliki <strong><?= $total_tagihan ?> tagihan</strong> untuk bulan yang sudah lewat dari jatuh tempo dan belum dibayarkan. 
            <a href="pembayaran.php" class="alert-link">Bayar Sekarang</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- KARTU STATISTIK (Tanpa stat-flex) -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card-statistik sc-anak">
                <h5 style="margin:0 0 5px 0;">Anak Didampingi</h5>
                <small>Total Anak Wali</small>
                <h2 class="stat-angka"><?= $jml_anak ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik sc-lunas">
                <h5 style="margin:0 0 5px 0;">Sudah Lunas</h5>
                <small>Total Transaksi Sukses</small>
                <h2 class="stat-angka"><?= $stat_lunas ?></h2>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card-statistik sc-menunggu">
                <h5 style="margin:0 0 5px 0;">Menunggu Bayar</h5>
                <small>Belum Ada Pembayaran</small>
                <h2 class="stat-angka"><?= $stat_menunggu ?></h2>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- KOLOM KIRI: DATA ANAK -->
        <div class="col-md-6 mb-3">
            <div class="box-putih" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; border-bottom: 1px solid #eee;">
                    <h5 style="margin:0; font-size: 16px;"><i class="bi bi-people me-2"></i>Data Anak yang Diwali</h5>
                </div>
                <table class="table table-bordered" style="margin:0; font-size: 14px;">
                    <thead style="background-color: #f9f9f9;">
                        <tr>
                            <th>Nama Anak</th>
                            <th>Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($anak, 0);
                        if (mysqli_num_rows($anak) > 0) {
                            while($a = mysqli_fetch_assoc($anak)) { ?>
                                <tr>
                                    <td><?= $a['nama'] ?></td>
                                    <td><?= $a['nama_kelas'] ?: 'Belum ada kelas' ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="2" class="text-center text-muted p-3">Belum ada data anak yang terhubung.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: RIWAYAT PEMBAYARAN TERAKHIR -->
        <div class="col-md-6 mb-3">
            <div class="box-putih" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="margin:0; font-size: 16px;"><i class="bi bi-clock-history me-2"></i>Riwayat Terakhir</h5>
                    <?php if (!empty($ids_anak)): ?>
                        <a href="riwayat.php" style="font-size: 13px; color: #2e7d32; text-decoration: none;">Lihat Semua →</a>
                    <?php endif; ?>
                </div>
                <table class="table table-bordered" style="margin:0; font-size: 14px;">
                    <thead style="background-color: #f9f9f9;">
                        <tr>
                            <th>Bulan</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($riwayat && mysqli_num_rows($riwayat) > 0) {
                            while($r = mysqli_fetch_assoc($riwayat)) { 
                                if ($r['status'] == 'lunas') $status = "<span class='status-lunas'>Lunas</span>";
                                elseif ($r['status'] == 'menunggu') $status = "<span class='status-menunggu'>Menunggu</span>";
                                else $status = "<span class='status-gagal'>Gagal</span>";
                                ?>
                                <tr>
                                    <td><?= $r['bulan'] ?></td>
                                    <td>Rp. <?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                                    <td style="text-align:center;"><?= $status ?></td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr><td colspan="3" class="text-center text-muted p-3" style="color:#999;">Belum ada riwayat pembayaran.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require '../templates/footer_ortu.php'; ?>