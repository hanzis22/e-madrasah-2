<?php
// ============================================
// KONFIGURASI MIDTRANS
// ============================================

// PENTING: Ganti kode di bawah ini dengan Key kamu dari dashboard tadi!
 $server_key = 'GANTI_ME_SERVER_KEY'; // ← GANTI INI
  $client_key = 'GANTI_ME_CLIENT_KEY'; // ← GANTI INI

// Setting koneksi ke Midtrans
 $is_production = false; // false = Sandbox (latihan), true = Production (asli)

// Jangan diubah kode di bawah ini
require_once __DIR__ . '/../vendor/autoload.php';

\Midtrans\Config::$serverKey = $server_key;
\Midtrans\Config::$isProduction = $is_production;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>