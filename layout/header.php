<?php

include_once __DIR__ . '/../app/app.php';

// Cek autentikasi login (jika bukan di halaman auth)
$current_path = $_SERVER['PHP_SELF'];
if (strpos($current_path, '/views/auth/') === false) {
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        // Fallback jika belum login (bisa dinonaktifkan atau diaktifkan)
        // header('Location: ../auth/login.php');
    }
}

$user_nama = $_SESSION['nama'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? 'User';

?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi Inventory</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .nav-link {
            font-weight: 500;
        }
    </style>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="../barang/index.php">
          <!-- <i class="bi bi-box-seam me-2 text-warning"></i>INVENTORY -->
           <img src="../../img/logo_white.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-top me-2">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav me-auto mb-2 lg-">
            <!-- Menu Barang -->
            <li class="nav-item">
              <a class="nav-link" href="../barang/index.php"><i class="me-1"></i> Barang</a>
            </li>

            <!-- Menu Kategori -->
            <li class="nav-item">
              <a class="nav-link" href="../kategori/index.php"><i class="me-1"></i> Kategori</a>
            </li>

            <!-- Menu Lokasi -->
            <li class="nav-item">
              <a class="nav-link" href="../lokasi/index.php"><i class="me-1"></i> Lokasi</a>
            </li>

            <!-- Menu Peminjaman -->
            <li class="nav-item">
              <a class="nav-link" href="../peminjaman/index.php"><i class="me-1"></i> Peminjaman</a>
            </li>

            <!-- Menu Transaksi -->
            <li class="nav-item">
              <a class="nav-link" href="../transaksi/index.php"><i class="me-1"></i> Transaksi</a>
            </li>

            <!-- Menu Dropdown Laporan -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLaporan" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="me-1"></i> Laporan
              </a>
              <ul class="dropdown-menu shadow" aria-labelledby="navbarDropdownLaporan">
                <li><a class="dropdown-item" href="../laporan/barang.php"><i class="bi bi-box me-2"></i>Laporan Barang</a></li>
                <li><a class="dropdown-item" href="../laporan/peminjaman.php"><i class="bi bi-arrow-repeat me-2"></i>Laporan Peminjaman</a></li>
                <li><a class="dropdown-item" href="../laporan/transaksi.php"><i class="bi bi-journal-check me-2"></i>Laporan Transaksi</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../laporan/kategori.php"><i class="bi bi-tags me-2"></i>Rekap Kategori</a></li>
                <li><a class="dropdown-item" href="../laporan/lokasi.php"><i class="bi bi-geo me-2"></i>Rekap Lokasi</a></li>
              </ul>
            </li>

            <!-- Menu Pengguna (Khusus Admin / Petugas) -->
            <li class="nav-item">
              <a class="nav-link" href="../users/index.php"><i class="me-1"></i> Pengguna</a>
            </li>
          </ul>

          <!-- User Info & Logout -->
          <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true) : ?>
                <span class="badge bg-secondary py-2 px-3">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user_nama); ?> (<?= htmlspecialchars($user_role); ?>)
                </span>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            <?php else : ?>
                <a href="../auth/login.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>