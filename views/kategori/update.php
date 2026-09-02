<h1>update data</h1>
<?php 

include '../../layout/header.php';

$id_kategori = (int)$_GET['id_kategori'];

$kategori = select_kategori("SELECT * FROM kategori WHERE id_kategori = $id_kategori")[0];

//chek apakah tombol ubah ditekan
if (isset($_POST['ubah'])) {
    if (update_kategori($_POST) > 0) {
         echo "<script> 
                alert('Data Kategori Berhasil Diubahkan');
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
        <h1>Update Kategori</h1>
        <hr>

        <form action="" method="post">
            <input type="hidden" name="id_kategori" value="<?= $kategori['id_kategori']; ?>">
            <div class="mb-3">
                <label for="nama_kategori" class="form-label">Nama kategori</label>
                <input type="text" class="form-control" id="nama_kategori" name="nama" value="<?= $kategori['nama_kategori'] ?>" placeholder="Nama Kategori...">
            </div>

            <button type='submit' name='ubah' class='btn btn-primary' style="float: right;">Ubah</button>
        </form>
    </div>

<?php include '../../layout/footer.php'; ?>
    