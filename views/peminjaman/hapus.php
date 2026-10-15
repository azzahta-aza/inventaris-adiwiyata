<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

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

$id_pinjam = filter_var(
    $_POST['id_pinjam'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id_pinjam === false || $id_pinjam === null || $id_pinjam <= 0) {
    echo "<script>
            alert('ID peminjaman tidak valid!');
            document.location.href = 'index.php';
          </script>";
    exit;
}

$hasil = delete_peminjaman($id_pinjam);

if ($hasil === 1) {
    $status = 'success';
} elseif ($hasil === -2) {
    $status = 'in_use';
} else {
    $status = 'failed';
}

header('Location: index.php?delete_status=' . $status);
exit;
