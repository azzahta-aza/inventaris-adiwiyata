<?php

include '../../layout/header.php';

//chek apakah tombol tambah ditekan
if (isset($_POST['tambah'])) {
    if (create_lokasi($_POST) > 0) {
        echo "<script> 
                alert('Data Lokasi Berhasil Ditambahkan');
                document.location.href = 'index.php';
                </script>";
    } else {
        echo "<script>
                alert('Data Lokasi Gagal Ditambahkan');
                document.location.href = 'index.php';
                </script>";
    }
}

?>

<div class="container mt-5">
    <h1>Tambah Lokasi</h1>
    <hr>

    <form action="" method="post">
        <div class="mb-3">
            <label for="nama_lokasi" class="form-label">Nama Lokasi</label>
            <input type="text" class="form-control" id="nama_lokasi" name="nama" placeholder="Nama lokasi...">
        </div>

        <button type='submit' name='tambah' class='btn btn-primary' style="float: right;">Tambah</button>
    </form>
</div>

<?php include '../../layout/footer.php'; ?>