<?php 
require_once '../functions/functions.php';
cek_login();
cek_role('guru');
kirim_keamanan_headers();

/** @var mysqli $conn */
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guru - E-Madrasah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0;
            color: #2c3e50;
        }
        
        /* HEADER ATAS */
           .header-atas {
            background-color: #2e7d32; 
            color: white;
            padding: 18px 30px; /* Header */
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            font-size: 15px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-kiri {
            display: flex;
            align-items: center;
            gap: 15px; /* Jarak antara logo dan tulisan */
            letter-spacing: 0.5px;
        }
        /* Styling untuk Logo Bulat */
        .logo-header {
            width: 40px; 
            height: 40px;
            background-color: white; /* Warna putih kalau gambar belum dimasukkan */
            border-radius: 50%;
            object-fit: cover;
        }
        /* Styling untuk Tombol Logout (Cuma Garis Putih) */
        .btn-logout {
            border: 1px solid rgba(255,255,255,0.5); /* garis putih transparan */
            background-color: transparent; /* Tanpa warna background */
            color: white;
            padding: 6px 18px;
            border-radius: 20px; /* melengkung */
            text-decoration: none;
            font-size: 13px;
            margin-left: 20px;
            transition: all 0.2s ease;
        }
        .btn-logout:hover {
            background-color: white; /* berubah putih saat disentuh mouse */
            color: #27ae60;
        }

        /* KONTAINER UTAMA */
        .kontainer-utama {
            display: flex;
            min-height: calc(100vh - 76px);
        }
        .menu-samping {
            width: 220px;
            background-color: #ffffff; 
            border-right: 1px solid #e1e4e8;
            padding-top: 15px;
        }
        .menu-samping a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: #5f6c7b;
            border-bottom: 1px solid #f4f6f9;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease; /*aminasi halus */
            border-left: 3px solid transparent; /* Area khusus buat garis indikator */
        }
        .menu-samping a:hover, .menu-samping a.aktif {
            background-color: #f0fdf4; 
            color: #27ae60;
            font-weight: 600;
            border-left: 3px solid #27ae60; /* Garis hijau di kiri saat aktif */
        }
        .isi-halaman {
            flex: 1;
            padding: 25px 30px;
            background-color: #f4f6f9;
        }
        
        /* BOX UNTUK KONTEN */
        .box-putih {
            background-color: #ffffff;
            border: none;
            border-radius: 8px; /* Buat sudutnya melengkung halus */
            padding: 20px 25px; 
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03); /* Bayangan sangat samar seolah mengambang tipis */
        }
        
        /* TABEL */
        .table { 
            color: #5f6c7b; 
            margin-bottom: 0 !important; 
        }
        .table thead th { 
            background-color: #f8f9fa !important; 
            color: #5f6c7b; 
            font-weight: 600; 
            border-bottom-width: 1px !important; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .table td { 
            font-size: 14px; 
            vertical-align: middle; 
            border-bottom: 1px solid #f4f6f9; 
        }
        .table-hover tbody tr:hover { 
            background-color: #f0fdf4 !important; /* Hijau sangat muda saat hover */
        } 
        
        /* STATISTIK KARTU */
        .stat-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .card-statistik { 
            background-color: #ffffff; 
            border: none; 
            border-radius: 8px; 
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .stat-angka { 
            font-size: 36px; 
            font-weight: 700; 
            color: #2c3e50; 
            margin: 0; 
        }

        /* TEMA TOMBOL */
        /* 1. Tombol Cari & Reset (Hijau Tua) */
        .btn-cari {
            background-color: #2e7d32;
            border-color: #2e7d32;
            color: white;
        }
        .btn-cari:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
            color: white;
        }

        /* 2. Tombol Edit (Hijau-Sage) */
        .btn-sage {
            background-color: #e8f5e9; /* Background sage muda */
            border: 1px solid #a5d6a7; /* Garis sage */
            color: #2e7d32;            /* Tulisan hijau tua biar jelas */
            font-size: 13px;
        }
        .btn-sage:hover {
            background-color: #c8e6c9;
            border-color: #81c784;
            color: #1b5e20;
        }

        /* 3. Tombol Hapus (Merah-Soft) */
        .btn-red {
            background-color: #ffebee; /* Background merah muda */
            border: 1px solid #ef9a9a; /* Garis merah */
            color: #c62828;            /* Tulisan merah tua biar jelas */
            font-size: 13px;
        }
        .btn-red:hover {
            background-color: #ffcdd2;
            border-color: #e57373;
            color: #b71c1c;
        }

        /* 4. Tombol Simpan & Batal (Hijau Tua) */
        .btn-simpan {
            background-color: #2e7d32;
            border-color: #2e7d32;
            color: white;
        }
        .btn-simpan:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
            color: white;
        }

        /* 4. Tombol Biru (Soft Blue) */
        .btn-blue {
            background-color: #e3f2fd; /* Background biru muda */
            border: 1px solid #90caf9; /* Garis biru */
            color: #1565c0;            /* Tulisan biru tua biar jelas */
            font-size: 13px;
        }

        .btn-blue:hover {
            background-color: #bbdefb;
            border-color: #64b5f6;
            color: #0d47a1;
        }

        /* 5. Tombol Putih Transparan (Pratinjau) */
        .btn-black-soft {
            background-color: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.8);
            color: #6c757d;
            font-size: 13px;
            backdrop-filter: blur(4px);
        }

        .btn-black-soft:hover {
            background-color: rgba(255, 255, 255, 0.85);
            border-color: rgba(0, 0, 0, 1);
            color: #495057;
        }

        /* 6. Tombol Batal */       
        .btn-batal {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #7f8c8d;
            padding: 6px 14px;
            white-space: nowrap;
        }

        .btn-batal:hover {
            background-color: #e9ecef;
            color: #495057;
        }

    </style>
</head>
<body>

    <div class="header-atas">
        <!-- SEBELAH KIRI: Logo + Tulisan -->
        <div class="header-kiri">

            <img src="../uploads/logo-madrasah.png" class="logo-header" alt="Logo">
            
            <b>SISTEM E-MADRASAH</b>
        </div>
        
        <!-- SEBELAH KANAN: Teks + Tombol Logout Garis -->
        <div style="display: flex; align-items: center; font-size: 14px;">
    <?php 
                $foto_fisik = '../uploads/profil/' . ($_SESSION['foto'] ?? '');
                if (empty($_SESSION['foto']) || !file_exists($foto_fisik)) {
                    $path_foto_header = '../assets/img/default.png';
                } else {
                    $path_foto_header = '../download.php?f=profil/' . rawurlencode($_SESSION['foto']); // H-04 FIX
                }
                ?>
    <img src="<?= $path_foto_header ?>" alt="Foto Profil" 
         style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.7); margin-right: 10px;">
    Hallo, <b><?= $_SESSION['nama'] ?></b> <small class="ms-2 opacity-75">(Guru)</small>
    <a href="../auth/logout.php" class="btn-logout">Logout ➜]</a>
        </div>
    </div>

    <div class="kontainer-utama">