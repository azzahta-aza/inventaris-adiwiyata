<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

http_response_code(403);

$user_role = strtolower(trim($_SESSION['role'] ?? ''));
$user_nama = $_SESSION['nama'] ?? 'Pengguna';

// Tentukan tujuan redirect halaman utama sesuai role
$landing_url = '../auth/login.php';
if ($user_role === 'admin') {
    $landing_url = '../barang/index.php';
} elseif ($user_role === 'petugas') {
    $landing_url = '../transaksi/index.php';
} elseif ($user_role === 'kepala') {
    $landing_url = '../laporan/barang.php';
}

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden - Akses Ditolak</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            max-width: 520px;
            width: 100%;
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .icon-forbidden {
            font-size: 4.5rem;
            color: #dc3545;
        }
    </style>
</head>
<body>

<div class="card error-card text-center p-4">
    <div class="card-body">
        <div class="icon-forbidden mb-3">
            <i class="bi bi-shield-x"></i>
        </div>
        <h1 class="h3 fw-bold text-danger mb-2">403 - Akses Ditolak</h1>
        <h6 class="text-secondary mb-3">Forbidden Access</h6>
        <p class="text-muted mb-4">
            Halo <strong><?= htmlspecialchars($user_nama); ?></strong> (Role: <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($user_role ?: 'Guest')); ?></span>),
            Anda tidak memiliki izin/hak akses untuk membuka halaman atau menjalankan tindakan ini.
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php if (!empty($user_role)) : ?>
                <a href="<?= $landing_url; ?>" class="btn btn-primary px-4">
                    <i class="bi bi-house-door me-1"></i> Halaman Utama Saya
                </a>
                <a href="../auth/logout.php" class="btn btn-outline-danger px-3" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            <?php else : ?>
                <a href="../auth/login.php" class="btn btn-primary px-4">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Ke Halaman Login
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 text-muted small">
        Sistem Manajemen Inventaris & Sarana Prasarana
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
