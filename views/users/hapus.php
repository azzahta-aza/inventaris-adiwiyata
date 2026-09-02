<?php

include_once '../../app/app.php';

$id_user = (int)$_GET['id_user'];
$result = delete_user($id_user);

if ($result > 0) {
    echo "<script> 
            alert('Pengguna berhasil dihapus.');
            document.location.href = 'index.php';
          </script>";
} elseif ($result === -2) {
    echo "<script> 
            alert('Gagal! Anda tidak dapat menghapus akun yang sedang Anda gunakan saat ini.');
            document.location.href = 'index.php';
          </script>";
} else {
    echo "<script> 
            alert('Gagal menghapus pengguna.');
            document.location.href = 'index.php';
          </script>";
}
