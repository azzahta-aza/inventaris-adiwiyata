<?php

include_once '../../app/app.php';

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - Sistem Inventory</title>
    <link rel="icon" type="image/png" href="../../img/logo_white.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .info-card {
            width: 100%;
            max-width: 440px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="card info-card p-4">
    <div class="card-body text-center">
        <div class="display-5 text-warning mb-3">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h4 class="fw-bold mb-2">Lupa Kata Sandi?</h4>
        <p class="text-muted mb-4">
            Untuk alasan keamanan, pemulihan atau pengaturan ulang kata sandi dapat dilakukan langsung oleh <strong>Administrator</strong> melalui menu <em>Manajemen Pengguna (Users)</em>.
        </p>

        <a href="login.php" class="btn btn-primary w-100 py-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Login
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
