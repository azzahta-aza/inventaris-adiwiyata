<?php

include_once '../../app/app.php';
check_role('admin');

$id_lokasi = (int)$_GET['id_lokasi'];

if (delete_lokasi($id_lokasi) > 0) {
    echo "<script> 
            alert('Data Lokasi Berhasil Dihapus');
            document.location.href = 'index.php';
          </script>";
} else {
    echo "<script> 
            alert('Data Lokasi Gagal Dihapus');
            document.location.href = 'index.php';
          </script>";
}
