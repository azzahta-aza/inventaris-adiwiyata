<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

$id_pinjam = (int)$_GET['id_pinjam'];

if (delete_peminjaman($id_pinjam) > 0) {
    echo "<script> 
            alert('Data Peminjaman Berhasil Dihapus');
            document.location.href = 'index.php';
          </script>";
} else {
    echo "<script> 
            alert('Data Peminjaman Gagal Dihapus');
            document.location.href = 'index.php';
          </script>";
}
