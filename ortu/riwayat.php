<?php require '../templates/header_ortu.php'; ?>
<?php require '../templates/sidebar_ortu.php'; ?>

<style>
.status-text{
    font-weight: 600;
    font-size: 13px;
}

.status-lunas{
    color: #4f7c4a;
}

.status-gagal{
    color: #c96b6b;
}

.status-menunggu{
    color: #b08a2e;
}
</style>

<?php
/** @var mysqli $conn */
 $id_ortu = $_SESSION['id'];

// 1. Ambil ID semua anak yang diwali oleh orang tua ini (Bapak atau Ibu)
 $data_anak = mysqli_query($conn, "SELECT id_siswa FROM ortu_siswa WHERE id_ortu = '$id_ortu'");
 $ids_anak = [];
while($a = mysqli_fetch_assoc($data_anak)) {
    $ids_anak[] = $a['id_siswa'];
}

// 2. Jika punya anak, ambil riwayat pembayaran berdasarkan ID ANAKnya (bukan id_ortu)
if (!empty($ids_anak)) {
    $list_ids = implode(',', $ids_anak);
    
    // Diganti: WHERE p.id_siswa IN ($list_ids) 
    $riwayat = mysqli_query($conn, "SELECT p.*, u.nama as nama_anak 
                                    FROM pembayaran p
                                    JOIN users u ON p.id_siswa = u.id
                                    WHERE p.id_siswa IN ($list_ids)
                                    ORDER BY p.id DESC");
}
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Riwayat Pembayaran</h4>
        <p style="margin:0; color:#666; font-size:14px;">Catatan semua transaksi keuangan yang telah dilakukan.</p>
    </div>

    <div class="box-putih" style="padding: 0; overflow: hidden;">
        <table class="table table-bordered" style="margin:0; font-size: 14px;">
            <thead style="background-color: #f8f9fa;">
                <tr>
                    <th width="50px" style="text-align:center;">No</th>
                    <th>Nama Anak</th>
                    <th>Jenis</th>
                    <th>Bulan</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th width="100px" style="text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($riwayat) > 0) {
                    while ($row = mysqli_fetch_assoc($riwayat)) { 
                        // Warna badge berdasarkan status
                        if ($row['status'] == 'lunas') {
                            $class_status = 'status-lunas';
                            $text = '✔ Lunas';
                        } elseif ($row['status'] == 'gagal') {
                            $class_status = 'status-gagal';
                            $text = '✖ Gagal';
                        } else {
                            $class_status = 'status-menunggu';
                            $text = '◴ Menunggu';
                        }
                        
                        $tanggal = date('d M Y, H:i', strtotime($row['tanggal_bayar']));
                        ?>
                        <tr>
                            <td style="text-align:center;"><?= $no++ ?></td>
                            <td><?= $row['nama_anak'] ?></td>
                            <td><?= $row['jenis_bayar'] ?></td>
                            <td><?= $row['bulan'] ?> <?= $row['tahun_ajaran'] ?></td>
                            <td>Rp. <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                            <td><?= $tanggal ?></td>
                            <td style="text-align:left;">
                                <span class="status-text <?= $class_status ?>"><?= $text ?></span>
                            </td>
                        </tr>
                        <?php 
                    }
                } else { ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#999; padding: 30px;">
                            Belum ada riwayat pembayaran.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require '../templates/footer_ortu.php'; ?>