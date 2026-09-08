<?php
// ============================================================
// H-04 FIX (FULL): Secure file download endpoint.
// Semua file dari folder uploads/ TIDAK boleh diakses langsung
// lewat URL. Link aplikasi diarahkan ke sini, yang:
//   1) Wajib login (semua akses file butuh sesi aktif).
//   2) Menolak path traversal (../, absolute path).
//   3) Ekstensi berbahaya (.php, .phtml, .phar, dll) disajikan
//      SEBAGAI DOWNLOAD (application/octet-stream), BUKAN
//      dieksekusi oleh server — mencegah RCE via upload.
//   4) Mencegah banner type-sniffing (X-Content-Type-Options).
// ============================================================

require_once __DIR__ . '/functions/functions.php';

// Login wajib — pakai cek manual karena cek_login() redirect ke ../auth
// (cocok untuk file di subfolder, TAPI download.php ada di root).
session_start();
if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
    header("Location: auth/login.php");
    exit();
}

$base = __DIR__ . '/uploads/';

// Ambil file request. Dukungan ?f=path (baru) & ?file= (backward).
$f = $_GET['f'] ?? $_GET['file'] ?? '';
if (is_array($f)) $f = '';

// 1) Path traversal protection: izinkan subfolder sah di dalam uploads/
//    (mis. "tugas/namafile.pdf", "profil/gambar.jpg"), TAPI tolak
//    "..", dan jangan izinkan lompat ke luar uploads/.
$f = str_replace('\\', '/', $f);          // normalisasi backslash
if (str_contains($f, '..') || str_starts_with($f, '/') || $f === '') {
    http_response_code(400);
    echo "Bad request: invalid file parameter.";
    exit;
}

// 2) Path absolut & validasi file.
$target = $base . $f;
$real   = realpath($target);
$baseReal = realpath($base);
if ($real === false || $baseReal === false || strpos($real, $baseReal) !== 0 || !is_file($real)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// 3) Map ekstensi -> ekstensi berbahaya yang HARUS disajikan sebagai
//    download, bukan dieksekusi/banner HTML. Defense in depth terhadap RCE.
$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
$dangerous = ['php'=>'application/octet-stream','php3'=>'application/octet-stream',
    'php4'=>'application/octet-stream','php5'=>'application/octet-stream',
    'php7'=>'application/octet-stream','phtml'=>'application/octet-stream',
    'pht'=>'application/octet-stream','phar'=>'application/octet-stream',
    'pl'=>'text/plain','py'=>'text/plain','cgi'=>'text/plain','sh'=>'text/plain',
    'asp'=>'text/plain','aspx'=>'text/plain','jsp'=>'text/plain','htaccess'=>'text/plain'];

// 4) Tentukan Content-Type: berbahaya -> octet-stream; lain -> MIME via finfo.
if (isset($dangerous[$ext])) {
    $ctype = $dangerous[$ext];
    $asDownload = true;
} else {
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $ctype = $finfo ? finfo_file($finfo, $real) : 'application/octet-stream';
    if ($finfo) finfo_close($finfo);
    // Gambar (JPEG/PNG/GIF/SVG?) boleh di-render langsung untuk <img>.
    $asDownload = !in_array($ext, ['jpg','jpeg','png','gif','webp']);
}

// 5) Serve file.
header('X-Content-Type-Options: nosniff');
if ($asDownload) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . rawurlencode(basename($real)) . '"');
} else {
    header('Content-Type: ' . $ctype);
    // Cache aman untuk gambar profil/logo di halaman.
    header('Cache-Control: public, max-age=3600');
}
header('Content-Length: ' . filesize($real));
header('X-Content-Type: ' . $ctype);

// H-04 FIX: file .php/.phtml/.phar DIPAKSA disajikan sebagai download
// octet-stream (bukan dieksekusi). readfile() mengirim isi MENTAH file,
// sehingga content-type sudah octet-stream + Content-Disposition attachment.
// Tidak ada parse/eksekusi karena file tidak masuk ke PHP interpreter.
readfile($real);
exit;