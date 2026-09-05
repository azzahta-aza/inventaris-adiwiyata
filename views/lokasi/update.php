<?php 

include '../../layout/header.php';

$id_lokasi = (int)$_GET['id_lokasi'];

$lokasi = select_lokasi("SELECT * FROM lokasi WHERE id_lokasi = $id_lokasi")[0];

//chek apakah tombol ubah ditekan
if (isset($_POST['ubah'])) {
    if (update_lokasi($_POST) > 0) {
         echo "<script> 
                alert('Data Lokasi Berhasil Diubahkan');
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
        <h1>Update Lokasi</h1>
        <hr>

        <form action="" method="post">
            <input type="hidden" name="id_lokasi" value="<?= $lokasi['id_lokasi']; ?>">
            <div class="mb-3">
                <label for="nama_lokasi" class="form-label">Nama lokasi</label>
                <input type="text" class="form-control" id="nama_lokasi" name="nama" value="<?= $lokasi['nama_lokasi'] ?>" placeholder="Nama Lokasi...">
            </div>

            <button type='submit' name='ubah' class='btn btn-primary' style="float: right;">Ubah</button>
        </form>
    </div>

<?php include '../../layout/footer.php'; ?>
    