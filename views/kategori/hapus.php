<?php

include_once '../../app/app.php';

$id_kategori = (int)$_GET['id_kategori'];

if (delete_kategori($id_kategori) > 0) {
    echo "<script> 
            alert('Data Kategori Berhasil Dihapus');
            document.location.href = 'index.php';
          </script>";
} else {
    echo "<script> 
            alert('Data Kategori Gagal Dihapus');
            document.location.href = 'index.php';
          </script>";
}
