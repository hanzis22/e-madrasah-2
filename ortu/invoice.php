<?php 
require '../templates/header_ortu.php';
require '../templates/sidebar_ortu.php'; 
?>

<style>
.bg-sage {
    background-color: rgba(156, 175, 136, 0.25);
    color: #556B45;
}

.bg-soft-warning {
    background-color: rgba(255, 220, 120, 0.3);
    color: #8a6d00;
}

.bg-soft-danger {
    background-color: rgba(255, 120, 120, 0.25);
    color: #b33a3a;
}

.badge {
    padding: 8px 12px;
    border-radius: 10px;
}
</style>

<?php
/** @var mysqli $conn */
 $id_ortu = $_SESSION['id'];
 $status_url = isset($_GET['status']) ? $_GET['status'] : '';
 $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

// Keamanan: Jika tidak ada order_id, kembalikan ke pembayaran
if (empty($order_id)) {
    header("Location: pembayaran.php");
    exit();
}

// Ambil data pembayaran berdasarkan order_id
 $data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, k.nama_kelas 
                             FROM pembayaran p 
                             JOIN users u ON p.id_siswa = u.id 
                             LEFT JOIN kelas k ON u.id_kelas = k.id
                             WHERE p.order_id = '$order_id' AND p.id_ortu = '$id_ortu'");

if (mysqli_num_rows($data) == 0) {
    header("Location: pembayaran.php");
    exit();
}

 $p = mysqli_fetch_assoc($data);

// ==========================================
// TAMBAHAN: CEK STATUS REALTIME KE MIDTRANS
// (Mengatasi localhost yang tidak bisa diakses Webhook)
// ==========================================
if ($p['status'] == 'menunggu') {
    require_once '../config/midtrans.php';
    try {
        // Tanya ke Midtrans: Apa status order_id ini?
        $status_midtrans = \Midtrans\Transaction::status($order_id);
        
            /** @var \stdClass $status_midtrans */

        // Jika Midtrans bilang SUDAH DIBAYAR
        if ($status_midtrans->transaction_status == 'settlement' || $status_midtrans->transaction_status == 'capture') {
            $paid_at = date('Y-m-d H:i:s');
            $metode = $status_midtrans->payment_type;
            
            // Update database lokal kita jadi LUNAS
            mysqli_query($conn, "UPDATE pembayaran SET status='lunas', metode_bayar='$metode', paid_at='$paid_at' WHERE order_id='$order_id'");
            
            // Refresh halaman sekali agar langsung berubah ke tampilan Lunas
            echo "<script>location.reload();</script>";
            exit();
        } 
        // Jika Midtrans bilang GAGAL / KADALUARSA
        elseif (in_array($status_midtrans->transaction_status, ['expire', 'deny', 'cancel'])) {
            $metode = $status_midtrans->payment_type;
            mysqli_query($conn, "UPDATE pembayaran SET status='gagal', metode_bayar='$metode' WHERE order_id='$order_id'");
            
            echo "<script>location.reload();</script>";
            exit();
        }
    } catch (Exception $e) {
        // Jika error (misal komputer lagi tidak ada internet), biarkan saja, tidak error di layar
    }
}
// ==========================================

// Menentukan tampilan berdasarkan STATUS DATABASE (bukan status dari URL, untuk keamanan)
if ($p['status'] == 'lunas') {
    $icon = '✅';
    $judul = 'Pembayaran Berhasil!';
    $warna = '#28a745';
    $deskripsi = 'Pembayaran SPP telah berhasil diproses. Terima kasih.';
} elseif ($p['status'] == 'menunggu') {
    $icon = '⏳';
    $judul = 'Menunggu Pembayaran';
    $warna = '#ffc107';
    $deskripsi = 'Silakan selesaikan pembayaran sebelum batas waktu yang ditentukan.';
} else {
    $icon = '❌';
    $judul = 'Pembayaran Gagal / Kadaluarsa';
    $warna = '#dc3545';
    $deskripsi = 'Pembayaran tidak berhasil atau melewati batas waktu. Silakan coba lagi.';
}

// Mapping nama metode bayar dari Midtrans agar rapi
 $metode = [
    'gopay' => 'GoPay',
    'shopeepay' => 'ShopeePay',
    'bank_transfer' => 'Transfer Bank',
    'bca_va' => 'Virtual Account BCA',
    'bni_va' => 'Virtual Account BNI',
    'bri_va' => 'Virtual Account BRI',
    'mandiri_va' => 'Virtual Account Mandiri',
    'permata_va' => 'Virtual Account Permata',
    'indomaret' => 'Indomaret',
    'alfamart' => 'Alfamart',
    'credit_card' => 'Kartu Kredit/Debit',
    'qris' => 'QRIS'
];

 $nama_metode = isset($metode[$p['metode_bayar']]) ? $metode[$p['metode_bayar']] : ($p['metode_bayar'] ?: 'Belum dipilih');
?>

<div class="isi-halaman">
    <!-- Kontainer dibatasi lebarnya supaya lebih rapi seperti struk -->
    <div style="max-width: 600px; margin: 0 auto;">
        
        <!-- BOX STATUS -->
        <div class="box-putih" style="text-align: center; border-top: 4px solid <?= $warna ?>; padding: 40px;">
            <div style="font-size: 60px; margin-bottom: 15px;"><?= $icon ?></div>
            <h4 style="margin: 0 0 10px 0; color: <?= $warna ?>;"><?= $judul ?></h4>
            <p style="color: #666; margin: 0;"><?= $deskripsi ?></p>
        </div>

        <!-- BOX DETAIL INVOICE -->
        <div class="box-putih" style="margin-top: 20px;">
            <h5 style="margin: 0 0 20px 0; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                📄 Detail Invoice
            </h5>
            
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0; color: #888; width: 150px;">No. Invoice</td>
                    <td style="padding: 8px 0; font-weight: 600;"><?= $order_id ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #888;">Nama Siswa</td>
                    <td style="padding: 8px 0;"><?= $p['nama_siswa'] ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #888;">Kelas</td>
                    <td style="padding: 8px 0;"><?= $p['nama_kelas'] ?: '-' ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #888;">Jenis Pembayaran</td>
                    <td style="padding: 8px 0;"><?= $p['jenis_bayar'] ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #888;">Periode</td>
                    <td style="padding: 8px 0;"><?= $p['bulan'] ?> <?= $p['tahun_ajaran'] ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #888;">Metode Bayar</td>
                    <td style="padding: 8px 0;"><?= $nama_metode ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #888;">Waktu Transaksi</td>
                    <td style="padding: 8px 0;">
                        <?= $p['paid_at'] ? date('d F Y, H:i', strtotime($p['paid_at'])) : date('d F Y, H:i', strtotime($p['tanggal_bayar'])) ?>
                    </td>
                </tr>
                
                <!-- Garis pemisah total -->
                <tr style="border-top: 2px dashed #ccc;">
                    <td style="padding: 15px 0; color: #333; font-weight: 700; font-size: 16px;">Total Bayar</td>
                    <td style="padding: 15px 0; font-weight: 700; font-size: 20px; color: #1565c0;">
                        Rp. <?= number_format($p['jumlah'], 0, ',', '.') ?>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 8px 0; color: #888;">Status</td>
                    <td style="padding: 8px 0;">
                        <?php if ($p['status'] == 'lunas'): ?>
                            <span class="badge bg-sage" style="font-size: 14px; padding: 8px 15px;">LUNAS</span>
                        <?php elseif ($p['status'] == 'menunggu'): ?>
                            <span class="badge bg-soft-warning" style="font-size: 14px; padding: 8px 15px;">MENUNGGU</span>
                        <?php else: ?>
                            <span class="badge bg-soft-danger" style="font-size: 14px; padding: 8px 15px;">GAGAL</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TOMBOL Aksi -->
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <a href="riwayat.php" class="btn btn-cari flex-fill" style="padding: 12px; text-align:center; text-decoration:none;"><i class="bi bi-file-earmark-fill me-2"></i>Lihat Riwayat</a>
            <a href="download_invoice.php?order_id=<?= $order_id ?>" class="btn btn-outline-secondary flex-fill" style="padding: 12px; text-align:center; text-decoration:none;"><i class="bi bi-cloud-download-fill me-2"></i>Download Invoice</a>
        </div>
        
    </div>
</div>

<?php require '../templates/footer_ortu.php'; ?>