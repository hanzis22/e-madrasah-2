<?php
/** @var mysqli $conn */
/**
 * NOTIFIKASI WEBHOOK MIDTRANS
 * 
 * File ini DIPANGGIL OLEH SERVER MIDTRANS secara otomatis di belakang layar.
 * JANGAN dipanggil manual oleh user (tidak ada tombol yang mengarah ke sini).
 */

require_once '../config/db.php';
require_once '../config/midtrans.php';

// 1. Ambil data JSON yang dikirim oleh Midtrans
 $json_result = file_get_contents('php://input');
 $result = json_decode($json_result, true);

// 2. Ambil data untuk verifikasi keamanan
 $signature_key = $result['signature_key'];
 $order_id = mysqli_real_escape_string($conn, $result['order_id']);
 $status_code = $result['status_code'];
 $gross_amount = $result['gross_amount'];
 $server_key = \Midtrans\Config::$serverKey;

// 3. Verifikasi Signature (Keamanan)
// Kita hitung signature sendiri, lalu cocokkan dengan signature dari Midtrans
// Kalau cocok, berarti ini PASTI berasal dari Midtrans, bukan hacker
 $expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

if ($signature_key != $expected_signature) {
    // Jika signature tidak cocok, tolak!
    http_response_code(403);
    exit('Invalid Signature');
}

// 4. Proses Status Transaksi
 $transaction_status = $result['transaction_status'];
 $payment_type = mysqli_real_escape_string($conn, $result['payment_type']);
 $fraud_status = isset($result['fraud_status']) ? $result['fraud_status'] : null;

 $status_db = '';
 $paid_at = date('Y-m-d H:i:s'); // Waktu sekarang

// Logika penentuan status di database kita
if ($transaction_status == 'capture') {
    // Untuk pembayaran Kartu Kredit
    if ($fraud_status == 'accept') {
        $status_db = 'lunas';
    }
} else if ($transaction_status == 'settlement') {
    // Untuk VA, E-Wallet, QRIS yang sudah dibayar
    $status_db = 'lunas';
} else if ($transaction_status == 'pending') {
    // Untuk VA/E-Wallet yang kode-nya sudah keluar tapi belum dibayar
    $status_db = 'menunggu';
} else if ($transaction_status == 'deny') {
    // Ditolak (misal kartu kredit ditolak bank)
    $status_db = 'gagal';
} else if ($transaction_status == 'expire') {
    // Kode VA sudah kadaluarsa (tidak dibayar dalam 24 jam)
    $status_db = 'gagal';
} else if ($transaction_status == 'cancel') {
    // Dibatalkan
    $status_db = 'gagal';
}

// 5. Update Database jika statusnya berubah
if ($status_db != '') {
    
    // Jika Lunas, kita simpan waktu bayarnya (paid_at)
    if ($status_db == 'lunas') {
        $query = "UPDATE pembayaran SET status = '$status_db', metode_bayar = '$payment_type', paid_at = '$paid_at' WHERE order_id = '$order_id'";
    } else {
        // Jika gagal/menunggu, paid_at tidak diisi
        $query = "UPDATE pembayaran SET status = '$status_db', metode_bayar = '$payment_type' WHERE order_id = '$order_id'";
    }
    
    mysqli_query($conn, $query);
}

// 6. Kirim respons sukses ke Midtrans agar Midtrans tahu pesannya diterima
http_response_code(200);
echo "OK";
?>