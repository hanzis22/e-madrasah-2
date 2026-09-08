<?php
/** @var mysqli $conn */
session_start();
if(!isset($_SESSION['login']) || $_SESSION['role'] != 'ortu') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';
// Panggil library Dompdf
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

 $id_ortu = $_SESSION['id'];
 $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    echo "Invoice tidak ditemukan.";
    exit();
}

// Ambil data invoice dari database
 $data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa, k.nama_kelas 
                             FROM pembayaran p 
                             JOIN users u ON p.id_siswa = u.id 
                             LEFT JOIN kelas k ON u.id_kelas = k.id
                             WHERE p.order_id = '$order_id' AND p.id_ortu = '$id_ortu'");

if (mysqli_num_rows($data) == 0) {
    echo "Data invoice tidak valid.";
    exit();
}

 $p = mysqli_fetch_assoc($data);

// Mapping metode bayar
 $metode = [
    'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'bank_transfer' => 'Transfer Bank',
    'bca_va' => 'Virtual Account BCA', 'bni_va' => 'Virtual Account BNI',
    'bri_va' => 'Virtual Account BRI', 'mandiri_va' => 'Virtual Account Mandiri',
    'indomaret' => 'Indomaret', 'alfamart' => 'Alfamart', 'credit_card' => 'Kartu Kredit/Debit', 'qris' => 'QRIS'
];
 $nama_metode = isset($metode[$p['metode_bayar']]) ? $metode[$p['metode_bayar']] : ($p['metode_bayar'] ?: '-');

// Tentukan status text
if ($p['status'] == 'lunas') { $status_text = 'LUNAS'; $status_color = '#28a745'; }
elseif ($p['status'] == 'menunggu') { $status_text = 'MENUNGGU'; $status_color = '#ffc107'; }
else { $status_text = 'GAGAL'; $status_color = '#dc3545'; }

// ==========================================
// BANGUN KODE HTML UNTUK PDF
// ==========================================
// Kode HTML ini tidak pakai Bootstrap, karena Dompdf lebih baik membaca CSS murni.
 $html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .header-invoice { display: flex; justify-content: space-between; border-bottom: 3px solid #2e7d32; padding-bottom: 15px; margin-bottom: 20px; }
        .header-invoice h2 { margin: 0; color: #2e7d32; }
        .header-invoice p { margin: 5px 0 0 0; color: #777; font-size: 12px; }
        table.info { width: 100%; margin-bottom: 25px; }
        table.info td { padding: 4px 0; vertical-align: top; }
        table.info .label { width: 150px; color: #777; }
        table.total { width: 100%; border-top: 2px dashed #ccc; margin-top: 20px; }
        table.total td { padding: 15px 0; font-size: 18px; font-weight: bold; color: #2e7d32; }
        .badge-status { padding: 5px 15px; color: white; font-weight: bold; border-radius: 4px; background-color: '.$status_color.'; }
        .footer { margin-top: 50px; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header-invoice">
        <div>
            <h2>SISTEM E-MADRASAH</h2>
            <p>Bukti Pembayaran SPP</p>
        </div>
        <div style="text-align: right;">
            <span class="badge-status">'.$status_text.'</span>
        </div>
    </div>

    <table class="info">
        <tr>
            <td class="label">No. Invoice</td>
            <td>: <strong>'.$order_id.'</strong></td>
        </tr>
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: '.$p['nama_siswa'].'</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>: '.($p['nama_kelas'] ?: '-').'</td>
        </tr>
        <tr>
            <td class="label">Jenis Pembayaran</td>
            <td>: '.$p['jenis_bayar'].'</td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td>: '.$p['bulan'].' '.$p['tahun_ajaran'].'</td>
        </tr>
        <tr>
            <td class="label">Metode Bayar</td>
            <td>: '.$nama_metode.'</td>
        </tr>
        <tr>
            <td class="label">Waktu Transaksi</td>
            <td>: '.($p['paid_at'] ? date('d F Y, H:i', strtotime($p['paid_at'])) : date('d F Y, H:i', strtotime($p['tanggal_bayar']))).'</td>
        </tr>
    </table>

    <table class="total">
        <tr>
            <td>TOTAL BAYAR</td>
            <td style="text-align: right;">Rp. '.number_format($p['jumlah'], 0, ',', '.').'</td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini dicetak secara otomatis oleh Sistem E-Madrasah pada tanggal '.date('d F Y, H:i').'.<br>
        Tanda tangan atau stempel asli tidak diperlukan untuk dokumen digital yang sah.
    </div>

</body>
</html>';

// ==========================================
// PROSES CONVERT HTML KE PDF
// ==========================================
 $dompdf = new Dompdf();
 $dompdf->loadHtml($html);

// Setting ukuran kertas dan orientasi
 $dompdf->setPaper('A4', 'portrait');

// Render ke PDF
 $dompdf->render();

// Paksa browser langsung mendownload file, bukan menampilkan di browser
 $dompdf->stream('Invoice_'.$order_id.'.pdf', ["Attachment" => true]);

?>