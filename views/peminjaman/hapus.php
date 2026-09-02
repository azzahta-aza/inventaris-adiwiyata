<?php

include_once '../../app/app.php';

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
