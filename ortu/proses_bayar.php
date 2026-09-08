<?php
/** @var mysqli $conn */

session_start();
// Panggil koneksi database dan config Midtrans
require_once '../config/db.php';
require_once '../config/midtrans.php';

 $id_ortu = $_SESSION['id'];
 $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

// Keamanan: Jika tidak ada order_id, lempar ke halaman pembayaran
if (empty($order_id)) {
    header("Location: pembayaran.php?msg=Order ID tidak ditemukan.&type=danger");
    exit();
}

// Ambil data pembayaran dari database berdasarkan order_id
// PENTING: WHERE id_ortu = '$id_ortu' supaya ortu tidak bisa bayar tagihan orang lain!
 $data = mysqli_query($conn, "SELECT p.*, u.nama as nama_siswa 
                             FROM pembayaran p 
                             JOIN users u ON p.id_siswa = u.id 
                             WHERE p.order_id = '$order_id' AND p.id_ortu = '$id_ortu'");

if (mysqli_num_rows($data) == 0) {
    header("Location: pembayaran.php?msg=Data tagihan tidak valid.&type=danger");
    exit();
}

 $p = mysqli_fetch_assoc($data);

// Jika sudah lunas, tidak perlu bayar lagi
if ($p['status'] == 'lunas') {
    header("Location: pembayaran.php?msg=Tagihan ini sudah lunas.&type=success");
    exit();
}

// ============================================
// PROSES MIDTRANS
// ============================================

// 1. Cek apakah token sudah pernah dibuat sebelumnya
 $snap_token = $p['snap_token'];

if (empty($snap_token)) {
    // 2. Susun data yang akan dikirim ke Midtrans
    $params = [
        'transaction_details' => [
            'order_id'     => $order_id,
            'gross_amount' => (int) $p['jumlah'], // Harus angka bulat tanpa titik
        ],
        'item_details' => [[
            'id'       => $order_id,
            'price'    => (int) $p['jumlah'],
            'quantity' => 1,
            'name'     => 'SPP ' . $p['bulan'] . ' - ' . $p['nama_siswa']
        ]],
        'customer_details' => [
            'first_name' => $_SESSION['nama'],
            'email'      => $_SESSION['email'] ?? 'ortu@emadrasah.com',
            'phone'      => $_SESSION['no_telp'] ?? '081234567890',
        ]
    ];

    try {
        // 3. Minta Snap Token ke Midtrans
        $snap_token = \Midtrans\Snap::getSnapToken($params);
        
        // 4. Simpan Snap Token ke database (untuk dipakai nanti jika bayar ulang)
        mysqli_query($conn, "UPDATE pembayaran SET snap_token = '$snap_token' WHERE order_id = '$order_id'");
        
    } catch (Exception $e) {
        // Jika gagal menghubungi Midtrans
        header("Location: pembayaran.php?msg=Gagal menghubungi payment gateway: " . urlencode($e->getMessage()) . "&type=danger");
        exit();
    }
}

// ============================================
// TAMPILKAN POPUP PEMBAYARAN
// ============================================
// Halaman ini HANYA berisi script untuk membuka popup Midtrans
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Memproses Pembayaran...</title>
    <!-- Panggil Snap.js dari Midtrans -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $client_key ?>"></script>
</head>
<body style="text-align: center; padding-top: 50px; font-family: sans-serif;">
    <p style="font-size: 18px; color: #555;">⏳ Memproses jendela pembayaran...</p>
    <p style="font-size: 14px; color: #999;">Jangan tutup halaman ini.</p>

    <script>
        // Jalankan popup Midtrans menggunakan token yang baru kita dapat
        window.snap.pay('<?= $snap_token ?>', {
            // Jika pembayaran sukses langsung (contoh: GoPay, QRIS yang langsung lunas)
            onSuccess: function(result) {
                window.location.href = 'invoice.php?status=success&order_id=<?= $order_id ?>';
            },
            // Jika pembayaran pending (contoh: Dapat nomor Virtual Account, belum dibayar)
            onPending: function(result) {
                window.location.href = 'invoice.php?status=pending&order_id=<?= $order_id ?>';
            },
            // Jika terjadi error
            onError: function(result) {
                window.location.href = 'invoice.php?status=error&order_id=<?= $order_id ?>';
            },
            // Jika user menutup popup secara manual
            onClose: function() {
                window.location.href = 'pembayaran.php';
            }
        });
    </script>
</body>
</html>