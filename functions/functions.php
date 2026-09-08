<?php
// Fungsi untuk memastikan halaman ini tidak bisa diakses kalau belum login
function cek_login() {
    session_start();
    if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Fungsi untuk mengecek role, misal: cek_role('admin');
function cek_role($role_yang_diizinkan) {
    if ($_SESSION['role'] != $role_yang_diizinkan) {
        // Kalau rolenya salah, lempar ke beranda
        header("Location: ../index.php");
        exit();
    }
}

// ==========================================
// L-02: Security headers (dipanggil di setiap header template)
// ==========================================
function kirim_keamanan_headers() {
    if (headers_sent()) {
        return;
    }
    header('X-Frame-Options: DENY');            // anti clickjacking
    header('X-Content-Type-Options: nosniff');  // anti MIME sniffing
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // CSP dasar: izinkan hanya domain sendiri + CDN yang dipakai templates
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com https://app.sandbox.midtrans.com 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; img-src 'self' data:; frame-src https://app.sandbox.midtrans.com; connect-src 'self' https://app.sandbox.midtrans.com;");
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ==========================================
// M-03: Rate-limiting login (per IP, file-based — cocok shared hosting)
// ==========================================
function cek_rate_limit($max_attempts = 10, $window_minutes = 15) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = sys_get_temp_dir() . '/e_madrasah_login';
    if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
    $file = $dir . '/' . md5($ip) . '.lock';
    $now  = time();
    $window = $window_minutes * 60;

    $attempts = [];
    if (file_exists($file)) {
        $attempts = @json_decode(@file_get_contents($file), true) ?: [];
    }
    // buang yang sudah lewat window
    $attempts = array_values(array_filter($attempts, function($t) use ($now, $window) {
        return ($now - $t) < $window;
    }));

    if (count($attempts) >= $max_attempts) {
        $msg = "Terlalu banyak percobaan login. Coba lagi dalam $window_minutes menit.";
        echo "<script>alert('$msg'); location.replace('../auth/login.php');</script>";
        exit();
    }
    return $file;
}

function catat_percobaan_login($file) {
    $attempts = [];
    if (file_exists($file)) {
        $attempts = @json_decode(@file_get_contents($file), true) ?: [];
    }
    $attempts[] = time();
    // simpan max ~40 untuk sesuaikan window
    $attempts = array_slice($attempts, -40);
    @file_put_contents($file, json_encode($attempts), LOCK_EX);
    @chmod($file, 0600);
}
?>