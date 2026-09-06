<?php

include_once '../../app/app.php';
check_role('admin');

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

<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Lokasi</h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="nama_lokasi" class="form-label fw-semibold">Nama Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lokasi" name="nama" placeholder="Masukkan nama lokasi/ruangan..." required autofocus>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" name="tambah" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Lokasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../layout/footer.php'; ?>