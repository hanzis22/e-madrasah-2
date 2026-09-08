<?php 
require '../templates/header.php'; 
require '../templates/sidebar.php'; 
/** @var mysqli $conn */

// LOGIKA FILTER
 $filter_bulan = isset($_GET['filter_bulan']) ? mysqli_real_escape_string($conn, $_GET['filter_bulan']) : '';
 $filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($conn, $_GET['filter_status']) : '';
 $filter_kelas = isset($_GET['filter_kelas']) ? mysqli_real_escape_string($conn, $_GET['filter_kelas']) : '';

 $where = "1=1";
if (!empty($filter_bulan)) $where .= " AND p.bulan = '$filter_bulan'";
if (!empty($filter_status)) $where .= " AND p.status = '$filter_status'";
if (!empty($filter_kelas)) $where .= " AND u.id_kelas = '$filter_kelas'";

// HITUNG STATISTIK
 $total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pembayaran p JOIN users u ON p.id_siswa = u.id WHERE p.status='lunas'"))['total'] ?: 0;
 $jumlah_lunas = mysqli_num_rows(mysqli_query($conn, "SELECT p.id FROM pembayaran p JOIN users u ON p.id_siswa = u.id WHERE p.status='lunas'"));
 $jumlah_nunggak = mysqli_num_rows(mysqli_query($conn, "SELECT p.id FROM pembayaran p JOIN users u ON p.id_siswa = u.id WHERE p.status='menunggu'"));
 $jumlah_gagal = mysqli_num_rows(mysqli_query($conn, "SELECT p.id FROM pembayaran p JOIN users u ON p.id_siswa = u.id WHERE p.status='gagal'"));

// AMBIL DATA PEMBAYARAN
 $data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, k.nama_kelas, o.nama as nama_ortu 
                             FROM pembayaran p
                             JOIN users u ON p.id_siswa = u.id
                             LEFT JOIN kelas k ON u.id_kelas = k.id
                             LEFT JOIN ortu_siswa os ON p.id_siswa = os.id_siswa
                             LEFT JOIN users o ON os.id_ortu = o.id
                             WHERE $where
                             ORDER BY p.tanggal_bayar DESC");
?>

<style>
.stat-flex { 
    display:flex; 
    justify-content:space-between; 
    align-items:center; 
}


.stat-angka { 
    font-size:28px; 
    font-weight:bold; 
    margin:0; 
}


.card-statistik {
    height:150px;
    display:flex;
    align-items:center;
}


/* khusus total uang masuk */
.stat-uang {
    flex-direction:column;
    align-items:flex-start;
    justify-content:center;
}


.stat-uang .stat-angka {
    margin-top:10px;
    margin-bottom:8px;
    font-size:26px;
}

.status-text {
    font-weight:600;
    font-size:13px;
}

.status-lunas {
    color:#4f7c4a;
}

.status-gagal {
    color:#c96b6b;
}

.status-menunggu {
    color:#b08a2e;
}
</style>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Laporan Keuangan SPP</h4>
        <p style="margin:0; color:#666; font-size:14px;">Pantau seluruh arus kas pembayaran SPP madrasah.</p>
    </div>

    <!-- STATISTIK KEUANGAN -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card-statistik stat-uang">

                <h5 style="margin:0 0 5px 0;">
                    Total Uang Masuk
                </h5>

                <h2 class="stat-angka" style="color:#2e7d32;">
                    Rp <?= number_format($total_masuk, 0, ',', '.') ?>
                </h2>

                <div style="border-top:1px solid #ddd; padding-top:10px; width:100%;">
                    <small style="color:#777;">
                        Transaksi Lunas
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-statistik stat-flex">
                <div>
                    <h5 style="margin:0 0 5px 0;">Lunas</h5>
                    <small style="color:#777;">Berhasil Dibayar</small>
                </div>
                <h2 class="stat-angka" style="color:#2e7d32;"><?= $jumlah_lunas ?></h2>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-statistik stat-flex">
                <div>
                    <h5 style="margin:0 0 5px 0;">Menunggu</h5>
                    <small style="color:#777;">Belum Dibayar</small>
                </div>
                <h2 class="stat-angka" style="color:#f57c00;"><?= $jumlah_nunggak ?></h2>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-statistik stat-flex">
                <div>
                    <h5 style="margin:0 0 5px 0;">Gagal/Expired</h5>
                    <small style="color:#777;">Kadaluarsa</small>
                </div>
                <h2 class="stat-angka" style="color:#c62828;"><?= $jumlah_gagal ?></h2>
            </div>
        </div>
    </div>

    <!-- FORM FILTER -->
    <div class="box-putih">
        <form method="get" action="" style="display:flex; gap:10px; align-items:flex-end; flex-wrap: wrap;">
            <div>
                <label class="form-label" style="font-size:13px;">Filter Bulan</label>
                <select name="filter_bulan" class="form-select form-select-sm" style="width: 150px;">
                    <option value="">Semua Bulan</option>
                    <?php 
                    $bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for($i=1; $i<=12; $i++) { 
                        $sel = ($filter_bulan == $bulan_indo[$i]) ? 'selected' : '';
                        echo "<option value='".$bulan_indo[$i]."' $sel>".$bulan_indo[$i]."</option>"; 
                    } 
                    ?>
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:13px;">Filter Status</label>
                <select name="filter_status" class="form-select form-select-sm" style="width: 130px;">
                    <option value="">Semua</option>
                    <option value="lunas" <?= $filter_status == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                    <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="gagal" <?= $filter_status == 'gagal' ? 'selected' : '' ?>>Gagal</option>
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:13px;">Filter Kelas</label>
                <select name="filter_kelas" class="form-select form-select-sm" style="width: 130px;">
                    <option value="">Semua Kelas</option>
                    <?php 
                    $kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                    while ($k = mysqli_fetch_assoc($kelas)) {
                        $sel = ($filter_kelas == $k['id']) ? 'selected' : '';
                        echo "<option value='".$k['id']."' $sel>".$k['nama_kelas']."</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-cari btn-sm">Terapkan</button>
            <a href="pembayaran.php" class="btn btn-outline-secondary btn-sm" style="text-decoration:none;">Reset</a>
        </form>
    </div>

    <!-- TABEL LAPORAN -->
    <div class="box-putih" style="padding: 0; overflow: hidden; margin-top: 20px;">
        <table class="table table-bordered table-hover" style="margin:0; font-size: 13px;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th width="40px">No</th>
                    <th>Tanggal</th>
                    <th>No. Invoice</th>
                    <th>Nama Siswa (Kelas)</th>
                    <th>Dibayar Oleh (Ortu)</th>
                    <th>Periode</th>
                    <th>Jumlah</th>
                    <th>Metode</th>
                    <th width="90px">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { 
                        $metode = [
                            'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'bank_transfer' => 'Transfer Bank',
                            'bca_va' => 'VA BCA', 'bni_va' => 'VA BNI', 'bri_va' => 'VA BRI', 'mandiri_bill' => 'VA Mandiri',
                            'indomaret' => 'Indomaret', 'alfamart' => 'Alfamart', 'credit_card' => 'Kartu Kredit', 'qris' => 'QRIS'
                        ];
                        $nama_metode = isset($metode[$row['metode_bayar']]) ? $metode[$row['metode_bayar']] : $row['metode_bayar'];

                        if ($row['status'] == 'lunas') { 
                            $class_status = 'status-lunas';
                            $text = 'Lunas';

                        }
                        elseif ($row['status'] == 'gagal') { 
                            $class_status = 'status-gagal';
                            $text = 'Gagal';

                        }
                        else { 
                            $class_status = 'status-menunggu';
                            $text = 'Menunggu';
                        }
                        
                        $tgl = date('d M Y, H:i', strtotime($row['tanggal_bayar']));
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $tgl ?></td>
                            <td><small><?= $row['order_id'] ?></small></td>
                            <td><?= $row['nama_siswa'] ?> <br><small class="text-muted">(<?= $row['nama_kelas'] ?: '-' ?>)</small></td>
                            <td><?= $row['nama_ortu'] ?: '-' ?></td>
                            <td><?= $row['bulan'] ?> <?= $row['tahun_ajaran'] ?></td>
                            <td><strong>Rp. <?= number_format($row['jumlah'], 0, ',', '.') ?></strong></td>
                            <td><?= $nama_metode ?></td>
                            <td style="text-align:left;">
                                <span class="status-text <?= $class_status ?>">
                                    <?= $text ?>
                                </span>
                            </td>
                        </tr>
                    <?php } 
                } else { ?>
                    <tr>
                        <td colspan="9" style="text-align:center; color:#999; padding: 30px;">Tidak ada data pembayaran.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../templates/footer.php'; ?>