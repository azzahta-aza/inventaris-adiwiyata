<?php

include_once '../../app/app.php';

check_role(['admin', 'petugas']);

// Penghapusan hanya boleh melalui POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validasi token CSRF.
$csrf_token = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrf_token) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrf_token)
) {
    header('Location: index.php?delete_status=invalid');
    exit;
}

// Validasi ID kategori.
$id_kategori = filter_var(
    $_POST['id_kategori'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id_kategori === false || $id_kategori === null || $id_kategori <= 0) {
    $status = 'invalid';
} else {
    $result = delete_kategori($id_kategori);

    if ($result === 1) {
        $status = 'success';
    } elseif ($result === -2) {
        $status = 'in_use';
    } else {
        $status = 'failed';
    }
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

header('Location: index.php?delete_status=' . $status);
exit;