<?php
// get_siswa.php — FIXED (C-02): added auth check + parameterized query.
require '../config/db.php';
/** @var mysqli $conn */

// FIX: require a logged-in guru role before doing anything.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    // Not logged in / not a guru -> reject.
    http_response_code(403);
    echo '<option value="">-- Akses Ditolak --</option>';
    exit();
}

if (isset($_POST['id_kelas'])) {
    // FIX: use a prepared statement instead of string interpolation.
    $stmt = mysqli_prepare($conn, "SELECT id, nama FROM users WHERE id_kelas = ? AND role = 'siswa' ORDER BY nama ASC");
    if ($stmt === false) {
        http_response_code(500);
        echo '<option value="">-- Database error --</option>';
        exit();
    }
    mysqli_stmt_bind_param($stmt, 's', $_POST['id_kelas']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    echo "<option value=''>-- Pilih Siswa --</option>";
    while ($s = mysqli_fetch_assoc($res)) {
        echo "<option value='" . htmlspecialchars($s['id'], ENT_QUOTES) . "'>" . htmlspecialchars($s['nama'], ENT_QUOTES) . "</option>";
    }
    mysqli_stmt_close($stmt);
}
?>