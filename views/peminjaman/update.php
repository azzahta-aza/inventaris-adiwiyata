<?php

include_once '../../layout/header.php';

$id_pinjam = (int)$_GET['id_pinjam'];

$peminjaman_data = select_peminjaman("SELECT p.*, b.nama_barang, b.kode_barang 
                                      FROM peminjaman p 
                                      JOIN barang b ON p.id_barang = b.id_barang 
                                      WHERE p.id_pinjam = $id_pinjam");

if (empty($peminjaman_data)) {
    echo "<script>
            alert('Data peminjaman tidak ditemukan.');
            document.location.href = 'index.php';
          </script>";
    exit;
}

$pinjam = $peminjaman_data[0];

if (isset($_POST['ubah'])) {
    if (update_peminjaman($_POST) > 0) {
        echo "<script> 
                alert('Data peminjaman berhasil diperbarui.');
                document.location.href = 'index.php';
              </script>";
    } else {
        echo "<script> 
                alert('Data peminjaman tidak berubah atau gagal diperbarui.');
                document.location.href = 'index.php';
              </script>";
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h4 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Data Peminjaman</h4>
        </div>
        <div class="card-body">
            <form action="" method="post">
                <input type="hidden" name="id_pinjam" value="<?= $pinjam['id_pinjam']; ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Barang</label>
                        <input type="text" class="form-control" value="[<?= htmlspecialchars($pinjam['kode_barang']); ?>] <?= htmlspecialchars($pinjam['nama_barang']); ?>" disabled>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nama_peminjam" class="form-label fw-bold">Nama Peminjam</label>
                        <input type="text" class="form-control" id="nama_peminjam" name="nama_peminjam" value="<?= htmlspecialchars($pinjam['nama_peminjam']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_pinjam" class="form-label fw-bold">Tanggal Pinjam</label>
                        <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" value="<?= $pinjam['tanggal_pinjam']; ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jumlah Pinjam</label>
                        <input type="number" class="form-control" value="<?= $pinjam['jumlah']; ?>" disabled>
                        <div class="form-text">Jumlah pinjam tidak dapat diubah setelah stok dikurangi.</div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" name="ubah" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../layout/footer.php'; ?>
