<?php require '../templates/header_ortu.php'; ?>
<?php require '../templates/sidebar_ortu.php'; ?>
<?php
/** @var mysqli $conn */
 $id_ortu = $_SESSION['id'];
 $anak = mysqli_query($conn, "SELECT u.* FROM ortu_siswa os JOIN users u ON os.id_siswa = u.id WHERE os.id_ortu = '$id_ortu'");

// Siapkan pesan notifikasi (jika ada)
 $pesan = isset($_GET['msg']) ? $_GET['msg'] : '';
 $tipe = isset($_GET['type']) ? $_GET['type'] : '';
?>

<div class="isi-halaman">
    <div style="margin-bottom: 20px;">
        <h4 style="margin:0 0 5px 0;">Pembayaran SPP</h4>
        <p style="margin:0; color:#666; font-size:14px;">Bayar SPP anak Anda secara online melalui berbagai metode pembayaran.</p>
    </div>

    <!-- NOTIFIKASI SUKSES / GAGAL -->
    <?php if ($pesan != ''): ?>
    <div class="alert alert-<?= ($tipe == 'success') ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- INFO METODE PEMBAYARAN -->
    <div class="box-putih" style="border-left: 4px solid #28a745; margin-bottom: 20px;">
        <h6 style="margin:0 0 10px 0; color:#28a745;">Metode Pembayaran Tersedia</h6>
        <div class="row" style="font-size: 13px; color: #666;">
            <div class="col-md-3 mb-2">
                <strong>Virtual Account</strong><br>BCA, BNI, BRI, Mandiri
            </div>
            <div class="col-md-3 mb-2">
                <strong>E-Wallet</strong><br>GoPay, ShopeePay, DANA
            </div>
            <div class="col-md-3 mb-2">
                <strong>Minimarket</strong><br>Indomaret, Alfamart
            </div>
            <div class="col-md-3 mb-2">
                <strong>Kartu</strong><br>Visa, Mastercard
            </div>
        </div>
    </div>

    <!-- FORM PEMBAYARAN -->
    <div class="box-putih">
        <h5 style="margin:0 0 15px 0;">Form Pembayaran</h5>
        <form method="post" action="">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pilih Anak</label>
                    <select name="id_siswa" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Anak --</option>
                        <?php while($a = mysqli_fetch_assoc($anak)) { ?>
                            <option value="<?= $a['id'] ?>"><?= $a['nama'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Bulan Bayar</label>
                    <select name="bulan" class="form-select form-select-sm" required>
                        <option value="">-- Pilih --</option>
                        <?php 
                        $bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        for($i=1; $i<=12; $i++) { 
                            echo "<option value='".$bulan_indo[$i]."'>".$bulan_indo[$i]."</option>"; 
                        } 
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" class="form-control form-control-sm" placeholder="150000" min="1000" required>
                </div>
                <div class="col-md-2 mb-3" style="display:flex; align-items:flex-end;">
                    <button type="submit" name="bayar" class="btn btn-cari btn-sm w-100"><i class="bi bi-wallet2 me-2"></i>Bayar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- DAFTAR TAGIHAN YANG BELUM DIBAYAR -->
    <div class="box-putih" style="margin-top: 20px;">
        <h5 style="margin:0 0 15px 0;">Tagihan Menunggu Pembayaran</h5>
        <?php
        // Ambil lagi data anak untuk keperluan tabel bawah
        $anak_tagihan = mysqli_query($conn, "SELECT u.id FROM ortu_siswa os JOIN users u ON os.id_siswa = u.id WHERE os.id_ortu = '$id_ortu'");
        $ids_anak = [];
        while($a = mysqli_fetch_assoc($anak_tagihan)) {
            $ids_anak[] = $a['id'];
        }
        
        if (!empty($ids_anak)) {
            $list_ids = implode(',', $ids_anak);
            $tagihan = mysqli_query($conn, "SELECT p.*, u.nama as nama_anak 
                                           FROM pembayaran p 
                                           JOIN users u ON p.id_siswa = u.id 
                                           WHERE p.id_siswa IN ($list_ids) AND p.status='menunggu' 
                                           ORDER BY p.tanggal_bayar DESC");
            
            if (mysqli_num_rows($tagihan) > 0) {
                ?>
                <table class="table table-bordered table-sm" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>Anak</th>
                            <th>Bulan</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t = mysqli_fetch_assoc($tagihan)) { 
                            // Tombol Bayar Ulang hanya muncul jika ada snap_token dari Midtrans
                            if ($t['snap_token']) {
                                $tombol = '<button type="button" onclick="bayarUlang(\''.$t['snap_token'].'\')" class="btn btn-sage btn-sm"><i class="bi bi-arrow-counterclockwise me-2"></i> Bayar Ulang</button>';
                            } else {
                                $tombol = '<span class="text-muted">Menunggu diproses</span>';
                            }
                            ?>
                            <tr>
                                <td><?= $t['nama_anak'] ?></td>
                                <td><?= $t['bulan'] ?></td>
                                <td>Rp. <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
                                <td><?= $tombol ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<p class="text-muted mb-0" style="margin:0;">Tidak ada tagihan yang menunggu.</p>';
            }
        }
        ?>
    </div>
</div>

<!-- SCRIPT JAVASCRIPT MIDTRANS (Snap.js) -->
<!-- GANTI XXXXX di bawah dengan Client Key kamu -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-FIGWZjCGNiK6eNNC"></script>

<script>
// Fungsi ini dipanggil jika user klik "Bayar Ulang" di tabel bawah
function bayarUlang(snapToken) {
    window.snap.pay(snapToken, {
        onSuccess: function(result) {
            // Jika bayar langsung sukses (seperti GoPay)
            window.location.href = 'invoice.php?status=success&order_id=' + result.order_id;
        },
        onPending: function(result) {
            // Jika dapat kode VA / QRIS tapi belum dibayar
            window.location.href = 'invoice.php?status=pending&order_id=' + result.order_id;
        },
        onError: function(result) {
            // Jika error
            window.location.href = 'invoice.php?status=error';
        },
        onClose: function() {
            // Jika user menutup popup pembayaran
            // Tidak melakukan apa-apa, biarkan di halaman ini
        }
    });
}
</script>

<?php 
// PROSES KETIKA TOMBOL BAYAR DIKLIK
if (isset($_POST['bayar'])) {
    $id_siswa = mysqli_real_escape_string($conn, $_POST['id_siswa']);
    $bulan = mysqli_real_escape_string($conn, $_POST['bulan']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    
    // 1. Cek apakah bulan ini sudah LUNAS
    $cek_lunas = mysqli_query($conn, "SELECT * FROM pembayaran WHERE id_siswa='$id_siswa' AND bulan='$bulan' AND status='lunas'");
    if (mysqli_num_rows($cek_lunas) > 0) {
        echo "<script>window.location.href='pembayaran.php?msg=SPP bulan $bulan sudah lunas!&type=danger';</script>";
        exit();
    }
    
    // 2. Cek apakah bulan ini sudah ADA DI DATABASE tapi statusnya MENUNGGU
    $cek_menunggu = mysqli_query($conn, "SELECT order_id, snap_token FROM pembayaran WHERE id_siswa='$id_siswa' AND bulan='$bulan' AND status='menunggu'");
    
    if (mysqli_num_rows($cek_menunggu) > 0) {
        $data_menunggu = mysqli_fetch_assoc($cek_menunggu);
        
        // Kalau sudah punya snap token, arahkan ke proses_bayar.php untuk membuka popupnya langsung
        if (!empty($data_menunggu['snap_token'])) {
            echo "<script>window.location.href='proses_bayar.php?order_id=" . $data_menunggu['order_id'] . "';</script>";
            exit();
        }
        
        // Kalau belum punya snap token (misal gagal saat generate sebelumnya), pakai order_id yang lama
        $order_id = $data_menunggu['order_id'];
    } else {
        // 3. Kalau belum ada sama sekali, buat data baru di database
        
        // Buat Kode Invoice Unik (Contoh: SPP-20241015-A1B2C3D4)
        $order_id = 'SPP-' . date('Ymd') . '-' . strtoupper(substr(md5(time()), 0, 8));
        
        mysqli_query($conn, "INSERT INTO pembayaran (order_id, id_ortu, id_siswa, bulan, tahun_ajaran, jumlah, status, jenis_bayar) 
                            VALUES ('$order_id', '$id_ortu', '$id_siswa', '$bulan', '2024/2025', '$jumlah', 'menunggu', 'SPP')");
    }
    
    // 4. Arahkan ke file PROSES_BAYAR.PHP (akan kita buat di langkah 5)
    echo "<script>window.location.href='proses_bayar.php?order_id=$order_id';</script>";
    exit();
}
?>
<?php require '../templates/footer_ortu.php'; ?>