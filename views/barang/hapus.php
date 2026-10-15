
<?php

include_once '../../app/app.php';

check_role(['admin', 'petugas']);

// Pastikan penghapusan hanya melalui metode POST.
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

// Validasi ID barang.
$id_barang = filter_var(
    $_POST['id_barang'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id_barang === false || $id_barang === null || $id_barang <= 0) {
    $status = 'invalid';
} else {
    $result = delete_barang($id_barang);

    if ($result === 1) {
        $status = 'success';
    } elseif ($result === -2) {
        $status = 'has_history';
    } else {
        $status = 'failed';
    }
}

// Cegah halaman hasil penghapusan tersimpan di cache.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

header('Location: index.php?delete_status=' . $status);
exit;