<?php
// ============================================
// KONFIGURASI MIDTRANS  (M-02: secrets from env, never hardcoded)
// ============================================

// Baca dari environment variable (set di server / .env). Jangan hardcode secret!
// - Production: set MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY di server env.
// - Dev/sandbox local: kosongkan (biarkan placeholder) atau set di .env.
 $server_key = getenv('MIDTRANS_SERVER_KEY') ?: 'GANTI_ME_SERVER_KEY';
 $client_key = getenv('MIDTRANS_CLIENT_KEY') ?: 'GANTI_ME_CLIENT_KEY';

// Setting koneksi ke Midtrans
 $is_production = (getenv('MIDTRANS_IS_PRODUCTION') ?: 'false') === 'true'; // false = Sandbox, true = Production

// Jangan diubah kode di bawah ini
require_once __DIR__ . '/../vendor/autoload.php';

\Midtrans\Config::$serverKey = $server_key;
\Midtrans\Config::$isProduction = $is_production;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>