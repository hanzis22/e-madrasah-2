<?php
session_start();
// Kalau sudah login, suruh balik ke index
if (isset($_SESSION['login'])) {
    header("Location: ../index.php");
    exit();
}

 $msg = "";
if (isset($_GET['msg'])) {
    if($_GET['msg'] == 'gagal') $msg = "<div class='alert alert-danger'>Username atau password salah!</div>";
    if($_GET['msg'] == 'denied') $msg = "<div class='alert alert-warning'>Anda harus login terlebih dahulu!</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login E-Madrasah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Login</h3>
                        <?= $msg ?>
                        <form action="proses_login.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-success w-100">Masuk</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>