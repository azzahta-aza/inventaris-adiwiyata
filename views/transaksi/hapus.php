
<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

// Penghapusan hanya boleh melalui POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validasi CSRF token.
$csrf_token = $_POST['csrf_token'] ?? '';
$session_token = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($csrf_token) ||
    !is_string($session_token) ||
    $session_token === '' ||
    !hash_equals($session_token, $csrf_token)
) {
    http_response_code(403);
    exit('Permintaan tidak valid. Silakan muat ulang halaman.');
}

// Validasi ID transaksi.
$id_transaksi = filter_var(
    $_POST['id_transaksi'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id_transaksi === false || $id_transaksi === null || $id_transaksi <= 0) {
    http_response_code(400);
    exit('ID transaksi tidak valid.');
}

$hasil = delete_transaksi($id_transaksi);

if ($hasil > 0) {
    $status = 'success';
} else {
    $status = 'failed';
}

header('Location: index.php?delete_status=' . $status);
exit;