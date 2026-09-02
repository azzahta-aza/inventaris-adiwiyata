<?php

include '../../layout/header.php';

//chek apakah tombol tambah ditekan
if (isset($_POST['tambah'])) {
    if (create_kategori($_POST) > 0) {
        echo "<script> 
                alert('Data Kategori Berhasil Ditambahkan');
                document.location.href = 'index.php';
                </script>";
    } else {
        echo "<script>
                alert('Data Kategori Gagal Ditambahkan');
                document.location.href = 'index.php';
                </script>";
    }
}

?>

<div class="container mt-5">
    <h1>Tambah Kategori</h1>
    <hr>

    <form action="" method="post">
        <div class="mb-3">
            <label for="nama_kategori" class="form-label">Nama Kategori</label>
            <input type="text" class="form-control" id="nama_kategori" name="nama" placeholder="Nama Kategori...">
        </div>

        <button type='submit' name='tambah' class='btn btn-primary' style="float: right;">Tambah</button>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>