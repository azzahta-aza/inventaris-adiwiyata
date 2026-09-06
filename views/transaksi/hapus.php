<?php

include_once '../../app/app.php';
check_role(['admin', 'petugas']);

$id_transaksi = (int)$_GET['id_transaksi'];

if (delete_transaksi($id_transaksi) > 0) {
    echo "<script> 
            alert('Data Transaksi Berhasil Dihapus dan Stok Telah Disesuaikan');
            document.location.href = 'index.php';
          </script>";
} else {
    echo "<script> 
            alert('Data Transaksi Gagal Dihapus');
            document.location.href = 'index.php';
          </script>";
}
