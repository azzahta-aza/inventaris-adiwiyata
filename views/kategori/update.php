<?php 

include_once '../../app/app.php';
check_role('admin');

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

<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Update Kategori</h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="post">
                        <input type="hidden" name="id_kategori" value="<?= $kategori['id_kategori']; ?>">
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama" value="<?= htmlspecialchars($kategori['nama_kategori']); ?>" placeholder="Nama Kategori..." required autofocus>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" name="ubah" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>